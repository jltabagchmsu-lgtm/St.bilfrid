<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Personnel;
use App\Models\Material;
use App\Models\ProjectMaterial;
use App\Models\ProjectTask;
use App\Models\Payment;
use App\Models\ProjectPhoto;
use App\Models\ProjectCost;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::whereIn('status', ['in_progress', 'on_hold', 'approved'])
            ->with(['personnel', 'tasks', 'payments', 'projectMaterials.material', 'costs', 'photos', 'primaryPhoto'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $personnelList = Personnel::orderBy('name')->get();

        return view('projects.index', compact('projects', 'personnelList'));
    }

    public function create()
    {
        $personnel = Personnel::orderBy('name')->get();
        return view('projects.create', compact('personnel'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_code' => 'nullable|string|max:100',
            'client_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'project_type' => 'nullable|string',
            'status' => 'nullable|string|in:in_progress,approved,on_hold,completed',
            'current_phase' => 'nullable|string',
            'land_area_sqm' => 'required|numeric|min:0',
            'floor_area_sqm' => 'required|numeric|min:0',
            'contract_budget' => 'required|numeric|min:0',
            'client_budget' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'finish_tier' => 'nullable|string|in:standard,executive,luxury',
            'financing_type' => 'nullable|string|in:bank_loan,pagibig_loan,cash_equity,combined',
            'financing_institution' => 'nullable|string|max:255',
            'loan_account_no' => 'nullable|string|max:100',
            'approved_loan_amount' => 'nullable|numeric|min:0',
            'client_equity_amount' => 'nullable|numeric|min:0',
            'payment_first_policy' => 'nullable|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'structural_weight' => 'nullable|integer|min:0|max:100',
            'electrical_weight' => 'nullable|integer|min:0|max:100',
            'piping_weight' => 'nullable|integer|min:0|max:100',
            'finishing_weight' => 'nullable|integer|min:0|max:100',
            'structural_progress' => 'nullable|integer|min:0|max:100',
            'electrical_progress' => 'nullable|integer|min:0|max:100',
            'piping_progress' => 'nullable|integer|min:0|max:100',
            'finishing_progress' => 'nullable|integer|min:0|max:100',
            'deployed_workers' => 'nullable|integer|min:0',
            'deployed_skilled_workers' => 'nullable|integer|min:0',
            'deployed_engineers' => 'nullable|integer|min:0',
            'deployed_architects' => 'nullable|integer|min:0',
            'deployed_foremen' => 'nullable|integer|min:0',
            'deployed_operators' => 'nullable|integer|min:0',
            'deployed_safety_officers' => 'nullable|integer|min:0',
        ]);

        $projectCode = !empty($validated['project_code']) ? trim($validated['project_code']) : ('PRJ-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4)));
        $validated['project_code'] = $projectCode;
        $validated['project_type'] = $request->input('project_type', 'Residential Build');
        $validated['status'] = $request->input('status', 'in_progress');
        $validated['spent_budget'] = 0;
        $validated['current_phase'] = $request->input('current_phase', 'Phase 1: Mobilization & Earthworks');
        $validated['financing_type'] = $request->input('financing_type', 'bank_loan');
        $validated['financing_institution'] = $request->input('financing_institution', 'BDO Unibank / Partner Bank');
        $validated['loan_account_no'] = $request->input('loan_account_no', 'LOG-' . date('Y') . '-' . rand(1000, 9999));
        $validated['approved_loan_amount'] = $request->input('approved_loan_amount', ($validated['contract_budget'] * 0.80));
        $validated['client_equity_amount'] = $request->input('client_equity_amount', ($validated['contract_budget'] * 0.20));
        $validated['payment_first_policy'] = (bool) $request->input('payment_first_policy', 1);
        $sW = (int) $request->input('structural_weight');
        $eW = (int) $request->input('electrical_weight');
        $pW = (int) $request->input('piping_weight');
        $fW = (int) $request->input('finishing_weight');
        if (($sW + $eW + $pW + $fW) <= 0) {
            $sW = 40; $eW = 25; $pW = 20; $fW = 15;
        }
        $validated['structural_weight'] = $sW;
        $validated['electrical_weight'] = $eW;
        $validated['piping_weight'] = $pW;
        $validated['finishing_weight'] = $fW;
        $validated['structural_progress'] = $request->input('structural_progress', 0);
        $validated['electrical_progress'] = $request->input('electrical_progress', 0);
        $validated['piping_progress'] = $request->input('piping_progress', 0);
        $validated['finishing_progress'] = $request->input('finishing_progress', 0);
        $validated['deployed_workers'] = $request->input('deployed_workers', 0);
        $validated['deployed_skilled_workers'] = $request->input('deployed_skilled_workers', 0);
        $validated['deployed_engineers'] = $request->input('deployed_engineers', 0);
        $validated['deployed_architects'] = $request->input('deployed_architects', 0);
        $validated['deployed_foremen'] = $request->input('deployed_foremen', 0);
        $validated['deployed_operators'] = $request->input('deployed_operators', 0);
        $validated['deployed_safety_officers'] = $request->input('deployed_safety_officers', 0);

        if ($validated['status'] === 'completed') {
            $validated['actual_completion_date'] = now();
        }

        $project = Project::create($validated);
        $project->overall_progress = $project->calculated_overall_progress;
        $project->save();

        if ($request->has('personnel_ids')) {
            $project->personnel()->sync($request->input('personnel_ids'));
        }

        // Handle project hero image upload if provided during creation
        if ($request->hasFile('project_photo_file')) {
            $file = $request->file('project_photo_file');
            $filename = 'prj_' . $project->id . '_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/projects');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }
            $file->move($destinationPath, $filename);
            $filePath = '/uploads/projects/' . $filename;
            
            ProjectPhoto::create([
                'project_id' => $project->id,
                'photo_type' => $request->input('project_photo_type', '3d_render'),
                'title' => $project->title . ' - Hero Concept Render',
                'description' => 'Primary design reference image uploaded during project creation.',
                'file_path' => $filePath,
                'is_primary' => true,
                'taken_at' => now(),
            ]);
        } elseif ($request->filled('project_photo_url')) {
            ProjectPhoto::create([
                'project_id' => $project->id,
                'photo_type' => $request->input('project_photo_type', '3d_render'),
                'title' => $project->title . ' - Hero Concept Render',
                'description' => 'Primary design reference image URL provided during project creation.',
                'file_path' => $request->input('project_photo_url'),
                'is_primary' => true,
                'taken_at' => now(),
            ]);
        }

        // Initialize Structural, Electrical, Piping, & Finishing Tasks with materials scaled to the project plan
        $project->seedDefaultChecklist();

        $rooms = json_decode($request->input('room_program_json', '[]'), true) ?: [];

        return redirect()->route('projects.show', $project->id)->with('success', 'Project ' . $project->project_code . ' initialized successfully! Tasks and materials aligned to floor plan (' . number_format($project->floor_area_sqm, 2) . ' m²).');
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'project_code' => 'nullable|string|max:100',
            'client_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'project_type' => 'required|string',
            'status' => 'required|string|in:in_progress,approved,on_hold,completed',
            'current_phase' => 'nullable|string|max:255',
            'land_area_sqm' => 'required|numeric|min:0',
            'floor_area_sqm' => 'required|numeric|min:0',
            'contract_budget' => 'required|numeric|min:0',
            'client_budget' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'finish_tier' => 'nullable|string|in:standard,executive,luxury',
            'financing_type' => 'nullable|string|in:bank_loan,pagibig_loan,cash_equity,combined',
            'financing_institution' => 'nullable|string|max:255',
            'loan_account_no' => 'nullable|string|max:100',
            'approved_loan_amount' => 'nullable|numeric|min:0',
            'client_equity_amount' => 'nullable|numeric|min:0',
            'payment_first_policy' => 'nullable|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'actual_completion_date' => 'nullable|date',
            'description' => 'nullable|string',
            'structural_progress' => 'nullable|integer|min:0|max:100',
            'electrical_progress' => 'nullable|integer|min:0|max:100',
            'piping_progress' => 'nullable|integer|min:0|max:100',
            'finishing_progress' => 'nullable|integer|min:0|max:100',
            'structural_weight' => 'nullable|integer|min:0|max:100',
            'electrical_weight' => 'nullable|integer|min:0|max:100',
            'piping_weight' => 'nullable|integer|min:0|max:100',
            'finishing_weight' => 'nullable|integer|min:0|max:100',
            'deployed_workers' => 'nullable|integer|min:0',
            'deployed_skilled_workers' => 'nullable|integer|min:0',
            'deployed_engineers' => 'nullable|integer|min:0',
            'deployed_architects' => 'nullable|integer|min:0',
            'deployed_foremen' => 'nullable|integer|min:0',
            'deployed_operators' => 'nullable|integer|min:0',
            'deployed_safety_officers' => 'nullable|integer|min:0',
        ]);

        if (!empty($validated['project_code'])) {
            $project->project_code = trim($validated['project_code']);
        }
        $project->title = $validated['title'];
        $project->client_name = $validated['client_name'];
        $project->location = $validated['location'];
        $project->project_type = $validated['project_type'];
        $project->status = $validated['status'];
        $project->current_phase = $validated['current_phase'] ?? $project->current_phase;
        $project->land_area_sqm = $validated['land_area_sqm'];
        $project->floor_area_sqm = $validated['floor_area_sqm'];
        $project->contract_budget = $validated['contract_budget'];
        if ($request->has('client_budget')) $project->client_budget = $validated['client_budget'];
        if ($request->has('estimated_cost')) $project->estimated_cost = $validated['estimated_cost'];
        if ($request->has('finish_tier')) $project->finish_tier = $validated['finish_tier'];
        $project->start_date = $validated['start_date'];
        $project->end_date = $validated['end_date'];
        $project->description = $validated['description'];

        if (isset($validated['financing_type'])) $project->financing_type = $validated['financing_type'];
        if (isset($validated['financing_institution'])) $project->financing_institution = $validated['financing_institution'];
        if (isset($validated['loan_account_no'])) $project->loan_account_no = $validated['loan_account_no'];
        if (isset($validated['approved_loan_amount'])) $project->approved_loan_amount = $validated['approved_loan_amount'];
        if (isset($validated['client_equity_amount'])) $project->client_equity_amount = $validated['client_equity_amount'];
        if ($request->has('payment_first_policy')) $project->payment_first_policy = (bool) $request->input('payment_first_policy');

        if (isset($validated['structural_progress'])) $project->structural_progress = $validated['structural_progress'];
        if (isset($validated['electrical_progress'])) $project->electrical_progress = $validated['electrical_progress'];
        if (isset($validated['piping_progress'])) $project->piping_progress = $validated['piping_progress'];
        if (isset($validated['finishing_progress'])) $project->finishing_progress = $validated['finishing_progress'];

        if (isset($validated['structural_weight'])) $project->structural_weight = (int) $validated['structural_weight'];
        if (isset($validated['electrical_weight'])) $project->electrical_weight = (int) $validated['electrical_weight'];
        if (isset($validated['piping_weight'])) $project->piping_weight = (int) $validated['piping_weight'];
        if (isset($validated['finishing_weight'])) $project->finishing_weight = (int) $validated['finishing_weight'];

        if (($project->structural_weight + $project->electrical_weight + $project->piping_weight + $project->finishing_weight) <= 0) {
            $project->structural_weight = 40;
            $project->electrical_weight = 25;
            $project->piping_weight = 20;
            $project->finishing_weight = 15;
        }

        if (isset($validated['deployed_workers'])) $project->deployed_workers = $validated['deployed_workers'];
        if (isset($validated['deployed_skilled_workers'])) $project->deployed_skilled_workers = $validated['deployed_skilled_workers'];
        if (isset($validated['deployed_engineers'])) $project->deployed_engineers = $validated['deployed_engineers'];
        if (isset($validated['deployed_architects'])) $project->deployed_architects = $validated['deployed_architects'];
        if (isset($validated['deployed_foremen'])) $project->deployed_foremen = $validated['deployed_foremen'];
        if (isset($validated['deployed_operators'])) $project->deployed_operators = $validated['deployed_operators'];
        if (isset($validated['deployed_safety_officers'])) $project->deployed_safety_officers = $validated['deployed_safety_officers'];

        if ($validated['status'] === 'completed') {
            $project->actual_completion_date = $validated['actual_completion_date'] ?: ($project->actual_completion_date ?: now());
        } else {
            $project->actual_completion_date = null;
        }

        $project->overall_progress = $project->calculated_overall_progress;
        $project->save();

        if ($request->has('personnel_ids')) {
            $project->personnel()->sync($request->input('personnel_ids'));
        }

        // Handle project hero image upload or update if provided in edit form
        if ($request->hasFile('project_photo_file')) {
            $file = $request->file('project_photo_file');
            $filename = 'prj_' . $project->id . '_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/projects');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }
            $file->move($destinationPath, $filename);
            $filePath = '/uploads/projects/' . $filename;
            
            $primaryPhoto = $project->primaryPhoto ?? $project->photos()->first();
            if ($primaryPhoto) {
                $primaryPhoto->update(['file_path' => $filePath, 'is_primary' => true]);
            } else {
                ProjectPhoto::create([
                    'project_id' => $project->id,
                    'photo_type' => $request->input('project_photo_type', '3d_render'),
                    'title' => $project->title . ' - Hero Concept Render',
                    'file_path' => $filePath,
                    'is_primary' => true,
                    'taken_at' => now(),
                ]);
            }
        } elseif ($request->filled('project_photo_url')) {
            $filePath = $request->input('project_photo_url');
            $primaryPhoto = $project->primaryPhoto ?? $project->photos()->first();
            if ($primaryPhoto) {
                $primaryPhoto->update(['file_path' => $filePath, 'is_primary' => true]);
            } else {
                ProjectPhoto::create([
                    'project_id' => $project->id,
                    'photo_type' => $request->input('project_photo_type', '3d_render'),
                    'title' => $project->title . ' - Hero Concept Render',
                    'file_path' => $filePath,
                    'is_primary' => true,
                    'taken_at' => now(),
                ]);
            }
        }

        return redirect()->route('projects.show', $project->id)->with('success', 'Project ' . $project->project_code . ' updated successfully! Loan & financing specs recalculated.');
    }

    public function destroy($id)
    {
        $project = Project::with(['photos', 'tasks', 'scopeItems.lines', 'projectMaterials', 'costs', 'payments'])->findOrFail($id);
        $projectCode = $project->project_code;
        $projectTitle = $project->title;
        $status = $project->status;

        DB::transaction(function () use ($project) {
            // Delete associated uploaded photo files from disk
            foreach ($project->photos as $photo) {
                if ($photo->file_path && File::exists(public_path($photo->file_path))) {
                    try {
                        File::delete(public_path($photo->file_path));
                    } catch (\Exception $e) {
                        // Ignore file deletion errors if file missing
                    }
                }
                $photo->delete();
            }

            // Delete task materials & tasks
            $taskIds = $project->tasks->pluck('id');
            \App\Models\ProjectTaskMaterial::whereIn('project_task_id', $taskIds)->delete();
            $project->tasks()->delete();

            // Delete inter-project transfers associated with this project
            \App\Models\ProjectMaterialTransfer::where('source_project_id', $project->id)
                ->orWhere('destination_project_id', $project->id)
                ->delete();

            // Delete daily usages
            \App\Models\DailyMaterialUsage::where('project_id', $project->id)->delete();

            // Delete BOM materials
            $project->projectMaterials()->delete();

            // Delete scope lines and scope items
            foreach ($project->scopeItems as $item) {
                $item->lines()->delete();
                $item->delete();
            }

            // Delete project costs
            $project->costs()->delete();

            // Delete payments
            $project->payments()->delete();

            // Nullify project_id in inventory logs
            \App\Models\InventoryLog::where('project_id', $project->id)->update(['project_id' => null]);

            // Detach personnel
            $project->personnel()->detach();

            // Delete the project
            $project->delete();
        });

        $returnUrl = $status === 'completed' ? route('history.index') : route('projects.index');
        return redirect($returnUrl)->with('success', "Project {$projectCode} ({$projectTitle}) and all associated records have been permanently deleted.");
    }

    public function show($id)
    {
        $project = Project::with([
            'personnel',
            'projectMaterials.material',
            'tasks.assignedPersonnel',
            'photos',
            'primaryPhoto',
            'inventoryLogs.material',
            'payments' => function ($q) {
                $q->orderBy('payment_date', 'asc');
            },
            'costs' => function ($q) {
                $q->orderBy('cost_date', 'desc');
            },
            'scopeItems.lines',
        ])->findOrFail($id);

        $allPersonnel = Personnel::orderBy('name')->get();
        $allMaterials = Material::orderBy('name')->get();
        $otherProjects = Project::where('id', '!=', $project->id)->orderBy('title')->get();

        // Comprehensive Financial & Sales Calculations
        $totalInvoiced = $project->payments->sum('amount');
        $totalPaid = $project->payments->where('status', 'paid')->sum('amount');
        $totalPending = $project->payments->where('status', 'pending')->sum('amount');
        $totalOverdue = $project->payments->where('status', 'overdue')->sum('amount');
        $uncollectedBalance = max(0, $project->contract_budget - $totalPaid);
        $salesCollectionRate = $project->contract_budget > 0 ? round(($totalPaid / $project->contract_budget) * 100, 1) : 0;

        // Project Costing Calculations
        $totalEstimatedCost = $project->total_estimated_cost;
        $totalIncurredCost = $project->total_incurred_cost;
        $grossMargin = $project->gross_margin;
        $grossMarginPercent = $project->gross_margin_percent;
        $costPerFloorSqm = $project->cost_per_floor_sqm;
        $costPerLandSqm = $project->cost_per_land_sqm;
        $costVariance = $project->cost_variance;
        $categorySummary = $project->category_cost_summary;
        $costHealthStatus = $project->cost_health_status;

        // BOM Material Calculations & Excess
        $bomAllocatedValue = $project->projectMaterials->sum(function ($pm) {
            return $pm->allocated_qty * $pm->unit_price;
        });
        $bomConsumedValue = $project->projectMaterials->sum(function ($pm) {
            return $pm->used_qty * $pm->unit_price;
        });
        $bomReturnedExcessValue = $project->total_returned_excess_value;
        $bomNetAllocatedValue = max(0, $bomAllocatedValue - $bomReturnedExcessValue);
        $bomRemainingValue = $project->projectMaterials->sum(function ($pm) {
            return $pm->remaining_qty * $pm->unit_price;
        });
        $bomUsagePercent = $bomNetAllocatedValue > 0 ? round(($bomConsumedValue / $bomNetAllocatedValue) * 100, 1) : 0;

        // Task Scheduling Stats
        $totalTasksCount = $project->tasks->count();
        $completedTasksCount = $project->tasks->where('status', 'completed')->count();
        $inProgressTasksCount = $project->tasks->where('status', 'in_progress')->count();
        $tasksAllocatedBudget = $project->tasks->sum('allocated_budget');
        $tasksActualCost = $project->tasks->sum('actual_cost');

        // Workforce Deployment Breakdown & Total Headcount
        $manpowerBreakdown = $project->manpower_breakdown;
        $totalDeployedManpower = $project->total_deployed_manpower;

        // Schedule Analytics
        $totalScheduleDays = $project->total_schedule_days;
        $elapsedDays = $project->elapsed_days;
        $remainingDays = $project->remaining_days;
        $scheduleProgressRatio = $project->schedule_progress_ratio;
        $scheduleHealth = $project->schedule_health_status;

        // Milestone Timeline History
        $milestones = [];
        $milestones[] = [
            'date' => $project->start_date->format('M d, Y'),
            'type' => 'Contract & Mobilization',
            'title' => 'Project Initialized & Mobilized',
            'description' => 'Contract budget of ₱' . number_format($project->contract_budget, 2) . ' executed. Land area of ' . number_format($project->land_area_sqm) . ' m² bounded.',
            'status' => 'completed',
            'icon' => '●',
        ];

        if ($project->structural_progress >= 50) {
            $milestones[] = [
                'date' => $project->start_date->addDays(min($totalScheduleDays, 45))->format('M d, Y'),
                'type' => 'Structural Works',
                'title' => 'Substructure & Concrete Framing Stage',
                'description' => 'Structural framing advanced past 50% milestone (' . $project->structural_progress . '% current).',
                'status' => $project->structural_progress >= 100 ? 'completed' : 'in_progress',
                'icon' => '●',
            ];
        }

        if ($project->electrical_progress > 0 || $project->piping_progress > 0) {
            $milestones[] = [
                'date' => $project->start_date->addDays(min($totalScheduleDays, 90))->format('M d, Y'),
                'type' => 'MEP Trade Works',
                'title' => 'Electrical Conduits & Plumbing Lines Installation',
                'description' => 'Electrical at ' . $project->electrical_progress . '% and Piping/Plumbing at ' . $project->piping_progress . '%.',
                'status' => ($project->electrical_progress >= 100 && $project->piping_progress >= 100) ? 'completed' : 'in_progress',
                'icon' => '●',
            ];
        }

        if ($project->finishing_progress > 0) {
            $milestones[] = [
                'date' => $project->start_date->addDays(min($totalScheduleDays, 140))->format('M d, Y'),
                'type' => 'Architectural Finishes',
                'title' => 'Interior Drywall, Tiling & Turnkey Finishes',
                'description' => 'Finishing stage active at ' . $project->finishing_progress . '%.',
                'status' => $project->finishing_progress >= 100 ? 'completed' : 'in_progress',
                'icon' => '●',
            ];
        }

        foreach ($project->payments->where('status', 'paid') as $paidInvoice) {
            $milestones[] = [
                'date' => $paidInvoice->payment_date->format('M d, Y'),
                'type' => 'Milestone Settlement',
                'title' => 'Payment Cleared: ' . $paidInvoice->payment_stage,
                'description' => 'Official Receipt ' . $paidInvoice->effective_or_number . ' for ₱' . number_format($paidInvoice->amount, 2) . ' via ' . $paidInvoice->payment_method . '.',
                'status' => 'completed',
                'icon' => '●',
            ];
        }

        if ($project->status === 'completed') {
            $turnoverDate = $project->actual_completion_date ? $project->actual_completion_date->format('M d, Y') : $project->end_date->format('M d, Y');
            $milestones[] = [
                'date' => $turnoverDate,
                'type' => 'Final Turnover',
                'title' => 'Final Building Inspection & Client Turnover',
                'description' => '100% construction completion achieved. Realized gross profit: ₱' . number_format($grossMargin, 2) . ' (' . $grossMarginPercent . '%).',
                'status' => 'completed',
                'icon' => '●',
            ];
        } else {
            $milestones[] = [
                'date' => $project->end_date->format('M d, Y'),
                'type' => 'Target Turnover',
                'title' => 'Target Handover & Commissioning Date',
                'description' => 'Scheduled completion window (' . $remainingDays . ' days remaining).',
                'status' => 'pending',
                'icon' => '○',
            ];
        }


        $structuralTasks = $project->structuralTasks()->get();
        $electricalTasks = $project->electricalTasks()->get();
        $pipingTasks = $project->pipingTasks()->get();
        $finishingTasks = $project->finishingTasks()->get();

        $structuralDone = $structuralTasks->where('progress', '>=', 100)->count();
        $electricalDone = $electricalTasks->where('progress', '>=', 100)->count();
        $pipingDone = $pipingTasks->where('progress', '>=', 100)->count();
        $finishingDone = $finishingTasks->where('progress', '>=', 100)->count();

        $activeMaterialsData = $project->getActiveMaterialsData();

        return view('projects.show', compact(
            'project',
            'allPersonnel',
            'allMaterials',
            'totalInvoiced',
            'totalPaid',
            'totalPending',
            'totalOverdue',
            'uncollectedBalance',
            'salesCollectionRate',
            'totalEstimatedCost',
            'totalIncurredCost',
            'grossMargin',
            'grossMarginPercent',
            'costPerFloorSqm',
            'costPerLandSqm',
            'costVariance',
            'categorySummary',
            'costHealthStatus',
            'bomAllocatedValue',
            'bomConsumedValue',
            'bomReturnedExcessValue',
            'bomNetAllocatedValue',
            'bomRemainingValue',
            'bomUsagePercent',
            'totalTasksCount',
            'completedTasksCount',
            'inProgressTasksCount',
            'tasksAllocatedBudget',
            'tasksActualCost',
            'manpowerBreakdown',
            'totalDeployedManpower',
            'totalScheduleDays',
            'elapsedDays',
            'remainingDays',
            'scheduleProgressRatio',
            'scheduleHealth',
            'milestones',
            'otherProjects',
            'structuralTasks',
            'electricalTasks',
            'pipingTasks',
            'finishingTasks',
            'structuralDone',
            'electricalDone',
            'pipingDone',
            'finishingDone',
            'activeMaterialsData'
        ));
    }

    public function updateProgress(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'structural_progress' => 'required|integer|min:0|max:100',
            'electrical_progress' => 'required|integer|min:0|max:100',
            'piping_progress' => 'required|integer|min:0|max:100',
            'finishing_progress' => 'nullable|integer|min:0|max:100',
            'status' => 'required|string',
            'spent_budget' => 'nullable|numeric|min:0',
        ]);

        $project->structural_progress = $validated['structural_progress'];
        $project->electrical_progress = $validated['electrical_progress'];
        $project->piping_progress = $validated['piping_progress'];
        $project->finishing_progress = $validated['finishing_progress'] ?? 0;
        $project->status = $validated['status'];

        if (isset($validated['spent_budget'])) {
            $project->spent_budget = $validated['spent_budget'];
        }

        // Calculate weighted overall progress
        $project->overall_progress = $project->calculated_overall_progress;

        if ($validated['status'] === 'completed' && !$project->actual_completion_date) {
            $project->actual_completion_date = now();
        }

        $project->save();

        return redirect()->back()->with('success', 'Project trade work progression updated! Weighted overall progress: ' . $project->overall_progress . '%.');
    }

    public function updateProgressionBases(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'structural_weight' => 'required|integer|min:0|max:100',
            'electrical_weight' => 'required|integer|min:0|max:100',
            'piping_weight' => 'required|integer|min:0|max:100',
            'finishing_weight' => 'required|integer|min:0|max:100',
            'schedule_notes' => 'nullable|string',
        ]);

        $sW = (int) ($validated['structural_weight'] ?? 40);
        $eW = (int) ($validated['electrical_weight'] ?? 25);
        $pW = (int) ($validated['piping_weight'] ?? 20);
        $fW = (int) ($validated['finishing_weight'] ?? 15);
        if (($sW + $eW + $pW + $fW) <= 0) {
            $sW = 40; $eW = 25; $pW = 20; $fW = 15;
        }
        $validated['structural_weight'] = $sW;
        $validated['electrical_weight'] = $eW;
        $validated['piping_weight'] = $pW;
        $validated['finishing_weight'] = $fW;

        $project->update($validated);
        $project->overall_progress = $project->calculated_overall_progress;
        $project->save();

        return redirect()->back()->with('success', 'Engineering progression weighting bases updated successfully!');
    }

    public function updateSchedule(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'current_phase' => 'required|string|max:255',
            'schedule_notes' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->back()->with('success', 'Project master schedule and phase timeline updated successfully!');
    }

    public function updateFinancing(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'financing_type' => 'required|string|in:bank_loan,pagibig_loan,client_equity,combined',
            'financing_institution' => 'nullable|string|max:255',
            'loan_account_no' => 'nullable|string|max:100',
            'approved_loan_amount' => 'nullable|numeric|min:0',
            'client_equity_amount' => 'nullable|numeric|min:0',
            'payment_first_policy' => 'nullable|boolean',
        ]);

        $project->financing_type = $validated['financing_type'];
        $project->financing_institution = $validated['financing_institution'] ?? null;
        $project->loan_account_no = $validated['loan_account_no'] ?? null;
        $project->approved_loan_amount = $validated['approved_loan_amount'] ?? 0;
        $project->client_equity_amount = $validated['client_equity_amount'] ?? 0;
        $project->payment_first_policy = $request->boolean('payment_first_policy', true);
        $project->save();

        return redirect()->back()->with('success', 'Bank / Pag-IBIG loan specifications and Payment-First policy updated for ' . $project->project_code . '!');
    }

    public function updateManpower(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'deployed_workers' => 'required|integer|min:0',
            'deployed_skilled_workers' => 'required|integer|min:0',
            'deployed_engineers' => 'required|integer|min:0',
            'deployed_architects' => 'required|integer|min:0',
            'deployed_foremen' => 'required|integer|min:0',
            'deployed_operators' => 'required|integer|min:0',
            'deployed_safety_officers' => 'required|integer|min:0',
        ]);

        $project->update($validated);

        return redirect()->back()->with('success', 'On-site workforce deployment headcount successfully updated (' . $project->total_deployed_manpower . ' total active manpower)!');
    }

    public function addTask(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'category' => 'required|string',
            'assigned_personnel_id' => 'nullable|exists:personnel,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'allocated_budget' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string',
            'timeline_phase' => 'nullable|string',
            'timeline_month' => 'nullable|string',
        ]);

        $progress = (int) ($validated['progress'] ?? 0);
        $status = $validated['status'] ?? ($progress >= 100 ? 'completed' : ($progress > 15 ? 'in_progress' : ($progress > 0 ? 'started' : 'not_started')));

        ProjectTask::create([
            'project_id' => $projectId,
            'task_name' => $validated['task_name'],
            'category' => $validated['category'],
            'assigned_personnel_id' => $validated['assigned_personnel_id'] ?? null,
            'start_date' => $validated['start_date'] ?? ($project->start_date ?: now()),
            'due_date' => $validated['due_date'] ?? ($project->end_date ?: now()->addMonths(6)),
            'allocated_budget' => $validated['allocated_budget'] ?? 0,
            'actual_cost' => 0,
            'progress' => $progress,
            'status' => $status,
            'timeline_phase' => $validated['timeline_phase'] ?? 'Phase 1: Mobilization & Substructure',
            'timeline_month' => $validated['timeline_month'] ?? 'Month 1 - 2',
            'sort_order' => $project->tasks()->count() + 1,
        ]);

        $project->recalculateTradeProgressFromTasks();

        return redirect()->back()->with('success', 'Task "' . $validated['task_name'] . '" added to ' . $validated['category'] . ' checklist! Overall progress recalculated.');
    }

    public function updateTask(Request $request, $taskId)
    {
        $task = ProjectTask::findOrFail($taskId);

        $validated = $request->validate([
            'task_name' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string',
            'progress' => 'required|integer|min:0|max:100',
            'actual_cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'timeline_phase' => 'nullable|string',
            'timeline_month' => 'nullable|string',
            'assigned_personnel_id' => 'nullable|exists:personnel,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        $progress = (int) $validated['progress'];
        $status = $validated['status'] ?? ($progress >= 100 ? 'completed' : ($progress > 15 ? 'in_progress' : ($progress > 0 ? 'started' : 'not_started')));

        if ($progress < $task->progress) {
            return redirect()->back()->with('error', 'Action Blocked: Forward-Only Progress rule enforced. Task progress cannot decrease from ' . $task->progress . '% to ' . $progress . '%.');
        }
        if ($task->status === 'completed' && $status !== 'completed') {
            return redirect()->back()->with('error', 'Action Blocked: Irreversible Completion rule enforced. Completed milestone status cannot be reverted.');
        }

        if (isset($validated['task_name'])) {
            $task->task_name = $validated['task_name'];
        }
        if (isset($validated['category'])) {
            $task->category = $validated['category'];
        }
        if (isset($validated['actual_cost'])) {
            $task->actual_cost = $validated['actual_cost'];
        }
        if (isset($validated['timeline_phase'])) {
            $task->timeline_phase = $validated['timeline_phase'];
        }
        if (isset($validated['timeline_month'])) {
            $task->timeline_month = $validated['timeline_month'];
        }
        if (array_key_exists('assigned_personnel_id', $validated)) {
            $task->assigned_personnel_id = $validated['assigned_personnel_id'];
        }
        if (isset($validated['start_date'])) {
            $task->start_date = $validated['start_date'];
        }
        if (isset($validated['due_date'])) {
            $task->due_date = $validated['due_date'];
        }

        $task->progress = $progress;
        $task->status = $status;
        $task->save();

        $task->project->recalculateTradeProgressFromTasks();

        return redirect()->back()->with('success', 'Task "' . $task->task_name . '" updated to ' . $task->progress . '% (' . $task->status_label . ')! Engineering progress updated.');
    }

    public function toggleTaskChecklist(Request $request, $taskId)
    {
        $task = ProjectTask::findOrFail($taskId);

        // Irreversible Completion: Completed tasks cannot be unchecked or undone
        if ($task->progress >= 100 || $task->status === 'completed') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Action Blocked: Irreversible Completion rule enforced. Completed checklist tasks cannot be reverted or unchecked.',
                    'task_id' => $task->id,
                    'progress' => 100,
                    'status' => 'completed',
                ], 422);
            }
            return redirect()->back()->with('error', 'Action Blocked: Irreversible Completion rule enforced. Completed checklist tasks are permanent and cannot be unchecked.');
        }

        // Monotonic forward progression to 100% completed
        $task->progress = 100;
        $task->status = 'completed';
        $task->save();

        $task->taskMaterials()->update(['status' => 'consumed']);

        $task->project->recalculateTradeProgressFromTasks();
        $project = $task->project;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'task_name' => $task->task_name,
                'progress' => $task->progress,
                'status' => $task->status,
                'status_label' => $task->status_label,
                'status_badge' => $task->status_badge_class,
                'is_completed' => true,
                'timeline_phase' => $task->timeline_phase,
                'timeline_phase_key' => $task->timeline_phase_key,
                'timeline_month' => $task->timeline_month,
                
                'structural_weight' => $project->structural_weight,
                'structural_progress' => $project->structural_progress,
                'structural_done' => $project->structuralTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'structural_total' => $project->structuralTasks()->count(),
                'structural_contrib' => round(($project->structural_progress * $project->structural_weight) / 100, 1),
                
                'electrical_weight' => $project->electrical_weight,
                'electrical_progress' => $project->electrical_progress,
                'electrical_done' => $project->electricalTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'electrical_total' => $project->electricalTasks()->count(),
                'electrical_contrib' => round(($project->electrical_progress * $project->electrical_weight) / 100, 1),
                
                'piping_weight' => $project->piping_weight,
                'piping_progress' => $project->piping_progress,
                'piping_done' => $project->pipingTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'piping_total' => $project->pipingTasks()->count(),
                'piping_contrib' => round(($project->piping_progress * $project->piping_weight) / 100, 1),
                
                'finishing_weight' => $project->finishing_weight,
                'finishing_progress' => $project->finishing_progress,
                'finishing_done' => $project->finishingTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'finishing_total' => $project->finishingTasks()->count(),
                'finishing_contrib' => round(($project->finishing_progress * $project->finishing_weight) / 100, 1),
                
                'overall_progress' => $project->overall_progress,
                'total_completed' => $project->tasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'total_tasks' => $project->tasks()->count(),

                'active_materials_data' => $project->getActiveMaterialsData(),
            ]);
        }

        return redirect()->back()->with('success', 'Task "' . $task->task_name . '" marked COMPLETED (100%)! Irreversible completion recorded.');
    }

    public function updateTaskStatusAjax(Request $request, $taskId)
    {
        $task = ProjectTask::findOrFail($taskId);
        $newStatus = $request->input('status', 'in_progress');

        // Forward-only rule validation
        if ($task->status === 'completed' && $newStatus !== 'completed') {
            return response()->json([
                'success' => false,
                'error' => 'Action Blocked: Irreversible Completion rule enforced. Completed tasks cannot be reverted.',
                'status' => 'completed',
            ], 422);
        }
        if ($task->status === 'in_progress' && $newStatus === 'not_started') {
            return response()->json([
                'success' => false,
                'error' => 'Action Blocked: Forward-Only Progress rule enforced. In-progress tasks cannot be moved back to Not Started.',
                'status' => 'in_progress',
            ], 422);
        }

        if ($newStatus === 'completed') {
            $task->progress = 100;
            $task->status = 'completed';
            $task->taskMaterials()->update(['status' => 'consumed']);
        } elseif ($newStatus === 'in_progress') {
            $task->progress = max(50, (int) $task->progress);
            $task->status = 'in_progress';
            $task->taskMaterials()->update(['status' => 'active']);
        } else {
            $task->progress = 0;
            $task->status = 'not_started';
            $task->taskMaterials()->update(['status' => 'pending']);
        }
        $task->save();

        $task->project->recalculateTradeProgressFromTasks();
        $project = $task->project;

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'task_name' => $task->task_name,
            'status' => $task->status,
            'status_label' => $task->status_label,
            'status_badge' => $task->status_badge_class,
            'is_completed' => $task->is_completed,
            'timeline_phase' => $task->timeline_phase,
            'timeline_phase_key' => $task->timeline_phase_key,
            'timeline_month' => $task->timeline_month,
            
            'structural_weight' => $project->structural_weight,
            'structural_progress' => $project->structural_progress,
            'structural_done' => $project->structuralTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
            'structural_total' => $project->structuralTasks()->count(),
            'structural_contrib' => round(($project->structural_progress * $project->structural_weight) / 100, 1),
            
            'electrical_weight' => $project->electrical_weight,
            'electrical_progress' => $project->electrical_progress,
            'electrical_done' => $project->electricalTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
            'electrical_total' => $project->electricalTasks()->count(),
            'electrical_contrib' => round(($project->electrical_progress * $project->electrical_weight) / 100, 1),
            
            'piping_weight' => $project->piping_weight,
            'piping_progress' => $project->piping_progress,
            'piping_done' => $project->pipingTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
            'piping_total' => $project->pipingTasks()->count(),
            'piping_contrib' => round(($project->piping_progress * $project->piping_weight) / 100, 1),
            
            'finishing_weight' => $project->finishing_weight,
            'finishing_progress' => $project->finishing_progress,
            'finishing_done' => $project->finishingTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
            'finishing_total' => $project->finishingTasks()->count(),
            'finishing_contrib' => round(($project->finishing_progress * $project->finishing_weight) / 100, 1),
            
            'overall_progress' => $project->overall_progress,
            'total_completed' => $project->tasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
            'total_tasks' => $project->tasks()->count(),

            'active_materials_data' => $project->getActiveMaterialsData(),
        ]);
    }

    public function getActiveMaterialsJson($id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project->getActiveMaterialsData());
    }

    public function updateTaskQuickProgress(Request $request, $taskId)
    {
        $task = ProjectTask::findOrFail($taskId);
        $progress = (int) $request->input('progress', 0);
        $progress = max(0, min(100, $progress));

        // Forward-Only Progress: Validate progress is >= current progress
        if ($progress < $task->progress) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Action Blocked: Forward-Only Progress rule enforced. Progress cannot decrease from ' . $task->progress . '% to ' . $progress . '%.',
                    'current_progress' => $task->progress,
                ], 422);
            }
            return redirect()->back()->with('error', 'Action Blocked: Forward-Only Progress rule enforced. Progress cannot decrease from ' . $task->progress . '% to ' . $progress . '%.');
        }

        $status = $progress >= 100 ? 'completed' : ($progress > 15 ? 'in_progress' : ($progress > 0 ? 'started' : 'not_started'));

        $task->progress = $progress;
        $task->status = $status;
        if ($status === 'completed') {
            $task->taskMaterials()->update(['status' => 'consumed']);
        } elseif ($status === 'in_progress') {
            $task->taskMaterials()->update(['status' => 'active']);
        }
        $task->save();

        $task->project->recalculateTradeProgressFromTasks();
        $project = $task->project;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'task_name' => $task->task_name,
                'progress' => $task->progress,
                'status' => $task->status,
                'status_label' => $task->status_label,
                'status_badge' => $task->status_badge_class,
                'is_completed' => $task->is_completed,
                'timeline_phase' => $task->timeline_phase,
                'timeline_phase_key' => $task->timeline_phase_key,
                'timeline_month' => $task->timeline_month,
                
                'structural_progress' => $project->structural_progress,
                'structural_done' => $project->structuralTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'structural_total' => $project->structuralTasks()->count(),
                'structural_contrib' => round(($project->structural_progress * $project->structural_weight) / 100, 1),
                
                'electrical_progress' => $project->electrical_progress,
                'electrical_done' => $project->electricalTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'electrical_total' => $project->electricalTasks()->count(),
                'electrical_contrib' => round(($project->electrical_progress * $project->electrical_weight) / 100, 1),
                
                'piping_progress' => $project->piping_progress,
                'piping_done' => $project->pipingTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'piping_total' => $project->pipingTasks()->count(),
                'piping_contrib' => round(($project->piping_progress * $project->piping_weight) / 100, 1),
                
                'finishing_progress' => $project->finishing_progress,
                'finishing_done' => $project->finishingTasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'finishing_total' => $project->finishingTasks()->count(),
                'finishing_contrib' => round(($project->finishing_progress * $project->finishing_weight) / 100, 1),
                
                'overall_progress' => $project->overall_progress,
                'total_completed' => $project->tasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count(),
                'total_tasks' => $project->tasks()->count(),

                'active_materials_data' => $project->getActiveMaterialsData(),
            ]);
        }

        return redirect()->back()->with('success', 'Task "' . $task->task_name . '" advanced to ' . $progress . '% (' . $task->status_label . ')!');
    }

    public function updateTaskQuickTimeline(Request $request, $taskId)
    {
        $task = ProjectTask::findOrFail($taskId);
        $phase = $request->input('timeline_phase');
        if ($phase) {
            $task->timeline_phase = $phase;
            if ($request->has('timeline_month')) {
                $task->timeline_month = $request->input('timeline_month');
            }
            $task->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'timeline_phase' => $task->timeline_phase,
                'timeline_phase_key' => $task->timeline_phase_key,
                'timeline_month' => $task->timeline_month,
            ]);
        }

        return redirect()->back()->with('success', 'Task "' . $task->task_name . '" moved to ' . $task->timeline_phase . '!');
    }

    public function getChecklistStateJson($id)
    {
        $project = Project::with(['tasks.assignedPersonnel'])->findOrFail($id);

        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->where(function($q) {
            $q->where('progress', '>=', 100)->orWhere('status', 'completed');
        })->count();

        $progressPercentage = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $tasksArray = $project->tasks->sortBy('sort_order')->values()->map(function($t) {
            $isDone = $t->progress >= 100 || $t->status === 'completed';
            return [
                'id' => $t->id,
                'task_name' => $t->task_name,
                'category' => $t->category,
                'timeline_phase' => $t->timeline_phase ?? 'Phase 1: Mobilization & Substructure',
                'timeline_month' => $t->timeline_month ?? 'Month 1 - 2',
                'is_completed' => $isDone,
                'status' => $t->status_label,
                'progress' => (int) $t->progress,
                'assigned_to' => $t->assignedPersonnel->name ?? null,
                'schedule_window' => $t->timeline_window_label,
            ];
        });

        $disciplines = [
            'Structural Works' => [
                'total' => $project->structuralTasks()->count(),
                'completed' => $project->structuralTasks()->where('progress', '>=', 100)->count(),
                'progress_percentage' => $project->structuralTasks()->count() > 0 
                    ? (int) round(($project->structuralTasks()->where('progress', '>=', 100)->count() / $project->structuralTasks()->count()) * 100)
                    : 0,
            ],
            'Electrical Works' => [
                'total' => $project->electricalTasks()->count(),
                'completed' => $project->electricalTasks()->where('progress', '>=', 100)->count(),
                'progress_percentage' => $project->electricalTasks()->count() > 0 
                    ? (int) round(($project->electricalTasks()->where('progress', '>=', 100)->count() / $project->electricalTasks()->count()) * 100)
                    : 0,
            ],
            'Piping & Plumbing' => [
                'total' => $project->pipingTasks()->count(),
                'completed' => $project->pipingTasks()->where('progress', '>=', 100)->count(),
                'progress_percentage' => $project->pipingTasks()->count() > 0 
                    ? (int) round(($project->pipingTasks()->where('progress', '>=', 100)->count() / $project->pipingTasks()->count()) * 100)
                    : 0,
            ],
            'Design-Build / Turnkey Finishing' => [
                'total' => $project->finishingTasks()->count(),
                'completed' => $project->finishingTasks()->where('progress', '>=', 100)->count(),
                'progress_percentage' => $project->finishingTasks()->count() > 0 
                    ? (int) round(($project->finishingTasks()->where('progress', '>=', 100)->count() / $project->finishingTasks()->count()) * 100)
                    : 0,
            ],
        ];

        return response()->json([
            'project_title' => $project->title . ' (' . $project->project_code . ')',
            'client_name' => $project->client_name,
            'total_task_count' => $totalTasks,
            'completed_task_count' => $completedTasks,
            'current_progress_percentage' => $progressPercentage,
            'disciplines' => $disciplines,
            'tasks' => $tasksArray,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function destroyTask($taskId)
    {
        $task = ProjectTask::findOrFail($taskId);
        $project = $task->project;
        $taskName = $task->task_name;
        $task->delete();

        $project->recalculateTradeProgressFromTasks();

        return redirect()->back()->with('success', 'Task "' . $taskName . '" removed from checklist. Progress recalculated.');
    }

    public function resetChecklist(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->seedDefaultChecklist(true);

        return redirect()->back()->with('success', 'Standard 53-item engineering checklist initialized for ' . $project->project_code . '! Structural, Electrical, Piping, and Design-Build tasks loaded.');
    }

    public function assignPersonnel(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $validated = $request->validate([
            'personnel_id' => 'required|exists:personnel,id',
            'assignment_role' => 'required|string|max:255',
        ]);

        $project->personnel()->attach($validated['personnel_id'], ['assignment_role' => $validated['assignment_role']]);

        return redirect()->back()->with('success', 'Professional successfully assigned to project!');
    }

    // Photo & Blueprint Gallery Methods
    public function uploadPhoto(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'photo_type' => 'required|string',
            'description' => 'nullable|string',
            'taken_at' => 'nullable|date',
            'photo_file' => 'nullable|image|max:10240', // 10MB
            'photo_url' => 'nullable|string|max:1000',
            'is_primary' => 'nullable|boolean',
        ]);

        $filePath = null;

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $filename = 'prj_' . $project->id . '_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/projects');
            
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }
            
            $file->move($destinationPath, $filename);
            $filePath = '/uploads/projects/' . $filename;
        } elseif ($request->filled('photo_url')) {
            $filePath = $request->input('photo_url');
        } else {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Please upload a photo image file or provide a media URL.'], 422);
            }
            return redirect()->back()->with('error', 'Please upload a photo image file or provide a media URL.');
        }

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            ProjectPhoto::where('project_id', $project->id)->update(['is_primary' => false]);
        }

        $photo = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo_type' => $validated['photo_type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'is_primary' => $isPrimary,
            'taken_at' => $validated['taken_at'] ?? now()->toDateString(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Project photo uploaded successfully!',
                'photo' => $photo,
            ]);
        }

        return redirect()->back()->with('success', 'Project blueprint / design photo successfully added to gallery!');
    }

    public function updatePhoto(Request $request, $photoId)
    {
        $photo = ProjectPhoto::findOrFail($photoId);
        $project = $photo->project;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'photo_type' => 'required|string',
            'description' => 'nullable|string',
            'taken_at' => 'nullable|date',
            'photo_file' => 'nullable|image|max:10240', // 10MB
            'photo_url' => 'nullable|string|max:1000',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $filename = 'prj_' . $project->id . '_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/projects');
            
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }
            
            $file->move($destinationPath, $filename);
            $photo->file_path = '/uploads/projects/' . $filename;
        } elseif ($request->filled('photo_url')) {
            $photo->file_path = $request->input('photo_url');
        }

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            ProjectPhoto::where('project_id', $project->id)->update(['is_primary' => false]);
            $photo->is_primary = true;
        } else {
            // Only unset if we are deliberately unsetting it
            if ($request->has('is_primary_submitted')) {
                $photo->is_primary = false;
            }
        }

        $photo->title = $validated['title'];
        $photo->photo_type = $validated['photo_type'];
        $photo->description = $validated['description'] ?? null;
        if (!empty($validated['taken_at'])) {
            $photo->taken_at = $validated['taken_at'];
        }
        $photo->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Project photo updated successfully!',
                'photo' => $photo,
            ]);
        }

        return redirect()->back()->with('success', 'Project image / blueprint "' . $photo->title . '" updated successfully!');
    }

    public function deletePhoto($photoId)
    {
        $photo = ProjectPhoto::findOrFail($photoId);
        $photoTitle = $photo->title;
        $photo->delete();

        return redirect()->back()->with('success', 'Photo "' . $photoTitle . '" removed from project gallery.');
    }

    public function setPrimaryPhoto($photoId)
    {
        $photo = ProjectPhoto::findOrFail($photoId);
        ProjectPhoto::where('project_id', $photo->project_id)->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        return redirect()->back()->with('success', 'Selected photo set as project primary profile hero banner!');
    }

    // Official Accomplishment & Monitoring Report (Printable Format)
    public function printReport($id)
    {
        $project = Project::with([
            'personnel',
            'projectMaterials.material',
            'tasks.assignedPersonnel',
            'photos',
            'inventoryLogs.material',
            'payments',
            'costs'
        ])->findOrFail($id);

        $totalPaid = $project->payments->where('status', 'paid')->sum('amount');
        $uncollectedBalance = max(0, $project->contract_budget - $totalPaid);
        $grossMargin = $project->gross_margin;
        $grossMarginPercent = $project->gross_margin_percent;
        $totalIncurredCost = $project->total_incurred_cost;
        $categorySummary = $project->category_cost_summary;
        $manpowerBreakdown = $project->manpower_breakdown;
        $totalDeployedManpower = $project->total_deployed_manpower;
        $totalScheduleDays = $project->total_schedule_days;
        $elapsedDays = $project->elapsed_days;
        $remainingDays = $project->remaining_days;
        $scheduleHealth = $project->schedule_health_status;

        $leadEngineer = $project->personnel->firstWhere('pivot.assignment_role', 'Project Lead') 
            ?? $project->personnel->first() 
            ?? (object) ['name' => 'Engr. Elena Rostova', 'title' => 'Chief Structural Engineer', 'license_no' => 'PE-330412'];

        $leadArchitect = $project->personnel->firstWhere('pivot.assignment_role', 'Lead Architect') 
            ?? (object) ['name' => 'Arch. Marcus Vance', 'title' => 'Lead Principal Architect', 'license_no' => 'ARC-991204'];

        return view('projects.print_report', compact(
            'project',
            'totalPaid',
            'uncollectedBalance',
            'grossMargin',
            'grossMarginPercent',
            'totalIncurredCost',
            'categorySummary',
            'manpowerBreakdown',
            'totalDeployedManpower',
            'totalScheduleDays',
            'elapsedDays',
            'remainingDays',
            'scheduleHealth',
            'leadEngineer',
            'leadArchitect'
        ));
    }
}
