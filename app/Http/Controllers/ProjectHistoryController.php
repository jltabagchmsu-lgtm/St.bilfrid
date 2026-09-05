<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Payment;
use App\Models\ProjectCost;
use Illuminate\Http\Request;

class ProjectHistoryController extends Controller
{
    public function index()
    {
        $completedProjects = Project::where('status', 'completed')
            ->with(['personnel', 'payments', 'tasks', 'costs'])
            ->orderBy('actual_completion_date', 'desc')
            ->get();

        $totalCompletedBudget = (float) $completedProjects->sum('contract_budget');
        $totalCompletedSpent = (float) $completedProjects->sum(function ($p) {
            return $p->total_incurred_cost;
        });
        $totalRealizedMargin = max(0, $totalCompletedBudget - $totalCompletedSpent);
        $avgRealizedMarginPercent = $totalCompletedBudget > 0 ? round(($totalRealizedMargin / $totalCompletedBudget) * 100, 1) : 0;
        
        $totalFloorAreaBuilt = (float) $completedProjects->sum('floor_area_sqm');
        $totalLandAreaDeveloped = (float) $completedProjects->sum('land_area_sqm');

        // Historical Workforce Deployed across Completed Builds
        $historicalWorkers = (int) $completedProjects->sum('deployed_workers');
        $historicalSkilled = (int) $completedProjects->sum('deployed_skilled_workers');
        $historicalEngineers = (int) $completedProjects->sum('deployed_engineers');
        $historicalArchitects = (int) $completedProjects->sum('deployed_architects');
        $historicalOperators = (int) $completedProjects->sum('deployed_operators');
        $historicalForemen = (int) $completedProjects->sum('deployed_foremen');
        $historicalSafety = (int) $completedProjects->sum('deployed_safety_officers');
        $totalHistoricalManpower = (int) $completedProjects->sum(function ($p) {
            return $p->total_deployed_manpower;
        });

        // Historical Sales & Milestone Transactions (Archived cleared receipts)
        $historicalPayments = Payment::whereHas('project', function ($q) {
                $q->where('status', 'completed');
            })
            ->with('project')
            ->orderBy('payment_date', 'desc')
            ->get();

        $totalHistoricalRevenue = (float) $historicalPayments->where('status', 'paid')->sum('amount');

        // Multi-year Completed Projects Summary
        $yearlyHistoricalSummary = [];
        foreach ([2024, 2025, 2026] as $yr) {
            $yrProjects = $completedProjects->filter(function ($p) use ($yr) {
                $compYear = $p->actual_completion_date ? (int)$p->actual_completion_date->format('Y') : ((int)$p->end_date->format('Y'));
                return $compYear === $yr;
            });

            if ($yrProjects->count() > 0) {
                $yrBudget = (float) $yrProjects->sum('contract_budget');
                $yrSpent = (float) $yrProjects->sum(function ($p) { return $p->total_incurred_cost; });
                $yrMargin = max(0, $yrBudget - $yrSpent);
                $yearlyHistoricalSummary[$yr] = [
                    'year' => $yr,
                    'count' => $yrProjects->count(),
                    'contract_value' => $yrBudget,
                    'final_spend' => $yrSpent,
                    'realized_margin' => $yrMargin,
                    'margin_percent' => $yrBudget > 0 ? round(($yrMargin / $yrBudget) * 100, 1) : 0,
                    'floor_area' => (float) $yrProjects->sum('floor_area_sqm'),
                    'manpower' => (int) $yrProjects->sum(function ($p) { return $p->total_deployed_manpower; }),
                ];
            }
        }

        return view('history.index', compact(
            'completedProjects',
            'totalCompletedBudget',
            'totalCompletedSpent',
            'totalRealizedMargin',
            'avgRealizedMarginPercent',
            'totalFloorAreaBuilt',
            'totalLandAreaDeveloped',
            'historicalWorkers',
            'historicalSkilled',
            'historicalEngineers',
            'historicalArchitects',
            'historicalOperators',
            'historicalForemen',
            'historicalSafety',
            'totalHistoricalManpower',
            'historicalPayments',
            'totalHistoricalRevenue',
            'yearlyHistoricalSummary'
        ));
    }
}
