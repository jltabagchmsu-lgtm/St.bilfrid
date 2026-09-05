<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\InventoryLog;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategory = $request->query('category');
        $search = $request->query('search');

        $query = Material::query();

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('category')->orderBy('name')->get();
        $allCategories = Material::select('category')->distinct()->pluck('category');

        $totalItemsCount = Material::count();
        $totalStockUnits = Material::sum('stock_quantity');
        $totalValuation = Material::all()->sum(function ($m) {
            return $m->stock_quantity * $m->unit_cost;
        });
        $lowStockCount = Material::where('stock_quantity', '<=', 500)->count();

        // Inventory movements & project excess return transaction logs
        $inventoryLogs = InventoryLog::with(['material', 'project'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $totalReturnedUnits = InventoryLog::where('transaction_type', 'excess_return')->sum('quantity');

        return view('inventory.index', compact(
            'materials',
            'allCategories',
            'selectedCategory',
            'search',
            'totalItemsCount',
            'totalStockUnits',
            'totalValuation',
            'lowStockCount',
            'inventoryLogs',
            'totalReturnedUnits'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $validated['material_code'] = 'MAT-' . strtoupper(substr(uniqid(), -5));

        $material = Material::create($validated);

        // Record Initial Stock in Inventory Log
        InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => null,
            'transaction_type' => 'restock',
            'quantity' => $validated['stock_quantity'],
            'unit_cost' => $validated['unit_cost'],
            'reference_no' => 'INIT-STOCK',
            'notes' => 'Initial warehouse catalog stock setup.',
        ]);

        return redirect()->route('inventory.index')->with('success', 'New material item added to warehouse inventory!');
    }

    public function updateStock(Request $request, $id)
    {
        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        $delta = $validated['stock_quantity'] - $material->stock_quantity;

        $material->update($validated);

        if ($delta != 0) {
            InventoryLog::create([
                'material_id' => $material->id,
                'project_id' => null,
                'transaction_type' => $delta > 0 ? 'restock' : 'adjustment',
                'quantity' => abs($delta),
                'unit_cost' => $validated['unit_cost'],
                'reference_no' => 'ADJ-' . date('Ymd'),
                'notes' => 'Manual warehouse stock adjustment (' . ($delta > 0 ? '+' : '-') . abs($delta) . ' ' . $material->unit . ').',
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Warehouse material inventory stock and pricing updated successfully!');
    }
}
