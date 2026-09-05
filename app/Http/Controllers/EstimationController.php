<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class EstimationController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::orderBy('created_at', 'desc')->get();
        return view('estimation.index', compact('requests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:50',
            'service_type' => 'required|string',
            'land_area_sqm' => 'required|numeric|min:1',
            'floor_area_sqm' => 'required|numeric|min:1',
            'requested_start_date' => 'required|date',
            'notes' => 'nullable|string',
            'initialize_project_now' => 'nullable|boolean',
        ]);

        // Automated Cost Estimation Calculation (₱)
        // Standard baseline rates: Floor Area + Land Preparation
        $ratePerFloorSqm = match ($validated['service_type']) {
            'Commercial Construction' => 1400,
            'Industrial Complex' => 1600,
            'Residential Build' => 1100,
            'Renovation & Overhaul' => 800,
            default => 1000,
        };

        $estimatedCost = ($validated['floor_area_sqm'] * $ratePerFloorSqm) + ($validated['land_area_sqm'] * 200);

        $requestCode = 'EST-' . strtoupper(substr(uniqid(), -6));
        $validated['request_code'] = $requestCode;
        $validated['estimated_cost'] = $estimatedCost;
        $validated['status'] = $request->boolean('initialize_project_now') ? 'approved' : 'pending';

        $serviceReq = ServiceRequest::create($validated);

        if ($request->boolean('initialize_project_now')) {
            return $this->initializeProject($serviceReq->id);
        }

        return redirect()->back()->with('success', 'Cost estimation calculated and recorded successfully!');
    }

    public function initializeProject($id)
    {
        $serviceReq = ServiceRequest::findOrFail($id);
        $serviceReq->status = 'approved';
        $serviceReq->save();

        // Convert calculated estimate into active monitored project
        $projectCode = 'PRJ-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
        $startDate = $serviceReq->requested_start_date ?? now();
        $endDate = (clone $startDate)->addMonths(8);

        $project = Project::create([
            'project_code' => $projectCode,
            'title' => $serviceReq->service_type . ' - ' . $serviceReq->client_name,
            'client_name' => $serviceReq->client_name,
            'location' => 'Client Specified Site',
            'project_type' => $serviceReq->service_type,
            'land_area_sqm' => $serviceReq->land_area_sqm,
            'floor_area_sqm' => $serviceReq->floor_area_sqm,
            'status' => 'in_progress',
            'contract_budget' => $serviceReq->estimated_cost,
            'spent_budget' => 0.00,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'structural_progress' => 0,
            'electrical_progress' => 0,
            'piping_progress' => 0,
            'finishing_progress' => 0,
            'structural_weight' => 40,
            'electrical_weight' => 25,
            'piping_weight' => 20,
            'finishing_weight' => 15,
            'overall_progress' => 0,
            'current_phase' => 'Phase 1: Site Mobilization & Earthworks',
            'deployed_workers' => 12,
            'deployed_skilled_workers' => 6,
            'deployed_engineers' => 2,
            'deployed_architects' => 1,
            'deployed_foremen' => 1,
            'deployed_operators' => 1,
            'deployed_safety_officers' => 1,
            'description' => 'Project created from estimate ' . $serviceReq->request_code . '. ' . $serviceReq->notes,
        ]);

        return redirect()->route('projects.show', $project->id)->with('success', 'Estimate initialized into Active Project Tracker as ' . $project->project_code . '!');
    }

    public function destroy($id)
    {
        $serviceReq = ServiceRequest::findOrFail($id);
        $serviceReq->delete();

        return redirect()->back()->with('success', 'Estimation record removed.');
    }
}
