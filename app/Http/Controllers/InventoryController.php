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
        ]);

        $validated['material_code'] = 'MAT-' . strtoupper(substr(uniqid(), -5));
        // Strict procurement rule: Initial warehouse stock is 0 until supplier purchase orders are delivered
        $validated['stock_quantity'] = 0;

        $material = Material::create($validated);

        // Record Initial Catalog Registration in Inventory Log
        InventoryLog::create([
            'material_id' => $material->id,
            'project_id' => null,
            'transaction_type' => 'adjustment',
            'quantity' => 0,
            'unit_cost' => $validated['unit_cost'],
            'reference_no' => 'CATALOG-INIT',
            'notes' => 'New catalog item registered. Ready for trade supplier procurement.',
        ]);

        return redirect()->route('inventory.index')->with('success', 'New material "' . $material->name . '" registered into warehouse catalog. Stock will automatically increment upon supplier order deliveries.');
    }

    public function updateStock(Request $request, $id)
    {
        // Direct manual stock quantity overrides are locked because materials are strictly procured from external suppliers
        return redirect()->route('inventory.index')->with('error', 'Manual stock quantity overrides are locked. Material stocks are strictly replenished upon Supplier Purchase Order delivery receipts.');
    }
}
