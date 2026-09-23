@extends('layouts.app')

@section('title', 'Project History & Completed Archives - St. Bilfrid Development Corporation')
@section('page_title', 'Project History & Completed Archives')

@section('top_actions')
    <a href="/projects" class="btn-secondary" style="font-size: 0.85rem;">
        Active Projects
    </a>
    <a href="/payments" class="btn-secondary" style="font-size: 0.85rem;">
        Internal Payment Ledger
    </a>
@endsection

@section('content')

<!-- Historical KPI Summary -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Completed Projects</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">DELIVERED</span>
        </div>
        <div class="kpi-val" style="color: #10b981;">{{ $completedProjects->count() }} Sites</div>
        <div class="kpi-sub">100% Successfully Delivered & Turned Over</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Archived Contract Sales</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981; border-color: rgba(16, 185, 129, 0.4);">SALES</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981; font-weight: 800; font-size: 1.6rem;">₱{{ number_format($totalCompletedBudget, 2) }}</div>
        <div class="kpi-sub">Total Historical Sales Value</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Final Expenditure</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">EXPENSES</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #f59e0b;">₱{{ number_format($totalCompletedSpent, 2) }}</div>
        <div class="kpi-sub">Actual Execution Spend</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Realized Profit Margin</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">PROFIT</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981;">₱{{ number_format($totalRealizedMargin, 2) }}</div>
        <div class="kpi-sub" style="color: #10b981; font-weight: 700;">{{ $avgRealizedMarginPercent }}% Realized Margin</div>
    </div>

    <div class="kpi-card" style="border-top: 3px solid #10b981;">
        <div class="kpi-header">
            <span class="kpi-title">Reclaimed Excess to INV</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981; background: rgba(16, 185, 129, 0.15);">RECOVERED</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981;">+₱{{ number_format($totalExcessReturnedValue, 2) }}</div>
        <div class="kpi-sub">{{ number_format($totalExcessReturnedUnits) }} units returned to warehouse</div>
    </div>
</div>

<!-- Historical Workforce Deployed Across Completed Builds -->
<div class="glass-panel" style="border: 1px solid rgba(56, 189, 248, 0.25); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(56, 189, 248, 0.2); display: grid; place-items: center; font-size: 0.75rem; font-weight: 800; color: #38bdf8;">
                HIST
            </div>
            <div>
                <h3 class="panel-title" style="font-size: 1.15rem;">Historical Workforce & Resource Mobilization Archive</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Cumulative manpower deployed across all delivered construction projects ({{ number_format($totalFloorAreaBuilt) }} m² constructible floor space built)
                </span>
            </div>
        </div>
        <span class="badge badge-in_progress" style="font-size: 0.85rem; padding: 6px 14px;">
            {{ $totalHistoricalManpower }} Cumulative Manpower
        </span>
    </div>

    <div class="manpower-grid">
        <div class="manpower-card" style="border-left: 3px solid #38bdf8;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #0f172a; border-color: #cbd5e1;">WORKERS</span>
                <span class="manpower-count" style="color: #0f172a;">{{ $historicalWorkers }}</span>
            </div>
            <div class="manpower-title">General Laborers</div>
            <div class="manpower-role">Construction Crew</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #818cf8;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #0f172a; border-color: #cbd5e1;">TRADES</span>
                <span class="manpower-count" style="color: #0f172a;">{{ $historicalSkilled }}</span>
            </div>
            <div class="manpower-title">Skilled Tradesmen</div>
            <div class="manpower-role">Masons, Carpenters, Welders</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #f59e0b;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #0f172a; border-color: #cbd5e1;">ENGR</span>
                <span class="manpower-count" style="color: #0f172a;">{{ $historicalEngineers }}</span>
            </div>
            <div class="manpower-title">Licensed Engineers</div>
            <div class="manpower-role">Structural, Electrical, Piping</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #ec4899;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #0f172a; border-color: #cbd5e1;">ARCH</span>
                <span class="manpower-count" style="color: #0f172a;">{{ $historicalArchitects }}</span>
            </div>
            <div class="manpower-title">Architects</div>
            <div class="manpower-role">Design & Spatial Planners</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #ef4444;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #0f172a; border-color: #cbd5e1;">OPERATORS</span>
                <span class="manpower-count" style="color: #0f172a;">{{ $historicalOperators }}</span>
            </div>
            <div class="manpower-title">Equipment Operators</div>
            <div class="manpower-role">Heavy Crane & Plant</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #10b981;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #0f172a; border-color: #cbd5e1;">FOREMEN</span>
                <span class="manpower-count" style="color: #0f172a;">{{ $historicalForemen }}</span>
            </div>
            <div class="manpower-title">Site Foremen</div>
            <div class="manpower-role">Trade Supervisors</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #14b8a6;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #0f172a; border-color: #cbd5e1;">QA/QC</span>
                <span class="manpower-count" style="color: #0f172a;">{{ $historicalSafety }}</span>
            </div>
            <div class="manpower-title">Safety Officers</div>
            <div class="manpower-role">QA/QC Compliance</div>
        </div>
    </div>
</div>

<!-- Completed Projects Material Recovery & Inventory Reconciliation Hub -->
<div class="glass-panel" style="border: 1px solid rgba(16, 185, 129, 0.35); background: linear-gradient(135deg, rgba(16, 185, 129, 0.04) 0%, rgba(56, 189, 248, 0.02) 100%); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); display: grid; place-items: center; font-size: 0.8rem; font-weight: 800; color: #10b981;">
                INV
            </div>
            <div>
                <h3 class="panel-title" style="font-size: 1.15rem; color: #10b981;">Completed Projects Material Recovery & Inventory Reconciliation</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Audit and return unused construction materials, surplus rebar, fixtures, and finishes back to Central Warehouse stock
                </span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.8rem; padding: 6px 12px;">
                +₱{{ number_format($totalExcessReturnedValue, 2) }} Recovered
            </span>
            @if($totalPendingExcessUnits > 0)
                <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.8rem; padding: 6px 12px;">
                    {{ number_format($totalPendingExcessUnits) }} Units Pending Reconciliation
                </span>
            @endif
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>Completed Project</th>
                    <th>Tracked BOM Items</th>
                    <th>Returned Excess to INV</th>
                    <th>Pending Site Balance</th>
                    <th>Reconciliation Status</th>
                    <th style="text-align: right;">Inventory Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($completedProjects as $cp)
                @php
                    $cpRemainingUnits = $cp->projectMaterials->sum('remaining_qty');
                    $cpRemainingValue = $cp->projectMaterials->sum(function($pm) { return $pm->remaining_qty * $pm->unit_price; });
                    $cpReturnedUnits = $cp->total_returned_excess_units;
                    $cpReturnedVal = $cp->total_returned_excess_value;
                    $hasUnreconciled = $cpRemainingUnits > 0;
                @endphp
                <tr>
                    <td>
                        <strong style="color: var(--text-primary); font-size: 0.95rem;">{{ $cp->title }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #ef4444;">{{ $cp->project_code }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Client: {{ $cp->client_name ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #38bdf8;">{{ $cp->projectMaterials->count() }} Materials</strong>
                        <div style="font-size: 0.725rem; color: var(--text-muted);">Allocated: {{ number_format($cp->projectMaterials->sum('allocated_qty')) }} units</div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #10b981;">+{{ number_format($cpReturnedUnits) }} Units</strong>
                        <div style="font-size: 0.725rem; color: #10b981; font-weight: 700;">+₱{{ number_format($cpReturnedVal, 2) }} recovered</div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: {{ $hasUnreconciled ? '#f59e0b' : '#94a3b8' }}; font-size: 0.95rem;">
                            {{ number_format($cpRemainingUnits) }} Units
                        </strong>
                        <div style="font-size: 0.725rem; color: var(--text-muted);">Est. ₱{{ number_format($cpRemainingValue, 2) }}</div>
                    </td>
                    <td>
                        @if($hasUnreconciled)
                            <span class="badge badge-pending" style="font-size: 0.75rem;">
                                {{ number_format($cpRemainingUnits) }} Units Available
                            </span>
                        @else
                            <span class="badge badge-completed" style="font-size: 0.75rem;">
                                100% Reconciled
                            </span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <button type="button" class="btn-primary" style="font-size: 0.8rem; padding: 6px 14px; background: #10b981; border-color: #10b981; font-weight: 700;" onclick="openReconcileExcessModalForProject({{ $cp->id }}, '{{ addslashes($cp->project_code) }}', '{{ addslashes($cp->title) }}', 'completed')">
                            Add Excess to INV &rarr;
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">No completed projects found for inventory reconciliation.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Historical Completed Projects Matrix Table -->
<div class="glass-panel" style="margin-bottom: 28px;">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Archived & Completed Construction Projects</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Fully delivered infrastructure and residential contracts</span>
        </div>
    </div>

    <table class="custom-table">
        <thead>
            <tr>
                <th>Code & Project</th>
                <th>Contract Budget</th>
                <th>Final Actual Cost</th>
                <th>Total Revenue</th>
                <th>Realized Profit</th>
                <th>Completed Date</th>
                <th>Final Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($completedProjects as $cp)
            @php
                $rev = $cp->total_revenue ?? 0;
                $cost = $cp->total_incurred_cost ?? 0;
                $profit = $rev - $cost;
            @endphp
            <tr>
                <td>
                    <strong style="color: var(--text-primary); font-size: 0.95rem;">{{ $cp->title }}</strong>
                    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #ef4444;">{{ $cp->project_code }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Client: {{ $cp->client_name ?? 'N/A' }}</div>
                </td>
                <td style="font-family: var(--font-mono); font-weight: 800; font-size: 1.05rem; color: #059669;">
                    ₱{{ number_format($cp->contract_budget, 2) }}
                </td>
                <td style="font-family: var(--font-mono); font-weight: 800; font-size: 1.05rem; color: {{ $cost > $cp->contract_budget ? '#dc2626' : '#d97706' }};">
                    ₱{{ number_format($cost, 2) }}
                </td>
                <td style="font-family: var(--font-mono); font-weight: 800; font-size: 1.05rem; color: #059669;">
                    ₱{{ number_format($rev, 2) }}
                </td>
                <td style="font-family: var(--font-mono); font-weight: 800; font-size: 1.05rem; color: {{ $profit >= 0 ? '#10b981' : '#dc2626' }};">
                    ₱{{ number_format($profit, 2) }}
                </td>
                <td style="font-family: var(--font-mono); font-size: 0.825rem; color: var(--text-secondary);">
                    {{ $cp->end_date ? $cp->end_date->format('M d, Y') : 'Completed' }}
                </td>
                <td>
                    <span class="badge badge-completed">100% Completed</span>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <button type="button" class="btn-primary" style="font-size: 0.75rem; padding: 4px 8px; text-align: center; background: #10b981; border-color: #10b981; font-weight: 700;" onclick="openReconcileExcessModalForProject({{ $cp->id }}, '{{ addslashes($cp->project_code) }}', '{{ addslashes($cp->title) }}', 'completed')">
                            Add Excess to INV
                        </button>
                        <a href="{{ route('projects.show', $cp->id) }}" class="btn-primary" style="font-size: 0.775rem; padding: 4px 8px; text-align: center; white-space: nowrap;">
                            Master Summary &rarr;
                        </a>
                        <a href="{{ route('projects.printBom', $cp->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.75rem; padding: 3px 6px; text-align: center; color: #10b981; border-color: rgba(16, 185, 129, 0.35);">
                            Print BOM
                        </a>
                        <a href="{{ route('costing.index', ['project_id' => $cp->id]) }}" class="btn-secondary" style="font-size: 0.75rem; padding: 3px 6px; text-align: center;">
                            Costing Sheet
                        </a>
                        <form action="{{ route('projects.destroy', $cp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="confirmation" value="DELETE">
                            <button type="submit" class="btn-secondary" style="font-size: 0.725rem; padding: 3px 6px; text-align: center; color: #f87171; width: 100%; border-color: rgba(239,68,68,0.25);">
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="color: var(--text-muted); text-align: center; padding: 32px;">
                    No historical completed projects logged yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Historical Sales & Cleared Revenue Ledger -->
<div class="glass-panel">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Historical Sales & Cleared Revenue Transactions</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Cleared milestone receipts and completed client settlements in Philippine Pesos (₱)</span>
        </div>
        <span class="badge badge-settled">₱{{ number_format($totalHistoricalRevenue, 2) }} Settled Inflow</span>
    </div>

    <table class="custom-table">
        <thead>
            <tr>
                <th>Invoice / Ref</th>
                <th>Project Code & Title</th>
                <th>Milestone Stage</th>
                <th>Settlement Date</th>
                <th>Payment Method</th>
                <th>Amount (₱)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historicalPayments as $hp)
            <tr>
                <td style="font-family: var(--font-mono); font-weight: 700; color: #38bdf8;">
                    {{ $hp->invoice_no }}
                </td>
                <td>
                    <a href="{{ route('projects.show', $hp->project->id) }}" style="text-decoration: none; color: inherit;">
                        <strong style="color: var(--text-primary);">{{ $hp->project->title }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">{{ $hp->project->project_code }}</div>
                    </a>
                </td>
                <td>{{ $hp->payment_stage }}</td>
                <td>{{ $hp->payment_date->format('M d, Y') }}</td>
                <td><span class="spec-chip" style="font-size: 0.75rem;">{{ $hp->payment_method }}</span></td>
                <td>
                    <strong style="font-family: var(--font-mono); color: #10b981; font-size: 1rem;">
                        ₱{{ number_format($hp->amount, 2) }}
                    </strong>
                </td>
                <td>
                    <span class="badge badge-completed">Cleared</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="color: var(--text-muted); text-align: center; padding: 24px;">No historical payment transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('projects.partials.excess_materials_modal')

@endsection
