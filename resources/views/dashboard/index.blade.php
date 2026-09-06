@extends('layouts.app')

@section('title', 'Executive Dashboard & Sales Command - St. Bilfrid Development Corporation')
@section('page_title', 'Executive Dashboard & Analytics')

@section('top_actions')
    <a href="/projects" class="btn-primary" style="font-size: 0.85rem;">
        <span>+</span> Add / Manage Projects
    </a>
    <a href="/payments" class="btn-secondary" style="font-size: 0.85rem;">
        Sales Ledger
    </a>
    <a href="/estimation" class="btn-secondary" style="font-size: 0.85rem;">
        + New Service Estimate
    </a>
@endsection

@section('content')

<!-- ====================================================
     SECTION 1: DEDICATED EXECUTIVE SALES & REVENUE COMMAND HUB
     ==================================================== -->
<div class="glass-panel" style="border: 1px solid rgba(16, 185, 129, 0.35); background: linear-gradient(180deg, rgba(16, 185, 129, 0.05) 0%, rgba(15, 23, 42, 0.8) 100%); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <div style="width: 40px; height: 40px; min-width: 40px; border-radius: var(--radius-md); background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: center; color: #10b981; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                    <polyline points="17 6 23 6 23 12"></polyline>
                </svg>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="panel-title" style="font-size: 1.2rem; color: #f8fafc;">Executive Sales & Revenue Command</h3>
                    <span class="sales-section-badge">Dedicated Sales Hub</span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Contract bookings, cleared cash revenues, sales margins, and multi-year performance (2024 &ndash; 2027)
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/payments" class="btn-primary" style="font-size: 0.8rem; padding: 6px 14px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-color: #10b981;">
                Full Sales Ledger &rarr;
            </a>
        </div>
    </div>

    <!-- 5 Executive Sales KPI Cards -->
    <div class="sales-kpi-grid">
        <div class="sales-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                <span>Total Booked Sales</span>
                <span class="spec-chip" style="font-size: 0.65rem;">BOOKED</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; font-family: var(--font-mono); color: #f8fafc; margin: 8px 0 4px 0;">
                ₱{{ number_format($totalBookedSales, 2) }}
            </div>
            <div style="font-size: 0.775rem; color: var(--text-secondary); display: flex; align-items: center; justify-content: space-between;">
                <span>Across {{ $totalProjectsCount }} contracted builds</span>
                <span class="growth-pill positive">Active Portfolio</span>
            </div>
        </div>

        <div class="sales-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                <span>Cleared Cash Inflow</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #10b981; border-color: rgba(16,185,129,0.3);">SETTLED</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; font-family: var(--font-mono); color: #10b981; margin: 8px 0 4px 0;">
                ₱{{ number_format($totalCollectedRevenue, 2) }}
            </div>
            <div style="font-size: 0.775rem; color: var(--text-secondary);">
                {{ $totalBookedSales > 0 ? round(($totalCollectedRevenue / $totalBookedSales) * 100, 1) : 0 }}% Collection Rate
            </div>
        </div>

        <div class="sales-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                <span>Pending Receivables</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b; border-color: rgba(245,158,11,0.3);">PENDING</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; font-family: var(--font-mono); color: #f59e0b; margin: 8px 0 4px 0;">
                ₱{{ number_format($pendingReceivables, 2) }}
            </div>
            <div style="font-size: 0.775rem; color: var(--text-secondary);">
                Milestone Invoices In Progress
            </div>
        </div>

        <div class="sales-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                <span>Realized Sales Margin</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8; border-color: rgba(56,189,248,0.3);">MARGIN</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; font-family: var(--font-mono); color: #38bdf8; margin: 8px 0 4px 0;">
                ₱{{ number_format($totalGrossMargin, 2) }}
            </div>
            <div style="font-size: 0.775rem; color: #10b981; font-weight: 700;">
                {{ $avgGrossMarginPercent }}% Overall Profit Margin
            </div>
        </div>

        <div class="sales-kpi-card">
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">
                <span>Avg Contract Deal Size</span>
                <span class="spec-chip" style="font-size: 0.65rem;">AVG</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; font-family: var(--font-mono); color: #e2e8f0; margin: 8px 0 4px 0;">
                ₱{{ number_format($avgDealSize / 1000000, 2) }}M
            </div>
            <div style="font-size: 0.775rem; color: var(--text-secondary);">
                Per Contracted Client
            </div>
        </div>
    </div>

    <!-- Yearly Sales Performance: Chart & Detailed Comparison Table -->
    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; margin-top: 10px;">
        
        <!-- Yearly Sales Multi-Year Trend Chart (2024 - 2027) -->
        <div style="background: rgba(0, 0, 0, 0.35); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">Multi-Year Sales & Revenue Trajectory</h4>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Annual Booked Contract Sales vs Cleared Cash Inflow (2024 &ndash; 2027)</span>
                </div>
                <span class="badge badge-in_progress" style="font-size: 0.7rem;">₱ in Millions</span>
            </div>

            <div style="position: relative; height: 240px;">
                <canvas id="yearlySalesChart"></canvas>
            </div>
        </div>

        <!-- Yearly Sales Comparison Matrix -->
        <div style="background: rgba(0, 0, 0, 0.35); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; overflow-x: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);">Yearly Sales Performance Matrix</h4>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Annual bookings, margin % & YoY growth</span>
                </div>
            </div>

            <table class="custom-table" style="font-size: 0.8rem;">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Booked Sales (₱)</th>
                        <th>Cleared Cash</th>
                        <th>Margin %</th>
                        <th>YoY Growth</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearlySalesMatrix as $yr => $data)
                    <tr>
                        <td>
                            <strong style="font-family: var(--font-mono); font-size: 0.9rem; color: {{ $yr == 2026 ? '#38bdf8' : '#f8fafc' }};">
                                {{ $yr }}
                            </strong>
                            @if($yr == 2026)
                                <span class="badge badge-in_progress" style="font-size: 0.6rem; padding: 1px 4px; margin-left: 4px;">Current</span>
                            @elseif($yr < 2026)
                                <span class="badge badge-settled" style="font-size: 0.6rem; padding: 1px 4px; margin-left: 4px;">Archived</span>
                            @else
                                <span class="badge badge-warning" style="font-size: 0.6rem; padding: 1px 4px; margin-left: 4px;">Forecast</span>
                            @endif
                        </td>
                        <td style="font-family: var(--font-mono); font-weight: 700; color: #f8fafc;">
                            ₱{{ number_format($data['booked_sales'] / 1000000, 2) }}M
                            <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $data['projects_count'] }} Contracts</div>
                        </td>
                        <td style="font-family: var(--font-mono); color: #10b981; font-weight: 700;">
                            ₱{{ number_format($data['cleared_revenue'] / 1000000, 2) }}M
                        </td>
                        <td>
                            <span style="color: {{ $data['margin_percent'] >= 20 ? '#10b981' : '#f59e0b' }}; font-weight: 700; font-family: var(--font-mono);">
                                {{ $data['margin_percent'] }}%
                            </span>
                        </td>
                        <td>
                            @if($data['growth_rate'] > 0)
                                <span class="growth-pill positive">+{{ $data['growth_rate'] }}%</span>
                            @elseif($data['growth_rate'] < 0)
                                <span class="growth-pill negative">{{ $data['growth_rate'] }}%</span>
                            @else
                                <span class="growth-pill neutral">--</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- ====================================================
     SECTION 2: COMPANY-WIDE ON-SITE WORKFORCE & DEPLOYMENT
     ==================================================== -->
<div class="glass-panel" style="border: 1px solid rgba(56, 189, 248, 0.25); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 40px; height: 40px; min-width: 40px; border-radius: var(--radius-md); background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); display: flex; align-items: center; justify-content: center; color: #38bdf8; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div>
                <h3 class="panel-title" style="font-size: 1.15rem;">Company-Wide On-Site Workforce & Resource Deployment</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Real-time field personnel and trade crews currently mobilized across active construction sites
                </span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge badge-in_progress" style="font-size: 0.85rem; padding: 6px 14px;">
                {{ $totalActiveManpower }} Total Active Manpower
            </span>
            <a href="/personnel" class="btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                Engineers & Architects Roster &rarr;
            </a>
        </div>
    </div>

    <!-- Workforce Deployment Breakdown Cards Grid -->
    <div class="manpower-grid">
        <div class="manpower-card" style="border-left: 3px solid #38bdf8;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">WORKERS</span>
                <span class="manpower-count" style="color: #38bdf8;">{{ $totalActiveWorkers }}</span>
            </div>
            <div class="manpower-title">General Workers</div>
            <div class="manpower-role">Laborers & Site Helpers</div>
            <div class="manpower-bar"><div class="manpower-bar-fill" style="width: {{ $totalActiveManpower > 0 ? round(($totalActiveWorkers / $totalActiveManpower)*100) : 0 }}%; background: #38bdf8;"></div></div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #818cf8;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #818cf8;">TRADES</span>
                <span class="manpower-count" style="color: #818cf8;">{{ $totalActiveSkilled }}</span>
            </div>
            <div class="manpower-title">Skilled Tradesmen</div>
            <div class="manpower-role">Masons, Carpenters & Welders</div>
            <div class="manpower-bar"><div class="manpower-bar-fill" style="width: {{ $totalActiveManpower > 0 ? round(($totalActiveSkilled / $totalActiveManpower)*100) : 0 }}%; background: #818cf8;"></div></div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #f59e0b;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">ENGR</span>
                <span class="manpower-count" style="color: #f59e0b;">{{ $totalActiveEngineers }}</span>
            </div>
            <div class="manpower-title">Field Engineers</div>
            <div class="manpower-role">Structural, Electrical, Piping</div>
            <div class="manpower-bar"><div class="manpower-bar-fill" style="width: {{ $totalActiveManpower > 0 ? round(($totalActiveEngineers / $totalActiveManpower)*100) : 0 }}%; background: #f59e0b;"></div></div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #ec4899;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #ec4899;">ARCH</span>
                <span class="manpower-count" style="color: #ec4899;">{{ $totalActiveArchitects }}</span>
            </div>
            <div class="manpower-title">Architects</div>
            <div class="manpower-role">Principal & Design Leads</div>
            <div class="manpower-bar"><div class="manpower-bar-fill" style="width: {{ $totalActiveManpower > 0 ? round(($totalActiveArchitects / $totalActiveManpower)*100) : 0 }}%; background: #ec4899;"></div></div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #ef4444;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #ef4444;">OPERATORS</span>
                <span class="manpower-count" style="color: #ef4444;">{{ $totalActiveOperators }}</span>
            </div>
            <div class="manpower-title">Equipment Operators</div>
            <div class="manpower-role">Cranes, Rigs & Heavy Plant</div>
            <div class="manpower-bar"><div class="manpower-bar-fill" style="width: {{ $totalActiveManpower > 0 ? round(($totalActiveOperators / $totalActiveManpower)*100) : 0 }}%; background: #ef4444;"></div></div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #10b981;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">FOREMEN</span>
                <span class="manpower-count" style="color: #10b981;">{{ $totalActiveForemen }}</span>
            </div>
            <div class="manpower-title">Site Foremen</div>
            <div class="manpower-role">Trade Supervisors & Leads</div>
            <div class="manpower-bar"><div class="manpower-bar-fill" style="width: {{ $totalActiveManpower > 0 ? round(($totalActiveForemen / $totalActiveManpower)*100) : 0 }}%; background: #10b981;"></div></div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #14b8a6;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #14b8a6;">QA/QC</span>
                <span class="manpower-count" style="color: #14b8a6;">{{ $totalActiveSafety }}</span>
            </div>
            <div class="manpower-title">Safety Officers</div>
            <div class="manpower-role">QA/QC & Site Compliance</div>
            <div class="manpower-bar"><div class="manpower-bar-fill" style="width: {{ $totalActiveManpower > 0 ? round(($totalActiveSafety / $totalActiveManpower)*100) : 0 }}%; background: #14b8a6;"></div></div>
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 3: EXECUTIVE VISUAL ANALYTICS CIRCLE SUITE
     ==================================================== -->
<div class="chart-grid">
    
    <!-- Circle Graph 1: Project Status Distribution -->
    <div class="chart-card">
        <div class="panel-header" style="margin-bottom: 8px;">
            <div>
                <h3 class="panel-title">Project Status</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Site portfolio distribution</span>
            </div>
            <span class="badge badge-in_progress">{{ $totalProjectsCount }} Total Sites</span>
        </div>
        
        <div class="chart-container-wrapper">
            <canvas id="projectStatusChart"></canvas>
            <div class="chart-center-metric">
                <span class="metric-number">{{ $totalProjectsCount }}</span>
                <span class="metric-label">Sites</span>
            </div>
        </div>

        <div class="chart-legend-grid">
            <div class="legend-pill" title="In Progress: {{ $inProgressCount }}">
                <span class="legend-dot" style="background: #38bdf8; color: #38bdf8;"></span>
                <span class="legend-text">In Progress</span>
                <strong style="margin-left: auto; font-family: var(--font-mono); color: #38bdf8;">{{ $inProgressCount }}</strong>
            </div>
            <div class="legend-pill" title="Approved / Planned: {{ $approvedCount }}">
                <span class="legend-dot" style="background: #f59e0b; color: #f59e0b;"></span>
                <span class="legend-text">Approved</span>
                <strong style="margin-left: auto; font-family: var(--font-mono); color: #f59e0b;">{{ $approvedCount }}</strong>
            </div>
            <div class="legend-pill" title="On Hold: {{ $onHoldCount }}">
                <span class="legend-dot" style="background: #ef4444; color: #ef4444;"></span>
                <span class="legend-text">On Hold</span>
                <strong style="margin-left: auto; font-family: var(--font-mono); color: #ef4444;">{{ $onHoldCount }}</strong>
            </div>
            <div class="legend-pill" title="Completed: {{ $completedCount }}">
                <span class="legend-dot" style="background: #10b981; color: #10b981;"></span>
                <span class="legend-text">Completed</span>
                <strong style="margin-left: auto; font-family: var(--font-mono); color: #10b981;">{{ $completedCount }}</strong>
            </div>
        </div>
    </div>

    <!-- Circle Graph 2: Cost & Expenditure Breakdown by Category -->
    <div class="chart-card">
        <div class="panel-header" style="margin-bottom: 8px;">
            <div>
                <h3 class="panel-title">Cost Breakdown</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Incurred expenditure by category</span>
            </div>
            <span class="badge badge-settled">₱{{ number_format($totalActualCost / 1000000, 2) }}M Incurred</span>
        </div>

        <div class="chart-container-wrapper">
            <canvas id="costCategoryChart"></canvas>
            <div class="chart-center-metric">
                <span class="metric-number" style="font-size: 1.15rem; color: #38bdf8;">₱{{ number_format($totalActualCost / 1000000, 1) }}M</span>
                <span class="metric-label">Incurred</span>
            </div>
        </div>

        <div class="chart-legend-grid">
            @php
                $catColors = ['#38bdf8', '#818cf8', '#f59e0b', '#ec4899', '#10b981', '#14b8a6', '#a855f7', '#64748b'];
            @endphp
            @foreach($costCategories as $idx => $catName)
                <div class="legend-pill" title="{{ $catName }}: ₱{{ number_format($costCategoryTotals[$idx] ?? 0, 2) }}">
                    <span class="legend-dot" style="background: {{ $catColors[$idx % count($catColors)] }}; color: {{ $catColors[$idx % count($catColors)] }};"></span>
                    <span class="legend-text">{{ Str::limit($catName, 13) }}</span>
                    <strong style="margin-left: auto; font-family: var(--font-mono); font-size: 0.725rem;">
                        ₱{{ number_format(($costCategoryTotals[$idx] ?? 0) / 1000, 0) }}k
                    </strong>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Circle Graph 3: Financial Capital & Margin Allocation -->
    <div class="chart-card">
        <div class="panel-header" style="margin-bottom: 8px;">
            <div>
                <h3 class="panel-title">Financial Margin & Budget</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Cost vs profit margin vs contract</span>
            </div>
            <span class="badge badge-healthy">{{ $avgGrossMarginPercent }}% Profit Margin</span>
        </div>

        <div class="chart-container-wrapper">
            <canvas id="financialMarginChart"></canvas>
            <div class="chart-center-metric">
                <span class="metric-number" style="font-size: 1.25rem; color: #10b981;">{{ $avgGrossMarginPercent }}%</span>
                <span class="metric-label">Margin Rate</span>
            </div>
        </div>

        <div class="chart-legend-grid" style="grid-template-columns: 1fr;">
            <div style="display: flex; gap: 8px;">
                <div class="legend-pill" style="flex: 1;" title="Actual Incurred: ₱{{ number_format($totalActualCost, 2) }}">
                    <span class="legend-dot" style="background: #ef4444; color: #ef4444;"></span>
                    <span class="legend-text">Incurred</span>
                    <strong style="margin-left: auto; font-family: var(--font-mono); color: #f87171;">₱{{ number_format($totalActualCost / 1000, 0) }}k</strong>
                </div>
                <div class="legend-pill" style="flex: 1;" title="Gross Profit Margin: ₱{{ number_format($totalGrossMargin, 2) }}">
                    <span class="legend-dot" style="background: #10b981; color: #10b981;"></span>
                    <span class="legend-text">Margin</span>
                    <strong style="margin-left: auto; font-family: var(--font-mono); color: #34d399;">₱{{ number_format($totalGrossMargin / 1000, 0) }}k</strong>
                </div>
            </div>
            <div class="legend-pill" title="Total Contract Value: ₱{{ number_format($totalContractBudget, 2) }}">
                <span class="legend-dot" style="background: #38bdf8; color: #38bdf8;"></span>
                <span class="legend-text">Total Contract Portfolio Budget</span>
                <strong style="margin-left: auto; font-family: var(--font-mono); color: #38bdf8;">₱{{ number_format($totalContractBudget / 1000, 0) }}k</strong>
            </div>
        </div>
    </div>

</div>

<!-- Multi-Trade Progression Circular Meters & Bars -->
<div class="glass-panel">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Active Projects Trade Work Progression</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Real-time averages across ongoing construction sites</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <span class="badge badge-in_progress">{{ $ongoingCount }} Active Sites</span>
        </div>
    </div>

    <!-- Trade Circular Radial Progress Gauges -->
    <div class="trade-meters-grid" style="margin-bottom: 24px;">
        
        <!-- Structural Meter -->
        <div class="trade-meter-card">
            <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #38bdf8; font-size: 0.9rem;">
                Structural Works
            </div>
            <div class="circle-gauge-wrap">
                <svg class="circle-gauge-svg" viewBox="0 0 100 100">
                    <circle class="circle-gauge-bg" cx="50" cy="50" r="40"></circle>
                    <circle class="circle-gauge-fill structural" cx="50" cy="50" r="40"
                            stroke-dasharray="251.2"
                            stroke-dashoffset="{{ 251.2 - (251.2 * min(100, max(0, round($avgStructural)))) / 100 }}"></circle>
                </svg>
                <div class="circle-gauge-val" style="color: #38bdf8;">{{ round($avgStructural) }}%</div>
            </div>
            <div class="progress-track" style="width: 100%; height: 6px;">
                <div class="progress-bar progress-bar-structural" style="width: {{ round($avgStructural) }}%;"></div>
            </div>
            <div style="font-size: 0.725rem; color: var(--text-muted); margin-top: 8px;">Foundation & Concrete Frame</div>
        </div>

        <!-- Electrical Meter -->
        <div class="trade-meter-card">
            <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #f59e0b; font-size: 0.9rem;">
                Electrical Works
            </div>
            <div class="circle-gauge-wrap">
                <svg class="circle-gauge-svg" viewBox="0 0 100 100">
                    <circle class="circle-gauge-bg" cx="50" cy="50" r="40"></circle>
                    <circle class="circle-gauge-fill electrical" cx="50" cy="50" r="40"
                            stroke-dasharray="251.2"
                            stroke-dashoffset="{{ 251.2 - (251.2 * min(100, max(0, round($avgElectrical)))) / 100 }}"></circle>
                </svg>
                <div class="circle-gauge-val" style="color: #f59e0b;">{{ round($avgElectrical) }}%</div>
            </div>
            <div class="progress-track" style="width: 100%; height: 6px;">
                <div class="progress-bar progress-bar-electrical" style="width: {{ round($avgElectrical) }}%;"></div>
            </div>
            <div style="font-size: 0.725rem; color: var(--text-muted); margin-top: 8px;">Conduits, Panels & Wiring</div>
        </div>

        <!-- Piping & Plumbing Meter -->
        <div class="trade-meter-card">
            <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #10b981; font-size: 0.9rem;">
                Piping & Plumbing
            </div>
            <div class="circle-gauge-wrap">
                <svg class="circle-gauge-svg" viewBox="0 0 100 100">
                    <circle class="circle-gauge-bg" cx="50" cy="50" r="40"></circle>
                    <circle class="circle-gauge-fill piping" cx="50" cy="50" r="40"
                            stroke-dasharray="251.2"
                            stroke-dashoffset="{{ 251.2 - (251.2 * min(100, max(0, round($avgPiping)))) / 100 }}"></circle>
                </svg>
                <div class="circle-gauge-val" style="color: #10b981;">{{ round($avgPiping) }}%</div>
            </div>
            <div class="progress-track" style="width: 100%; height: 6px;">
                <div class="progress-bar progress-bar-piping" style="width: {{ round($avgPiping) }}%;"></div>
            </div>
            <div style="font-size: 0.725rem; color: var(--text-muted); margin-top: 8px;">Water Risers & Drainage</div>
        </div>

        <!-- Overall Site Average Meter -->
        <div class="trade-meter-card">
            <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: #f87171; font-size: 0.9rem;">
                Overall Progress
            </div>
            <div class="circle-gauge-wrap">
                <svg class="circle-gauge-svg" viewBox="0 0 100 100">
                    <circle class="circle-gauge-bg" cx="50" cy="50" r="40"></circle>
                    <circle class="circle-gauge-fill overall" cx="50" cy="50" r="40"
                            stroke-dasharray="251.2"
                            stroke-dashoffset="{{ 251.2 - (251.2 * min(100, max(0, round($avgOverall)))) / 100 }}"></circle>
                </svg>
                <div class="circle-gauge-val" style="color: #f87171;">{{ round($avgOverall) }}%</div>
            </div>
            <div class="progress-track" style="width: 100%; height: 6px;">
                <div class="progress-bar progress-bar-overall" style="width: {{ round($avgOverall) }}%;"></div>
            </div>
            <div style="font-size: 0.725rem; color: var(--text-muted); margin-top: 8px;">Company-wide Site Mean</div>
        </div>

    </div>
</div>

<!-- ====================================================
     SECTION 4: ACTIVE ONGOING PROJECTS MATRIX WITH SUMMARY & DEPLOYMENT
     ==================================================== -->
<div class="glass-panel">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Active Projects, Workforce Deployment & Financials</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Real-time execution, manpower allocations, costing, and trade tracking</span>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="/costing" class="btn-secondary" style="font-size: 0.825rem; padding: 6px 14px;">Costing Matrix &rarr;</a>
            <a href="/projects" class="btn-primary" style="font-size: 0.825rem; padding: 6px 14px;">View All Projects &rarr;</a>
        </div>
    </div>

    <table class="custom-table">
        <thead>
            <tr>
                <th>Code & Project Title</th>
                <th>Client & Area</th>
                <th>Workforce Deployed</th>
                <th>Incurred Cost vs Budget</th>
                <th>Trade Progress</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ongoingProjects as $prj)
            <tr>
                <td>
                    <strong style="color: var(--text-primary); font-size: 0.95rem;">{{ $prj->title }}</strong>
                    <div style="font-family: var(--font-mono); font-size: 0.775rem; color: #ef4444;">{{ $prj->project_code }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $prj->project_type }}</div>
                </td>
                <td>
                    <strong style="color: var(--text-secondary);">{{ $prj->client_name }}</strong>
                    <div style="margin-top: 4px; display: flex; flex-direction: column; gap: 2px;">
                        <span class="spec-chip" style="font-size: 0.7rem;">{{ number_format($prj->land_area_sqm) }} m² Land</span>
                        <span class="spec-chip" style="font-size: 0.7rem;">{{ number_format($prj->floor_area_sqm) }} m² Floor</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 3px;">
                        <div style="font-weight: 800; font-family: var(--font-mono); color: #38bdf8; font-size: 0.95rem;">
                            {{ $prj->total_deployed_manpower }} Total
                        </div>
                        <div style="font-size: 0.725rem; color: var(--text-muted);">
                            {{ $prj->deployed_workers }} Workers &bull; {{ $prj->deployed_engineers }} Engr
                        </div>
                        <div style="font-size: 0.725rem; color: var(--text-muted);">
                            {{ $prj->deployed_architects }} Arch &bull; {{ $prj->deployed_operators }} Ops
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-family: var(--font-mono);">
                        <strong style="color: #38bdf8;">₱{{ number_format($prj->total_incurred_cost, 2) }}</strong>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">of ₱{{ number_format($prj->contract_budget, 2) }}</div>
                        <div style="font-size: 0.75rem; color: {{ $prj->gross_margin >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            Margin: ₱{{ number_format($prj->gross_margin, 2) }}
                        </div>
                    </div>
                </td>
                <td style="min-width: 150px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px;">
                        <span>Overall</span>
                        <strong style="font-family: var(--font-mono);">{{ $prj->overall_progress }}%</strong>
                    </div>
                    <div class="progress-track">
                        <div class="progress-bar progress-bar-overall" style="width: {{ $prj->overall_progress }}%;"></div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-{{ $prj->status }}">{{ str_replace('_', ' ', $prj->status) }}</span>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <a href="{{ route('projects.show', $prj->id) }}" class="btn-primary" style="font-size: 0.75rem; padding: 5px 10px; text-align: center;">
                            Master Summary &rarr;
                        </a>
                        <a href="{{ route('costing.index', ['project_id' => $prj->id]) }}" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; text-align: center;">
                            Costing
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js failed to load.');
        return;
    }

    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif";
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.95)';
    Chart.defaults.plugins.tooltip.titleColor = '#f8fafc';
    Chart.defaults.plugins.tooltip.bodyColor = '#e2e8f0';
    Chart.defaults.plugins.tooltip.borderColor = 'rgba(255, 255, 255, 0.12)';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.boxPadding = 6;
    Chart.defaults.plugins.tooltip.usePointStyle = true;

    const formatCurrency = (val) => '₱' + Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const formatCurrencyM = (val) => '₱' + (Number(val) / 1000000).toFixed(2) + 'M';

    // ==========================================
    // 0. Multi-Year Sales Trajectory Chart (2024 - 2027)
    // ==========================================
    const salesCtx = document.getElementById('yearlySalesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: @json($yearlySalesLabels),
                datasets: [
                    {
                        label: 'Booked Contract Sales',
                        data: @json($yearlyBookedSales),
                        backgroundColor: '#38bdf8',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Cleared Cash Inflow',
                        data: @json($yearlyClearedRevenue),
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Incurred Cost',
                        data: @json($yearlyIncurredCost),
                        backgroundColor: '#ef4444',
                        borderRadius: 6,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { font: { weight: 'bold' } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: {
                            callback: function (val) {
                                return '₱' + (val / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11, weight: 'bold' } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ` ${ctx.dataset.label}: ${formatCurrency(ctx.raw)}`;
                            }
                        }
                    }
                },
                animation: { duration: 1200 }
            }
        });
    }

    // ==========================================
    // 1. Project Status Circle Chart
    // ==========================================
    const statusCtx = document.getElementById('projectStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['In Progress', 'Approved / Planned', 'On Hold', 'Completed'],
                datasets: [{
                    data: [
                        {{ $inProgressCount }},
                        {{ $approvedCount }},
                        {{ $onHoldCount }},
                        {{ $completedCount }}
                    ],
                    backgroundColor: ['#38bdf8', '#f59e0b', '#ef4444', '#10b981'],
                    borderColor: '#0f172a',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const val = ctx.raw || 0;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${val} Sites (${pct}%)`;
                            }
                        }
                    }
                },
                animation: { animateScale: true, animateRotate: true, duration: 1000 }
            }
        });
    }

    // ==========================================
    // 2. Cost Breakdown Circle Chart
    // ==========================================
    const costCtx = document.getElementById('costCategoryChart');
    if (costCtx) {
        const catLabels = @json($costCategories);
        const catData = @json($costCategoryTotals);
        const palette = ['#38bdf8', '#818cf8', '#f59e0b', '#ec4899', '#10b981', '#14b8a6', '#a855f7', '#64748b'];

        new Chart(costCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: palette.slice(0, catLabels.length),
                    borderColor: '#0f172a',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const val = ctx.raw || 0;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${formatCurrency(val)} (${pct}%)`;
                            }
                        }
                    }
                },
                animation: { animateScale: true, animateRotate: true, duration: 1200 }
            }
        });
    }

    // ==========================================
    // 3. Financial Margin Circle Chart
    // ==========================================
    const marginCtx = document.getElementById('financialMarginChart');
    if (marginCtx) {
        new Chart(marginCtx, {
            type: 'doughnut',
            data: {
                labels: ['Total Incurred Cost', 'Projected Gross Margin'],
                datasets: [{
                    data: [{{ (float)$totalActualCost }}, {{ (float)$totalGrossMargin }}],
                    backgroundColor: ['#ef4444', '#10b981'],
                    borderColor: '#0f172a',
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const val = ctx.raw || 0;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${formatCurrency(val)} (${pct}%)`;
                            }
                        }
                    }
                },
                animation: { animateScale: true, animateRotate: true, duration: 1400 }
            }
        });
    }
});
</script>
@endsection
