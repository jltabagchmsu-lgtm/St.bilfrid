<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\Material;
use App\Models\ProjectCost;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $ongoingProjects = Project::whereIn('status', ['in_progress', 'on_hold', 'approved'])
            ->with(['costs', 'personnel', 'payments'])
            ->get();
        $recentlyCompleted = Project::where('status', 'completed')
            ->with(['costs', 'personnel', 'payments'])
            ->orderBy('actual_completion_date', 'desc')
            ->take(5)
            ->get();
        $allProjects = Project::with(['costs', 'personnel', 'payments'])->get();
        
        // ----------------------------------------------------
        // 1. Executive Sales & Revenue Performance Hub
        // ----------------------------------------------------
        $totalBookedSales = Project::sum('contract_budget');
        $totalCollectedRevenue = Payment::where('status', 'paid')->sum('amount');
        $pendingReceivables = Payment::where('status', 'pending')->sum('amount');
        $overdueReceivables = Payment::where('status', 'overdue')->sum('amount');
        $totalActualCost = ProjectCost::sum('actual_cost');
        if ($totalActualCost <= 0) {
            $totalActualCost = Project::sum('spent_budget');
        }
        $totalContractBudget = $totalBookedSales;
        $totalGrossMargin = max(0, $totalContractBudget - $totalActualCost);
        $avgGrossMarginPercent = $totalContractBudget > 0 ? round(($totalGrossMargin / $totalContractBudget) * 100, 1) : 0;
        $avgDealSize = $allProjects->count() > 0 ? round($totalBookedSales / $allProjects->count(), 2) : 0;

        // Multi-Year Sales Breakdown (2024, 2025, 2026, 2027)
        $years = [2024, 2025, 2026, 2027];
        $yearlySalesMatrix = [];
        $yearlySalesLabels = [];
        $yearlyBookedSales = [];
        $yearlyClearedRevenue = [];
        $yearlyIncurredCost = [];
        $yearlyGrossMargin = [];

        $prevBookedSales = 0;
        foreach ($years as $yr) {
            $yrProjects = $allProjects->filter(function ($p) use ($yr) {
                return $p->start_date && (int)$p->start_date->format('Y') === $yr;
            });
            $yrBooked = (float) $yrProjects->sum('contract_budget');
            
            // Payments cleared in this year
            $yrCleared = (float) Payment::where('status', 'paid')
                ->whereYear('payment_date', $yr)
                ->sum('amount');
            
            // Payments pending in this year
            $yrPending = (float) Payment::where('status', 'pending')
                ->whereYear('payment_date', $yr)
                ->sum('amount');

            // Costs incurred in this year
            $yrCost = (float) ProjectCost::whereYear('cost_date', $yr)->sum('actual_cost');
            if ($yrCost <= 0 && $yrProjects->count() > 0) {
                $yrCost = (float) $yrProjects->sum('spent_budget');
            }

            $yrMargin = max(0, $yrBooked - $yrCost);
            $yrMarginPct = $yrBooked > 0 ? round(($yrMargin / $yrBooked) * 100, 1) : 0;
            
            $growthRate = 0;
            if ($prevBookedSales > 0) {
                $growthRate = round((($yrBooked - $prevBookedSales) / $prevBookedSales) * 100, 1);
            } elseif ($prevBookedSales == 0 && $yrBooked > 0 && $yr > 2024) {
                $growthRate = 100.0;
            }

            $yearlySalesMatrix[$yr] = [
                'year' => $yr,
                'booked_sales' => $yrBooked,
                'cleared_revenue' => $yrCleared,
                'pending_receivables' => $yrPending,
                'incurred_cost' => $yrCost,
                'gross_margin' => $yrMargin,
                'margin_percent' => $yrMarginPct,
                'projects_count' => $yrProjects->count(),
                'completed_count' => $yrProjects->where('status', 'completed')->count(),
                'active_count' => $yrProjects->whereIn('status', ['in_progress', 'approved', 'on_hold'])->count(),
                'growth_rate' => $growthRate,
            ];

            $yearlySalesLabels[] = (string) $yr;
            $yearlyBookedSales[] = $yrBooked;
            $yearlyClearedRevenue[] = $yrCleared;
            $yearlyIncurredCost[] = $yrCost;
            $yearlyGrossMargin[] = $yrMargin;

            $prevBookedSales = $yrBooked;
        }

        // ----------------------------------------------------
        // 2. Company-Wide Workforce & Manpower Deployment Matrix
        // ----------------------------------------------------
        $totalActiveWorkers = (int) $ongoingProjects->sum('deployed_workers');
        $totalActiveSkilled = (int) $ongoingProjects->sum('deployed_skilled_workers');
        $totalActiveEngineers = (int) $ongoingProjects->sum('deployed_engineers');
        $totalActiveArchitects = (int) $ongoingProjects->sum('deployed_architects');
        $totalActiveForemen = (int) $ongoingProjects->sum('deployed_foremen');
        $totalActiveOperators = (int) $ongoingProjects->sum('deployed_operators');
        $totalActiveSafety = (int) $ongoingProjects->sum('deployed_safety_officers');
        $totalActiveManpower = $totalActiveWorkers + $totalActiveSkilled + $totalActiveEngineers + 
            $totalActiveArchitects + $totalActiveForemen + $totalActiveOperators + $totalActiveSafety;

        // Historical Manpower Deployed across all projects (including completed)
        $totalHistoricalWorkers = (int) $allProjects->sum('deployed_workers');
        $totalHistoricalEngineers = (int) $allProjects->sum('deployed_engineers');
        $totalHistoricalArchitects = (int) $allProjects->sum('deployed_architects');
        $totalHistoricalOperators = (int) $allProjects->sum('deployed_operators');
        $totalHistoricalManpower = (int) $allProjects->sum(function ($p) {
            return $p->total_deployed_manpower;
        });

        // ----------------------------------------------------
        // 3. Project Status & Pipeline Analytics
        // ----------------------------------------------------
        $totalProjectsCount = Project::count();
        $inProgressCount = Project::where('status', 'in_progress')->count();
        $approvedCount = Project::where('status', 'approved')->count();
        $onHoldCount = Project::where('status', 'on_hold')->count();
        $completedCount = Project::where('status', 'completed')->count();
        $ongoingCount = $ongoingProjects->count();
        
        $totalEstimatesCount = ServiceRequest::count();
        $totalEstimatedValue = ServiceRequest::sum('estimated_cost');
        
        // Trade progress averages across active sites
        $avgStructural = $ongoingProjects->avg('structural_progress') ?? 0;
        $avgElectrical = $ongoingProjects->avg('electrical_progress') ?? 0;
        $avgPiping = $ongoingProjects->avg('piping_progress') ?? 0;
        $avgOverall = $ongoingProjects->avg('overall_progress') ?? 0;

        // Cost category breakdown
        $costCategoryQuery = ProjectCost::select('cost_category', DB::raw('SUM(actual_cost) as total_spent'))
            ->groupBy('cost_category')
            ->having('total_spent', '>', 0)
            ->orderBy('total_spent', 'desc')
            ->get();

        $costCategories = [];
        $costCategoryTotals = [];
        
        if ($costCategoryQuery->count() > 0) {
            foreach ($costCategoryQuery as $cat) {
                $costCategories[] = $cat->cost_category;
                $costCategoryTotals[] = (float) $cat->total_spent;
            }
        } else {
            $costCategories = ['Materials & Consumables', 'Labor & Engineering', 'Equipment & Machinery', 'Subcontractors', 'Site Overhead'];
            $costCategoryTotals = [
                round($totalActualCost * 0.40, 2),
                round($totalActualCost * 0.25, 2),
                round($totalActualCost * 0.15, 2),
                round($totalActualCost * 0.12, 2),
                round($totalActualCost * 0.08, 2),
            ];
        }

        return view('dashboard.index', compact(
            'ongoingProjects',
            'recentlyCompleted',
            'allProjects',
            'totalBookedSales',
            'totalCollectedRevenue',
            'pendingReceivables',
            'overdueReceivables',
            'totalActualCost',
            'totalContractBudget',
            'totalGrossMargin',
            'avgGrossMarginPercent',
            'avgDealSize',
            'yearlySalesMatrix',
            'yearlySalesLabels',
            'yearlyBookedSales',
            'yearlyClearedRevenue',
            'yearlyIncurredCost',
            'yearlyGrossMargin',
            'totalActiveWorkers',
            'totalActiveSkilled',
            'totalActiveEngineers',
            'totalActiveArchitects',
            'totalActiveForemen',
            'totalActiveOperators',
            'totalActiveSafety',
            'totalActiveManpower',
            'totalHistoricalWorkers',
            'totalHistoricalEngineers',
            'totalHistoricalArchitects',
            'totalHistoricalOperators',
            'totalHistoricalManpower',
            'totalProjectsCount',
            'inProgressCount',
            'approvedCount',
            'onHoldCount',
            'completedCount',
            'ongoingCount',
            'totalEstimatesCount',
            'totalEstimatedValue',
            'avgStructural',
            'avgElectrical',
            'avgPiping',
            'avgOverall',
            'costCategories',
            'costCategoryTotals'
        ));
    }
}
