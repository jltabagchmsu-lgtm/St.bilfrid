<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectScopeItem;
use App\Models\ProjectScopeLine;
use App\Models\Material;
use Illuminate\Http\Request;

class ProjectScopeController extends Controller
{
    /**
     * Add a new Scope of Work item.
     */
    public function storeScopeItem(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'item_number' => 'required|integer|min:1',
            'item_name' => 'required|string|max:255',
            'volume_or_area' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'contingency_percent' => 'nullable|numeric|min:0|max:100',
            'taxes_percent' => 'nullable|numeric|min:0|max:100',
            'profit_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['project_id'] = $project->id;
        $validated['contingency_percent'] = $validated['contingency_percent'] ?? 0.00;
        $validated['taxes_percent'] = $validated['taxes_percent'] ?? 0.00;
        $validated['profit_percent'] = $validated['profit_percent'] ?? 0.00;

        $item = ProjectScopeItem::create($validated);
        $item->recalculate();

        return redirect()->back()->with('success', 'Scope Item ' . $item->item_number . ' (' . $item->item_name . ') created successfully!');
    }

    /**
     * Add an itemized Line entry under a Scope Item.
     */
    public function storeScopeLine(Request $request, $projectId, $itemId)
    {
        $project = Project::findOrFail($projectId);
        $item = ProjectScopeItem::where('project_id', $project->id)->findOrFail($itemId);

        $validated = $request->validate([
            'category' => 'required|string|in:material,labor,equipment',
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'material_id' => 'nullable|exists:materials,id',
        ]);

        $validated['project_scope_item_id'] = $item->id;
        $validated['total_cost'] = round($validated['quantity'] * $validated['unit_price'], 2);

        ProjectScopeLine::create($validated);
        $item->recalculate();

        return redirect()->back()->with('success', 'Line item "' . $validated['description'] . '" added to Item ' . $item->item_number . '!');
    }

    /**
     * Update Scope Item headers and markups.
     */
    public function updateScopeItem(Request $request, $itemId)
    {
        $item = ProjectScopeItem::findOrFail($itemId);

        $validated = $request->validate([
            'item_number' => 'required|integer|min:1',
            'item_name' => 'required|string|max:255',
            'volume_or_area' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'contingency_percent' => 'nullable|numeric|min:0|max:100',
            'taxes_percent' => 'nullable|numeric|min:0|max:100',
            'profit_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $item->update($validated);
        $item->recalculate();

        return redirect()->back()->with('success', 'Scope Item ' . $item->item_number . ' updated successfully!');
    }

    /**
     * Delete a Scope Item.
     */
    public function destroyScopeItem($itemId)
    {
        $item = ProjectScopeItem::findOrFail($itemId);
        $itemNumber = $item->item_number;
        $item->delete();

        return redirect()->back()->with('success', 'Scope Item ' . $itemNumber . ' deleted.');
    }

    /**
     * Delete a specific line entry.
     */
    public function destroyScopeLine($lineId)
    {
        $line = ProjectScopeLine::findOrFail($lineId);
        $item = $line->scopeItem;
        $desc = $line->description;
        $line->delete();
        $item->recalculate();

        return redirect()->back()->with('success', 'Line item "' . $desc . '" removed.');
    }

    /**
     * Update an existing line item (material, labor, equipment).
     */
    public function updateScopeLine(Request $request, $lineId)
    {
        $line = ProjectScopeLine::findOrFail($lineId);
        $item = $line->scopeItem;

        $validated = $request->validate([
            'category' => 'required|string|in:material,labor,equipment',
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $validated['total_cost'] = round($validated['quantity'] * $validated['unit_price'], 2);

        $line->update($validated);
        $item->recalculate();

        return redirect()->back()->with('success', 'Line item "' . $line->description . '" updated successfully!');
    }

    /**
     * Helper to load items & lines from template definition.
     */
    protected function applyTemplate(Project $project, array $template): float
    {
        $project->scopeItems()->delete();

        foreach ($template as $itemData) {
            $scopeItem = ProjectScopeItem::create([
                'project_id' => $project->id,
                'item_number' => $itemData['item_number'],
                'item_name' => $itemData['item_name'],
                'volume_or_area' => $itemData['volume_or_area'] ?? null,
                'notes' => $itemData['notes'] ?? null,
                'contingency_percent' => $itemData['contingency_percent'] ?? 0.00,
                'contingency_amount' => $itemData['contingency_amount'] ?? 0.00,
                'taxes_percent' => $itemData['taxes_percent'] ?? 0.00,
                'taxes_amount' => $itemData['taxes_amount'] ?? 0.00,
                'profit_percent' => $itemData['profit_percent'] ?? 0.00,
                'profit_amount' => $itemData['profit_amount'] ?? 0.00,
            ]);

            // Insert Materials
            if (!empty($itemData['materials'])) {
                foreach ($itemData['materials'] as $m) {
                    ProjectScopeLine::create([
                        'project_scope_item_id' => $scopeItem->id,
                        'category' => 'material',
                        'description' => $m['description'],
                        'quantity' => $m['quantity'],
                        'unit' => $m['unit'],
                        'unit_price' => $m['unit_price'],
                        'total_cost' => round($m['quantity'] * $m['unit_price'], 2),
                    ]);
                }
            }

            // Insert Labors
            if (!empty($itemData['labors'])) {
                foreach ($itemData['labors'] as $l) {
                    ProjectScopeLine::create([
                        'project_scope_item_id' => $scopeItem->id,
                        'category' => 'labor',
                        'description' => $l['description'],
                        'quantity' => $l['quantity'],
                        'unit' => $l['unit'],
                        'unit_price' => $l['unit_price'],
                        'total_cost' => round($l['quantity'] * $l['unit_price'], 2),
                    ]);
                }
            }

            // Insert Equipment / Contingencies
            if (!empty($itemData['equipments'])) {
                foreach ($itemData['equipments'] as $e) {
                    ProjectScopeLine::create([
                        'project_scope_item_id' => $scopeItem->id,
                        'category' => 'equipment',
                        'description' => $e['description'],
                        'quantity' => $e['quantity'],
                        'unit' => $e['unit'],
                        'unit_price' => $e['unit_price'],
                        'total_cost' => round($e['quantity'] * $e['unit_price'], 2),
                    ]);
                }
            }

            $scopeItem->recalculate();
        }

        $grandTotal = $project->grand_scope_cost;
        if ($grandTotal > 0) {
            $project->contract_budget = $grandTotal;
            $project->approved_loan_amount = round($grandTotal * 0.80, 2);
            $project->client_equity_amount = round($grandTotal * 0.20, 2);
            $project->save();
        }

        return $grandTotal;
    }

    /**
     * 1-Click Load 3 Bedroom Bungalow Single Detached Residential Unit (₱1,778,062.08) - PDF 1.
     */
    public function load3BrBungalowTemplate($projectId)
    {
        $project = Project::findOrFail($projectId);

        $template = [
            [
                'item_number' => 1,
                'item_name' => 'Foundation and Footings',
                'volume_or_area' => 'V=9.4m³',
                'notes' => 'Layout, excavation, footings rebar, formwork and concrete pour',
                'materials' => [
                    ['quantity' => 25, 'unit' => 'm³', 'description' => 'Layout & Excavation', 'unit_price' => 600.00],
                    ['quantity' => 19, 'unit' => 'pcs', 'description' => '12 mm Deformed Bar', 'unit_price' => 310.00],
                    ['quantity' => 33, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 12, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 44, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 6, 'unit' => 'm³', 'description' => '3/4 Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 8, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Masonry Pail', 'unit_price' => 70.00],
                    ['quantity' => 10, 'unit' => 'pcs', 'description' => '2"x3"x10ft Coco Lumber', 'unit_price' => 140.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => '2"x2"x10ft Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 5, 'unit' => 'kgs', 'description' => '4" Common Nails', 'unit_price' => 70.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Phenolic Board', 'unit_price' => 1300.00],
                    ['quantity' => 5, 'unit' => 'roll', 'description' => '#100 Nylon String', 'unit_price' => 25.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Foundation & Footings Labor (45%)', 'unit_price' => 25301.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 8433.75],
                ],
            ],
            [
                'item_number' => 2,
                'item_name' => 'Columns',
                'volume_or_area' => 'V=4.67m³',
                'notes' => 'Column rebar cage, phenolic formworks, and concrete pour',
                'materials' => [
                    ['quantity' => 13, 'unit' => 'pcs', 'description' => '16 mm Deformed Bar', 'unit_price' => 450.00],
                    ['quantity' => 19, 'unit' => 'pcs', 'description' => '12 mm Deformed Bar', 'unit_price' => 310.00],
                    ['quantity' => 21, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 31, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 22, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 2, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => 'Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 12, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 77, 'unit' => 'pcs', 'description' => '2"x2"x8\' Coco Lumber', 'unit_price' => 90.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => 'Phenolic Board', 'unit_price' => 1300.00],
                    ['quantity' => 5, 'unit' => 'kgs', 'description' => '2.5" Common Nails', 'unit_price' => 80.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Masonry Pail', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Columns Labor (45%)', 'unit_price' => 22401.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 7467.00],
                ],
            ],
            [
                'item_number' => 3,
                'item_name' => 'Beams',
                'volume_or_area' => 'V=6.13m³',
                'notes' => 'Roof beams and tie beams framing and concrete pour',
                'materials' => [
                    ['quantity' => 30, 'unit' => 'pcs', 'description' => '16 mm Deformed Bar', 'unit_price' => 450.00],
                    ['quantity' => 17, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 57, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 29, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 2, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 4, 'unit' => 'm³', 'description' => 'Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 17, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 40, 'unit' => 'pcs', 'description' => '2"x2"x8\' Coco Lumber', 'unit_price' => 90.00],
                    ['quantity' => 9, 'unit' => 'pcs', 'description' => 'Phenolic Board', 'unit_price' => 1300.00],
                    ['quantity' => 5, 'unit' => 'kgs', 'description' => '2.5" Common Nail', 'unit_price' => 80.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Masonry Pail', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Beams Labor (45%)', 'unit_price' => 24822.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 8274.00],
                ],
            ],
            [
                'item_number' => 4,
                'item_name' => 'Slab on Fill',
                'volume_or_area' => 'V=9.97m³',
                'notes' => 'Earth backfill, gravel bedding, wire mesh & slab topping',
                'materials' => [
                    ['quantity' => 33, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 45, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 5, 'unit' => 'm³', 'description' => 'Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 17, 'unit' => 'm³', 'description' => 'Backfill', 'unit_price' => 600.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Slab on Fill Labor (45%)', 'unit_price' => 15324.75],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 5108.25],
                ],
            ],
            [
                'item_number' => 5,
                'item_name' => 'Suspended Slab (for two storey only)',
                'volume_or_area' => 'N/A (Single Storey Bungalow)',
                'notes' => 'Not applicable for single-detached bungalow unit',
                'materials' => [],
                'labors' => [],
                'equipments' => [],
            ],
            [
                'item_number' => 6,
                'item_name' => 'Exterior Walls',
                'volume_or_area' => '4" CHB Walls',
                'notes' => 'Perimeter concrete hollow block masonry and plastering',
                'materials' => [
                    ['quantity' => 59, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 51, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 5, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 957, 'unit' => 'pcs', 'description' => '4" CHB', 'unit_price' => 13.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => 'Bamboo', 'unit_price' => 200.00],
                    ['quantity' => 13, 'unit' => 'pcs', 'description' => '2"x2"x10ft Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => 'Scaffolding Steel', 'unit_price' => 1300.00],
                    ['quantity' => 5, 'unit' => 'kgs', 'description' => 'Assorted Common Nail', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Exterior Walls Labor (45%)', 'unit_price' => 22979.70],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 7659.90],
                ],
            ],
            [
                'item_number' => 7,
                'item_name' => 'Interior Walls with Partitions',
                'volume_or_area' => '4" & 6" CHB Partitions',
                'notes' => 'Dividing interior masonry walls and plastering',
                'materials' => [
                    ['quantity' => 75, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 78, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 7, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 1098, 'unit' => 'pcs', 'description' => '4" CHB', 'unit_price' => 13.00],
                    ['quantity' => 438, 'unit' => 'pcs', 'description' => '6" CHB', 'unit_price' => 16.00],
                    ['quantity' => 3, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'Bamboo', 'unit_price' => 200.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => '2"x2"x10ft Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'Scaffolding Steel', 'unit_price' => 1300.00],
                    ['quantity' => 4, 'unit' => 'kgs', 'description' => 'Assorted Common Nail', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Interior Walls Labor (45%)', 'unit_price' => 30877.65],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 10292.55],
                ],
            ],
            [
                'item_number' => 8,
                'item_name' => 'Roofing',
                'volume_or_area' => '155 ln.m. Pre-painted Long Span',
                'notes' => 'C-Purlins framing, long span sheets, gutters, and flashing',
                'materials' => [
                    ['quantity' => 33, 'unit' => 'pcs', 'description' => 'GA. 20 (2x4) C-Purlins', 'unit_price' => 600.00],
                    ['quantity' => 33, 'unit' => 'pcs', 'description' => 'GA. 20 (2x3) C-Purlins', 'unit_price' => 480.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Hacksaw Blade', 'unit_price' => 50.00],
                    ['quantity' => 3, 'unit' => 'box', 'description' => 'Tekscrew', 'unit_price' => 510.00],
                    ['quantity' => 25, 'unit' => 'kgs', 'description' => 'Welding rod', 'unit_price' => 120.00],
                    ['quantity' => 155, 'unit' => 'ln.m.', 'description' => 'Rib-Type pre-painted long span roofing', 'unit_price' => 410.00],
                    ['quantity' => 9, 'unit' => 'pcs', 'description' => 'False Gutter', 'unit_price' => 450.00],
                    ['quantity' => 9, 'unit' => 'pcs', 'description' => 'End Flashing', 'unit_price' => 585.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Roof Sealant', 'unit_price' => 210.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => '4" Disc for Grinder', 'unit_price' => 335.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '1/8x1x1 Angle Bars', 'unit_price' => 450.00],
                    ['quantity' => 1, 'unit' => 'box', 'description' => '1/8"x1/2" Blind Rivets', 'unit_price' => 280.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Roofing Installation Labor (45%)', 'unit_price' => 53039.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 17679.75],
                ],
            ],
            [
                'item_number' => 9,
                'item_name' => 'Ceiling',
                'volume_or_area' => 'Ficem Board & Metal Furring',
                'notes' => 'Suspension system, angle bars, and hardiflex fascia board',
                'materials' => [
                    ['quantity' => 32, 'unit' => 'pcs', 'description' => '4.5mm Ficem Board', 'unit_price' => 450.00],
                    ['quantity' => 12, 'unit' => 'pcs', 'description' => '1/8x1x1 Angle Bars', 'unit_price' => 450.00],
                    ['quantity' => 9, 'unit' => 'pcs', 'description' => 'Carrying Channel', 'unit_price' => 120.00],
                    ['quantity' => 30, 'unit' => 'pcs', 'description' => 'Metal Furring', 'unit_price' => 120.00],
                    ['quantity' => 3, 'unit' => 'box', 'description' => '1/8"x1/2" Blind Rivets', 'unit_price' => 280.00],
                    ['quantity' => 3, 'unit' => 'kgs', 'description' => 'Assorted Common Nails', 'unit_price' => 70.00],
                    ['quantity' => 27, 'unit' => 'pcs', 'description' => '10 mm Suspension Rods', 'unit_price' => 65.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'Hardiflex for fascia Board', 'unit_price' => 1600.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Ceiling Labor (45%)', 'unit_price' => 14438.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 4812.75],
                ],
            ],
            [
                'item_number' => 10,
                'item_name' => 'Floor Finishes',
                'volume_or_area' => '0.5x0.5 & 0.3x0.3 Tiles',
                'notes' => 'Granite & ceramic tiles with cement screed',
                'materials' => [
                    ['quantity' => 25, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 60, 'unit' => 'pcs', 'description' => '0.3x0.3 Tiles', 'unit_price' => 45.00],
                    ['quantity' => 194, 'unit' => 'pcs', 'description' => '0.5x0.5 Tiles', 'unit_price' => 75.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Floor Tile Setting Labor (45%)', 'unit_price' => 11441.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 3813.75],
                ],
            ],
            [
                'item_number' => 11,
                'item_name' => 'Wall Finishes',
                'volume_or_area' => 'Wall Plaster & 0.3x0.3 Tiles',
                'notes' => 'Waterproofing compound, plastering and wall tiles',
                'materials' => [
                    ['quantity' => 15, 'unit' => 'pack', 'description' => 'Cement Waterproofing Compound', 'unit_price' => 60.00],
                    ['quantity' => 125, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 12, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 208, 'unit' => 'pcs', 'description' => '0.3x0.3 Tiles', 'unit_price' => 45.00],
                    ['quantity' => 1, 'unit' => 'roll', 'description' => 'Hardware Cloth', 'unit_price' => 1120.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Wall Finishes Labor (45%)', 'unit_price' => 22367.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 7455.75],
                ],
            ],
            [
                'item_number' => 12,
                'item_name' => 'Doors',
                'volume_or_area' => 'Panel, Flush & PVC Doors',
                'notes' => 'Complete door sets, locksets, hinges, jambs, and wood preservative',
                'materials' => [
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'PVC Door w/ Jamb 0.60mx2.10m', 'unit_price' => 1700.00],
                    ['quantity' => 1, 'unit' => 'sets', 'description' => 'Panel Door 0.90mx2.10m', 'unit_price' => 4500.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Flush Door 0.80mx2.10', 'unit_price' => 4200.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Flush Door 0.70mx2.10', 'unit_price' => 3800.00],
                    ['quantity' => 1, 'unit' => 'sets', 'description' => 'Door Lockset (Main Door)', 'unit_price' => 3000.00],
                    ['quantity' => 4, 'unit' => 'sets', 'description' => 'Door Lockset (Exit/Bedrooms)', 'unit_price' => 1500.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Door Lockset (T & B)', 'unit_price' => 450.00],
                    ['quantity' => 1, 'unit' => 'sets', 'description' => 'Door Jamb 0.90m (2x4)', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Door Jamb 0.80m (2x4)', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Door Jamb 0.70m (2x4)', 'unit_price' => 1300.00],
                    ['quantity' => 7, 'unit' => 'pairs', 'description' => 'Loosepin Hinges 3½x3½', 'unit_price' => 170.00],
                    ['quantity' => 1, 'unit' => 'gals', 'description' => 'Solignum Clear', 'unit_price' => 3000.00],
                    ['quantity' => 3, 'unit' => 'kgs', 'description' => 'Assorted Common Nails', 'unit_price' => 70.00],
                    ['quantity' => 2, 'unit' => 'qrt', 'description' => 'Stikwell', 'unit_price' => 850.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Doors Installation Labor (45%)', 'unit_price' => 20880.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 6960.00],
                ],
            ],
            [
                'item_number' => 13,
                'item_name' => 'Windows',
                'volume_or_area' => 'Sliding Analoc Aluminum Frame Windows',
                'notes' => 'Complete window fabrication, delivery and installation',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'LS', 'description' => 'Sliding Analoc Window w/ Alum. Frame', 'unit_price' => 20000.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Window Installation Labor (45%)', 'unit_price' => 9000.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 3000.00],
                ],
            ],
            [
                'item_number' => 14,
                'item_name' => 'Kitchen Counter',
                'volume_or_area' => 'Precast Counter & Stainless Sink',
                'notes' => 'Precast slab, stainless sink, faucet, and ceramic tiles',
                'materials' => [
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Precast Kitchen Counter', 'unit_price' => 1800.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Kitchen Sink with Fittings', 'unit_price' => 3520.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Stainless Kitchen Sink', 'unit_price' => 3500.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Faucet', 'unit_price' => 350.00],
                    ['quantity' => 6, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 32, 'unit' => 'pcs', 'description' => 'CHB', 'unit_price' => 13.00],
                    ['quantity' => 30, 'unit' => 'pcs', 'description' => '0.3x0.3 Tiles', 'unit_price' => 45.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Kitchen Counter Labor (45%)', 'unit_price' => 6721.20],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2240.40],
                ],
            ],
            [
                'item_number' => 15,
                'item_name' => 'Plumbing',
                'volume_or_area' => '2 Full Toilet & Bath + Kitchen Drainage',
                'notes' => 'Toilet sets, shower sets, PPR hot/cold lines, cleanouts & fittings',
                'materials' => [
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Head Shower set', 'unit_price' => 1500.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Faucet', 'unit_price' => 350.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Toilet Bowl with Comp. Accs.', 'unit_price' => 8500.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Floor Drain', 'unit_price' => 450.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Catch Basin', 'unit_price' => 750.00],
                    ['quantity' => 1, 'unit' => 'LS', 'description' => 'PPR Pipes, Elbows And Fittings', 'unit_price' => 30000.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Clean Out 4"', 'unit_price' => 175.00],
                    ['quantity' => 2, 'unit' => 'qrt', 'description' => 'Vulca Seal', 'unit_price' => 400.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Master Plumber Labor (45%)', 'unit_price' => 23996.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 7998.75],
                ],
            ],
            [
                'item_number' => 16,
                'item_name' => 'Electrical',
                'volume_or_area' => 'Complete Service Entrance & Wiring',
                'notes' => 'Panelboard 60A/20A, LED lights, THHN wires #12/#14/#6, switches & outlets',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Service Cap', 'unit_price' => 60.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Meter Socket Class 100', 'unit_price' => 550.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Panel Board with 60A Main and 20A branch', 'unit_price' => 3500.00],
                    ['quantity' => 28, 'unit' => 'pcs', 'description' => '10Watts LED Light', 'unit_price' => 350.00],
                    ['quantity' => 7, 'unit' => 'pcs', 'description' => 'Wall Light', 'unit_price' => 450.00],
                    ['quantity' => 19, 'unit' => 'pcs', 'description' => 'Convenience Outlets', 'unit_price' => 360.00],
                    ['quantity' => 18, 'unit' => 'pcs', 'description' => 'Utility Box', 'unit_price' => 50.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => '2 Gang Switch', 'unit_price' => 200.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Single Switch', 'unit_price' => 60.00],
                    ['quantity' => 15, 'unit' => 'pcs', 'description' => 'Junction Box PVC', 'unit_price' => 55.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => 'Electrical Tape', 'unit_price' => 45.00],
                    ['quantity' => 10, 'unit' => 'packs', 'description' => 'Plastic Ties', 'unit_price' => 15.00],
                    ['quantity' => 23, 'unit' => 'lengths', 'description' => '1/2" PVC Pipe Orange', 'unit_price' => 95.00],
                    ['quantity' => 3, 'unit' => 'lengths', 'description' => '1" PVC Pipe Orange', 'unit_price' => 150.00],
                    ['quantity' => 23, 'unit' => 'pcs', 'description' => '1/2" PVC Elbow', 'unit_price' => 25.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '1" PVC Elbow', 'unit_price' => 60.00],
                    ['quantity' => 1, 'unit' => 'L.S.', 'description' => 'Conduit Pipe, Elbow and Coupling', 'unit_price' => 5500.00],
                    ['quantity' => 1, 'unit' => 'L.S.', 'description' => 'Cable, Telephone and Internet Wire', 'unit_price' => 3500.00],
                    ['quantity' => 2, 'unit' => 'rolls', 'description' => '#12 THHN Wire', 'unit_price' => 7500.00],
                    ['quantity' => 1, 'unit' => 'rolls', 'description' => '#14 THHN Wire', 'unit_price' => 7400.00],
                    ['quantity' => 15, 'unit' => 'meters', 'description' => '#6 THHN Wire', 'unit_price' => 130.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Electrical Wiring Labor (45%)', 'unit_price' => 30615.75],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 10205.25],
                ],
            ],
            [
                'item_number' => 17,
                'item_name' => 'Painting',
                'volume_or_area' => 'Interior & Exterior Painting',
                'notes' => 'Primer, semi-gloss latex topcoat, skimcoat, and surface prep',
                'materials' => [
                    ['quantity' => 12, 'unit' => 'gals', 'description' => 'Primer White', 'unit_price' => 2100.00],
                    ['quantity' => 21, 'unit' => 'gals', 'description' => 'Latex Semi-Gloss', 'unit_price' => 1120.00],
                    ['quantity' => 5, 'unit' => 'sack', 'description' => 'Skimcoat', 'unit_price' => 500.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => 'Sanding Paper #120', 'unit_price' => 15.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => '2" Paint Brush', 'unit_price' => 75.00],
                    ['quantity' => 2, 'unit' => 'gals', 'description' => 'Paint Thinner', 'unit_price' => 320.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Painting Works Labor (45%)', 'unit_price' => 23404.50],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 7801.50],
                ],
            ],
            [
                'item_number' => 18,
                'item_name' => 'Stairs',
                'volume_or_area' => 'N/A (Single Storey Bungalow)',
                'notes' => 'Not applicable for single-detached bungalow unit',
                'materials' => [],
                'labors' => [],
                'equipments' => [],
            ],
            [
                'item_number' => 19,
                'item_name' => 'Septic Tank',
                'volume_or_area' => 'Sanitary Septic Digester',
                'notes' => 'CHB chamber, 10mm rebar, cement, sand, gravel & manhole cover',
                'materials' => [
                    ['quantity' => 17, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 2, 'unit' => 'm³', 'description' => 'Sand', 'unit_price' => 850.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 17, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'Precast Manhole cover and frame', 'unit_price' => 300.00],
                    ['quantity' => 113, 'unit' => 'pcs', 'description' => 'CHB', 'unit_price' => 13.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => 'Tie Wire #18', 'unit_price' => 85.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Septic Tank Labor (45%)', 'unit_price' => 5946.30],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 1982.10],
                ],
            ],
            [
                'item_number' => 20,
                'item_name' => 'Others (Fencing, Taxes, Profit & Insurance)',
                'volume_or_area' => 'Perimeter Fence, 5% Taxes, 10% Contractor Profit & Insurance',
                'notes' => '20.1 Fencing (₱248,974.40) + 20.2 Taxes 5% (₱77,080.96) + 20.3 Profit 10% (₱154,161.92) + 20.4 Insurance 2-Yr (₱5,200.00)',
                'materials' => [
                    ['quantity' => 121, 'unit' => 'bags', 'description' => '20.1 Fencing - Cement', 'unit_price' => 225.00],
                    ['quantity' => 43, 'unit' => 'pcs', 'description' => '20.1 Fencing - 8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 297, 'unit' => 'pcs', 'description' => '20.1 Fencing - 10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 59, 'unit' => 'pcs', 'description' => '20.1 Fencing - GI Square tube 1.0', 'unit_price' => 390.00],
                    ['quantity' => 17, 'unit' => 'kgs', 'description' => '20.1 Fencing - GI Wire #18', 'unit_price' => 85.00],
                    ['quantity' => 11, 'unit' => 'm³', 'description' => '20.1 Fencing - Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 9, 'unit' => 'm³', 'description' => '20.1 Fencing - 3/4 Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 623, 'unit' => 'pcs', 'description' => '20.1 Fencing - 4" CHB', 'unit_price' => 13.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => '20.1 Fencing - Acrylic Thinner', 'unit_price' => 440.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => '20.1 Fencing - Epoxy Primer', 'unit_price' => 950.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '20.1 Fencing - Paint Brush 2"', 'unit_price' => 75.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '20.1 Fencing - Baby Roller', 'unit_price' => 95.00],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => '20.2 Taxes - 5% of item 1-20.1', 'unit_price' => 77080.96],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => '20.3 Profit - 10% of item 1-20.1', 'unit_price' => 154161.92],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => '20.4 Force Majeure Insurance (2 yrs @ ₱2,600/yr)', 'unit_price' => 5200.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '20.1 Fencing Labor (45%)', 'unit_price' => 70024.05],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '20.1 Fencing Contingencies and Price Escalation (15%)', 'unit_price' => 23341.35],
                ],
            ],
        ];

        $grandTotal = $this->applyTemplate($project, $template);

        return redirect()->back()->with('success', 'Official 3-Bedroom Bungalow Bill of Materials & Cost Estimates (Total ₱' . number_format($grandTotal, 2) . ') loaded successfully!');
    }

    /**
     * 1-Click Load 2 Bedroom Bungalow Single Detached Residential Building (₱1,831,613.80) - PDF 2.
     */
    public function load2BrBungalowTemplate($projectId)
    {
        $project = Project::findOrFail($projectId);

        $template = [
            [
                'item_number' => 1,
                'item_name' => 'FOUNDATION AND FOOTING',
                'volume_or_area' => 'Volume of Concrete : 2.61 cu.m',
                'notes' => 'Structural excavation, footing rebar, formwork, and concrete pour.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 8658.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 3464.00,
                'profit_percent' => 10.00,
                'profit_amount' => 5772.00,
                'materials' => [
                    ['quantity' => 18, 'unit' => 'lghts', 'description' => '16mmx6m Corr. Steel bar', 'unit_price' => 430.00],
                    ['quantity' => 31, 'unit' => 'lghts', 'description' => '10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => 11, 'unit' => 'lghts', 'description' => '9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => 6, 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => 60, 'unit' => 'pcs', 'description' => '2x2x10 Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 5, 'unit' => 'kls', 'description' => 'Assorted sizes Nails', 'unit_price' => 70.00],
                    ['quantity' => 2.61, 'unit' => 'cu.m', 'description' => 'Premix Concrete (3/4in Aggregate 3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Other Consumables', 'unit_price' => 1500.00],
                ],
                'labors' => [
                    ['quantity' => 23.90, 'unit' => 'cu.m', 'description' => 'Excavation', 'unit_price' => 420.00],
                    ['quantity' => 320.00, 'unit' => 'kgs', 'description' => 'Rebar', 'unit_price' => 10.00],
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Lay-out', 'unit_price' => 2000.00],
                    ['quantity' => 2.61, 'unit' => 'cu.m', 'description' => 'Pouring', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 3500.00],
                ],
            ],
            [
                'item_number' => 2,
                'item_name' => 'COLUMNS',
                'volume_or_area' => 'Volume 3.46 cu.m',
                'notes' => '4 units C1, 5 units C2, 3 units C3 & 2 units C4',
                'contingency_percent' => 15.00,
                'contingency_amount' => 14314.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 5726.00,
                'profit_percent' => 10.00,
                'profit_amount' => 9543.00,
                'materials' => [
                    ['quantity' => 16, 'unit' => 'lghts', 'description' => '16mmx6m Corr. Steel bar', 'unit_price' => 430.00],
                    ['quantity' => 20, 'unit' => 'lghts', 'description' => '12mmx6m Corr. Steel bar', 'unit_price' => 240.00],
                    ['quantity' => 14, 'unit' => 'lghts', 'description' => '10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => 100, 'unit' => 'lghts', 'description' => '9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => 15, 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => 3.5, 'unit' => 'cu.m', 'description' => 'Premix Concrete (3/4in Aggregate 3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => 6, 'unit' => 'sheets', 'description' => 'Phenolic board 3/8x4x8', 'unit_price' => 1300.00],
                    ['quantity' => 100, 'unit' => 'pcs', 'description' => '2x2x10 Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 12, 'unit' => 'kls', 'description' => 'Assorted sizes Nails', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 44.30, 'unit' => 'sq.m', 'description' => 'Formworks', 'unit_price' => 350.00],
                    ['quantity' => 607.00, 'unit' => 'kgs', 'description' => 'Rebar', 'unit_price' => 10.00],
                    ['quantity' => 3.46, 'unit' => 'cu.m', 'description' => 'Pouring', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 6148.00],
                ],
            ],
            [
                'item_number' => 3,
                'item_name' => 'BEAMS',
                'volume_or_area' => 'Volume 2.07 cu.m',
                'notes' => 'Roof beams and tie beams framing.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 8952.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 3581.00,
                'profit_percent' => 10.00,
                'profit_amount' => 5968.00,
                'materials' => [
                    ['quantity' => 43, 'unit' => 'lghts', 'description' => '10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => 50, 'unit' => 'lghts', 'description' => '9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => 9, 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => 2.07, 'unit' => 'cu.m', 'description' => 'Premix Concrete (3/4in Aggregate 3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => 5, 'unit' => 'sheets', 'description' => 'Phenolic board 3/8x4x8', 'unit_price' => 1300.00],
                    ['quantity' => 60, 'unit' => 'pcs', 'description' => '2x2x10 Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 8, 'unit' => 'kls', 'description' => 'Assorted sizes Nails', 'unit_price' => 70.00],
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Other Consumables', 'unit_price' => 1500.00],
                ],
                'labors' => [
                    ['quantity' => 31.67, 'unit' => 'sq.m', 'description' => 'Formworks', 'unit_price' => 350.00],
                    ['quantity' => 336.28, 'unit' => 'kgs', 'description' => 'Rebar', 'unit_price' => 10.00],
                    ['quantity' => 2.07, 'unit' => 'cu.m', 'description' => 'Pouring', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 3773.00],
                ],
            ],
            [
                'item_number' => 4,
                'item_name' => 'SLAB ON FILL',
                'volume_or_area' => 'Volume 3.90 cu.m (0.075m thk or 75mm)',
                'notes' => 'Earth fill, compaction, rebar mesh, and concrete topping.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 8988.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 3595.00,
                'profit_percent' => 10.00,
                'profit_amount' => 5992.00,
                'materials' => [
                    ['quantity' => 25, 'unit' => 'cu.m', 'description' => 'Backfill', 'unit_price' => 700.00],
                    ['quantity' => 3.9, 'unit' => 'cu.m', 'description' => 'Premix Concrete (3/4in Aggregate 3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => 32, 'unit' => 'lghts', 'description' => '9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => 5, 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 25.00, 'unit' => 'cu.m', 'description' => 'Backfilling and Compaction', 'unit_price' => 350.00],
                    ['quantity' => 98.00, 'unit' => 'kgs', 'description' => 'Rebar', 'unit_price' => 10.00],
                    ['quantity' => 3.9, 'unit' => 'cu.m', 'description' => 'Pouring', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 3924.00],
                ],
            ],
            [
                'item_number' => 5,
                'item_name' => 'EXTERIOR WALLS',
                'volume_or_area' => '6in CHB: 55.18 sq.m Firewall | 4in CHB: 71.75 sq.m',
                'notes' => 'Perimeter walls and firewall construction.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 21996.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 8799.00,
                'profit_percent' => 10.00,
                'profit_amount' => 14664.00,
                'materials' => [
                    ['quantity' => 718, 'unit' => 'pcs', 'description' => '6inx8inx16in CHB', 'unit_price' => 19.50],
                    ['quantity' => 933, 'unit' => 'pcs', 'description' => '4inx8inx16in CHB', 'unit_price' => 15.00],
                    ['quantity' => 90, 'unit' => 'lghts', 'description' => '10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => 3, 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => 9, 'unit' => 'cu.m', 'description' => 'Mixing Sand for Filler', 'unit_price' => 800.00],
                    ['quantity' => 6, 'unit' => 'cu.m', 'description' => 'Screened Sand for Finishing', 'unit_price' => 1300.00],
                    ['quantity' => 147, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 240.00],
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Other Consumables', 'unit_price' => 1000.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Masonry & Plastering Labor', 'unit_price' => 42573.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 9461.00],
                ],
            ],
            [
                'item_number' => 6,
                'item_name' => 'INTERIOR WALLS/PARTITION',
                'volume_or_area' => 'A= 60.18 Sq.m',
                'notes' => 'Bedroom, bathroom, and kitchen dividing walls.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 9207.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 3683.00,
                'profit_percent' => 10.00,
                'profit_amount' => 6138.00,
                'materials' => [
                    ['quantity' => 783, 'unit' => 'pcs', 'description' => '4inx8inx16in CHB', 'unit_price' => 15.00],
                    ['quantity' => 42, 'unit' => 'lghts', 'description' => '10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => 2, 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => 3, 'unit' => 'cu.m', 'description' => 'Mixing Sand for Filler', 'unit_price' => 800.00],
                    ['quantity' => 3, 'unit' => 'cu.m', 'description' => 'Screend Sand for Finishing', 'unit_price' => 1300.00],
                    ['quantity' => 60, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 240.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Interior Masonry Labor', 'unit_price' => 17839.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 3900.00],
                ],
            ],
            [
                'item_number' => 7,
                'item_name' => 'ROOFING',
                'volume_or_area' => 'Area : 79.24 Sq.m More or Less',
                'notes' => 'Pre-painted rib type roofing sheets, C-purlins, flashing, and gutters.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 16094.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 6438.00,
                'profit_percent' => 10.00,
                'profit_amount' => 10729.00,
                'materials' => [
                    ['quantity' => 9, 'unit' => 'shts', 'description' => '6.20mx1.05mx0.40mm pre painted Roofing rib type', 'unit_price' => 1736.00],
                    ['quantity' => 7, 'unit' => 'shts', 'description' => '4.0mx1.05x0.40mm pre painted Roofing rib type', 'unit_price' => 1120.00],
                    ['quantity' => 1, 'unit' => 'sht', 'description' => '4.60mx1.05x0.40mm pre painted Roofing Rib type', 'unit_price' => 450.00],
                    ['quantity' => 18, 'unit' => 'pcs', 'description' => '0.40mm pre painted fascia board', 'unit_price' => 450.00],
                    ['quantity' => 18, 'unit' => 'pcs', 'description' => '3/8in.x4inx8ft pre cut hardiflex', 'unit_price' => 150.00],
                    ['quantity' => 6, 'unit' => 'pcs', 'description' => 'wall cap 0.40mm', 'unit_price' => 700.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '0.40mm end flashing', 'unit_price' => 480.00],
                    ['quantity' => 8, 'unit' => 'lghts', 'description' => '10mmx2x6x20ft C-Purlins', 'unit_price' => 1200.00],
                    ['quantity' => 25, 'unit' => 'lghts', 'description' => '10mmx2x3x20ft C-Purlins', 'unit_price' => 670.00],
                    ['quantity' => 700, 'unit' => 'pcs', 'description' => 'Tekscrew 2in', 'unit_price' => 1.50],
                    ['quantity' => 2, 'unit' => 'boxes', 'description' => 'Blind Rivets', 'unit_price' => 260.00],
                    ['quantity' => 8, 'unit' => 'kls', 'description' => 'Welding Rod', 'unit_price' => 120.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Roof Truss & Sheet Installation Labor', 'unit_price' => 31155.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 6900.00],
                ],
            ],
            [
                'item_number' => 8,
                'item_name' => 'CEILING',
                'volume_or_area' => 'EAVES: 33.84 Sq.m More or Less',
                'notes' => 'Hardiflex ceiling on metal furring framework.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 12461.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 4985.00,
                'profit_percent' => 10.00,
                'profit_amount' => 8308.00,
                'materials' => [
                    ['quantity' => 201, 'unit' => 'pcs', 'description' => '0.5mm metal furring', 'unit_price' => 145.00],
                    ['quantity' => 27, 'unit' => 'pcs', 'description' => 'Light Hardiflex', 'unit_price' => 500.00],
                    ['quantity' => 2, 'unit' => 'boxes', 'description' => 'Blind Rivets', 'unit_price' => 300.00],
                    ['quantity' => 14, 'unit' => 'lghts', 'description' => '1x1x21ft tubular aluminum', 'unit_price' => 540.00],
                    ['quantity' => 150, 'unit' => 'pcs', 'description' => 'Aluminum screw', 'unit_price' => 2.00],
                    ['quantity' => 22, 'unit' => 'meters', 'description' => '0.40 Screen', 'unit_price' => 115.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Ceiling Installation & Framing Labor', 'unit_price' => 24136.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 5300.00],
                ],
            ],
            [
                'item_number' => 9,
                'item_name' => 'FLOOR FINISHES',
                'volume_or_area' => '2 CR Floor: 4.95 sq.m | Wall: 18 sq.m | Floor Master Bed: 10.73 sq.m | Bed: 7.54 sq.m | Dining/Living/Kit: 20.33 sq.m | Porch: 3.45 sq.m | Hall: 7.34 sq.m',
                'notes' => 'Granite floor tiles and ceramic wall tiles.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 13590.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 5436.00,
                'profit_percent' => 10.00,
                'profit_amount' => 9060.00,
                'materials' => [
                    ['quantity' => 70, 'unit' => 'pcs', 'description' => '0.30x0.30 floor tiles', 'unit_price' => 40.00],
                    ['quantity' => 220, 'unit' => 'pcs', 'description' => '0.30x0.30 wall tiles', 'unit_price' => 45.00],
                    ['quantity' => 141, 'unit' => 'pcs', 'description' => '0.60x0.60 floor tiles', 'unit_price' => 250.00],
                    ['quantity' => 42, 'unit' => 'pcs', 'description' => 'Port Tile 0.60x0.60', 'unit_price' => 250.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Tile Setting & Grouting Labor', 'unit_price' => 26303.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '10% of Materials Equipment Expense', 'unit_price' => 5845.00],
                ],
            ],
            [
                'item_number' => 10,
                'item_name' => 'WALL FINISHES',
                'volume_or_area' => 'Front Grooving 6.60 sq.m | Natural Stone : 1.98 sq.m',
                'notes' => 'Natural stone accent cladding and front concrete canopy molding.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 2220.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 888.00,
                'profit_percent' => 10.00,
                'profit_amount' => 1480.00,
                'materials' => [
                    ['quantity' => 2, 'unit' => 'sq.m', 'description' => 'Natural Stone', 'unit_price' => 1200.00],
                    ['quantity' => 9.6, 'unit' => 'meters', 'description' => 'Front Canopy: 9.60x0.40x0.10m thk', 'unit_price' => 480.00],
                    ['quantity' => 6.6, 'unit' => 'ln.m', 'description' => '0.10x0.025m concrete molding', 'unit_price' => 380.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Stone Masonry & Canopy Labor', 'unit_price' => 4283.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Equipment Expense', 'unit_price' => 1000.00],
                ],
            ],
            [
                'item_number' => 11,
                'item_name' => 'DOORS',
                'volume_or_area' => '7 Total Door Sets',
                'notes' => 'Main panel door, bedroom doors, PVC doors, and sliding glass door.',
                'contingency_percent' => 15.00,
                'contingency_amount' => 10838.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 4335.00,
                'profit_percent' => 10.00,
                'profit_amount' => 7225.00,
                'materials' => [
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Main Panel Door 0.90x2.10 with Door Jamb', 'unit_price' => 8500.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Bedroom Door 0.80x2.10 with Door Jamb', 'unit_price' => 8200.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'PVC Door 0.60x2.10', 'unit_price' => 2000.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => '1.50mx2.10m Sliding Door 1/4 in glass on aluminum Frame', 'unit_price' => 20000.00],
                    ['quantity' => 3, 'unit' => 'sets', 'description' => 'Door Knob', 'unit_price' => 3500.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'CR Door Knob', 'unit_price' => 750.00],
                    ['quantity' => 12, 'unit' => 'pcs', 'description' => '3½x3½in Loose pin hinges', 'unit_price' => 300.00],
                ],
                'labors' => [
                    ['quantity' => 3, 'unit' => 'Sets', 'description' => 'Door with Door Jamb Installation', 'unit_price' => 2000.00],
                    ['quantity' => 1, 'unit' => 'Set', 'description' => 'PVC Door', 'unit_price' => 1500.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Equipment Expense', 'unit_price' => 1000.00],
                ],
            ],
            [
                'item_number' => 12,
                'item_name' => 'WINDOWS',
                'volume_or_area' => '8 Total Window Openings',
                'notes' => 'Materials (Including Installation) for aluminum frame glass sliding & awning windows',
                'contingency_percent' => 15.00,
                'contingency_amount' => 6452.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 2581.00,
                'profit_percent' => 10.00,
                'profit_amount' => 4301.00,
                'materials' => [
                    ['quantity' => 1, 'unit' => 'unit', 'description' => '1.20x2.0m 1/4 glass on aluminum frame sliding window', 'unit_price' => 10500.00],
                    ['quantity' => 3, 'unit' => 'units', 'description' => '1.20x1.20m 1/4 glass on aluminum frame sliding window', 'unit_price' => 6300.00],
                    ['quantity' => 2, 'unit' => 'units', 'description' => '0.60mx0.90m 1/4in glass on aluminum frame sliding window', 'unit_price' => 2363.00],
                    ['quantity' => 2, 'unit' => 'units', 'description' => 'Awning window 1.80mx0.45m', 'unit_price' => 3544.00],
                    ['quantity' => 1, 'unit' => 'unit', 'description' => '0.90x0.450 awning window', 'unit_price' => 1794.00],
                ],
                'labors' => [],
                'equipments' => [],
            ],
            [
                'item_number' => 13,
                'item_name' => 'KITCHEN COUNTER',
                'volume_or_area' => '1.50m x 0.60m Granite slab',
                'notes' => 'Precast counter, granite slab, and prefabricated cabinet doors',
                'contingency_percent' => 15.00,
                'contingency_amount' => 5371.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 2149.00,
                'profit_percent' => 10.00,
                'profit_amount' => 3581.00,
                'materials' => [
                    ['quantity' => 1, 'unit' => 'lot', 'description' => '1.50mx0.60m Granite slab', 'unit_price' => 14000.00],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => '1.50x0.60mx0.075m Precast Concrete Counter', 'unit_price' => 2500.00],
                    ['quantity' => 4, 'unit' => 'sets', 'description' => '14in wide pre fabricated Cabinet Door', 'unit_price' => 850.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'Hinges', 'unit_price' => 350.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'Cab Door Handle', 'unit_price' => 250.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Door Frame', 'unit_price' => 800.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Counter Fabrication Labor', 'unit_price' => 10395.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Equipment Expense', 'unit_price' => 2310.00],
                ],
            ],
            [
                'item_number' => 14,
                'item_name' => 'PLUMBING',
                'volume_or_area' => 'PVC Sanitary, Moldex Waterlines & Fixtures',
                'notes' => 'Water closets, lavatories, faucets, and kitchen sink fixtures',
                'contingency_percent' => 15.00,
                'contingency_amount' => 11591.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 4637.00,
                'profit_percent' => 10.00,
                'profit_amount' => 7727.00,
                'materials' => [
                    ['quantity' => 12, 'unit' => 'lghts', 'description' => '4inꝊx10ft Sanitary PVC Pipe', 'unit_price' => 420.00],
                    ['quantity' => 10, 'unit' => 'lghts', 'description' => '3inꝊx10ft Sanitary PVC Pipe', 'unit_price' => 360.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => '4inꝊx90ᵒ PVC Elbow', 'unit_price' => 90.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => '4inꝊ PVC Coupling', 'unit_price' => 90.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '3inꝊ PVC Wye', 'unit_price' => 130.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '3inꝊ PVC Tee', 'unit_price' => 85.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '3inꝊ PVC Clean Out', 'unit_price' => 175.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => '3inꝊ PVC Elbow 90ᵒ', 'unit_price' => 85.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '3inꝊ PVC Elbow 45ᵒ', 'unit_price' => 85.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => '4in PVC Cleanout', 'unit_price' => 220.00],
                    ['quantity' => 12, 'unit' => 'rolls', 'description' => '20mmꝊx10ft Moldex Waterline', 'unit_price' => 100.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => '20mmꝊx90ᵒ Elbow (Moldex)', 'unit_price' => 15.00],
                    ['quantity' => 7, 'unit' => 'pcs', 'description' => '20mmꝊ Coupling Moldex', 'unit_price' => 15.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Water Closet with Filling', 'unit_price' => 9500.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Lavatory', 'unit_price' => 4500.00],
                    ['quantity' => 6, 'unit' => 'pcs', 'description' => 'Stainless faucet 20mm', 'unit_price' => 450.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Shower valve 20mm', 'unit_price' => 750.00],
                    ['quantity' => 5, 'unit' => 'cans', 'description' => 'Solvent', 'unit_price' => 75.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Stainless Sink', 'unit_price' => 2500.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Kitchen Sink faucet', 'unit_price' => 1200.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'P-Trap 2inꝊ PVC', 'unit_price' => 95.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Plumbing Installation Labor', 'unit_price' => 22432.50],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Equipment Expense', 'unit_price' => 4985.00],
                ],
            ],
            [
                'item_number' => 15,
                'item_name' => 'ELECTRICAL',
                'volume_or_area' => 'Complete Rough-in, Fixtures & Service Entrance',
                'notes' => 'Panelboard, breakers, THHN wires, switches, outlets, downlights & chandelier',
                'contingency_percent' => 15.00,
                'contingency_amount' => 31739.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 12696.00,
                'profit_percent' => 10.00,
                'profit_amount' => 21159.00,
                'materials' => [
                    ['quantity' => 35, 'unit' => 'pcs', 'description' => 'Utility Box 2x4', 'unit_price' => 35.00],
                    ['quantity' => 35, 'unit' => 'pcs', 'description' => 'Junction Box 4x4', 'unit_price' => 40.00],
                    ['quantity' => 3, 'unit' => 'rolls', 'description' => 'Flexible Pipe 1/2', 'unit_price' => 900.00],
                    ['quantity' => 35, 'unit' => 'pcs', 'description' => '1/2x10ft PVC Pipes (for electrical)', 'unit_price' => 135.00],
                    ['quantity' => 35, 'unit' => 'pcs', 'description' => 'PVC Elbow 1/2', 'unit_price' => 18.00],
                    ['quantity' => 2, 'unit' => 'cans', 'description' => 'Solvet', 'unit_price' => 95.00],
                    ['quantity' => 3, 'unit' => 'boxes', 'description' => 'THW wire #12', 'unit_price' => 5000.00],
                    ['quantity' => 3, 'unit' => 'boxes', 'description' => 'THW wire #10', 'unit_price' => 4800.00],
                    ['quantity' => 10, 'unit' => 'pcs', 'description' => 'Electrical Tape', 'unit_price' => 60.00],
                    ['quantity' => 60, 'unit' => 'mts', 'description' => 'RG 6 Cable wire', 'unit_price' => 32.00],
                    ['quantity' => 60, 'unit' => 'mts', 'description' => 'Telephone wire #22', 'unit_price' => 30.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'ACB plug-in (67) 12 holes allied', 'unit_price' => 2000.00],
                    ['quantity' => 1, 'unit' => 'pc', 'description' => '100A main', 'unit_price' => 1750.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => '20A', 'unit_price' => 900.00],
                    ['quantity' => 1, 'unit' => 'pc', 'description' => '30A', 'unit_price' => 900.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => '15A', 'unit_price' => 900.00],
                    ['quantity' => 105, 'unit' => 'pcs', 'description' => 'Flexible Connection 1/2', 'unit_price' => 12.00],
                    ['quantity' => 1, 'unit' => 'pc', 'description' => 'High capacity Meter sucket hub 1½', 'unit_price' => 2000.00],
                    ['quantity' => 1, 'unit' => 'pc', 'description' => 'Service Cap 1½', 'unit_price' => 140.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => 'Metal Clamp 1½', 'unit_price' => 50.00],
                    ['quantity' => 1, 'unit' => 'lght', 'description' => 'RSC Pipe 1½', 'unit_price' => 1650.00],
                    ['quantity' => 1, 'unit' => 'pc', 'description' => 'Secondary rack 2 spool', 'unit_price' => 400.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Machine Bolt 1/2x14', 'unit_price' => 100.00],
                    ['quantity' => 30, 'unit' => 'mts', 'description' => 'THW #38mm² (black)', 'unit_price' => 400.00],
                    ['quantity' => 30, 'unit' => 'mts', 'description' => 'THW #38mm² (white)', 'unit_price' => 400.00],
                    ['quantity' => 30, 'unit' => 'mts', 'description' => 'THW #8mm (Green)', 'unit_price' => 90.00],
                    ['quantity' => 10, 'unit' => 'pcs', 'description' => 'PVC pipe 1½', 'unit_price' => 400.00],
                    ['quantity' => 10, 'unit' => 'pcs', 'description' => 'PVC Elbow 1½', 'unit_price' => 90.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Ground rod w/ clamp', 'unit_price' => 1000.00],
                    ['quantity' => 20, 'unit' => 'sets', 'description' => '2 gang outlet universal panasonic', 'unit_price' => 260.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => '2 aircon outlet', 'unit_price' => 400.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Water proof cover panasonic', 'unit_price' => 1150.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'water proof cover', 'unit_price' => 65.00],
                    ['quantity' => 10, 'unit' => 'sets', 'description' => '2 gang switch', 'unit_price' => 250.00],
                    ['quantity' => 10, 'unit' => 'sets', 'description' => '3 gang switch', 'unit_price' => 340.00],
                    ['quantity' => 10, 'unit' => 'sets', 'description' => '1 gang switch', 'unit_price' => 160.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Chandeller w/ fan', 'unit_price' => 10500.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Telephone outlet', 'unit_price' => 450.00],
                    ['quantity' => 2, 'unit' => 'sets', 'description' => 'Cable outlet', 'unit_price' => 450.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => '2 gang outlet panasonic (GFA)', 'unit_price' => 1850.00],
                    ['quantity' => 30, 'unit' => 'pcs', 'description' => '4" Down Light', 'unit_price' => 130.00],
                    ['quantity' => 30, 'unit' => 'pcs', 'description' => 'Led Bulb 5watts', 'unit_price' => 95.00],
                    ['quantity' => 10, 'unit' => 'pcs', 'description' => 'Led Bulb 3watts', 'unit_price' => 90.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Master Electrician & Wiring Labor', 'unit_price' => 65295.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Equipment Expense', 'unit_price' => 1192.00],
                ],
            ],
            [
                'item_number' => 16,
                'item_name' => 'PAINTING',
                'volume_or_area' => 'Exterior Wall 126.93 sq.m | Interior Wall 120.36 sq.m | Fire Wall 55.18 sq.m',
                'notes' => 'Mortaflex, skimcoat, primer white, semi gloss latex & marine epoxy',
                'contingency_percent' => 15.00,
                'contingency_amount' => 15501.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 6201.00,
                'profit_percent' => 10.00,
                'profit_amount' => 10334.00,
                'materials' => [
                    ['quantity' => 12, 'unit' => 'gals', 'description' => 'Mortaflex', 'unit_price' => 500.00],
                    ['quantity' => 30, 'unit' => 'bags', 'description' => 'Skimcoat', 'unit_price' => 550.00],
                    ['quantity' => 5, 'unit' => 'pails', 'description' => 'Primer White', 'unit_price' => 2500.00],
                    ['quantity' => 9, 'unit' => 'pails', 'description' => 'Semi Gloss latex', 'unit_price' => 2580.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Marine Epoxy Bostik', 'unit_price' => 2800.00],
                    ['quantity' => 35, 'unit' => 'pcs', 'description' => '#120 Sanding Paper', 'unit_price' => 19.00],
                    ['quantity' => 10, 'unit' => 'pcs', 'description' => '8in Roller', 'unit_price' => 60.00],
                    ['quantity' => 10, 'unit' => 'pcs', 'description' => '3in Paint Brush', 'unit_price' => 35.00],
                    ['quantity' => 7, 'unit' => 'gals', 'description' => 'Neutralizer', 'unit_price' => 500.00],
                    ['quantity' => 5, 'unit' => 'rolls', 'description' => 'Joint Tape', 'unit_price' => 130.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Surface Preparation & Painting Labor', 'unit_price' => 30053.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Equipment Expense', 'unit_price' => 6500.00],
                ],
            ],
            [
                'item_number' => 17,
                'item_name' => 'SEPTIC TANK',
                'volume_or_area' => 'Standard Sanitary Digester',
                'notes' => 'CHB, 10mm rebar, cement, tie wire, and phenolic formworks',
                'contingency_percent' => 15.00,
                'contingency_amount' => 4432.00,
                'taxes_percent' => 6.00,
                'taxes_amount' => 1773.00,
                'profit_percent' => 10.00,
                'profit_amount' => 2955.00,
                'materials' => [
                    ['quantity' => 290, 'unit' => 'pcs', 'description' => '4"x8"x16" CHB', 'unit_price' => 15.00],
                    ['quantity' => 30, 'unit' => 'lghts', 'description' => '10mmx6m Corr. Steel Bar', 'unit_price' => 168.00],
                    ['quantity' => 4, 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => 28, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 240.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Phenolic Board', 'unit_price' => 1300.00],
                    ['quantity' => 1, 'unit' => 'kl', 'description' => 'Nail', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Septic Tank Excavation & Masonry Labor', 'unit_price' => 8577.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Equipment Expense', 'unit_price' => 1906.00],
                ],
            ],
            [
                'item_number' => 18,
                'item_name' => 'OTHERS',
                'volume_or_area' => 'Professional & Permit Fees',
                'notes' => 'Professional Fee (Civil Engr., Electrical Engr., Master Plumber, Geodetic Engr.) and Permit Fee',
                'contingency_percent' => 0.00,
                'contingency_amount' => 0.00,
                'taxes_percent' => 0.00,
                'taxes_amount' => 0.00,
                'profit_percent' => 0.00,
                'profit_amount' => 0.00,
                'materials' => [
                    ['quantity' => 1, 'unit' => 'lot', 'description' => 'Professional Fee (Civil Engr., Electrical Engr., Master Plumber, Geodetic Engr.)', 'unit_price' => 50000.00],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => 'Permit Fee', 'unit_price' => 14000.00],
                ],
                'labors' => [],
                'equipments' => [],
            ],
        ];

        $grandTotal = $this->applyTemplate($project, $template);

        return redirect()->back()->with('success', 'Official 2-Bedroom Bungalow Bill of Materials & Cost Estimates (Total ₱' . number_format($grandTotal, 2) . ') loaded successfully!');
    }

    /**
     * 1-Click Load 31 m² Housing Unit (1 Side of Residential Duplex) (₱742,800.74) - PDF 3.
     */
    public function loadDuplexHousingTemplate($projectId)
    {
        $project = Project::findOrFail($projectId);

        $template = [
            [
                'item_number' => 1,
                'item_name' => 'Foundation and Footings',
                'volume_or_area' => 'V=7m³',
                'notes' => 'Layout, excavation, rebar, cement, sand, gravel, soil guard and formworks',
                'materials' => [
                    ['quantity' => 7, 'unit' => 'm³', 'description' => 'Layout & Excavation', 'unit_price' => 600.00],
                    ['quantity' => 26, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 7, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 18, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => '3/4 Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 3, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Masonry Pail', 'unit_price' => 70.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => '2"x3"x10ft Coco Lumber', 'unit_price' => 140.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => '2"x2"x10ft Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => '4" Common Nails', 'unit_price' => 70.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Phenolic Board', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'liters', 'description' => 'Soil Guard', 'unit_price' => 1350.00],
                    ['quantity' => 5, 'unit' => 'roll', 'description' => '#100 Nylon String', 'unit_price' => 25.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Foundation & Footings Labor (45%)', 'unit_price' => 11466.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 3822.00],
                ],
            ],
            [
                'item_number' => 2,
                'item_name' => 'Columns',
                'volume_or_area' => 'Duplex Columns',
                'notes' => 'Column rebar, cement, sand, gravel, phenolic board and formworks',
                'materials' => [
                    ['quantity' => 19, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 22, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 9, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 8, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 33, 'unit' => 'pcs', 'description' => '2"x2"x8\' Coco Lumber', 'unit_price' => 90.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'Phenolic Board', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => '2.5" Common Nails', 'unit_price' => 80.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Columns Labor (45%)', 'unit_price' => 8466.75],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2822.25],
                ],
            ],
            [
                'item_number' => 3,
                'item_name' => 'Beams',
                'volume_or_area' => 'Duplex Tie Beams & Roof Beams',
                'notes' => '10mm & 8mm rebar, cement, sand, gravel and phenolic formworks',
                'materials' => [
                    ['quantity' => 26, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 31, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 12, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 11, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 16, 'unit' => 'pcs', 'description' => '2"x2"x8\' Coco Lumber', 'unit_price' => 90.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'Phenolic Board', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => '2.5" Common Nail', 'unit_price' => 80.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Beams Labor (45%)', 'unit_price' => 9375.75],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 3125.25],
                ],
            ],
            [
                'item_number' => 4,
                'item_name' => 'Slab on Fill',
                'volume_or_area' => 'Duplex Ground Slab',
                'notes' => 'Backfill 9 m³, rebar, cement, sand, and gravel',
                'materials' => [
                    ['quantity' => 21, 'unit' => 'pcs', 'description' => '8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 23, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 2, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => 'Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 3, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 9, 'unit' => 'm³', 'description' => 'Backfill', 'unit_price' => 600.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Slab on Fill Labor (45%)', 'unit_price' => 8676.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2892.00],
                ],
            ],
            [
                'item_number' => 5,
                'item_name' => 'Suspended Slab (for two storey only)',
                'volume_or_area' => 'N/A (Single Storey Duplex Unit)',
                'notes' => 'Not applicable for single-storey duplex unit',
                'materials' => [],
                'labors' => [],
                'equipments' => [],
            ],
            [
                'item_number' => 6,
                'item_name' => 'Exterior Walls',
                'volume_or_area' => '4" CHB Perimeter Walls',
                'notes' => '4" CHB 603 pcs, 10mm rebar, cement, sand and scaffolding',
                'materials' => [
                    ['quantity' => 34, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 30, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 603, 'unit' => 'pcs', 'description' => 'CHB', 'unit_price' => 13.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'Bamboo', 'unit_price' => 200.00],
                    ['quantity' => 7, 'unit' => 'pcs', 'description' => '2"x2"x10ft Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'Scaffolding Steel', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => 'Assorted Common Nail', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Exterior Walls Labor (45%)', 'unit_price' => 13648.05],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 4549.35],
                ],
            ],
            [
                'item_number' => 7,
                'item_name' => 'Interior Walls / Partitions',
                'volume_or_area' => 'Interior CHB & Hardiflex Partitions',
                'notes' => '4" CHB 489 pcs, 3.5mm Hardiflex 8 pcs, 2x3 C-purlins & cement',
                'materials' => [
                    ['quantity' => 27, 'unit' => 'pcs', 'description' => '10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 24, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 3, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 489, 'unit' => 'pcs', 'description' => 'CHB', 'unit_price' => 13.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => '#18 Tie Wire', 'unit_price' => 85.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => '3.5mm Hardiflex', 'unit_price' => 455.00],
                    ['quantity' => 9, 'unit' => 'pcs', 'description' => '2x3 C-Purlins', 'unit_price' => 480.00],
                    ['quantity' => 7, 'unit' => 'kgs', 'description' => 'Welding Rod', 'unit_price' => 120.00],
                    ['quantity' => 1, 'unit' => 'box', 'description' => '1/8"x1/2" Blind Rivets', 'unit_price' => 280.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Bamboo', 'unit_price' => 200.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => '2"x2"x10ft Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'Scaffolding Steel', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => 'Assorted Common Nail', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Interior Walls Labor (45%)', 'unit_price' => 15451.65],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 5150.55],
                ],
            ],
            [
                'item_number' => 8,
                'item_name' => 'Roofing',
                'volume_or_area' => '49 ln.m. Rib-Type Pre-Painted Long Span',
                'notes' => 'C-Purlins (2x4 & 2x3), long span roofing, gutters, wall flashing, end flashing',
                'materials' => [
                    ['quantity' => 11, 'unit' => 'pcs', 'description' => 'GA. 20 (2x4) C-Purlins', 'unit_price' => 600.00],
                    ['quantity' => 27, 'unit' => 'pcs', 'description' => 'GA. 20 (2x3) C-Purlins', 'unit_price' => 480.00],
                    ['quantity' => 2, 'unit' => 'box', 'description' => 'Tekscrew', 'unit_price' => 510.00],
                    ['quantity' => 2, 'unit' => 'box', 'description' => 'Blind Rivets', 'unit_price' => 280.00],
                    ['quantity' => 13, 'unit' => 'kgs', 'description' => 'Welding rod', 'unit_price' => 120.00],
                    ['quantity' => 49, 'unit' => 'ln.m.', 'description' => 'Rib-Type pre-painted long span roofing', 'unit_price' => 410.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'False Gutter 0.4mmx0.6mm', 'unit_price' => 450.00],
                    ['quantity' => 6, 'unit' => 'pcs', 'description' => 'Wall Flashing 0.4mmx18"x8\'', 'unit_price' => 360.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'End Flashing 0.4mmx32"x8\'', 'unit_price' => 585.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Roofing Installation Labor (45%)', 'unit_price' => 22090.50],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 7363.50],
                ],
            ],
            [
                'item_number' => 9,
                'item_name' => 'Ceiling',
                'volume_or_area' => 'Ficem Board & Metal Furring',
                'notes' => '4.5mm Ficem Board 16 pcs, metal furring 43 pcs, blind rivets & nails',
                'materials' => [
                    ['quantity' => 16, 'unit' => 'pcs', 'description' => '4.5mm Ficem Board', 'unit_price' => 450.00],
                    ['quantity' => 43, 'unit' => 'pcs', 'description' => 'Metal Furring', 'unit_price' => 120.00],
                    ['quantity' => 3, 'unit' => 'box', 'description' => '1/8"x1/2" Blind Rivets', 'unit_price' => 280.00],
                    ['quantity' => 3, 'unit' => 'kgs', 'description' => 'Assorted Common Nails', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Ceiling Installation Labor (45%)', 'unit_price' => 6034.50],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2011.50],
                ],
            ],
            [
                'item_number' => 10,
                'item_name' => 'Floor Finishes',
                'volume_or_area' => 'Power Floor & 0.3x0.3 Tiles',
                'notes' => 'Power floor coating, cement, sand and floor tiles',
                'materials' => [
                    ['quantity' => 5, 'unit' => 'gal', 'description' => 'Power Floor', 'unit_price' => 2400.00],
                    ['quantity' => 6, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 27, 'unit' => 'pcs', 'description' => '0.3x0.3 Tiles', 'unit_price' => 45.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Floor Finishes Labor (45%)', 'unit_price' => 6936.75],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2312.25],
                ],
            ],
            [
                'item_number' => 11,
                'item_name' => 'Wall Finishes',
                'volume_or_area' => 'Cement Plaster, 0.3x0.3 Tiles & Hardware Cloth',
                'notes' => 'Waterproofing compound, plastering, tiles and wire reinforcement',
                'materials' => [
                    ['quantity' => 4, 'unit' => 'pack', 'description' => 'Cement Waterproofing Compound', 'unit_price' => 60.00],
                    ['quantity' => 48, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 5, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 80, 'unit' => 'pcs', 'description' => '0.3x0.3 Tiles', 'unit_price' => 45.00],
                    ['quantity' => 1, 'unit' => 'roll', 'description' => 'Hardware Cloth', 'unit_price' => 1120.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Wall Finishes Labor (45%)', 'unit_price' => 9004.50],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 3001.50],
                ],
            ],
            [
                'item_number' => 12,
                'item_name' => 'Doors',
                'volume_or_area' => 'Panel, PVC & Door Jambs',
                'notes' => 'Panel doors 0.80m & 0.70m, PVC door 0.60m, locksets and loosepin hinges',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Door Jamb 0.80m (2x4)', 'unit_price' => 1300.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Door Jamb 0.70m (2x4)', 'unit_price' => 1300.00],
                    ['quantity' => 4, 'unit' => 'set', 'description' => 'Loosepin Hinges 3½x3½', 'unit_price' => 170.00],
                    ['quantity' => 2, 'unit' => 'kgs', 'description' => 'Assorted Common Nails', 'unit_price' => 70.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'PVC Door w/ Jamb 0.60mx2.10m', 'unit_price' => 1700.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Panel Door 0.80mx2.10', 'unit_price' => 4200.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Panel Door 0.70mx2.10', 'unit_price' => 3800.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Door Lockset (Main Door)', 'unit_price' => 3000.00],
                    ['quantity' => 2, 'unit' => 'set', 'description' => 'Door Lockset (Exit/Bedrooms)', 'unit_price' => 1500.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Door Lockset (T & B)', 'unit_price' => 450.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Doors Installation Labor (45%)', 'unit_price' => 11101.50],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 3700.50],
                ],
            ],
            [
                'item_number' => 13,
                'item_name' => 'Windows',
                'volume_or_area' => 'Sliding Analoc Aluminum Frame Windows',
                'notes' => 'Complete window fabrication, delivery and installation',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'LS', 'description' => 'Sliding Analoc Window w/ Alum. Frame', 'unit_price' => 15000.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Windows Labor (45%)', 'unit_price' => 6750.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2250.00],
                ],
            ],
            [
                'item_number' => 14,
                'item_name' => 'Kitchen Counter',
                'volume_or_area' => 'Precast Counter & Kitchen Sink',
                'notes' => 'Precast slab, stainless kitchen sink, cement, sand, CHB and tiles',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'Pcs', 'description' => 'Precast Kitchen Counter', 'unit_price' => 1800.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Kitchen Sink', 'unit_price' => 890.00],
                    ['quantity' => 4, 'unit' => 'bags', 'description' => 'Cement', 'unit_price' => 225.00],
                    ['quantity' => 1, 'unit' => 'm³', 'description' => 'Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 16, 'unit' => 'pcs', 'description' => 'CHB', 'unit_price' => 13.00],
                    ['quantity' => 15, 'unit' => 'pcs', 'description' => '0.3x0.3 Tiles', 'unit_price' => 45.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Kitchen Counter Labor (45%)', 'unit_price' => 2395.35],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 798.45],
                ],
            ],
            [
                'item_number' => 15,
                'item_name' => 'Plumbing',
                'volume_or_area' => 'Sanitary & Waterline System',
                'notes' => 'PVC sanitary pipes (4" & 2"), solvent, toilet bowl set, shower, drain, cleanouts & fittings',
                'materials' => [
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'PVC Sanitary Pipe 2"', 'unit_price' => 180.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'PVC Sanitary Pipe 4"', 'unit_price' => 420.00],
                    ['quantity' => 2, 'unit' => 'can', 'description' => 'Solvent 400cc', 'unit_price' => 225.00],
                    ['quantity' => 1, 'unit' => 'sets', 'description' => 'Toilet Bowl with Complete Accessories', 'unit_price' => 8500.00],
                    ['quantity' => 1, 'unit' => 'sets', 'description' => 'Telephone Shower', 'unit_price' => 620.00],
                    ['quantity' => 1, 'unit' => 'sets', 'description' => 'Floor Drain', 'unit_price' => 450.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Clean Out 2"', 'unit_price' => 150.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Clean Out 4"', 'unit_price' => 175.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'SAN Elbow 4x45', 'unit_price' => 98.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'SAN Elbow 2x45', 'unit_price' => 55.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'SAN Elbow 2x90', 'unit_price' => 39.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'WYE 2x2', 'unit_price' => 45.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'TEE 2x2', 'unit_price' => 65.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'TEE 4x4', 'unit_price' => 215.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'WYE 4x2', 'unit_price' => 130.00],
                    ['quantity' => 11, 'unit' => 'pcs', 'description' => 'Elbow 1/2"', 'unit_price' => 25.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'TEE 1/2"', 'unit_price' => 20.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'Water Pipe 1/2"', 'unit_price' => 150.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Plumbing Installation Labor (45%)', 'unit_price' => 6214.05],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2071.35],
                ],
            ],
            [
                'item_number' => 16,
                'item_name' => 'Electrical',
                'volume_or_area' => 'Duplex Electrical Installation',
                'notes' => 'Conduit, THHN wires (3.5mm, 5.5mm, #6, #10), boxes, switches, 10W ceiling lights, breakers & ground rod',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Flexible Hose 1/2"', 'unit_price' => 950.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'PVC Pipe 1"', 'unit_price' => 150.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => 'PVC Elbow 1/2"', 'unit_price' => 25.00],
                    ['quantity' => 11, 'unit' => 'pcs', 'description' => 'PVC Pipe 1/2"', 'unit_price' => 95.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => 'TEE 1/2"', 'unit_price' => 20.00],
                    ['quantity' => 1, 'unit' => 'box', 'description' => 'THHN Wire 3.5mm', 'unit_price' => 4300.00],
                    ['quantity' => 40, 'unit' => 'meters', 'description' => 'THHN Wire 5.5mm', 'unit_price' => 130.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => 'Utility Box', 'unit_price' => 50.00],
                    ['quantity' => 5, 'unit' => 'pcs', 'description' => 'Junction Box', 'unit_price' => 55.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => '2 Gang Outlet', 'unit_price' => 200.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => 'Receptacle', 'unit_price' => 25.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => '2 Gang Switch', 'unit_price' => 200.00],
                    ['quantity' => 8, 'unit' => 'pcs', 'description' => '10W Ceiling Lights', 'unit_price' => 620.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => '3 Gang Switch', 'unit_price' => 250.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Single Switch', 'unit_price' => 60.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Breaker 20A', 'unit_price' => 790.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Breaker 30A', 'unit_price' => 790.00],
                    ['quantity' => 3, 'unit' => 'pcs', 'description' => 'Breaker 60A', 'unit_price' => 110.00],
                    ['quantity' => 1, 'unit' => 'length', 'description' => 'Ground Rod', 'unit_price' => 990.00],
                    ['quantity' => 1, 'unit' => 'length', 'description' => 'RSC Pipe', 'unit_price' => 700.00],
                    ['quantity' => 19, 'unit' => 'meters', 'description' => 'THHN Wire #6 BLK', 'unit_price' => 155.00],
                    ['quantity' => 19, 'unit' => 'meters', 'description' => 'THHN Wire #6 WHITE', 'unit_price' => 155.00],
                    ['quantity' => 1, 'unit' => 'roll', 'description' => 'THHN Wire #10 GREEN', 'unit_price' => 7300.00],
                    ['quantity' => 1, 'unit' => 'pcs', 'description' => 'Meter Socket 1"', 'unit_price' => 550.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Electrical Works Labor (45%)', 'unit_price' => 16247.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 5415.75],
                ],
            ],
            [
                'item_number' => 17,
                'item_name' => 'Painting',
                'volume_or_area' => 'Duplex Painting Finishes',
                'notes' => 'Flat white, concrete primer, brushes, rollers, skim coat, epoxy primer, thinner, Gibson compound, mortaflex & vulca seal',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'pail', 'description' => 'Flat White', 'unit_price' => 2100.00],
                    ['quantity' => 2, 'unit' => 'pail', 'description' => 'Concrete Primer', 'unit_price' => 2100.00],
                    ['quantity' => 4, 'unit' => 'pcs', 'description' => 'Roller #7', 'unit_price' => 120.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Paint Brush 1"', 'unit_price' => 60.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Paint Brush 2"', 'unit_price' => 75.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => 'Paint Brush 4"', 'unit_price' => 115.00],
                    ['quantity' => 2, 'unit' => 'sack', 'description' => 'Skim Coat', 'unit_price' => 500.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => 'Epoxy Primer', 'unit_price' => 950.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => 'Paint Thinner', 'unit_price' => 320.00],
                    ['quantity' => 1, 'unit' => 'bag', 'description' => 'Gibson Compound', 'unit_price' => 345.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => 'Mortaflex', 'unit_price' => 720.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => 'Vulca Seal', 'unit_price' => 1850.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Painting Labor (45%)', 'unit_price' => 7337.25],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation (15%)', 'unit_price' => 2445.75],
                ],
            ],
            [
                'item_number' => 18,
                'item_name' => 'Stairs',
                'volume_or_area' => 'N/A (Single Storey Duplex)',
                'notes' => 'Not applicable for single-storey unit',
                'materials' => [],
                'labors' => [],
                'equipments' => [],
            ],
            [
                'item_number' => 19,
                'item_name' => 'Septic Tank',
                'volume_or_area' => 'Precast Sanitary Septic Tank & Casing',
                'notes' => 'Septic tank set, precast casing, rough plumbing installation to catch basin',
                'materials' => [
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Septic tank', 'unit_price' => 7100.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Precast Septic Tank Casing', 'unit_price' => 3500.00],
                    ['quantity' => 1, 'unit' => 'LS', 'description' => 'Rough Plumbing Installation to Catch Basin', 'unit_price' => 2000.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Septic Tank Labor', 'unit_price' => 5670.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Contingencies and Price Escalation', 'unit_price' => 1890.00],
                ],
            ],
            [
                'item_number' => 20,
                'item_name' => 'Others (Fencing & 5% Taxes)',
                'volume_or_area' => 'Perimeter Fencing & Taxes',
                'notes' => '20.1 Fencing (₱117,300.80) + 20.2 Taxes @ 5% (₱32,199.14)',
                'materials' => [
                    ['quantity' => 65, 'unit' => 'bags', 'description' => '20.1 Fencing - Cement', 'unit_price' => 225.00],
                    ['quantity' => 27, 'unit' => 'pcs', 'description' => '20.1 Fencing - 8 mm Deformed Bar', 'unit_price' => 120.00],
                    ['quantity' => 59, 'unit' => 'pcs', 'description' => '20.1 Fencing - 10 mm Deformed Bar', 'unit_price' => 220.00],
                    ['quantity' => 35, 'unit' => 'pcs', 'description' => '20.1 Fencing - GI Square tube 1.0', 'unit_price' => 390.00],
                    ['quantity' => 4, 'unit' => 'kgs', 'description' => '20.1 Fencing - GI Wire #18', 'unit_price' => 85.00],
                    ['quantity' => 6, 'unit' => 'm³', 'description' => '20.1 Fencing - Mixing Sand', 'unit_price' => 850.00],
                    ['quantity' => 5, 'unit' => 'm³', 'description' => '20.1 Fencing - 3/4 Gravel', 'unit_price' => 1410.00],
                    ['quantity' => 316, 'unit' => 'pcs', 'description' => '20.1 Fencing - 4" CHB', 'unit_price' => 13.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => '20.1 Fencing - Acrylic Thinner', 'unit_price' => 440.00],
                    ['quantity' => 2, 'unit' => 'gal', 'description' => '20.1 Fencing - Epoxy Primer', 'unit_price' => 950.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => '20.1 Fencing - Paint Brush 2"', 'unit_price' => 75.00],
                    ['quantity' => 2, 'unit' => 'pcs', 'description' => '20.1 Fencing - Baby Roller', 'unit_price' => 95.00],
                    ['quantity' => 7, 'unit' => 'pcs', 'description' => '20.1 Fencing - 1" GI Pipe', 'unit_price' => 1300.00],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => '20.2 Taxes @ 5%', 'unit_price' => 32199.14],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '20.1 Fencing Labor', 'unit_price' => 32990.85],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => '20.1 Fencing Contingencies and Price Escalation', 'unit_price' => 10996.95],
                ],
            ],
        ];

        $grandTotal = $this->applyTemplate($project, $template);

        return redirect()->back()->with('success', 'Official 31 m² Housing Unit Duplex Bill of Materials & Cost Estimates (Total ₱' . number_format($grandTotal, 2) . ') loaded successfully!');
    }

    /**
     * 1-Click Load BOM calibrated specifically to the open project's title, scope, floor area, and budget.
     */
    public function loadProjectTemplate($projectId)
    {
        $project = Project::findOrFail($projectId);
        $projectName = $project->title ?: ($project->project_code ?? 'Project');
        $floorArea = $project->floor_area_sqm > 0 ? (float) $project->floor_area_sqm : 80.0;

        // Determine target budget from project's own financial figures
        if ($project->contract_budget > 0) {
            $targetBudget = (float) $project->contract_budget;
        } elseif ($project->estimated_cost > 0) {
            $targetBudget = (float) $project->estimated_cost;
        } elseif ($project->client_budget > 0) {
            $targetBudget = (float) $project->client_budget;
        } elseif ($project->floor_area_sqm > 0) {
            $targetBudget = (float) $project->floor_area_sqm * 28000.0;
        } else {
            $targetBudget = 1831613.80;
        }

        // Base 18-item template total with markups is ₱1,831,613.80
        $baseTotal = 1831613.80;
        $scale = $targetBudget > 0 ? ($targetBudget / $baseTotal) : 1.0;
        $scale = max(0.05, $scale);

        $template = [
            [
                'item_number' => 1,
                'item_name' => 'FOUNDATION AND FOOTING',
                'volume_or_area' => 'Concrete: ' . round(2.61 * $scale, 2) . ' cu.m',
                'notes' => $projectName . ' - Structural excavation, footing rebar, formwork, and concrete pour.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(18 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 16mmx6m Corr. Steel bar', 'unit_price' => 430.00],
                    ['quantity' => max(1, round(31 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => max(1, round(11 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => max(1, round(6 * $scale)), 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => max(1, round(60 * $scale)), 'unit' => 'pcs', 'description' => '2x2x10 Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => max(1, round(5 * $scale)), 'unit' => 'kls', 'description' => 'Assorted sizes Nails', 'unit_price' => 70.00],
                    ['quantity' => round(2.61 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Premix Concrete (3/4in Aggregate 3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => $projectName . ' Consumables', 'unit_price' => round(1500.00 * $scale, 2)],
                ],
                'labors' => [
                    ['quantity' => round(23.90 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Foundation Excavation & Earthworks', 'unit_price' => 420.00],
                    ['quantity' => round(320.00 * $scale, 2), 'unit' => 'kgs', 'description' => 'Footing Rebar Installation & Bending', 'unit_price' => 10.00],
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Staking, Batterboards & Layout', 'unit_price' => round(2000.00 * $scale, 2)],
                    ['quantity' => round(2.61 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Foundation Concrete Pouring & Tamping', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Concrete Vibrator & Compactor Rental', 'unit_price' => round(3500.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 2,
                'item_name' => 'COLUMNS',
                'volume_or_area' => 'Volume: ' . round(3.46 * $scale, 2) . ' cu.m',
                'notes' => $projectName . ' - Reinforced concrete columns, ties, formworks and pouring.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(16 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 16mmx6m Corr. Steel bar', 'unit_price' => 430.00],
                    ['quantity' => max(1, round(20 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 12mmx6m Corr. Steel bar', 'unit_price' => 240.00],
                    ['quantity' => max(1, round(14 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => max(1, round(100 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => max(1, round(15 * $scale)), 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => round(3.5 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Premix Concrete (3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => max(1, round(6 * $scale)), 'unit' => 'sheets', 'description' => 'Phenolic board 3/8x4x8', 'unit_price' => 1300.00],
                    ['quantity' => max(1, round(100 * $scale)), 'unit' => 'pcs', 'description' => '2x2x10 Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => max(1, round(12 * $scale)), 'unit' => 'kls', 'description' => 'Assorted sizes Nails', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => round(44.30 * $scale, 2), 'unit' => 'sq.m', 'description' => 'Column Formworks Fabrication & Stripping', 'unit_price' => 350.00],
                    ['quantity' => round(607.00 * $scale, 2), 'unit' => 'kgs', 'description' => 'Column Rebar Assembly & Ties', 'unit_price' => 10.00],
                    ['quantity' => round(3.46 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Column Concrete Pouring & Scaffolding', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Column Scaffoldings & Support Equipment', 'unit_price' => round(6148.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 3,
                'item_name' => 'BEAMS',
                'volume_or_area' => 'Volume: ' . round(2.07 * $scale, 2) . ' cu.m',
                'notes' => $projectName . ' - Roof beams, tie beams framing, formworks and concrete.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(43 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => max(1, round(50 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => max(1, round(9 * $scale)), 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => round(2.07 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Premix Concrete (3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => max(1, round(5 * $scale)), 'unit' => 'sheets', 'description' => 'Phenolic board 3/8x4x8', 'unit_price' => 1300.00],
                    ['quantity' => max(1, round(60 * $scale)), 'unit' => 'pcs', 'description' => '2x2x10 Coco Lumber', 'unit_price' => 100.00],
                    ['quantity' => max(1, round(8 * $scale)), 'unit' => 'kls', 'description' => 'Assorted sizes Nails', 'unit_price' => 70.00],
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Beam Shoring Consumables', 'unit_price' => round(1500.00 * $scale, 2)],
                ],
                'labors' => [
                    ['quantity' => round(31.67 * $scale, 2), 'unit' => 'sq.m', 'description' => 'Beam Formworks & Shoring', 'unit_price' => 350.00],
                    ['quantity' => round(336.28 * $scale, 2), 'unit' => 'kgs', 'description' => 'Beam Rebar Assembly & Stirrups', 'unit_price' => 10.00],
                    ['quantity' => round(2.07 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Beam Concrete Pouring', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Beam Equipment Expense', 'unit_price' => round(3773.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 4,
                'item_name' => 'SLAB ON FILL',
                'volume_or_area' => 'Volume: ' . round(3.90 * $scale, 2) . ' cu.m (' . $floorArea . ' sq.m footprint)',
                'notes' => $projectName . ' - Earth fill, gravel bedding, rebar mesh, and slab topping.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(25 * $scale)), 'unit' => 'cu.m', 'description' => 'Base Coarse / Backfill Bedding', 'unit_price' => 700.00],
                    ['quantity' => round(3.9 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Premix Concrete (3000 psi)', 'unit_price' => 4500.00],
                    ['quantity' => max(1, round(32 * $scale)), 'unit' => 'lghts', 'description' => $projectName . ' - 9mmx6m Corr. Steel Bars', 'unit_price' => 120.00],
                    ['quantity' => max(1, round(5 * $scale)), 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                ],
                'labors' => [
                    ['quantity' => round(25.00 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Backfilling, Leveling & Compaction', 'unit_price' => 350.00],
                    ['quantity' => round(98.00 * $scale, 2), 'unit' => 'kgs', 'description' => 'Slab Rebar Mesh Laying', 'unit_price' => 10.00],
                    ['quantity' => round(3.9 * $scale, 2), 'unit' => 'cu.m', 'description' => 'Slab Pouring, Screeding & Trowel Finish', 'unit_price' => 1800.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Plate Compactor & Power Trowel Rental', 'unit_price' => round(3924.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 5,
                'item_name' => 'CHB WALLS',
                'volume_or_area' => 'Total Area: ' . round(196.40 * $scale, 1) . ' sq.m',
                'notes' => $projectName . ' - 4" Concrete Hollow Blocks for exterior and interior masonry partitions.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(2500 * $scale)), 'unit' => 'pcs', 'description' => '4" Concrete Hollow Blocks (CHB)', 'unit_price' => 15.00],
                    ['quantity' => max(1, round(120 * $scale)), 'unit' => 'lghts', 'description' => '10mmx6m Corr. Steel Bars', 'unit_price' => 168.00],
                    ['quantity' => max(1, round(10 * $scale)), 'unit' => 'kls', 'description' => '#18 G.I Tie Wire', 'unit_price' => 70.00],
                    ['quantity' => max(1, round(150 * $scale)), 'unit' => 'bags', 'description' => 'Portland Cement (Type 1)', 'unit_price' => 240.00],
                    ['quantity' => max(1, round(15 * $scale)), 'unit' => 'cu.m', 'description' => 'Fine Washed Sand', 'unit_price' => 1000.00],
                ],
                'labors' => [
                    ['quantity' => round(196.40 * $scale, 2), 'unit' => 'sq.m', 'description' => 'CHB Laying, Mortar Filling & Jointing', 'unit_price' => 220.00],
                    ['quantity' => round(376.80 * $scale, 2), 'unit' => 'kgs', 'description' => 'Wall Horizontal & Vertical Reinforcements', 'unit_price' => 10.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Masonry Scaffolding & Tools', 'unit_price' => round(9387.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 6,
                'item_name' => 'ROOF TRUSSES',
                'volume_or_area' => 'Roof Span: ' . round(110.0 * $scale, 1) . ' sq.m coverage',
                'notes' => $projectName . ' - Structural steel roof framing, angle bars, and C-purlins.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(45 * $scale)), 'unit' => 'lghts', 'description' => '2"x2"x1/4" Angle Bar', 'unit_price' => 950.00],
                    ['quantity' => max(1, round(35 * $scale)), 'unit' => 'lghts', 'description' => '1 1/2"x1 1/2"x3/16" Angle Bar', 'unit_price' => 680.00],
                    ['quantity' => max(1, round(65 * $scale)), 'unit' => 'lghts', 'description' => '2"x4"x1.5mm C-Purlins', 'unit_price' => 520.00],
                    ['quantity' => max(1, round(25 * $scale)), 'unit' => 'boxes', 'description' => 'Welding Rod E6013', 'unit_price' => 380.00],
                    ['quantity' => max(1, round(6 * $scale)), 'unit' => 'gal', 'description' => 'Red Oxide / Epoxy Primer', 'unit_price' => 850.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Steel Truss Fabrication & Erection Labor', 'unit_price' => round(42000.00 * $scale, 2)],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Inverter Welding Machine & Cutting Rig', 'unit_price' => round(8500.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 7,
                'item_name' => 'ROOFING & FLASHING',
                'volume_or_area' => 'Area: ' . round(120.0 * $scale, 1) . ' sq.m',
                'notes' => $projectName . ' - 0.40mm Prepainted Longspan Rib-Type roofing, ridge rolls, and gutter.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(120 * $scale)), 'unit' => 'ln.m', 'description' => '0.40mm Longspan Rib-Type Roof Sheets', 'unit_price' => 450.00],
                    ['quantity' => max(1, round(18 * $scale)), 'unit' => 'pcs', 'description' => '0.40mm Prepainted Ridge Roll', 'unit_price' => 380.00],
                    ['quantity' => max(1, round(14 * $scale)), 'unit' => 'pcs', 'description' => '0.40mm Prepainted Valley & End Flashing', 'unit_price' => 380.00],
                    ['quantity' => max(1, round(800 * $scale)), 'unit' => 'pcs', 'description' => '2 1/2" Tekscrew for Metal', 'unit_price' => 2.50],
                    ['quantity' => max(1, round(6 * $scale)), 'unit' => 'tubes', 'description' => 'Elastomeric Sealant / Vulca Seal', 'unit_price' => 280.00],
                ],
                'labors' => [
                    ['quantity' => round(120.0 * $scale, 2), 'unit' => 'sq.m', 'description' => 'Roofing Installation & Flashing Sealing Labor', 'unit_price' => 180.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Fall Protection & Roofing Equipment', 'unit_price' => round(4200.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 8,
                'item_name' => 'CEILING SYSTEM',
                'volume_or_area' => 'Area: ' . round(85.0 * $scale, 1) . ' sq.m',
                'notes' => $projectName . ' - 4.5mm Hardiflex fiber cement board on metal furring ceiling joists.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(32 * $scale)), 'unit' => 'sheets', 'description' => '4.5mm x 4ft x 8ft Hardiflex Fiber Cement Board', 'unit_price' => 480.00],
                    ['quantity' => max(1, round(110 * $scale)), 'unit' => 'pcs', 'description' => '0.50mm x 19mm x 50mm Metal Furring', 'unit_price' => 150.00],
                    ['quantity' => max(1, round(22 * $scale)), 'unit' => 'pcs', 'description' => '0.60mm x 12mm x 38mm Carrying Channel', 'unit_price' => 180.00],
                    ['quantity' => max(1, round(24 * $scale)), 'unit' => 'pcs', 'description' => 'Wall Angle 25mm x 25mm', 'unit_price' => 85.00],
                    ['quantity' => max(1, round(1500 * $scale)), 'unit' => 'pcs', 'description' => 'Blind Rivets & Drywall Screws', 'unit_price' => 1.50],
                ],
                'labors' => [
                    ['quantity' => round(85.0 * $scale, 2), 'unit' => 'sq.m', 'description' => 'Ceiling Framing & Board Installation Labor', 'unit_price' => 240.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Ceiling Scaffoldings & Power Tools', 'unit_price' => round(4800.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 9,
                'item_name' => 'FLOOR FINISHES',
                'volume_or_area' => 'Area: ' . round(75.0 * $scale, 1) . ' sq.m',
                'notes' => $projectName . ' - 60x60cm Polished Granite Tiles and 30x30cm Non-Skid Bath Tiles.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(180 * $scale)), 'unit' => 'pcs', 'description' => '60cm x 60cm Polished Vitrified Floor Tiles', 'unit_price' => 240.00],
                    ['quantity' => max(1, round(90 * $scale)), 'unit' => 'pcs', 'description' => '30cm x 30cm Non-Skid Bathroom Tiles', 'unit_price' => 45.00],
                    ['quantity' => max(1, round(28 * $scale)), 'unit' => 'bags', 'description' => 'Heavy-Duty Tile Adhesive (25kg)', 'unit_price' => 280.00],
                    ['quantity' => max(1, round(12 * $scale)), 'unit' => 'bags', 'description' => 'Tile Grout (2kg)', 'unit_price' => 90.00],
                ],
                'labors' => [
                    ['quantity' => round(75.0 * $scale, 2), 'unit' => 'sq.m', 'description' => 'Tile Setting, Cutting & Grouting Labor', 'unit_price' => 280.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Tile Cutter & Leveling Spacers', 'unit_price' => round(3800.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 10,
                'item_name' => 'WALL FINISHES & PLASTERING',
                'volume_or_area' => 'Area: ' . round(196.0 * $scale, 1) . ' sq.m',
                'notes' => $projectName . ' - Two-coat cement plastering, grooving and accent stonework.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(85 * $scale)), 'unit' => 'bags', 'description' => 'Portland Cement (Type 1P)', 'unit_price' => 235.00],
                    ['quantity' => max(1, round(12 * $scale)), 'unit' => 'cu.m', 'description' => 'Fine Sifted Plaster Sand', 'unit_price' => 1050.00],
                    ['quantity' => max(1, round(10 * $scale)), 'unit' => 'sq.m', 'description' => $projectName . ' Accent Wall Cladding / Grooving', 'unit_price' => 1200.00],
                ],
                'labors' => [
                    ['quantity' => round(196.0 * $scale, 2), 'unit' => 'sq.m', 'description' => 'Interior & Exterior Wall Plastering Labor', 'unit_price' => 170.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Plastering Platform & Finishing Tools', 'unit_price' => round(3200.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 11,
                'item_name' => 'DOORS & HARDWARE',
                'volume_or_area' => 'Doors: ' . max(4, round(6 * $scale)) . ' Total Sets',
                'notes' => $projectName . ' - Main entrance panel door, flush interior doors, PVC toilet doors.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => 1, 'unit' => 'set', 'description' => $projectName . ' Main Entrance Solid Panel Door (0.90x2.10m) with Jamb', 'unit_price' => 9500.00],
                    ['quantity' => max(1, round(3 * $scale)), 'unit' => 'sets', 'description' => 'Bedroom Flush Wood Doors (0.80x2.10m) with Jamb', 'unit_price' => 4800.00],
                    ['quantity' => max(1, round(2 * $scale)), 'unit' => 'sets', 'description' => 'PVC Bathroom Doors with Louvers (0.60x2.10m)', 'unit_price' => 2200.00],
                    ['quantity' => max(1, round(6 * $scale)), 'unit' => 'sets', 'description' => 'Heavy-Duty Cylindrical Locksets & Hinges', 'unit_price' => 850.00],
                ],
                'labors' => [
                    ['quantity' => max(4, round(6 * $scale)), 'unit' => 'sets', 'description' => 'Door Jamb Alignment & Hanging Labor', 'unit_price' => 1200.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Carpentry Installation Tools', 'unit_price' => round(2500.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 12,
                'item_name' => 'WINDOWS & GLAZING',
                'volume_or_area' => 'Windows: ' . max(5, round(8 * $scale)) . ' Units',
                'notes' => $projectName . ' - Powder-coated aluminum frame sliding and awning glass windows.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(2 * $scale)), 'unit' => 'sets', 'description' => '1.20m x 1.50m Sliding Glass Window (Aluminum Frame)', 'unit_price' => 6800.00],
                    ['quantity' => max(1, round(4 * $scale)), 'unit' => 'sets', 'description' => '1.20m x 1.20m Sliding Glass Window (Aluminum Frame)', 'unit_price' => 5400.00],
                    ['quantity' => max(1, round(2 * $scale)), 'unit' => 'sets', 'description' => '0.60m x 0.60m Awning Bath Window (Aluminum Frame)', 'unit_price' => 2400.00],
                ],
                'labors' => [
                    ['quantity' => max(5, round(8 * $scale)), 'unit' => 'sets', 'description' => 'Window Installation & Silicone Caulking Labor', 'unit_price' => 950.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Glazing Alignment Equipment', 'unit_price' => round(2000.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 13,
                'item_name' => 'KITCHEN COUNTER',
                'volume_or_area' => 'Counter: ' . round(2.4 * $scale, 1) . ' ln.m',
                'notes' => $projectName . ' - Reinforced concrete slab with polished granite counter, stainless sink and base cabinets.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => 1, 'unit' => 'lot', 'description' => $projectName . ' Natural Granite Countertop Slab', 'unit_price' => round(12500.00 * $scale, 2)],
                    ['quantity' => 1, 'unit' => 'set', 'description' => 'Single Bowl Stainless Steel Kitchen Sink with Gooseneck Faucet', 'unit_price' => 3800.00],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => 'Marine Plywood Under-Counter Cabinets with Concealed Hinges', 'unit_price' => round(11000.00 * $scale, 2)],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Kitchen Counter Masonry & Cabinetry Labor', 'unit_price' => round(9500.00 * $scale, 2)],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Granite Cutting & Polishing Equipment', 'unit_price' => round(2200.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 14,
                'item_name' => 'PLUMBING & SANITARY SYSTEM',
                'volume_or_area' => 'Complete Rough-in & Fixtures',
                'notes' => $projectName . ' - PVC sanitary/drain lines, PPR waterline distribution, water closet, and bathroom fixtures.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(12 * $scale)), 'unit' => 'pcs', 'description' => '4" x 10ft PVC Sanitary Pipe (Series 1000)', 'unit_price' => 450.00],
                    ['quantity' => max(1, round(10 * $scale)), 'unit' => 'pcs', 'description' => '2" x 10ft PVC Sanitary Pipe', 'unit_price' => 240.00],
                    ['quantity' => max(1, round(14 * $scale)), 'unit' => 'pcs', 'description' => '1/2" PPR Hot/Cold Waterline Pipes', 'unit_price' => 220.00],
                    ['quantity' => max(1, round(2 * $scale)), 'unit' => 'sets', 'description' => 'Dual-Flush Water Closet with Tank & Fittings', 'unit_price' => 6800.00],
                    ['quantity' => max(1, round(2 * $scale)), 'unit' => 'sets', 'description' => 'Wall-Hung Ceramic Lavatory with Chrome Faucet', 'unit_price' => 3200.00],
                    ['quantity' => max(1, round(2 * $scale)), 'unit' => 'sets', 'description' => 'Stainless Shower Set with Valve', 'unit_price' => 1850.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Plumbing Rough-in & Fixture Installation Labor', 'unit_price' => round(24000.00 * $scale, 2)],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'PPR Fusion Machine & Hydrotesting Pump', 'unit_price' => round(3800.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 15,
                'item_name' => 'ELECTRICAL SYSTEM',
                'volume_or_area' => 'Complete Rough-in, Panel & Fixtures',
                'notes' => $projectName . ' - PVC conduits, THHN copper wires, 8-branch panelboard, LED fixtures, and convenience outlets.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(40 * $scale)), 'unit' => 'pcs', 'description' => '1/2" x 10ft Electrical PVC Conduit Pipes', 'unit_price' => 110.00],
                    ['quantity' => max(1, round(4 * $scale)), 'unit' => 'boxes', 'description' => '3.5mm² (AWG #12) THHN Stranded Copper Wire (150m)', 'unit_price' => 4600.00],
                    ['quantity' => max(1, round(3 * $scale)), 'unit' => 'boxes', 'description' => '2.0mm² (AWG #14) THHN Stranded Copper Wire (150m)', 'unit_price' => 3200.00],
                    ['quantity' => 1, 'unit' => 'set', 'description' => $projectName . ' 8-Branch Main Breaker Panelboard (Plug-in)', 'unit_price' => 4500.00],
                    ['quantity' => max(1, round(22 * $scale)), 'unit' => 'sets', 'description' => '9W LED Recessed Downlights & Switches/Outlets', 'unit_price' => 420.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Electrical Roughing-in, Wiring & Testing Labor', 'unit_price' => round(28000.00 * $scale, 2)],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Multi-Tester, Insulation Megger & Pulling Gear', 'unit_price' => round(3500.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 16,
                'item_name' => 'PAINTING WORKS',
                'volume_or_area' => 'Total Area: ' . round(320.0 * $scale, 1) . ' sq.m',
                'notes' => $projectName . ' - Concrete primer sealer, skimcoat putty, 2-coat acrylic latex topcoats.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(6 * $scale)), 'unit' => 'pails', 'description' => 'Acrylic Concrete Primer Sealer (16L)', 'unit_price' => 2600.00],
                    ['quantity' => max(1, round(8 * $scale)), 'unit' => 'pails', 'description' => 'Semi-Gloss Latex Topcoat Paint (16L)', 'unit_price' => 3100.00],
                    ['quantity' => max(1, round(12 * $scale)), 'unit' => 'bags', 'description' => 'Skimcoat Masonry Finishing Powder (20kg)', 'unit_price' => 480.00],
                    ['quantity' => max(1, round(14 * $scale)), 'unit' => 'pcs', 'description' => 'Paint Rollers, Brushes & Sanding Paper', 'unit_price' => 120.00],
                ],
                'labors' => [
                    ['quantity' => round(320.0 * $scale, 2), 'unit' => 'sq.m', 'description' => 'Surface Preparation, Sanding & 3-Coat Paint Application', 'unit_price' => 110.00],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Painters Scaffolding & Drop Cloths', 'unit_price' => round(3200.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 17,
                'item_name' => 'SEPTIC TANK & DRAINAGE',
                'volume_or_area' => '1 Complete Sanitary Vault Unit',
                'notes' => $projectName . ' - 3-Chamber reinforced septic vault with heavy-duty inspection manholes.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => max(1, round(350 * $scale)), 'unit' => 'pcs', 'description' => '5" Heavy-Duty CHB for Septic Vault', 'unit_price' => 18.00],
                    ['quantity' => max(1, round(24 * $scale)), 'unit' => 'bags', 'description' => 'Waterproofed Cement Mix', 'unit_price' => 260.00],
                    ['quantity' => max(1, round(18 * $scale)), 'unit' => 'lghts', 'description' => '10mm Deformed Steel Rebar', 'unit_price' => 168.00],
                    ['quantity' => max(1, round(2 * $scale)), 'unit' => 'sets', 'description' => 'Cast Iron / Concrete Manhole Covers', 'unit_price' => 2200.00],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Septic Vault Excavation, Masonry & Waterproofing Labor', 'unit_price' => round(16500.00 * $scale, 2)],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Submersible De-watering Pump & Excavation Tools', 'unit_price' => round(2800.00 * $scale, 2)],
                ],
            ],
            [
                'item_number' => 18,
                'item_name' => 'MISCELLANEOUS & ENGINEERING OVERHEAD',
                'volume_or_area' => 'Project Duration Provision',
                'notes' => $projectName . ' - Temporary site facilities, water/power utilities, safety gear, and final turnover cleaning.',
                'contingency_percent' => 15.00,
                'taxes_percent' => 6.00,
                'profit_percent' => 10.00,
                'materials' => [
                    ['quantity' => 1, 'unit' => 'lot', 'description' => $projectName . ' Personal Protective Equipment (PPE) & First Aid', 'unit_price' => round(6500.00 * $scale, 2)],
                    ['quantity' => 1, 'unit' => 'lot', 'description' => 'Temporary Site Bunkhouse, Storage Enclosure & Signage', 'unit_price' => round(12000.00 * $scale, 2)],
                ],
                'labors' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Site Security, Waste Hauling & Final Turnover Deep Cleaning', 'unit_price' => round(14000.00 * $scale, 2)],
                ],
                'equipments' => [
                    ['quantity' => 1, 'unit' => 'Lump Sum', 'description' => 'Safety Harnesses, Barricades & Site Utilities Provision', 'unit_price' => round(4500.00 * $scale, 2)],
                ],
            ],
        ];

        $grandTotal = $this->applyTemplate($project, $template);

        return redirect()->back()->with('success', 'Official Itemized Bill of Materials & DUPA for "' . $projectName . '" (Total ₱' . number_format($grandTotal, 2) . ') generated and matched successfully!');
    }

    /**
     * Backward compatibility alias for 2BR Bungalow Template.
     */
    public function loadBungalowTemplate($projectId)
    {
        return $this->loadProjectTemplate($projectId);
    }

    /**
     * Official Printable Bill of Materials and Cost Estimates Document.
     */
    public function printBom($projectId)
    {
        $project = Project::with(['scopeItems.lines', 'personnel'])->findOrFail($projectId);
        $scopeItems = $project->scopeItems;
        $grandTotal = $project->grand_scope_cost;

        // Determine certified engineer details from project or personnel
        $civilEngineer = $project->personnel->firstWhere('title', 'Registered Civil Engineer') 
            ?? $project->personnel->firstWhere('license_no', '0180490')
            ?? $project->personnel->firstWhere('license_no', '0042019');

        $isDuplex = str_contains(strtolower($project->title), 'duplex') || str_contains(strtolower($project->title), '31 m');
        
        $certName = $civilEngineer ? $civilEngineer->name : ($isDuplex ? 'ENGR. IGNACIO S. LONZAGA' : 'ENGR. ESABYL B. MITRA');
        $certTitle = 'REGISTERED CIVIL ENGINEER';
        $certLicense = $civilEngineer ? $civilEngineer->license_no : ($isDuplex ? '0042019' : '0180490');
        $certPtr = $isDuplex ? '2901354' : '4531999';
        $certDate = $isDuplex ? '01-10-2024' : '01-07-2025';
        $certPlace = 'SILAY CITY';

        $ownerName = 'ENGR. IGNACIO S. LONZAGA';
        $ownerTitle = 'OWNER / DEVELOPER';

        return view('projects.print_bom', compact(
            'project',
            'scopeItems',
            'grandTotal',
            'certName',
            'certTitle',
            'certLicense',
            'certPtr',
            'certDate',
            'certPlace',
            'ownerName',
            'ownerTitle'
        ));
    }
}
