<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Material;
use App\Models\ProjectMaterial;
use App\Models\ProjectMaterialTransfer;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoofingTransferController extends Controller
{
    /**
     * Roofing keywords and categories to strictly identify roofing materials.
     */
    protected array $roofingKeywords = [
        'roof', 'purlin', 'ridge', 'flashing', 'gutter', 'tekscrew', 
        'sealant', 'rib-type', 'c-purlin', 'insulation', 'metal sheet', 'long span'
    ];

    /**
     * Helper query to filter roofing materials.
     */
    protected function getRoofingMaterialsQuery()
    {
        return Material::where(function ($q) {
            $q->where('category', 'Roofing & Metal Sheets')
              ->orWhere('category', 'Roofing')
              ->orWhere(function ($sub) {
                  foreach ($this->roofingKeywords as $keyword) {
                      $sub->orWhere('name', 'like', "%{$keyword}%")
                          ->orWhere('material_code', 'like', "%ROOF%");
                  }
              });
        });
    }

    /**
     * Display the Roofing Material Transfer Station.
     */
    public function index(Request $request)
    {
        $selectedProjectId = $request->query('project_id');
        $search = $request->query('search');

        // Available warehouse roofing materials
        $materialsQuery = $this->getRoofingMaterialsQuery();
        if ($search) {
            $materialsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%");
            });
        }
        $roofingMaterials = $materialsQuery->orderBy('name')->get();
        $roofingMaterialIds = $roofingMaterials->pluck('id')->toArray();

        // Active construction projects
        $projects = Project::orderBy('status', 'asc')->orderBy('title', 'asc')->get();
        $selectedProject = $selectedProjectId ? Project::find($selectedProjectId) : null;

        // Active project site allocations for roofing
        $siteBOMQuery = ProjectMaterial::with(['project', 'material'])
            ->whereIn('material_id', $roofingMaterialIds);
        
        if ($selectedProjectId) {
            $siteBOMQuery->where('project_id', $selectedProjectId);
        }
        $projectRoofingMaterials = $siteBOMQuery->get();

        // Recent Roofing Transfers
        $transfersQuery = ProjectMaterialTransfer::with(['sourceProject', 'destinationProject', 'material'])
            ->where(function ($q) use ($roofingMaterialIds) {
                $q->whereIn('material_id', $roofingMaterialIds)
                  ->orWhere('transfer_reference_no', 'like', 'XFER-ROOF-%');
            });

        if ($selectedProjectId) {
            $transfersQuery->where(function ($q) use ($selectedProjectId) {
                $q->where('source_project_id', $selectedProjectId)
                  ->orWhere('destination_project_id', $selectedProjectId);
            });
        }

        $recentTransfers = $transfersQuery->orderBy('transfer_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        // Roofing Stats
        $totalWarehouseStockUnits = $roofingMaterials->sum('stock_quantity');
        $totalWarehouseValuation = $roofingMaterials->sum(function ($m) {
            return $m->stock_quantity * $m->unit_cost;
        });
        $totalAllocatedUnits = $projectRoofingMaterials->sum('allocated_qty');
        $totalUsedUnits = $projectRoofingMaterials->sum('used_qty');
        $totalRemainingOnSite = $projectRoofingMaterials->sum(function ($pm) {
            return $pm->remaining_qty;
        });

        return view('transfer_portal.roofing.index', compact(
            'roofingMaterials',
            'projects',
            'selectedProject',
            'selectedProjectId',
            'projectRoofingMaterials',
            'recentTransfers',
            'search',
            'totalWarehouseStockUnits',
            'totalWarehouseValuation',
            'totalAllocatedUnits',
            'totalUsedUnits',
            'totalRemainingOnSite'
        ));
    }

    /**
     * Dispatch roofing materials directly from Central Warehouse stock to a project site.
     */
    public function dispatchFromStock(Request $request)
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Administrator accounts have View-Only auditing permissions for trade transfer portals. Changes must be executed by the Roofing Transfer Officer.');
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'destination_project_id' => 'required|exists:projects,id',
            'transfer_qty' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $material = Material::findOrFail($validated['material_id']);
        $destProject = Project::findOrFail($validated['destination_project_id']);
        $qtyToTransfer = (float) $validated['transfer_qty'];

        if ($material->stock_quantity < $qtyToTransfer) {
            return redirect()->back()->with('error', "Insufficient warehouse stock! Available: " . number_format($material->stock_quantity) . " {$material->unit}, Requested: " . number_format($qtyToTransfer) . " {$material->unit}.");
        }

        // 1. Deduct from Central Warehouse Stock
        $material->decrement('stock_quantity', $qtyToTransfer);

        // 2. Allocate to Project BOM
        $projectMaterial = ProjectMaterial::where('project_id', $destProject->id)
            ->where('material_id', $material->id)
            ->first();

        if ($projectMaterial) {
            $projectMaterial->increment('allocated_qty', $qtyToTransfer);
        } else {
            ProjectMaterial::create([
                'project_id' => $destProject->id,
                'material_id' => $material->id,
                'allocated_qty' => $qtyToTransfer,
                'used_qty' => 0,
                'excess_returned_qty' => 0,
                'unit_price' => $material->unit_cost,
            ]);
        }

        // 3. Create Transfer Voucher Record
        $transferRef = 'XFER-ROOF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $transfer = ProjectMaterialTransfer::create([
            'transfer_reference_no' => $transferRef,
            'source_project_id' => $destProject->id, // Source is warehouse dispatch to this project
            'destination_project_id' => $destProject->id,
            'material_id' => $material->id,
            'quantity_transferred' => $qtyToTransfer,
            'transfer_date' => $validated['transfer_date'],
            'transfer_type' => 'warehouse_dispatch',
            'reason' => $validated['reason'] ?? 'Roofing materials dispatched from central warehouse to active build site.',
            'authorized_by' => Auth::user()->name ?? 'Roofing Transfer Officer',
        ]);

        // 4. Create Inventory Log
        InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => $destProject->id,
            'transaction_type' => 'allocation',
            'quantity' => $qtyToTransfer,
            'unit_cost' => $material->unit_cost,
            'reference_no' => $transferRef,
            'notes' => 'Roofing dispatch: ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' of ' . $material->name . ' to ' . $destProject->project_code . ' (' . $destProject->title . ').',
        ]);

        return redirect()->back()->with('success', 'Successfully dispatched ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' of ' . $material->name . ' to ' . $destProject->project_code . '! (Voucher Ref: ' . $transferRef . ')');
    }

    /**
     * Transfer surplus roofing materials between two active projects.
     */
    public function transferInterProject(Request $request)
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Administrator accounts have View-Only auditing permissions for trade transfer portals. Changes must be executed by the Roofing Transfer Officer.');
        }

        $validated = $request->validate([
            'source_project_id' => 'required|exists:projects,id',
            'destination_project_id' => 'required|exists:projects,id|different:source_project_id',
            'material_id' => 'required|exists:materials,id',
            'transfer_qty' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $sourceProject = Project::findOrFail($validated['source_project_id']);
        $destProject = Project::findOrFail($validated['destination_project_id']);
        $material = Material::findOrFail($validated['material_id']);
        $qtyToTransfer = (float) $validated['transfer_qty'];

        $sourceBom = ProjectMaterial::where('project_id', $sourceProject->id)
            ->where('material_id', $material->id)
            ->first();

        if (!$sourceBom || $sourceBom->remaining_qty < $qtyToTransfer) {
            $available = $sourceBom ? $sourceBom->remaining_qty : 0;
            return redirect()->back()->with('error', "Insufficient remaining balance on source project! Available: " . number_format($available, 2) . " {$material->unit}, Requested: " . number_format($qtyToTransfer, 2) . " {$material->unit}.");
        }

        // Deduct from source project (increment excess returned)
        $sourceBom->increment('excess_returned_qty', $qtyToTransfer);

        // Allocate to destination project
        $destBom = ProjectMaterial::where('project_id', $destProject->id)
            ->where('material_id', $material->id)
            ->first();

        if ($destBom) {
            $destBom->increment('allocated_qty', $qtyToTransfer);
        } else {
            ProjectMaterial::create([
                'project_id' => $destProject->id,
                'material_id' => $material->id,
                'allocated_qty' => $qtyToTransfer,
                'used_qty' => 0,
                'excess_returned_qty' => 0,
                'unit_price' => $sourceBom->unit_price,
            ]);
        }

        $transferRef = 'XFER-ROOF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        ProjectMaterialTransfer::create([
            'transfer_reference_no' => $transferRef,
            'source_project_id' => $sourceProject->id,
            'destination_project_id' => $destProject->id,
            'material_id' => $material->id,
            'quantity_transferred' => $qtyToTransfer,
            'transfer_date' => $validated['transfer_date'],
            'transfer_type' => 'inter_project',
            'reason' => $validated['reason'] ?? 'Inter-project roofing material balance transfer.',
            'authorized_by' => Auth::user()->name ?? 'Roofing Transfer Officer',
        ]);

        InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => $sourceProject->id,
            'transaction_type' => 'inter_project_transfer',
            'quantity' => $qtyToTransfer,
            'unit_cost' => $sourceBom->unit_price,
            'reference_no' => $transferRef,
            'notes' => 'Transferred ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' of ' . $material->name . ' from ' . $sourceProject->project_code . ' to ' . $destProject->project_code . '.',
        ]);

        return redirect()->back()->with('success', 'Successfully transferred ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' of ' . $material->name . ' from ' . $sourceProject->project_code . ' to ' . $destProject->project_code . '! (Ref: ' . $transferRef . ')');
    }

    /**
     * Return unused site roofing materials back into Central Warehouse stock.
     */
    public function returnExcessToStock(Request $request)
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Administrator accounts have View-Only auditing permissions for trade transfer portals. Changes must be executed by the Roofing Transfer Officer.');
        }

        $validated = $request->validate([
            'project_material_id' => 'required|exists:project_materials,id',
            'return_qty' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $projectMaterial = ProjectMaterial::with(['project', 'material'])->findOrFail($validated['project_material_id']);
        $qtyToReturn = (float) $validated['return_qty'];

        if ($projectMaterial->remaining_qty < $qtyToReturn) {
            return redirect()->back()->with('error', 'Return quantity cannot exceed remaining site balance (' . number_format($projectMaterial->remaining_qty, 2) . ' ' . $projectMaterial->material->unit . ')!');
        }

        $projectMaterial->increment('excess_returned_qty', $qtyToReturn);
        $projectMaterial->material->increment('stock_quantity', $qtyToReturn);

        $transferRef = 'RET-ROOF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        ProjectMaterialTransfer::create([
            'transfer_reference_no' => $transferRef,
            'source_project_id' => $projectMaterial->project_id,
            'destination_project_id' => null,
            'material_id' => $projectMaterial->material_id,
            'quantity_transferred' => $qtyToReturn,
            'transfer_date' => $validated['transfer_date'],
            'transfer_type' => 'warehouse_stock',
            'reason' => $validated['reason'] ?? 'Surplus roofing materials reclaimed back into central warehouse inventory.',
            'authorized_by' => Auth::user()->name ?? 'Roofing Transfer Officer',
        ]);

        InventoryLog::create([
            'material_id' => $projectMaterial->material_id,
            'project_id' => $projectMaterial->project_id,
            'transaction_type' => 'excess_return',
            'quantity' => $qtyToReturn,
            'unit_cost' => $projectMaterial->unit_price,
            'reference_no' => $transferRef,
            'notes' => 'Returned ' . number_format($qtyToReturn) . ' ' . $projectMaterial->material->unit . ' of ' . $projectMaterial->material->name . ' from ' . $projectMaterial->project->project_code . ' to central warehouse stock.',
        ]);

        return redirect()->back()->with('success', 'Successfully returned ' . number_format($qtyToReturn) . ' ' . $projectMaterial->material->unit . ' of ' . $projectMaterial->material->name . ' back into Central Warehouse Inventory! (Ref: ' . $transferRef . ')');
    }

    /**
     * Restock roofing materials into Central Warehouse stock (Low Stock replenishment).
     */
    public function restockStock(Request $request)
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'Administrator accounts have View-Only auditing permissions for trade transfer portals. Changes must be executed by the Roofing Transfer Officer.');
        }

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'restock_qty' => 'required|numeric|min:0.01',
            'supplier_name' => 'nullable|string|max:255',
            'unit_cost' => 'nullable|numeric|min:0',
            'delivery_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $material = Material::findOrFail($validated['material_id']);
        $restockQty = (float) $validated['restock_qty'];

        if (isset($validated['unit_cost']) && $validated['unit_cost'] > 0) {
            $material->unit_cost = (float) $validated['unit_cost'];
        }

        $material->increment('stock_quantity', $restockQty);

        $refNo = 'PO-ROOF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => null,
            'transaction_type' => 'restock',
            'quantity' => $restockQty,
            'unit_cost' => $material->unit_cost,
            'reference_no' => $refNo,
            'notes' => 'Roofing Restock: +' . number_format($restockQty) . ' ' . $material->unit . ' of ' . $material->name . ($validated['supplier_name'] ? ' (Supplier: ' . $validated['supplier_name'] . ')' : '') . ($validated['notes'] ? ' - ' . $validated['notes'] : ''),
        ]);

        return redirect()->back()->with('success', 'Successfully restocked +' . number_format($restockQty) . ' ' . $material->unit . ' of ' . $material->name . ' into Central Warehouse! New Available Stock: ' . number_format($material->stock_quantity) . ' ' . $material->unit . ' (Ref: ' . $refNo . ')');
    }

    /**
     * Print official Material Transfer Voucher / Delivery Slip.
     */
    public function printTransferVoucher($id)
    {
        $transfer = ProjectMaterialTransfer::with(['sourceProject', 'destinationProject', 'material'])->findOrFail($id);
        $departmentName = 'Roofing & Metal Sheets Division';
        $departmentOfficer = 'Roofing Materials Transfer Officer';

        return view('transfer_portal.voucher', compact('transfer', 'departmentName', 'departmentOfficer'));
    }
}
