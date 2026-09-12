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
<div class="kpi-grid">
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
            <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">SALES</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #38bdf8;">₱{{ number_format($totalCompletedBudget, 2) }}</div>
        <div class="kpi-sub">Total Historical Value</div>
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
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">WORKERS</span>
                <span class="manpower-count" style="color: #38bdf8;">{{ $historicalWorkers }}</span>
            </div>
            <div class="manpower-title">General Laborers</div>
            <div class="manpower-role">Construction Crew</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #818cf8;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #818cf8;">TRADES</span>
                <span class="manpower-count" style="color: #818cf8;">{{ $historicalSkilled }}</span>
            </div>
            <div class="manpower-title">Skilled Tradesmen</div>
            <div class="manpower-role">Masons, Carpenters, Welders</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #f59e0b;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">ENGR</span>
                <span class="manpower-count" style="color: #f59e0b;">{{ $historicalEngineers }}</span>
            </div>
            <div class="manpower-title">Licensed Engineers</div>
            <div class="manpower-role">Structural, Electrical, Piping</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #ec4899;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #ec4899;">ARCH</span>
                <span class="manpower-count" style="color: #ec4899;">{{ $historicalArchitects }}</span>
            </div>
            <div class="manpower-title">Architects</div>
            <div class="manpower-role">Design & Spatial Planners</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #ef4444;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #ef4444;">OPERATORS</span>
                <span class="manpower-count" style="color: #ef4444;">{{ $historicalOperators }}</span>
            </div>
            <div class="manpower-title">Equipment Operators</div>
            <div class="manpower-role">Heavy Crane & Plant</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #10b981;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">FOREMEN</span>
                <span class="manpower-count" style="color: #10b981;">{{ $historicalForemen }}</span>
            </div>
            <div class="manpower-title">Site Foremen</div>
            <div class="manpower-role">Trade Supervisors</div>
        </div>

        <div class="manpower-card" style="border-left: 3px solid #14b8a6;">
            <div class="manpower-head">
                <span class="spec-chip" style="font-size: 0.65rem; color: #14b8a6;">QA/QC</span>
                <span class="manpower-count" style="color: #14b8a6;">{{ $historicalSafety }}</span>
            </div>
            <div class="manpower-title">Safety Officers</div>
            <div class="manpower-role">QA/QC Compliance</div>
        </div>
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
                <td style="font-family: var(--font-mono); font-weight: 700; color: #f8fafc;">
                    ₱{{ number_format($cp->contract_budget, 2) }}
                </td>
                <td style="font-family: var(--font-mono); font-weight: 700; color: #f87171;">
                    ₱{{ number_format($cost, 2) }}
                </td>
                <td style="font-family: var(--font-mono); font-weight: 700; color: #38bdf8;">
                    ₱{{ number_format($rev, 2) }}
                </td>
                <td style="font-family: var(--font-mono); font-weight: 700; color: {{ $profit >= 0 ? '#10b981' : '#ef4444' }};">
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
                        <a href="{{ route('projects.show', $cp->id) }}" class="btn-primary" style="font-size: 0.775rem; padding: 4px 8px; text-align: center; white-space: nowrap;">
                            Master Summary &rarr;
                        </a>
                        <a href="{{ route('projects.printBom', $cp->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.75rem; padding: 3px 6px; text-align: center; color: #10b981; border-color: rgba(16, 185, 129, 0.35);">
                            Print BOM
                        </a>
                        <a href="{{ route('costing.index', ['project_id' => $cp->id]) }}" class="btn-secondary" style="font-size: 0.75rem; padding: 3px 6px; text-align: center;">
                            Costing Sheet
                        </a>
                        <form action="{{ route('projects.destroy', $cp->id) }}" method="POST" onsubmit="
                            const text = prompt('DANGER: Permanently delete project archive ({{ addslashes($cp->project_code) }})?\n\nType DELETE to confirm:');
                            if (text !== 'DELETE') {
                                if (text !== null) alert('Deletion aborted: Confirmation text must exactly match DELETE.');
                                return false;
                            }
                            this.querySelector('input[name=confirmation]').value = text;
                            return true;
                        ">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="confirmation" value="">
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

@endsection
