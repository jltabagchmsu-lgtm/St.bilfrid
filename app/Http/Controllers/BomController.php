<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Material;
use App\Models\ProjectMaterial;
use App\Models\ProjectScopeItem;
use App\Models\ProjectScopeLine;
use App\Models\InventoryLog;
use App\Models\DailyMaterialUsage;
use App\Models\ProjectMaterialTransfer;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function index(Request $request)
    {
        $selectedProjectId = $request->query('project_id');
        $projects = Project::withCount('scopeItems')->orderBy('status', 'asc')->orderBy('title', 'asc')->get();
        
        // If no project selected, default to the first project with scope items, or the first project
        if (!$selectedProjectId && $projects->count() > 0) {
            $firstWithScope = $projects->firstWhere('scope_items_count', '>', 0);
            $selectedProjectId = $firstWithScope ? $firstWithScope->id : $projects->first()->id;
        }

        $selectedProject = null;
        if ($selectedProjectId) {
            $selectedProject = Project::with([
                'scopeItems' => function ($q) {
                    $q->orderBy('item_number', 'asc');
                },
                'scopeItems.lines',
                'scopeItems.materials',
                'scopeItems.labors',
                'scopeItems.equipments',
                'projectMaterials.material',
                'personnel',
                'inventoryLogs.material',
                'dailyMaterialUsages.material',
                'materialTransfersOut.material',
                'materialTransfersOut.destinationProject',
                'materialTransfersIn.material',
                'materialTransfersIn.sourceProject',
            ])->find($selectedProjectId);
        }

        $materialsCatalog = Material::orderBy('category')->orderBy('name')->get();

        // Build Consolidated Master "All Materials" Table for Selected Project
        $masterMaterialsList = collect();
        if ($selectedProject && $selectedProject->scopeItems) {
            $allScopeMaterials = $selectedProject->scopeItems->flatMap(function ($item) {
                return $item->materials->map(function ($line) use ($item) {
                    $line->scope_item_number = $item->item_number;
                    $line->scope_item_name = $item->item_name;
                    return $line;
                });
            });

            $grouped = $allScopeMaterials->groupBy(function ($line) {
                return strtolower(trim($line->description)) . '|' . strtolower(trim($line->unit));
            });

            $index = 1;
            foreach ($grouped as $key => $groupLines) {
                $first = $groupLines->first();
                $totalQty = (float) $groupLines->sum('quantity');
                $totalCost = (float) $groupLines->sum('total_cost');
                $unitPrice = $totalQty > 0 ? ($totalCost / $totalQty) : (float) $first->unit_price;
                
                $scopeItemsUsed = $groupLines->map(function ($l) {
                    return [
                        'item_number' => $l->scope_item_number,
                        'item_name' => $l->scope_item_name,
                        'line_qty' => $l->quantity,
                        'line_id' => $l->id,
                    ];
                })->values()->all();

                // Determine Material Category
                $category = $this->determineMaterialCategory($first->description);

                // Match with Central Warehouse Stock
                $matchingWarehouse = $materialsCatalog->first(function ($m) use ($first) {
                    return stripos($m->name, $first->description) !== false || stripos($first->description, $m->name) !== false;
                });

                // Match with Project Site Allocation
                $siteAllocation = $selectedProject->projectMaterials->first(function ($pm) use ($first) {
                    return stripos($pm->material->name ?? '', $first->description) !== false || stripos($first->description, $pm->material->name ?? '') !== false;
                });

                $masterMaterialsList->push((object)[
                    'index' => $index++,
                    'description' => $first->description,
                    'unit' => $first->unit,
                    'category' => $category,
                    'total_quantity' => $totalQty,
                    'unit_price' => $unitPrice,
                    'total_cost' => $totalCost,
                    'scope_items' => $scopeItemsUsed,
                    'scope_items_count' => count($scopeItemsUsed),
                    'warehouse_material_id' => $matchingWarehouse ? $matchingWarehouse->id : null,
                    'warehouse_stock' => $matchingWarehouse ? $matchingWarehouse->stock_quantity : null,
                    'warehouse_unit' => $matchingWarehouse ? $matchingWarehouse->unit : null,
                    'site_allocated_qty' => $siteAllocation ? $siteAllocation->allocated_qty : 0,
                    'site_used_qty' => $siteAllocation ? $siteAllocation->used_qty : 0,
                    'site_remaining_qty' => $siteAllocation ? $siteAllocation->remaining_qty : 0,
                    'sample_line_id' => $first->id,
                ]);
            }
        }

        // Scope DUPA Financial Totals
        $scopeMaterialsSubtotal = $selectedProject ? $selectedProject->total_scope_materials_cost : 0;
        $scopeLaborSubtotal = $selectedProject ? $selectedProject->total_scope_labor_cost : 0;
        $scopeEquipmentSubtotal = $selectedProject ? $selectedProject->total_scope_equipment_cost : 0;
        $scopeDirectCost = $selectedProject ? $selectedProject->total_scope_direct_cost : 0;
        $scopeGrandTotal = $selectedProject ? ($selectedProject->grand_scope_cost ?: $selectedProject->contract_budget) : 0;

        $masterMaterialsTotalCost = $masterMaterialsList->sum('total_cost');
        $masterMaterialsDistinctCount = $masterMaterialsList->count();
        $masterMaterialsTotalLineCount = $selectedProject && $selectedProject->scopeItems ? $selectedProject->scopeItems->sum(function ($item) {
            return $item->materials->count();
        }) : 0;

        // Warehouse site allocation queries
        $query = ProjectMaterial::with(['project', 'material', 'dailyUsages']);
        if ($selectedProjectId) {
            $query->where('project_id', $selectedProjectId);
        }
        $projectMaterials = $query->get();

        // Project specific or overall BOM site allocation metrics
        $totalAllocatedCost = $projectMaterials->sum(function ($pm) {
            return $pm->allocated_qty * $pm->unit_price;
        });

        $totalConsumedCost = $projectMaterials->sum(function ($pm) {
            return $pm->used_qty * $pm->unit_price;
        });

        $totalReturnedExcessValue = $projectMaterials->sum(function ($pm) {
            return $pm->returned_excess_value;
        });

        $netAllocatedCost = max(0, $totalAllocatedCost - $totalReturnedExcessValue);

        $totalRemainingValue = $projectMaterials->sum(function ($pm) {
            return $pm->remaining_qty * $pm->unit_price;
        });

        $consumptionRate = $netAllocatedCost > 0 ? round(($totalConsumedCost / $netAllocatedCost) * 100, 1) : 0;
        
        $lowRemainingCount = $projectMaterials->filter(function ($pm) {
            return $pm->remaining_qty <= ($pm->allocated_qty * 0.2) && $pm->remaining_qty > 0;
        })->count();

        $excessAvailableCount = $projectMaterials->filter(function ($pm) {
            return $pm->remaining_qty > 0;
        })->count();

        // Recent inventory logs (filtered if project selected)
        $returnsQuery = InventoryLog::with(['material', 'project'])
            ->whereIn('transaction_type', ['excess_return', 'inter_project_transfer', 'usage']);
        if ($selectedProjectId) {
            $returnsQuery->where('project_id', $selectedProjectId);
        }
        $recentReturns = $returnsQuery->orderBy('created_at', 'desc')->limit(15)->get();

        // Recent inter-project transfers (filtered if project selected)
        $transfersQuery = ProjectMaterialTransfer::with(['sourceProject', 'destinationProject', 'material']);
        if ($selectedProjectId) {
            $transfersQuery->where(function ($q) use ($selectedProjectId) {
                $q->where('source_project_id', $selectedProjectId)
                  ->orWhere('destination_project_id', $selectedProjectId);
            });
        }
        $recentTransfers = $transfersQuery->orderBy('transfer_date', 'desc')->orderBy('created_at', 'desc')->limit(15)->get();

        // Recent daily usages (filtered if project selected)
        $dailyUsagesQuery = DailyMaterialUsage::with(['project', 'material']);
        if ($selectedProjectId) {
            $dailyUsagesQuery->where('project_id', $selectedProjectId);
        }
        $recentDailyUsages = $dailyUsagesQuery->orderBy('usage_date', 'desc')->orderBy('created_at', 'desc')->limit(20)->get();

        return view('bom.index', compact(
            'projects',
            'projectMaterials',
            'materialsCatalog',
            'selectedProjectId',
            'selectedProject',
            'masterMaterialsList',
            'masterMaterialsTotalCost',
            'masterMaterialsDistinctCount',
            'masterMaterialsTotalLineCount',
            'scopeMaterialsSubtotal',
            'scopeLaborSubtotal',
            'scopeEquipmentSubtotal',
            'scopeDirectCost',
            'scopeGrandTotal',
            'totalAllocatedCost',
            'totalConsumedCost',
            'totalReturnedExcessValue',
            'netAllocatedCost',
            'totalRemainingValue',
            'consumptionRate',
            'lowRemainingCount',
            'excessAvailableCount',
            'recentReturns',
            'recentTransfers',
            'recentDailyUsages'
        ));
    }

    public function projectBom($id)
    {
        return redirect()->route('bom.index', ['project_id' => $id]);
    }

    /**
     * 1-Click Auto-Allocate Scope BOM Materials directly into Site Material Allocations Tracker
     */
    public function autoAllocateFromScope($id)
    {
        $project = Project::with(['scopeItems.materials'])->findOrFail($id);

        if ($project->scopeItems->count() === 0) {
            return redirect()->back()->with('error', 'This project does not have any Scope BOM items yet. Please load a template or add items first.');
        }

        $allMaterials = $project->scopeItems->flatMap->materials;
        $grouped = $allMaterials->groupBy(function ($line) {
            return strtolower(trim($line->description)) . '|' . strtolower(trim($line->unit));
        });

        $allocatedCount = 0;
        foreach ($grouped as $groupLines) {
            $first = $groupLines->first();
            $totalQty = (float) $groupLines->sum('quantity');
            $unitPrice = (float) $first->unit_price;

            // Find or create matching Material catalog item
            $cleanName = trim($first->description);
            $material = Material::where('name', $cleanName)->first();
            if (!$material) {
                $materialCode = 'MAT-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cleanName), 0, 4)) . '-' . rand(100, 999);
                $material = Material::create([
                    'material_code' => $materialCode,
                    'name' => $cleanName,
                    'category' => $this->determineMaterialCategory($cleanName),
                    'unit' => $first->unit,
                    'unit_cost' => $unitPrice,
                    'stock_quantity' => max(1000, (int) ceil($totalQty * 1.5)),
                ]);
            }

            // Create or update ProjectMaterial site allocation
            $pm = ProjectMaterial::where('project_id', $project->id)
                ->where('material_id', $material->id)
                ->first();

            if ($pm) {
                $pm->update([
                    'allocated_qty' => (int) ceil($totalQty),
                    'unit_price' => $unitPrice,
                ]);
            } else {
                $pm = ProjectMaterial::create([
                    'project_id' => $project->id,
                    'material_id' => $material->id,
                    'allocated_qty' => (int) ceil($totalQty),
                    'used_qty' => 0,
                    'excess_returned_qty' => 0,
                    'unit_price' => $unitPrice,
                ]);

                InventoryLog::create([
                    'material_id' => $material->id,
                    'project_id' => $project->id,
                    'transaction_type' => 'allocation',
                    'quantity' => (int) ceil($totalQty),
                    'unit_cost' => $unitPrice,
                    'reference_no' => 'BOM-SYNC-' . $pm->id,
                    'notes' => 'Auto-allocated from Scope BOM specification (' . number_format($totalQty) . ' ' . $first->unit . ').',
                ]);
            }

            $allocatedCount++;
        }

        return redirect()->route('bom.index', ['project_id' => $project->id])
            ->with('success', 'Successfully synchronized and allocated ' . $allocatedCount . ' materials from Scope BOM directly into Site Tracking!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'material_id' => 'nullable|exists:materials,id',
            'custom_material_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'allocated_qty' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $qty = (float) $validated['allocated_qty'];
        $unitPrice = (float) $validated['unit_price'];

        if (!empty($validated['material_id'])) {
            $material = Material::findOrFail($validated['material_id']);
        } else {
            $matName = trim($validated['custom_material_name'] ?? 'Custom Project Material');
            $material = Material::where('name', $matName)->first();
            if (!$material) {
                $code = 'MAT-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $matName), 0, 4)) . '-' . rand(100, 999);
                $material = Material::create([
                    'material_code' => $code,
                    'name' => $matName,
                    'category' => $validated['category'] ?? 'General',
                    'unit' => $validated['unit'] ?? 'pcs',
                    'unit_cost' => $unitPrice,
                    'stock_quantity' => (int) ceil($qty * 2),
                ]);
            }
        }

        // Check if warehouse stock is available; if low, top up warehouse inventory to fulfill allocation
        if ($material->stock_quantity < $qty) {
            $material->increment('stock_quantity', (int) ceil($qty * 2));
        }

        // Deduct from warehouse stock
        $material->decrement('stock_quantity', (int) ceil($qty));

        // Find or create ProjectMaterial site record
        $existing = ProjectMaterial::where('project_id', $project->id)
            ->where('material_id', $material->id)
            ->first();

        if ($existing) {
            $existing->increment('allocated_qty', $qty);
            $existing->update(['unit_price' => $unitPrice]);
            $bomItem = $existing;
        } else {
            $bomItem = ProjectMaterial::create([
                'project_id' => $project->id,
                'material_id' => $material->id,
                'allocated_qty' => $qty,
                'used_qty' => 0,
                'excess_returned_qty' => 0,
                'unit_price' => $unitPrice,
            ]);
        }

        // Record Inventory Log
        InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => $project->id,
            'transaction_type' => 'allocation',
            'quantity' => $qty,
            'unit_cost' => $unitPrice,
            'reference_no' => 'BOM-ALC-' . $bomItem->id,
            'notes' => 'Allocated ' . number_format($qty) . ' ' . $material->unit . ' to project ' . $project->project_code . '.',
        ]);

        return redirect()->back()->with('success', 'Successfully allocated ' . number_format($qty) . ' ' . $material->unit . ' of ' . $material->name . ' to project site BOM!');
    }

    public function recordDailyUsage(Request $request, $id)
    {
        $bomItem = ProjectMaterial::with(['material', 'project'])->findOrFail($id);

        $maxAvailable = $bomItem->remaining_qty;

        $validated = $request->validate([
            'usage_date' => 'required|date',
            'quantity_used' => 'required|numeric|min:0.01|max:' . $maxAvailable,
            'activity_description' => 'required|string|max:255',
        ]);

        $qtyUsed = (float) $validated['quantity_used'];

        // 1. Increment used_qty on ProjectMaterial
        $bomItem->increment('used_qty', $qtyUsed);

        // 2. Create DailyMaterialUsage record
        $usage = DailyMaterialUsage::create([
            'project_id' => $bomItem->project_id,
            'project_material_id' => $bomItem->id,
            'material_id' => $bomItem->material_id,
            'usage_date' => $validated['usage_date'],
            'quantity_used' => $qtyUsed,
            'activity_description' => $validated['activity_description'],
            'logged_by' => auth()->user()->name ?? 'Lead Site Engineer',
            'notes' => 'Daily site consumption recorded for activity: ' . $validated['activity_description'],
        ]);

        // 3. Record in Inventory Logs
        InventoryLog::create([
            'material_id' => $bomItem->material_id,
            'project_id' => $bomItem->project_id,
            'transaction_type' => 'usage',
            'quantity' => $qtyUsed,
            'unit_cost' => $bomItem->unit_price,
            'reference_no' => 'USE-' . date('Ymd') . '-' . $usage->id,
            'notes' => 'Daily consumption: ' . $validated['activity_description'] . ' (' . $qtyUsed . ' ' . $bomItem->material->unit . ')',
        ]);

        return redirect()->back()->with('success', 'Logged daily consumption of ' . number_format($qtyUsed, 2) . ' ' . $bomItem->material->unit . ' of ' . $bomItem->material->name . ' for "' . $validated['activity_description'] . '".');
    }

    public function transferToProject(Request $request, $id)
    {
        $bomItem = ProjectMaterial::with(['material', 'project'])->findOrFail($id);

        $maxTransferable = $bomItem->remaining_qty;

        $validated = $request->validate([
            'transfer_type' => 'required|in:inter_project,warehouse_stock',
            'destination_project_id' => 'nullable|required_if:transfer_type,inter_project|exists:projects,id',
            'transfer_date' => 'required|date',
            'transfer_qty' => 'required|numeric|min:0.01|max:' . $maxTransferable,
            'reason' => 'nullable|string|max:500',
        ]);

        $qtyToTransfer = (float) $validated['transfer_qty'];
        $material = $bomItem->material;
        $sourceProject = $bomItem->project;
        $transferRef = 'XFER-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        if ($validated['transfer_type'] === 'inter_project') {
            $destProject = Project::findOrFail($validated['destination_project_id']);

            if ($destProject->id === $sourceProject->id) {
                return redirect()->back()->with('error', 'Destination project must be different from source project!');
            }

            // Deduct from source project (increment excess returned)
            $bomItem->increment('excess_returned_qty', $qtyToTransfer);

            // Add/Allocate to destination project
            $destBomItem = ProjectMaterial::where('project_id', $destProject->id)
                ->where('material_id', $material->id)
                ->first();

            if ($destBomItem) {
                $destBomItem->increment('allocated_qty', $qtyToTransfer);
            } else {
                ProjectMaterial::create([
                    'project_id' => $destProject->id,
                    'material_id' => $material->id,
                    'allocated_qty' => $qtyToTransfer,
                    'used_qty' => 0,
                    'excess_returned_qty' => 0,
                    'unit_price' => $bomItem->unit_price,
                ]);
            }

            // Record Transfer Voucher
            ProjectMaterialTransfer::create([
                'transfer_reference_no' => $transferRef,
                'source_project_id' => $sourceProject->id,
                'destination_project_id' => $destProject->id,
                'material_id' => $material->id,
                'quantity_transferred' => $qtyToTransfer,
                'transfer_date' => $validated['transfer_date'],
                'reason' => $validated['reason'] ?? 'Surplus site materials transferred to active build site.',
                'authorized_by' => auth()->user()->name ?? 'Project Director',
            ]);

            InventoryLog::create([
                'material_id' => $material->id,
                'project_id' => $sourceProject->id,
                'transaction_type' => 'inter_project_transfer',
                'quantity' => $qtyToTransfer,
                'unit_cost' => $bomItem->unit_price,
                'reference_no' => $transferRef,
                'notes' => 'Transferred ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' from ' . $sourceProject->project_code . ' to ' . $destProject->project_code . '.',
            ]);

            return redirect()->back()->with('success', 'Successfully transferred ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' of ' . $material->name . ' from ' . $sourceProject->project_code . ' to ' . $destProject->project_code . '! (Ref: ' . $transferRef . ')');
        } else {
            // Stock back into Central Warehouse Inventory for future builds
            $bomItem->increment('excess_returned_qty', $qtyToTransfer);
            $material->increment('stock_quantity', $qtyToTransfer);

            ProjectMaterialTransfer::create([
                'transfer_reference_no' => $transferRef,
                'source_project_id' => $sourceProject->id,
                'destination_project_id' => null,
                'material_id' => $material->id,
                'quantity_transferred' => $qtyToTransfer,
                'transfer_date' => $validated['transfer_date'],
                'reason' => $validated['reason'] ?? 'Stocked back into central warehouse for future project pipeline.',
                'authorized_by' => auth()->user()->name ?? 'Project Director',
            ]);

            InventoryLog::create([
                'material_id' => $material->id,
                'project_id' => $sourceProject->id,
                'transaction_type' => 'excess_return',
                'quantity' => $qtyToTransfer,
                'unit_cost' => $bomItem->unit_price,
                'reference_no' => $transferRef,
                'notes' => 'Restocked ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' to central inventory for next project.',
            ]);

            return redirect()->back()->with('success', 'Successfully stocked ' . number_format($qtyToTransfer) . ' ' . $material->unit . ' of ' . $material->name . ' back into Central Warehouse Inventory for future projects! (Ref: ' . $transferRef . ')');
        }
    }

    public function updateUsage(Request $request, $id)
    {
        $bomItem = ProjectMaterial::findOrFail($id);

        $maxUsable = $bomItem->allocated_qty - $bomItem->excess_returned_qty;

        $validated = $request->validate([
            'used_qty' => 'required|integer|min:0|max:' . $maxUsable,
        ]);

        $prevUsed = $bomItem->used_qty;
        $bomItem->update($validated);

        $deltaUsed = $validated['used_qty'] - $prevUsed;
        if ($deltaUsed > 0) {
            InventoryLog::create([
                'material_id' => $bomItem->material_id,
                'project_id' => $bomItem->project_id,
                'transaction_type' => 'usage',
                'quantity' => $deltaUsed,
                'unit_cost' => $bomItem->unit_price,
                'reference_no' => 'BOM-USE-' . $bomItem->id,
                'notes' => 'Logged consumption of ' . $deltaUsed . ' ' . ($bomItem->material->unit ?? 'units') . ' on-site.',
            ]);
        }

        return redirect()->back()->with('success', 'Material usage recorded! Remaining on-site stock recalculated.');
    }

    public function returnExcessMaterial(Request $request, $id)
    {
        $bomItem = ProjectMaterial::with(['material', 'project'])->findOrFail($id);

        $maxAvailableForReturn = $bomItem->remaining_qty;

        $validated = $request->validate([
            'return_qty' => 'required|integer|min:1|max:' . $maxAvailableForReturn,
            'return_notes' => 'nullable|string|max:500',
        ]);

        $returnQty = $validated['return_qty'];

        // 1. Increment central warehouse stock
        $material = $bomItem->material;
        $material->increment('stock_quantity', $returnQty);

        // 2. Increment excess returned quantity on project material
        $bomItem->increment('excess_returned_qty', $returnQty);

        // 3. Create audit transaction in inventory log
        $returnRef = 'RET-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => $bomItem->project_id,
            'transaction_type' => 'excess_return',
            'quantity' => $returnQty,
            'unit_cost' => $bomItem->unit_price,
            'reference_no' => $returnRef,
            'notes' => $validated['return_notes'] ?? ('Returned ' . $returnQty . ' ' . $material->unit . ' of unused site excess back to central warehouse stock.'),
        ]);

        return redirect()->back()->with('success', 'Successfully returned ' . number_format($returnQty) . ' ' . $material->unit . ' of unused excess ' . $material->name . ' to central inventory warehouse! (Ref: ' . $returnRef . ')');
    }

    /**
     * Categorize material names into engineering work phases
     */
    protected function determineMaterialCategory(string $desc): string
    {
        $desc = strtolower($desc);
        if (preg_match('/(cement|sand|gravel|chb|concrete|block|grout|mortar|masonry)/', $desc)) {
            return 'Concrete & Masonry';
        }
        if (preg_match('/(deformed bar|rebar|tie wire|steel|angle bar|c-purlin|flange|beam)/', $desc)) {
            return 'Rebar & Structural Steel';
        }
        if (preg_match('/(coco lumber|lumber|phenolic|plywood|formwork|nails|string|timber)/', $desc)) {
            return 'Formworks & Lumber';
        }
        if (preg_match('/(roof|corrugated|rib-type|gutter|ridge|flash|insulation|tek screw)/', $desc)) {
            return 'Roofing & Metal Sheets';
        }
        if (preg_match('/(wire|cable|conduit|breaker|panel|outlet|switch|light|led|junction)/', $desc)) {
            return 'Electrical Works';
        }
        if (preg_match('/(pipe|pvc|elbow|tee|faucet|valve|drain|fitting|trap|water closet|lavatory)/', $desc)) {
            return 'Plumbing & Sanitary';
        }
        if (preg_match('/(tile|paint|primer|tinting|door|window|hinge|lockset|glass|sealant|grout)/', $desc)) {
            return 'Architectural & Finishes';
        }
        return 'General Building Materials';
    }
}
