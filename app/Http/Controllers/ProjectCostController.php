<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCost;
use Illuminate\Http\Request;

class ProjectCostController extends Controller
{
    public function index(Request $request)
    {
        $selectedProjectId = $request->query('project_id');
        $selectedCategory = $request->query('category');

        $allProjects = Project::with(['costs', 'projectMaterials.material', 'tasks'])
            ->orderBy('status', 'asc')
            ->orderBy('title', 'asc')
            ->get();

        $selectedProject = null;
        if ($selectedProjectId) {
            $selectedProject = Project::with(['costs', 'projectMaterials.material', 'tasks.assignedPersonnel', 'personnel', 'payments'])->find($selectedProjectId);
        }

        // Queries for cost items
        $costsQuery = ProjectCost::with('project')->orderBy('cost_date', 'desc');

        if ($selectedProject) {
            $costsQuery->where('project_id', $selectedProject->id);
        }

        if ($selectedCategory && $selectedCategory !== 'All') {
            $costsQuery->where('cost_category', $selectedCategory);
        }

        $costItems = $costsQuery->get();

        // High-level company-wide costing analytics
        $totalSystemBudget = $allProjects->sum('contract_budget');
        $totalSystemEstimatedCost = ProjectCost::sum('estimated_cost');
        $totalSystemActualCost = ProjectCost::sum('actual_cost');
        $totalSystemGrossMargin = max(0, $totalSystemBudget - $totalSystemActualCost);
        $totalSystemMarginPercent = $totalSystemBudget > 0 ? round(($totalSystemGrossMargin / $totalSystemBudget) * 100, 1) : 0;
        
        $totalFloorArea = $allProjects->sum('floor_area_sqm');
        $avgSystemCostPerSqm = $totalFloorArea > 0 ? round($totalSystemActualCost / $totalFloorArea, 2) : 0;

        // Categories list
        $categories = [
            'Materials & Consumables',
            'Labor & Engineering',
            'Equipment & Heavy Machinery',
            'Subcontractor & Trade',
            'Permits & Regulatory',
            'Site Overhead & Utilities',
            'Contingency & Testing',
        ];

        return view('costing.index', compact(
            'allProjects',
            'selectedProject',
            'costItems',
            'totalSystemBudget',
            'totalSystemEstimatedCost',
            'totalSystemActualCost',
            'totalSystemGrossMargin',
            'totalSystemMarginPercent',
            'avgSystemCostPerSqm',
            'categories',
            'selectedCategory'
        ));
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'cost_category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'cost_type' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_rate' => 'required|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:budgeted,committed,incurred,settled',
            'cost_date' => 'required|date',
            'vendor_payee' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Calculate defaults
        $quantity = (float) $validated['quantity'];
        $unitRate = (float) $validated['unit_rate'];
        $computedCost = $quantity * $unitRate;

        $validated['project_id'] = $project->id;
        $validated['cost_code'] = 'CST-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        $validated['estimated_cost'] = isset($validated['estimated_cost']) && $validated['estimated_cost'] !== null 
            ? (float) $validated['estimated_cost'] 
            : $computedCost;
        $validated['actual_cost'] = isset($validated['actual_cost']) && $validated['actual_cost'] !== null 
            ? (float) $validated['actual_cost'] 
            : $computedCost;

        $cost = ProjectCost::create($validated);

        // Synchronize project spent_budget
        $totalIncurred = $project->costs()->sum('actual_cost');
        if ($totalIncurred > 0) {
            $project->spent_budget = $totalIncurred;
            $project->save();
        }

        return redirect()->back()->with('success', 'Cost item [' . $cost->cost_code . '] recorded successfully for ' . $project->project_code . '!');
    }

    public function update(Request $request, $costId)
    {
        $cost = ProjectCost::findOrFail($costId);

        $validated = $request->validate([
            'cost_category' => 'required|string',
            'item_name' => 'required|string|max:255',
            'cost_type' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'required|string|max:50',
            'unit_rate' => 'required|numeric|min:0',
            'estimated_cost' => 'required|numeric|min:0',
            'actual_cost' => 'required|numeric|min:0',
            'status' => 'required|in:budgeted,committed,incurred,settled',
            'cost_date' => 'required|date',
            'vendor_payee' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $cost->update($validated);

        // Synchronize project spent_budget
        $project = $cost->project;
        $totalIncurred = $project->costs()->sum('actual_cost');
        if ($totalIncurred > 0) {
            $project->spent_budget = $totalIncurred;
            $project->save();
        }

        return redirect()->back()->with('success', 'Project cost item updated successfully!');
    }

    public function destroy($costId)
    {
        $cost = ProjectCost::findOrFail($costId);
        $project = $cost->project;
        $cost->delete();

        // Synchronize project spent_budget
        $totalIncurred = $project->costs()->sum('actual_cost');
        $project->spent_budget = $totalIncurred;
        $project->save();

        return redirect()->back()->with('success', 'Cost item removed successfully.');
    }

    public function autoSync(Request $request, $projectId)
    {
        $project = Project::with(['projectMaterials.material', 'tasks'])->findOrFail($projectId);

        $syncedCount = 0;

        // 1. Sync BOM Material usage into ProjectCost
        foreach ($project->projectMaterials as $pm) {
            $existing = ProjectCost::where('project_id', $project->id)
                ->where('reference_no', 'BOM-MAT-' . $pm->material_id)
                ->first();

            $estimatedCost = $pm->allocated_qty * $pm->unit_price;
            $actualCost = $pm->used_qty * $pm->unit_price;

            if ($existing) {
                $existing->update([
                    'quantity' => $pm->used_qty > 0 ? $pm->used_qty : $pm->allocated_qty,
                    'unit_rate' => $pm->unit_price,
                    'estimated_cost' => $estimatedCost,
                    'actual_cost' => $actualCost,
                    'status' => $pm->used_qty >= $pm->allocated_qty ? 'settled' : 'incurred',
                ]);
            } else {
                ProjectCost::create([
                    'project_id' => $project->id,
                    'cost_code' => 'CST-BOM-' . strtoupper(substr(uniqid(), -4)),
                    'cost_category' => 'Materials & Consumables',
                    'item_name' => $pm->material->name . ' (' . $pm->material->category . ')',
                    'cost_type' => 'Direct',
                    'quantity' => $pm->used_qty > 0 ? $pm->used_qty : $pm->allocated_qty,
                    'unit' => $pm->material->unit,
                    'unit_rate' => $pm->unit_price,
                    'estimated_cost' => $estimatedCost,
                    'actual_cost' => $actualCost,
                    'status' => $pm->used_qty > 0 ? 'incurred' : 'budgeted',
                    'cost_date' => now()->toDateString(),
                    'vendor_payee' => 'Central Warehouse Inventory',
                    'reference_no' => 'BOM-MAT-' . $pm->material_id,
                    'notes' => 'Auto-synchronized from Project Bill of Materials (BOM)',
                ]);
                $syncedCount++;
            }
        }

        // 2. Sync Scheduled Task costs into ProjectCost
        foreach ($project->tasks as $t) {
            $existing = ProjectCost::where('project_id', $project->id)
                ->where('reference_no', 'TASK-' . $t->id)
                ->first();

            $cat = match ($t->category) {
                'Structural' => 'Labor & Engineering',
                'Electrical' => 'Labor & Engineering',
                'Piping' => 'Labor & Engineering',
                default => 'Labor & Engineering',
            };

            if ($existing) {
                $existing->update([
                    'estimated_cost' => $t->allocated_budget,
                    'actual_cost' => $t->actual_cost,
                    'status' => $t->status === 'completed' ? 'settled' : 'incurred',
                ]);
            } else {
                ProjectCost::create([
                    'project_id' => $project->id,
                    'cost_code' => 'CST-TSK-' . strtoupper(substr(uniqid(), -4)),
                    'cost_category' => $cat,
                    'item_name' => $t->task_name . ' (' . $t->category . ')',
                    'cost_type' => 'Direct',
                    'quantity' => 1,
                    'unit' => 'lot',
                    'unit_rate' => $t->allocated_budget,
                    'estimated_cost' => $t->allocated_budget,
                    'actual_cost' => $t->actual_cost,
                    'status' => $t->status === 'completed' ? 'settled' : ($t->status === 'in_progress' ? 'incurred' : 'budgeted'),
                    'cost_date' => $t->start_date ? $t->start_date->toDateString() : now()->toDateString(),
                    'vendor_payee' => $t->assignedPersonnel ? $t->assignedPersonnel->name : 'Site Construction Crew',
                    'reference_no' => 'TASK-' . $t->id,
                    'notes' => 'Auto-synchronized from Project Task Schedule',
                ]);
                $syncedCount++;
            }
        }

        // Update project spent budget
        $totalIncurred = $project->costs()->sum('actual_cost');
        if ($totalIncurred > 0) {
            $project->spent_budget = $totalIncurred;
            $project->save();
        }

        return redirect()->back()->with('success', 'Synchronized ' . $syncedCount . ' items from BOM and Tasks to Project Costing Ledger!');
    }
}
