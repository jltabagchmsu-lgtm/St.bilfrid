@extends('layouts.app')

@section('title', ($selectedProject ? $selectedProject->title . ' - Project Costing' : 'All Projects Costing Matrix') . ' - St. Bilfrid Development Corporation')
@section('page_title', $selectedProject ? 'Project Costing: ' . $selectedProject->title : 'Project Costing & Expenditure Analysis')

@section('top_actions')
    @if($selectedProject)
        <button class="btn-secondary" style="font-size: 0.85rem;" onclick="openModal('autoSyncModal')">
            Sync BOM & Tasks
        </button>
        <button class="btn-primary" style="font-size: 0.85rem;" onclick="openModal('addCostModal')">
            <span>+</span> Record Cost Item
        </button>
        <a href="{{ route('projects.show', $selectedProject->id) }}" class="btn-secondary" style="font-size: 0.85rem;">
            Master Summary &rarr;
        </a>
    @else
        <button class="btn-secondary" style="font-size: 0.85rem;" onclick="window.print()">
            Export / Print Matrix
        </button>
    @endif
@endsection

@section('content')

<!-- Project Switcher & Category Filter Bar -->
<div class="glass-panel" style="padding: 16px 22px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
        <span style="font-weight: 700; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Select Project:</span>
        <select class="form-select" style="width: auto; min-width: 320px; font-weight: 600;" onchange="window.location.href=this.value">
            <option value="{{ route('costing.index') }}" {{ !$selectedProject ? 'selected' : '' }}>
                [All Projects Consolidated Costing Matrix]
            </option>
            @foreach($allProjects as $prj)
                <option value="{{ route('costing.index', ['project_id' => $prj->id]) }}" {{ $selectedProject && $selectedProject->id == $prj->id ? 'selected' : '' }}>
                    {{ $prj->title }} ({{ $prj->project_code }})
                </option>
            @endforeach
        </select>
    </div>

    @if($selectedProject)
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.8rem; color: var(--text-muted);">Category Filter:</span>
            <a href="{{ route('costing.index', ['project_id' => $selectedProject->id]) }}" class="spec-chip {{ !$selectedCategory || $selectedCategory == 'All' ? 'spec-chip-active' : '' }}">All</a>
            @foreach($categories as $cat)
                <a href="{{ route('costing.index', ['project_id' => $selectedProject->id, 'category' => $cat]) }}" class="spec-chip {{ $selectedCategory == $cat ? 'spec-chip-active' : '' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    @endif
</div>

@if($selectedProject)
    <!-- Individual Project Costing Overview -->
    <div class="glass-panel" style="padding: 22px 28px; margin-bottom: 24px; border: 1px solid var(--border-accent); background: rgba(239, 68, 68, 0.03);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <span class="badge badge-{{ $selectedProject->status }}">{{ str_replace('_', ' ', $selectedProject->status) }}</span>
                    <span style="font-family: var(--font-mono); font-size: 0.85rem; color: #ef4444; font-weight: 700;">{{ $selectedProject->project_code }}</span>
                    <span class="spec-chip">{{ $selectedProject->project_type }}</span>
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">{{ $selectedProject->title }}</h2>
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 4px;">
                    Client: <strong style="color: var(--text-primary);">{{ $selectedProject->client_name }}</strong> &bull; Location: <strong style="color: var(--text-primary);">{{ $selectedProject->location ?? 'Main Site' }}</strong>
                </div>
            </div>

            <div style="text-align: right; display: flex; gap: 20px;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Constructible Floor Area</div>
                    <div style="font-size: 1.2rem; font-weight: 800; color: #38bdf8;">{{ number_format($selectedProject->floor_area_sqm) }} m²</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Site Land Area</div>
                    <div style="font-size: 1.2rem; font-weight: 800; color: #10b981;">{{ number_format($selectedProject->land_area_sqm) }} m²</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Budget Health</div>
                    <div>
                        @if($selectedProject->cost_health_status === 'healthy')
                            <span class="badge badge-healthy">Under Budget</span>
                        @elseif($selectedProject->cost_health_status === 'warning')
                            <span class="badge badge-warning">Near Ceiling</span>
                        @else
                            <span class="badge badge-overrun">Cost Overrun</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Costing KPI Metric Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Total Contract Value (₱)</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">CONTRACT</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: #38bdf8;">₱{{ number_format($selectedProject->contract_budget, 2) }}</div>
            <div class="kpi-sub">Target Contract Baseline</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Total Incurred Actual Cost (₱)</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #ef4444;">ACTUAL</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: #f8fafc;">₱{{ number_format($selectedProject->total_incurred_cost, 2) }}</div>
            <div class="kpi-sub">
                {{ $selectedProject->contract_budget > 0 ? round(($selectedProject->total_incurred_cost / $selectedProject->contract_budget) * 100, 1) : 0 }}% of Contract Budget Utilized
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Projected Gross Profit / Margin</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">MARGIN</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: {{ $selectedProject->gross_margin >= 0 ? '#10b981' : '#ef4444' }};">
                ₱{{ number_format($selectedProject->gross_margin, 2) }}
            </div>
            <div class="kpi-sub" style="color: {{ $selectedProject->gross_margin >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 700;">
                {{ $selectedProject->gross_margin_percent }}% Gross Margin
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Unit Cost per Floor Area</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">UNIT COST</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: #f59e0b;">
                ₱{{ number_format($selectedProject->cost_per_floor_sqm, 2) }}<span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">/m²</span>
            </div>
            <div class="kpi-sub">Land Rate: ₱{{ number_format($selectedProject->cost_per_land_sqm, 2) }}/m²</div>
        </div>
    </div>

    <!-- Multi-Category Cost Distribution Analysis -->
    @php
        $catSummary = $selectedProject->category_cost_summary;
        $totalActual = $selectedProject->total_incurred_cost > 0 ? $selectedProject->total_incurred_cost : 1;
    @endphp
    <div class="glass-panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Cost Head Breakdown & Proportional Distribution</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Itemized expenditures divided across structural, trade, labor, and operational cost heads</span>
            </div>
            <button class="btn-primary" style="font-size: 0.8rem; padding: 5px 12px;" onclick="openModal('addCostModal')">
                + Add Cost Item
            </button>
        </div>

        <!-- Proportional Cost Distribution Visual Bar -->
        <div class="cost-distribution-bar">
            @foreach($catSummary as $catName => $data)
                @if($data['actual'] > 0)
                    @php $pct = round(($data['actual'] / $totalActual) * 100, 1); @endphp
                    <div class="cost-dist-segment" style="width: {{ $pct }}%; background-color: {{ $data['color'] }};" title="{{ $catName }}: ₱{{ number_format($data['actual'], 2) }} ({{ $pct }}%)"></div>
                @endif
            @endforeach
        </div>

        <!-- Category Breakdown Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
            @foreach($catSummary as $catName => $data)
                <div style="background: rgba(0,0,0,0.3); padding: 14px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); border-left: 4px solid {{ $data['color'] }};">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.825rem; font-weight: 700; color: {{ $data['color'] }};">{{ $catName }}</span>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $data['count'] }} items</span>
                    </div>
                    <div style="font-size: 1.15rem; font-weight: 800; font-family: var(--font-mono); color: #f8fafc; margin: 6px 0 2px 0;">
                        ₱{{ number_format($data['actual'], 2) }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary); display: flex; justify-content: space-between;">
                        <span>Est: ₱{{ number_format($data['estimated'], 2) }}</span>
                        <span style="font-weight: 700; color: {{ $data['color'] }};">{{ round(($data['actual'] / $totalActual) * 100, 1) }}%</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Itemized Project Costing Table -->
    <div class="glass-panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Project Itemized Cost Ledger ({{ $costItems->count() }} Entries)</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Detailed expenditure ledger entries with unit rates, quantities, estimated vs actual spend, variance, and vendor records
                </span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;" onclick="openModal('autoSyncModal')">
                    Auto-Sync BOM & Tasks
                </button>
                <button class="btn-primary" style="font-size: 0.8rem; padding: 6px 14px;" onclick="openModal('addCostModal')">
                    + Record Cost Item
                </button>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>Cost Code & Description</th>
                    <th>Category & Type</th>
                    <th>Qty & Rate</th>
                    <th>Estimated Cost (₱)</th>
                    <th>Actual Incurred (₱)</th>
                    <th>Variance</th>
                    <th>Status</th>
                    <th>Vendor / Payee</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costItems as $cost)
                <tr>
                    <td>
                        <strong style="color: #f8fafc; font-size: 0.925rem;">{{ $cost->item_name }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.775rem; color: #ef4444; margin-top: 2px;">
                            {{ $cost->cost_code }} &bull; {{ $cost->cost_date->format('M d, Y') }}
                        </div>
                        @if($cost->notes)
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">{{ $cost->notes }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="cost-cat-pill">
                            {{ $cost->cost_category }}
                        </span>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                            Type: <strong style="color: var(--text-secondary);">{{ $cost->cost_type }}</strong>
                        </div>
                    </td>
                    <td>
                        <div style="font-family: var(--font-mono); font-size: 0.85rem;">
                            {{ number_format($cost->quantity, 2) }} {{ $cost->unit }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            @ ₱{{ number_format($cost->unit_rate, 2) }}
                        </div>
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 0.9rem; color: var(--text-secondary);">
                        ₱{{ number_format($cost->estimated_cost, 2) }}
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; color: #38bdf8;">
                        ₱{{ number_format($cost->actual_cost, 2) }}
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 0.85rem;">
                        @if($cost->variance >= 0)
                            <span style="color: #10b981;">+₱{{ number_format($cost->variance, 2) }}</span>
                            <div style="font-size: 0.725rem; color: #10b981;">Under Budget</div>
                        @else
                            <span style="color: #ef4444;">-₱{{ number_format(abs($cost->variance), 2) }}</span>
                            <div style="font-size: 0.725rem; color: #ef4444;">Overrun</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $cost->status }}">{{ ucfirst($cost->status) }}</span>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem; color: var(--text-primary); font-weight: 600;">
                            {{ $cost->vendor_payee ?? 'Internal / Firm' }}
                        </div>
                        @if($cost->reference_no)
                            <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">Ref: {{ $cost->reference_no }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px;" onclick="openEditCostModal({{ json_encode($cost) }})">
                                Edit
                            </button>
                            <form action="{{ route('projects.costs.destroy', $cost->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this cost item?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; color: #ef4444;">
                                    &times;
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        No itemized cost records logged yet for this project. Click <strong>"+ Record Cost Item"</strong> or <strong>"Auto-Sync BOM & Tasks"</strong> to initialize costing.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@else
    <!-- All Projects Consolidated Costing Matrix View -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Total System Contract Budgets</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">CONTRACT</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: #38bdf8;">₱{{ number_format($totalSystemBudget, 2) }}</div>
            <div class="kpi-sub">Across {{ $allProjects->count() }} Projects</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Total Incurred Execution Cost</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #ef4444;">ACTUAL</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: #f8fafc;">₱{{ number_format($totalSystemActualCost, 2) }}</div>
            <div class="kpi-sub">Actual Materials, Labor & Subcontract Spend</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Company-Wide Projected Margin</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">MARGIN</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981;">₱{{ number_format($totalSystemGrossMargin, 2) }}</div>
            <div class="kpi-sub" style="color: #10b981; font-weight: 700;">{{ $totalSystemMarginPercent }}% Overall Profit Margin</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Average Cost per Floor Area</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">AVG / M²</span>
            </div>
            <div class="kpi-val" style="font-family: var(--font-mono); color: #f59e0b;">₱{{ number_format($avgSystemCostPerSqm, 2) }}<span style="font-size: 0.9rem; font-weight: 500; color: var(--text-muted);">/m²</span></div>
            <div class="kpi-sub">Benchmark Construction Rate</div>
        </div>
    </div>

    <div class="glass-panel">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Master Project Costing & Profitability Matrix</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Compare cost expenditures, cost per square meter, budget variances, and profit margins across all projects</span>
            </div>
        </div>

        <table class="custom-table">
            <thead>
                <tr>
                    <th>Project Name & Code</th>
                    <th>Status</th>
                    <th>Land & Floor Area</th>
                    <th>Contract Budget (₱)</th>
                    <th>Total Actual Cost (₱)</th>
                    <th>Cost / m² Floor</th>
                    <th>Projected Margin</th>
                    <th>Budget Health</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allProjects as $prj)
                <tr>
                    <td>
                        <strong style="font-size: 1.05rem; color: var(--text-primary);">{{ $prj->title }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.8rem; color: #ef4444; margin-top: 2px;">{{ $prj->project_code }}</div>
                        <div style="font-size: 0.775rem; color: var(--text-muted);">{{ $prj->project_type }} &bull; {{ $prj->client_name }}</div>
                    </td>
                    <td>
                        <span class="badge badge-{{ $prj->status }}">{{ str_replace('_', ' ', $prj->status) }}</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 3px;">
                            <span class="spec-chip">Floor: {{ number_format($prj->floor_area_sqm) }} m²</span>
                            <span class="spec-chip">Land: {{ number_format($prj->land_area_sqm) }} m²</span>
                        </div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); font-size: 1rem; color: #f8fafc;">
                            ₱{{ number_format($prj->contract_budget, 2) }}
                        </strong>
                    </td>
                    <td>
                        <div style="font-family: var(--font-mono); font-size: 1rem; color: #38bdf8; font-weight: 700;">
                            ₱{{ number_format($prj->total_incurred_cost, 2) }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ $prj->contract_budget > 0 ? round(($prj->total_incurred_cost / $prj->contract_budget) * 100, 1) : 0 }}% of budget
                        </div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #f59e0b;">
                            ₱{{ number_format($prj->cost_per_floor_sqm, 2) }}/m²
                        </strong>
                    </td>
                    <td>
                        <div style="font-family: var(--font-mono); font-weight: 700; color: {{ $prj->gross_margin >= 0 ? '#10b981' : '#ef4444' }};">
                            ₱{{ number_format($prj->gross_margin, 2) }}
                        </div>
                        <div style="font-size: 0.75rem; color: {{ $prj->gross_margin >= 0 ? '#10b981' : '#ef4444' }}; font-weight: 600;">
                            {{ $prj->gross_margin_percent }}% Margin
                        </div>
                    </td>
                    <td>
                        @if($prj->cost_health_status === 'healthy')
                            <span class="badge badge-healthy">Under Budget</span>
                        @elseif($prj->cost_health_status === 'warning')
                            <span class="badge badge-warning">Near Ceiling</span>
                        @else
                            <span class="badge badge-overrun">Overrun</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <a href="{{ route('costing.index', ['project_id' => $prj->id]) }}" class="btn-primary" style="font-size: 0.8rem; padding: 6px 12px; text-align: center;">
                                View Costing
                            </a>
                            <a href="{{ route('projects.show', $prj->id) }}" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; text-align: center;">
                                Summary &rarr;
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Modal: Add Cost Item (When Project is selected) -->
@if($selectedProject)
<div class="modal-overlay" id="addCostModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Record Cost Item &bull; {{ $selectedProject->project_code }}</h3>
            <button onclick="closeModal('addCostModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.costs.store', $selectedProject->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Cost Item Name / Description</label>
                <input type="text" name="item_name" class="form-input" placeholder="e.g. Tower Crane 50m Boom Monthly Lease" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Cost Category</label>
                    <select name="cost_category" class="form-select" required>
                        <option value="Materials & Consumables">Materials & Consumables</option>
                        <option value="Labor & Engineering">Labor & Engineering</option>
                        <option value="Equipment & Heavy Machinery">Equipment & Heavy Machinery</option>
                        <option value="Subcontractor & Trade">Subcontractor & Trade</option>
                        <option value="Permits & Regulatory">Permits & Regulatory</option>
                        <option value="Site Overhead & Utilities">Site Overhead & Utilities</option>
                        <option value="Contingency & Testing">Contingency & Testing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Cost Classification Type</label>
                    <select name="cost_type" class="form-select" required>
                        <option value="Direct">Direct Cost</option>
                        <option value="Indirect">Indirect Cost</option>
                        <option value="Subcontract">Subcontracted Trade</option>
                        <option value="Overhead">Site Overhead</option>
                        <option value="Contingency">Contingency / Safety</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" name="quantity" id="addQty" class="form-input" value="1.00" oninput="calcAddCostTotal()" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" name="unit" class="form-input" placeholder="e.g. months, days, lot, sq.m, tons" value="lot" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Rate (₱)</label>
                    <input type="number" step="0.01" name="unit_rate" id="addRate" class="form-input" placeholder="0.00" oninput="calcAddCostTotal()" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Estimated Cost (₱)</label>
                    <input type="number" step="0.01" name="estimated_cost" id="addEstimated" class="form-input" placeholder="Auto-calculated">
                </div>

                <div class="form-group">
                    <label class="form-label">Actual Incurred Cost (₱)</label>
                    <input type="number" step="0.01" name="actual_cost" id="addActual" class="form-input" placeholder="Auto-calculated">
                </div>

                <div class="form-group">
                    <label class="form-label">Expenditure Status</label>
                    <select name="status" class="form-select" required>
                        <option value="incurred">Incurred / Active</option>
                        <option value="budgeted">Budgeted / Estimated</option>
                        <option value="committed">Committed / PO Issued</option>
                        <option value="settled">Settled & Cleared</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Cost Incurred Date</label>
                    <input type="date" name="cost_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Vendor / Contractor / Payee</label>
                    <input type="text" name="vendor_payee" class="form-input" placeholder="e.g. Heavy Equipment Rentals Co.">
                </div>

                <div class="form-group">
                    <label class="form-label">Reference No (PO / Invoice / Voucher)</label>
                    <input type="text" name="reference_no" class="form-input" placeholder="e.g. PO-2026-889">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes / Remarks</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="Engineering remarks, contract terms, or billing breakdown..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addCostModal')">Cancel</button>
                <button type="submit" class="btn-primary">Record Cost Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Cost Item -->
<div class="modal-overlay" id="editCostModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Edit Cost Item &bull; <span id="editModalCode" style="color: #ef4444;"></span></h3>
            <button onclick="closeModal('editCostModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="editCostForm" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Cost Item Name / Description</label>
                <input type="text" name="item_name" id="editItemName" class="form-input" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Cost Category</label>
                    <select name="cost_category" id="editCategory" class="form-select" required>
                        <option value="Materials & Consumables">Materials & Consumables</option>
                        <option value="Labor & Engineering">Labor & Engineering</option>
                        <option value="Equipment & Heavy Machinery">Equipment & Heavy Machinery</option>
                        <option value="Subcontractor & Trade">Subcontractor & Trade</option>
                        <option value="Permits & Regulatory">Permits & Regulatory</option>
                        <option value="Site Overhead & Utilities">Site Overhead & Utilities</option>
                        <option value="Contingency & Testing">Contingency & Testing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Cost Classification Type</label>
                    <select name="cost_type" id="editCostType" class="form-select" required>
                        <option value="Direct">Direct Cost</option>
                        <option value="Indirect">Indirect Cost</option>
                        <option value="Subcontract">Subcontracted Trade</option>
                        <option value="Overhead">Site Overhead</option>
                        <option value="Contingency">Contingency / Safety</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" name="quantity" id="editQty" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" name="unit" id="editUnit" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Rate (₱)</label>
                    <input type="number" step="0.01" name="unit_rate" id="editRate" class="form-input" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Estimated Cost (₱)</label>
                    <input type="number" step="0.01" name="estimated_cost" id="editEstimated" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Actual Incurred Cost (₱)</label>
                    <input type="number" step="0.01" name="actual_cost" id="editActual" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Expenditure Status</label>
                    <select name="status" id="editStatus" class="form-select" required>
                        <option value="budgeted">Budgeted / Estimated</option>
                        <option value="committed">Committed / PO Issued</option>
                        <option value="incurred">Incurred / Active</option>
                        <option value="settled">Settled & Cleared</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Cost Incurred Date</label>
                    <input type="date" name="cost_date" id="editDate" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Vendor / Contractor / Payee</label>
                    <input type="text" name="vendor_payee" id="editVendor" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Reference No</label>
                    <input type="text" name="reference_no" id="editRef" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes / Remarks</label>
                <textarea name="notes" id="editNotes" class="form-textarea" rows="2"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editCostModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update Cost Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Auto-Sync Confirmation -->
<div class="modal-overlay" id="autoSyncModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Auto-Sync Project Costing</h3>
            <button onclick="closeModal('autoSyncModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px;">
            This will automatically import and sync:
            <br>• <strong>Project Materials (BOM)</strong> consumed quantities and unit rates under <em>"Materials & Consumables"</em>
            <br>• <strong>Scheduled Tasks</strong> allocated budgets and actual incurred costs under <em>"Labor & Engineering"</em>
        </p>

        <form action="{{ route('projects.costs.autoSync', $selectedProject->id) }}" method="POST">
            @csrf
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn-secondary" onclick="closeModal('autoSyncModal')">Cancel</button>
                <button type="submit" class="btn-primary">Execute Auto-Sync</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function calcAddCostTotal() {
        const qty = parseFloat(document.getElementById('addQty').value) || 0;
        const rate = parseFloat(document.getElementById('addRate').value) || 0;
        const total = (qty * rate).toFixed(2);
        
        const estField = document.getElementById('addEstimated');
        const actField = document.getElementById('addActual');
        
        if (!estField.value || estField.dataset.manual !== 'true') {
            estField.value = total;
        }
        if (!actField.value || actField.dataset.manual !== 'true') {
            actField.value = total;
        }
    }

    function openEditCostModal(cost) {
        document.getElementById('editCostForm').action = '/projects/costs/' + cost.id + '/update';
        document.getElementById('editModalCode').innerText = cost.cost_code;
        document.getElementById('editItemName').value = cost.item_name;
        document.getElementById('editCategory').value = cost.cost_category;
        document.getElementById('editCostType').value = cost.cost_type;
        document.getElementById('editQty').value = cost.quantity;
        document.getElementById('editUnit').value = cost.unit;
        document.getElementById('editRate').value = cost.unit_rate;
        document.getElementById('editEstimated').value = cost.estimated_cost;
        document.getElementById('editActual').value = cost.actual_cost;
        document.getElementById('editStatus').value = cost.status;
        document.getElementById('editDate').value = cost.cost_date ? cost.cost_date.substring(0, 10) : '';
        document.getElementById('editVendor').value = cost.vendor_payee || '';
        document.getElementById('editRef').value = cost.reference_no || '';
        document.getElementById('editNotes').value = cost.notes || '';
        openModal('editCostModal');
    }
</script>
@endsection
