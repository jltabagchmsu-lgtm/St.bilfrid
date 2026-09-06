@extends('layouts.app')

@section('title', ($selectedProject ? $selectedProject->title . ' - Bill of Materials (BOM)' : 'Bill of Materials (BOM) & Materials Master Table'))
@section('page_title', ($selectedProject ? 'Project Bill of Materials (BOM): ' . $selectedProject->title : 'Bill of Materials (BOM) & Materials Master Hub'))

@section('top_actions')
    @if($selectedProject)
        <a href="{{ route('projects.printBom', $selectedProject->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.85rem; color: #10b981; border-color: rgba(16, 185, 129, 0.3); display: inline-flex; align-items: center; gap: 6px; height: 38px;">
            Print 8-Page BOM
        </a>
        <a href="{{ route('projects.show', $selectedProject->id) }}" class="btn-secondary" style="font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; height: 38px;">
            View Project Master &rarr;
        </a>
    @endif
    <button class="btn-primary" onclick="openModal('addScopeItemModal')" style="display: inline-flex; align-items: center; gap: 6px; height: 38px;">
        <span>+</span> Add Scope Item
    </button>
@endsection

@section('content')

<style>
    /* Dedicated BOM Pixel-Perfect Alignment Styles */
    .bom-aligned-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .bom-aligned-table th {
        vertical-align: middle;
        padding: 12px 14px;
        background: rgba(15, 23, 42, 0.85);
        color: var(--text-secondary);
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }
    .bom-aligned-table td {
        vertical-align: middle;
        padding: 11px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        font-size: 0.85rem;
    }
    .bom-aligned-table tbody tr:hover td {
        background: rgba(56, 189, 248, 0.04);
    }
    .col-num {
        font-family: var(--font-mono);
        font-variant-numeric: tabular-nums;
    }
    .cat-pill {
        transition: all 0.2s ease;
    }
    .cat-pill:hover {
        transform: translateY(-1px);
    }
</style>

<!-- Project Selector & Quick Jump Dropdown -->
<div class="glass-panel" style="padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <form action="{{ route('bom.index') }}" method="GET" style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 320px;">
        <label style="font-weight: 700; color: var(--text-secondary); white-space: nowrap; font-size: 0.9rem;">
            Select Project BOM:
        </label>
        <select name="project_id" class="form-select" style="max-width: 440px; font-weight: 600;" onchange="this.form.submit()">
            @foreach($projects as $p)
                <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                    {{ $p->project_code }} - {{ $p->title }} ({{ ucfirst(str_replace('_', ' ', $p->status)) }}) [{{ $p->scope_items_count }} Items]
                </option>
            @endforeach
        </select>
        @if($selectedProjectId)
            <a href="{{ route('bom.index', ['project_id' => $selectedProjectId]) }}" class="btn-secondary" style="font-size: 0.825rem; padding: 8px 14px; height: 38px; display: inline-flex; align-items: center;">
                Refresh
            </a>
        @endif
    </form>

    <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Quick Switch:</span>
        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
            @foreach($projects->take(4) as $p)
                <a href="{{ route('bom.index', ['project_id' => $p->id]) }}" class="spec-chip {{ $selectedProjectId == $p->id ? 'spec-chip-active' : '' }}" style="text-decoration: none; font-size: 0.775rem; padding: 5px 10px; font-weight: 700;">
                    {{ $p->project_code }}
                </a>
            @endforeach
        </div>
    </div>
</div>

@if($selectedProject)
<!-- Individual Project Header Banner with Action Buttons -->
<div class="glass-panel" style="padding: 20px 24px; margin-bottom: 24px; background: rgba(56, 189, 248, 0.04); border: 1px solid rgba(56, 189, 248, 0.25);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; flex-direction: column; gap: 4px;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span class="badge badge-{{ $selectedProject->status }}" style="text-transform: capitalize;">{{ str_replace('_', ' ', $selectedProject->status) }}</span>
                <span style="font-family: var(--font-mono); font-size: 0.9rem; color: #38bdf8; font-weight: 800;">{{ $selectedProject->project_code }}</span>
                <span class="spec-chip" style="font-size: 0.75rem; color: #10b981; font-weight: 600;">
                    {{ $selectedProject->scopeItems->count() }} Scope Items &bull; {{ $masterMaterialsDistinctCount }} Materials Required
                </span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--text-primary); margin: 2px 0 0 0;">{{ $selectedProject->title }}</h2>
            <div style="font-size: 0.85rem; color: var(--text-secondary);">
                Client: <strong style="color: var(--text-primary);">{{ $selectedProject->client_name }}</strong> &bull; Location: {{ $selectedProject->location ?? 'Site N/A' }}
            </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <!-- Auto-Allocate Scope to Site Tracker -->
            <form action="{{ route('bom.autoAllocateScope', $selectedProject->id) }}" method="POST" onsubmit="return confirm('Synchronize and auto-allocate all materials from the Scope BOM into the Site Tracker warehouse allocation?');">
                @csrf
                <button type="submit" class="btn-primary" style="font-size: 0.8rem; height: 36px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-color: #10b981; display: inline-flex; align-items: center; gap: 6px;" title="Sync all Scope BOM materials to Site Tracker">
                    Sync Scope to Site Tracker
                </button>
            </form>

            <button class="btn-primary" style="font-size: 0.8rem; height: 36px; background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); border-color: #38bdf8; display: inline-flex; align-items: center; gap: 6px;" onclick="openModal('addScopeItemModal')">
                + Add Scope Item
            </button>
        </div>
    </div>
</div>
@endif

<!-- ====================================================
     3-TAB NAVIGATION BAR FOR BILL OF MATERIALS
     ==================================================== -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; flex-wrap: wrap;">
    <button type="button" id="tabBtnMaster" class="btn-tab active" onclick="switchBomTab('master')" style="padding: 10px 20px; font-size: 0.9rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: rgba(56, 189, 248, 0.15); color: #38bdf8; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; height: 42px;">
        Master Consolidated Materials Table
        <span class="badge" style="background: rgba(56, 189, 248, 0.3); color: #f8fafc; font-size: 0.725rem;">{{ $masterMaterialsDistinctCount }} Materials</span>
    </button>
    <button type="button" id="tabBtnScope" class="btn-tab" onclick="switchBomTab('scope')" style="padding: 10px 20px; font-size: 0.9rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid transparent; background: rgba(15, 23, 42, 0.5); color: var(--text-secondary); cursor: pointer; display: inline-flex; align-items: center; gap: 8px; height: 42px;">
        Itemized Scope BOM Breakdown (DUPA)
        <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: var(--text-secondary); font-size: 0.725rem;">{{ $selectedProject ? $selectedProject->scopeItems->count() : 0 }} Scope Items</span>
    </button>
    <button type="button" id="tabBtnSite" class="btn-tab" onclick="switchBomTab('site')" style="padding: 10px 20px; font-size: 0.9rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid transparent; background: rgba(15, 23, 42, 0.5); color: var(--text-secondary); cursor: pointer; display: inline-flex; align-items: center; gap: 8px; height: 42px;">
        Site Stock Allocations & Daily Usage
        <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: var(--text-secondary); font-size: 0.725rem;">{{ $projectMaterials->count() }} Site Items</span>
    </button>
</div>

<!-- ====================================================
     TAB 1: MASTER CONSOLIDATED MATERIALS TABLE (ALL MATERIALS ON IT)
     ==================================================== -->
<div id="tabContentMaster" class="bom-tab-content">
    <!-- Top KPI Cards for Master Materials -->
    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
        <div class="kpi-card" style="border-left: 3px solid #38bdf8;">
            <div class="kpi-header">
                <span class="kpi-title">Total Materials Direct Cost</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">MATERIALS SUM</span>
            </div>
            <div class="kpi-val col-num" style="color: #38bdf8;">₱{{ number_format($masterMaterialsTotalCost, 2) }}</div>
            <div class="kpi-sub">Across All Scope Work Phases</div>
        </div>

        <div class="kpi-card" style="border-left: 3px solid #10b981;">
            <div class="kpi-header">
                <span class="kpi-title">Distinct Material Items</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">TYPES</span>
            </div>
            <div class="kpi-val col-num" style="color: #10b981;">{{ $masterMaterialsDistinctCount }}</div>
            <div class="kpi-sub">Unique Engineering Specifications</div>
        </div>

        <div class="kpi-card" style="border-left: 3px solid #f59e0b;">
            <div class="kpi-header">
                <span class="kpi-title">Total Scope Line Entries</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">LINE ITEMS</span>
            </div>
            <div class="kpi-val col-num" style="color: #f59e0b;">{{ $masterMaterialsTotalLineCount }}</div>
            <div class="kpi-sub">Phase Allocations Across Scope Items</div>
        </div>

        <div class="kpi-card" style="border-left: 3px solid #ec4899;">
            <div class="kpi-header">
                <span class="kpi-title">Grand Total Scope Cost</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #ec4899;">TOTAL DUPA</span>
            </div>
            <div class="kpi-val col-num" style="color: #ec4899;">₱{{ number_format($scopeGrandTotal, 2) }}</div>
            <div class="kpi-sub">With Labor + Markups + Profit</div>
        </div>
    </div>

    <!-- Master Materials Table Panel with Live Search & Category Filter -->
    <div class="glass-panel" style="margin-bottom: 28px; padding: 22px;">
        <div class="panel-header" style="flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
            <div>
                <h3 class="panel-title" style="display: flex; align-items: center; gap: 10px;">
                    Complete Bill of Materials (BOM) Master Table
                </h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Unified engineering material requirements, aggregated total quantities, unit costs, and warehouse stock tracking.
                </span>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <!-- Real-time Search Input -->
                <div style="position: relative;">
                    <input type="text" id="bomSearchInput" onkeyup="filterMasterMaterials()" placeholder="Search materials..." class="form-input" style="padding: 8px 14px; font-size: 0.825rem; min-width: 240px; border-radius: var(--radius-sm);">
                </div>

                <button type="button" class="btn-secondary" style="font-size: 0.8rem; height: 36px; color: #38bdf8; border-color: rgba(56, 189, 248, 0.3); display: inline-flex; align-items: center; gap: 6px;" onclick="exportMasterBomToCsv()">
                    Export CSV
                </button>
                <button type="button" class="btn-secondary" style="font-size: 0.8rem; height: 36px; color: #10b981; border-color: rgba(16, 185, 129, 0.3); display: inline-flex; align-items: center; gap: 6px;" onclick="window.print()">
                    Print View
                </button>
            </div>
        </div>

        <!-- Category Filter Pills -->
        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 18px; align-items: center;">
            <button type="button" class="cat-pill active" onclick="filterMasterCategory('all', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 700; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(56, 189, 248, 0.2); color: #38bdf8; cursor: pointer;">
                All ({{ $masterMaterialsDistinctCount }})
            </button>
            <button type="button" class="cat-pill" onclick="filterMasterCategory('Concrete & Masonry', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 600; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor: pointer;">
                Concrete & Masonry
            </button>
            <button type="button" class="cat-pill" onclick="filterMasterCategory('Rebar & Structural Steel', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 600; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor: pointer;">
                Rebar & Steel
            </button>
            <button type="button" class="cat-pill" onclick="filterMasterCategory('Formworks & Lumber', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 600; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor: pointer;">
                Formworks & Lumber
            </button>
            <button type="button" class="cat-pill" onclick="filterMasterCategory('Roofing & Metal Sheets', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 600; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor: pointer;">
                Roofing & Metal
            </button>
            <button type="button" class="cat-pill" onclick="filterMasterCategory('Electrical Works', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 600; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor: pointer;">
                Electrical
            </button>
            <button type="button" class="cat-pill" onclick="filterMasterCategory('Plumbing & Sanitary', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 600; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor: pointer;">
                Plumbing
            </button>
            <button type="button" class="cat-pill" onclick="filterMasterCategory('Architectural & Finishes', this)" style="padding: 6px 14px; font-size: 0.775rem; font-weight: 600; border-radius: 20px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-secondary); cursor: pointer;">
                Finishes & Hardware
            </button>
        </div>

        <!-- Master Materials Full Table with Explicit Column Widths and Aligned Text -->
        <div style="overflow-x: auto;">
            <table class="bom-aligned-table" id="masterMaterialsTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="min-width: 240px; text-align: left;">Material Item & Specification</th>
                        <th style="width: 160px; text-align: left;">Category</th>
                        <th style="width: 140px; text-align: right;">Total Required Qty</th>
                        <th style="width: 80px; text-align: left;">Unit</th>
                        <th style="width: 140px; text-align: right;">Unit Price (@ ₱)</th>
                        <th style="width: 150px; text-align: right;">Total Amount (₱)</th>
                        <th style="min-width: 220px; text-align: left;">Scope Items Breakdown</th>
                        <th style="width: 140px; text-align: right;">Central Warehouse</th>
                        <th style="width: 120px; text-align: center;">Site Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($masterMaterialsList as $item)
                        <tr class="master-mat-row" data-cat="{{ $item->category }}" data-desc="{{ strtolower($item->description) }}">
                            <td style="text-align: center; color: var(--text-muted); font-size: 0.8rem;" class="col-num">
                                {{ $item->index }}
                            </td>
                            <td style="text-align: left;">
                                <strong style="color: #f8fafc; font-size: 0.925rem;">{{ $item->description }}</strong>
                            </td>
                            <td style="text-align: left;">
                                <span class="spec-chip" style="font-size: 0.7rem; color: #38bdf8; padding: 3px 8px;">
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 800; font-size: 0.95rem; color: #f8fafc;" class="col-num">
                                {{ number_format($item->total_quantity, 2) }}
                            </td>
                            <td style="text-align: left; color: var(--text-secondary); font-size: 0.85rem;">
                                {{ $item->unit }}
                            </td>
                            <td style="text-align: right; color: var(--text-secondary);" class="col-num">
                                ₱{{ number_format($item->unit_price, 2) }}
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #10b981; font-size: 0.95rem;" class="col-num">
                                ₱{{ number_format($item->total_cost, 2) }}
                            </td>
                            <td style="text-align: left;">
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($item->scope_items as $sc)
                                        <span class="spec-chip" style="font-size: 0.675rem; background: rgba(15, 23, 42, 0.8); border-color: rgba(56, 189, 248, 0.2);" title="{{ $sc['item_name'] }}: {{ $sc['line_qty'] }} {{ $item->unit }}">
                                            Item {{ $sc['item_number'] }} ({{ number_format($sc['line_qty']) }} {{ $item->unit }})
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td style="text-align: right;">
                                @if($item->warehouse_stock !== null)
                                    <span class="col-num" style="font-size: 0.8rem; font-weight: 700; color: {{ $item->warehouse_stock >= $item->total_quantity ? '#10b981' : '#f59e0b' }};">
                                        {{ number_format($item->warehouse_stock) }} {{ $item->warehouse_unit }}
                                    </span>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Stock N/A</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($item->site_allocated_qty > 0)
                                    <span class="badge badge-completed" style="font-size: 0.7rem; padding: 3px 8px;">
                                        Allocated: {{ number_format($item->site_allocated_qty) }}
                                    </span>
                                @else
                                    <span class="badge" style="font-size: 0.7rem; padding: 3px 8px; background: rgba(255,255,255,0.06); color: var(--text-muted);">
                                        Unallocated
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                                <div style="font-size: 1.05rem; font-weight: 700; color: #f8fafc;">No Material Requirements in BOM Yet</div>
                                <div style="font-size: 0.85rem; margin-top: 4px;">Add Scope Items and itemized materials to generate this Bill of Materials.</div>
                                <div style="margin-top: 16px;">
                                    <button class="btn-primary" onclick="openModal('addScopeItemModal')">
                                        + Add Scope Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($masterMaterialsList->count() > 0)
                    <tfoot>
                        <tr style="background: rgba(0, 0, 0, 0.5); font-weight: 800; border-top: 2px solid var(--border-color);">
                            <td colspan="3" style="text-align: right; text-transform: uppercase; color: var(--text-secondary); font-size: 0.85rem; padding-right: 16px;">
                                Consolidated Materials Total:
                            </td>
                            <td style="text-align: right; color: #f8fafc; font-size: 0.95rem;" class="col-num">
                                {{ number_format($masterMaterialsList->sum('total_quantity'), 2) }}
                            </td>
                            <td></td>
                            <td></td>
                            <td style="text-align: right; color: #10b981; font-size: 1.1rem;" class="col-num">
                                ₱{{ number_format($masterMaterialsTotalCost, 2) }}
                            </td>
                            <td colspan="3" style="text-align: left; font-size: 0.775rem; color: var(--text-muted); padding-left: 14px;">
                                {{ $masterMaterialsDistinctCount }} distinct material items required for construction
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- ====================================================
     TAB 2: ITEMIZED SCOPE BOM BREAKDOWN (DUPA)
     ==================================================== -->
<div id="tabContentScope" class="bom-tab-content" style="display: none;">
    <!-- Scope Financial Breakdown Summary Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px;">
        <div class="summary-block" style="border-left: 3px solid #38bdf8; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">A. Materials Subtotal</div>
            <div class="summary-block-val col-num" style="color: #38bdf8;">₱{{ number_format($scopeMaterialsSubtotal, 2) }}</div>
            <div class="summary-block-sub">Itemized Materials Sum</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #f59e0b; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">B. Labor Subtotal</div>
            <div class="summary-block-val col-num" style="color: #f59e0b;">₱{{ number_format($scopeLaborSubtotal, 2) }}</div>
            <div class="summary-block-sub">Excavation, Formwork, Trades</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #ec4899; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">C. Equipment Expense</div>
            <div class="summary-block-val col-num" style="color: #ec4899;">₱{{ number_format($scopeEquipmentSubtotal, 2) }}</div>
            <div class="summary-block-sub">Machinery & Tools Overhead</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #64748b; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Total Direct Cost (A+B+C)</div>
            <div class="summary-block-val col-num" style="color: #f8fafc;">₱{{ number_format($scopeDirectCost, 2) }}</div>
            <div class="summary-block-sub">Base Project Direct Cost</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #10b981; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Grand Total Scope Cost</div>
            <div class="summary-block-val col-num" style="color: #10b981; font-size: 1.3rem;">
                ₱{{ number_format($scopeGrandTotal, 2) }}
            </div>
            <div class="summary-block-sub">With Contingency + Taxes + Profit</div>
        </div>
    </div>

    @if($selectedProject && $selectedProject->scopeItems->count() > 0)
        <!-- Itemized Scope Accordion List (Matching Reference Screenshot) -->
        <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 28px;">
            @foreach($selectedProject->scopeItems as $item)
                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.25);">
                    <!-- Scope Item Header -->
                    <div style="padding: 16px 22px; background: rgba(0, 0, 0, 0.4); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: 800; color: #ef4444; font-family: var(--font-mono); font-size: 1.05rem;">ITEM {{ $item->item_number }}.</span>
                                <span style="font-weight: 800; font-size: 1.1rem; color: #f8fafc; text-transform: uppercase; letter-spacing: 0.02em;">{{ $item->item_name }}</span>
                                @if($item->volume_or_area)
                                    <span class="spec-chip" style="font-size: 0.75rem; color: #38bdf8; background: rgba(56, 189, 248, 0.15); border-color: rgba(56, 189, 248, 0.3);">
                                        {{ $item->volume_or_area }}
                                    </span>
                                @endif
                            </div>
                            @if($item->notes)
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">{{ $item->notes }}</div>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="text-align: right;">
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Total Item Cost (with Markups)</div>
                                <div class="col-num" style="font-weight: 800; font-size: 1.15rem; color: #10b981;">
                                    ₱{{ number_format($item->total_item_cost, 2) }}
                                </div>
                            </div>
                            <div style="display: inline-flex; gap: 6px;">
                                <button class="btn-primary" style="font-size: 0.75rem; padding: 5px 10px; background: #38bdf8; border-color: #38bdf8;" onclick="openAddScopeLineModal({{ $item->id }}, {{ $item->item_number }}, '{{ addslashes($item->item_name) }}')">
                                    + Add Line
                                </button>
                                <button class="btn-secondary" style="font-size: 0.75rem; padding: 5px 8px;" onclick="openEditScopeItemModal({{ $item->id }}, {{ $item->item_number }}, '{{ addslashes($item->item_name) }}', '{{ addslashes($item->volume_or_area ?? '') }}', '{{ addslashes($item->notes ?? '') }}', {{ $item->contingency_percent ?? 0 }}, {{ $item->taxes_percent ?? 0 }}, {{ $item->profit_percent ?? 0 }})">
                                    Edit
                                </button>
                                <form action="{{ route('projects.scopeItems.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete Item {{ $item->item_number }} ({{ $item->item_name }}) and all its line items?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="font-size: 0.75rem; padding: 5px 8px; color: #ef4444;" title="Delete Item">
                                        &times;
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 18px 22px;">
                        <!-- A. Materials Breakdown Table (Exact Match to Screenshot) -->
                        @if($item->materials->count() > 0)
                            <div style="font-weight: 700; font-size: 0.875rem; color: #38bdf8; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>A. Materials Breakdown</span>
                                <span class="col-num" style="font-size: 0.85rem; color: #38bdf8;">
                                    Subtotal: ₱{{ number_format($item->materials_subtotal, 2) }}
                                </span>
                            </div>
                            <table class="bom-aligned-table" style="margin-bottom: 16px;">
                                <thead>
                                    <tr>
                                        <th style="width: 80px; text-align: right;">Qty</th>
                                        <th style="width: 80px; text-align: left;">Unit</th>
                                        <th style="text-align: left;">Material Item Description</th>
                                        <th style="width: 140px; text-align: right;">Unit Price (@ ₱)</th>
                                        <th style="width: 150px; text-align: right;">Total Amount (₱)</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->materials as $mat)
                                        <tr>
                                            <td style="text-align: right; font-weight: 800; font-size: 0.9rem; color: #f8fafc;" class="col-num">
                                                {{ is_numeric($mat->quantity) && floor($mat->quantity) == $mat->quantity ? number_format($mat->quantity, 0) : number_format($mat->quantity, 2) }}
                                            </td>
                                            <td style="text-align: left; color: var(--text-secondary);">{{ $mat->unit }}</td>
                                            <td style="text-align: left;">
                                                <strong style="color: #f8fafc;">{{ $mat->description }}</strong>
                                            </td>
                                            <td style="text-align: right; color: var(--text-secondary);" class="col-num">
                                                ₱{{ number_format($mat->unit_price, 2) }}
                                            </td>
                                            <td style="text-align: right; font-weight: 800; color: #38bdf8; font-size: 0.9rem;" class="col-num">
                                                ₱{{ number_format($mat->total_cost, 2) }}
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 4px; justify-content: center; align-items: center;">
                                                    <button type="button" style="background:none; border:none; color:#38bdf8; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Edit Line" onclick="openEditScopeLineModal({{ $mat->id }}, 'material', '{{ addslashes($mat->description) }}', {{ $mat->quantity }}, '{{ addslashes($mat->unit) }}', {{ $mat->unit_price }})">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('projects.scopeLines.destroy', $mat->id) }}" method="POST" onsubmit="return confirm('Delete material line: {{ $mat->description }}?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.9rem; padding: 2px 4px;" title="Delete Line">&times;</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <!-- B. Labor Breakdown Table -->
                        @if($item->labors->count() > 0)
                            <div style="font-weight: 700; font-size: 0.875rem; color: #f59e0b; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>B. Labor Breakdown</span>
                                <span class="col-num" style="font-size: 0.85rem; color: #f59e0b;">
                                    Subtotal: ₱{{ number_format($item->labor_subtotal, 2) }}
                                </span>
                            </div>
                            <table class="bom-aligned-table" style="margin-bottom: 16px;">
                                <thead>
                                    <tr>
                                        <th style="width: 80px; text-align: right;">Qty</th>
                                        <th style="width: 80px; text-align: left;">Unit</th>
                                        <th style="text-align: left;">Labor Sub-activity Description</th>
                                        <th style="width: 140px; text-align: right;">Unit Rate (@ ₱)</th>
                                        <th style="width: 150px; text-align: right;">Total Amount (₱)</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->labors as $lab)
                                        <tr>
                                            <td style="text-align: right; color: #f8fafc;" class="col-num">
                                                {{ $lab->quantity > 1 ? $lab->quantity : '' }}
                                            </td>
                                            <td style="text-align: left; color: var(--text-secondary);">{{ $lab->unit }}</td>
                                            <td style="text-align: left;"><strong style="color: #f8fafc;">{{ $lab->description }}</strong></td>
                                            <td style="text-align: right; color: var(--text-secondary);" class="col-num">
                                                {{ $lab->unit_price > 0 ? '₱' . number_format($lab->unit_price, 2) : '-' }}
                                            </td>
                                            <td style="text-align: right; font-weight: 800; color: #f59e0b; font-size: 0.9rem;" class="col-num">
                                                ₱{{ number_format($lab->total_cost, 2) }}
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 4px; justify-content: center; align-items: center;">
                                                    <button type="button" style="background:none; border:none; color:#38bdf8; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Edit Line" onclick="openEditScopeLineModal({{ $lab->id }}, 'labor', '{{ addslashes($lab->description) }}', {{ $lab->quantity }}, '{{ addslashes($lab->unit) }}', {{ $lab->unit_price }})">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('projects.scopeLines.destroy', $lab->id) }}" method="POST" onsubmit="return confirm('Delete labor line: {{ $lab->description }}?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.9rem; padding: 2px 4px;" title="Delete Line">&times;</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <!-- C. Equipment Expenses Table -->
                        @if($item->equipments->count() > 0)
                            <div style="font-weight: 700; font-size: 0.875rem; color: #ec4899; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span>C. Equipment Expense & Contingency</span>
                                <span class="col-num" style="font-size: 0.85rem; color: #ec4899;">
                                    Subtotal: ₱{{ number_format($item->equipment_subtotal, 2) }}
                                </span>
                            </div>
                            <table class="bom-aligned-table" style="margin-bottom: 16px;">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Equipment / Machinery Description</th>
                                        <th style="width: 150px; text-align: right;">Total Amount (₱)</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->equipments as $eq)
                                        <tr>
                                            <td style="text-align: left;"><strong style="color: #f8fafc;">{{ $eq->description }}</strong></td>
                                            <td style="text-align: right; font-weight: 800; color: #ec4899; font-size: 0.9rem;" class="col-num">
                                                ₱{{ number_format($eq->total_cost, 2) }}
                                            </td>
                                            <td style="text-align: center;">
                                                <div style="display: inline-flex; gap: 4px; justify-content: center; align-items: center;">
                                                    <button type="button" style="background:none; border:none; color:#38bdf8; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Edit Line" onclick="openEditScopeLineModal({{ $eq->id }}, 'equipment', '{{ addslashes($eq->description) }}', {{ $eq->quantity }}, '{{ addslashes($eq->unit) }}', {{ $eq->unit_price }})">
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('projects.scopeLines.destroy', $eq->id) }}" method="POST" onsubmit="return confirm('Delete equipment line: {{ $eq->description }}?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.9rem; padding: 2px 4px;" title="Delete Line">&times;</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <!-- Direct Cost & Markups Formula Calculation Strip (Aligned Columns) -->
                        <div style="display: flex; justify-content: flex-end; margin-top: 12px;">
                            <div style="background: rgba(0, 0, 0, 0.45); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px 20px; min-width: 360px; font-size: 0.825rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <strong>DIRECT COST (A+B+C):</strong>
                                    <strong class="col-num" style="color: #f8fafc; font-size: 0.9rem;">₱{{ number_format($item->direct_cost, 2) }}</strong>
                                </div>
                                @if($item->contingency_percent > 0)
                                    <div style="display: flex; justify-content: space-between; color: var(--text-muted); margin-bottom: 3px;">
                                        <span>Plus: Contingency ({{ (int)$item->contingency_percent }}%):</span>
                                        <span class="col-num">₱{{ number_format($item->contingency_amount, 2) }}</span>
                                    </div>
                                @endif
                                @if($item->taxes_percent > 0)
                                    <div style="display: flex; justify-content: space-between; color: var(--text-muted); margin-bottom: 3px;">
                                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Taxes ({{ (int)$item->taxes_percent }}%):</span>
                                        <span class="col-num">₱{{ number_format($item->taxes_amount, 2) }}</span>
                                    </div>
                                @endif
                                @if($item->profit_percent > 0)
                                    <div style="display: flex; justify-content: space-between; color: var(--text-muted); margin-bottom: 4px;">
                                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Profit ({{ (int)$item->profit_percent }}%):</span>
                                        <span class="col-num">₱{{ number_format($item->profit_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div style="display: flex; justify-content: space-between; border-top: 1px solid #10b981; padding-top: 6px; font-weight: 800; color: #10b981; font-size: 1rem;">
                                    <span>TOTAL ITEM COST:</span>
                                    <span class="col-num">₱{{ number_format($item->total_item_cost, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 48px 20px; background: rgba(0, 0, 0, 0.2); border-radius: var(--radius-md); border: 1px dashed var(--border-color); margin-bottom: 28px;">
            <h4 style="font-size: 1.15rem; font-weight: 700; color: #f8fafc; margin-bottom: 6px;">No Itemized Scope Items Created Yet</h4>
            <p style="font-size: 0.85rem; color: var(--text-muted); max-width: 540px; margin: 0 auto 16px auto;">
                Generate an itemized Scope of Work Bill of Materials tailored specifically for <strong>{{ $selectedProject ? ($selectedProject->title ?: $selectedProject->project_code) : 'this project' }}</strong> (Foundation, Columns, Beams, Walls, Roofing, Plumbing, Electrical, Finishes) with itemized Materials (A), Labor (B), Equipment (C), and official Philippine markups.
            </p>
            <div style="display: inline-flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                <button class="btn-primary" onclick="openModal('addScopeItemModal')">
                    + Add Scope Item
                </button>
            </div>
        </div>
    @endif
</div>

<!-- ====================================================
     TAB 3: SITE STOCK ALLOCATIONS & DAILY USAGE
     ==================================================== -->
<div id="tabContentSite" class="bom-tab-content" style="display: none;">
    <!-- Project BOM Financial & Material KPIs (5 Cards) -->
    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">{{ $selectedProject ? 'Allocated Site Budget' : 'Total BOM Allocations' }}</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">ALLOCATED</span>
            </div>
            <div class="kpi-val col-num" style="color: #38bdf8;">₱{{ number_format($totalAllocatedCost, 2) }}</div>
            <div class="kpi-sub">Total Material Value Assigned</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Materials Consumed / Used</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">CONSUMED</span>
            </div>
            <div class="kpi-val col-num" style="color: #f59e0b;">₱{{ number_format($totalConsumedCost, 2) }}</div>
            <div class="kpi-sub">{{ $consumptionRate }}% Consumed to Date</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Returned Excess to Inventory</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">RECONCILED</span>
            </div>
            <div class="kpi-val col-num" style="color: #10b981;">+₱{{ number_format($totalReturnedExcessValue, 2) }}</div>
            <div class="kpi-sub">Restocked back into Central INV</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Remaining On-Site Stock</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">SITE STOCK</span>
            </div>
            <div class="kpi-val col-num" style="color: #38bdf8;">₱{{ number_format($totalRemainingValue, 2) }}</div>
            <div class="kpi-sub">Unused on Active Sites</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Low Stock / Depleted Alerts</span>
                <span class="spec-chip" style="font-size: 0.65rem; color: #ef4444;">ALERT</span>
            </div>
            <div class="kpi-val" style="color: #ef4444;">{{ $lowRemainingCount }}</div>
            <div class="kpi-sub">Below 20% remaining threshold</div>
        </div>
    </div>

    <!-- Project Bill of Materials Site Allocations Table -->
    <div class="glass-panel" style="margin-bottom: 28px; padding: 22px;">
        <div class="panel-header" style="margin-bottom: 16px;">
            <div>
                <h3 class="panel-title">
                    {{ $selectedProject ? 'Site Material Allocations - ' . $selectedProject->title : 'All Project Material Allocations' }}
                </h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Tracking warehouse-allocated stock vs logged daily consumption and excess restocked into inventory.
                </span>
            </div>
            <button class="btn-primary" style="font-size: 0.825rem; padding: 6px 14px; height: 36px;" onclick="openModal('allocateBomModal')">
                + Allocate Material
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table class="bom-aligned-table">
                <thead>
                    <tr>
                        @if(!$selectedProject)
                            <th style="text-align: left;">Project</th>
                        @endif
                        <th style="text-align: left;">Material & Category</th>
                        <th style="width: 140px; text-align: right;">Unit Price (₱)</th>
                        <th style="width: 130px; text-align: right;">Allocated Qty</th>
                        <th style="width: 120px; text-align: right;">Used Qty</th>
                        <th style="width: 130px; text-align: right;">Excess Returned</th>
                        <th style="width: 140px; text-align: right;">Remaining on Site</th>
                        <th style="width: 150px; text-align: right;">Net Cost (₱)</th>
                        <th style="width: 140px; text-align: center;">Usage Progress</th>
                        <th style="width: 180px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectMaterials as $bm)
                    @php
                        $itemAllocatedCost = $bm->allocated_qty * $bm->unit_price;
                        $itemConsumedCost = $bm->used_qty * $bm->unit_price;
                        $itemNetAllocated = $bm->net_allocated_qty;
                        $itemUsagePercent = $itemNetAllocated > 0 ? round(($bm->used_qty / $itemNetAllocated) * 100, 1) : 0;
                    @endphp
                    <tr>
                        @if(!$selectedProject)
                            <td style="text-align: left;">
                                <a href="{{ route('bom.index', ['project_id' => $bm->project->id]) }}" style="text-decoration: none; color: inherit;">
                                    <strong style="color: var(--text-primary);">{{ $bm->project->title }}</strong>
                                    <div style="font-size: 0.775rem; font-family: var(--font-mono); color: #38bdf8;">{{ $bm->project->project_code }}</div>
                                </a>
                            </td>
                        @endif
                        <td style="text-align: left;">
                            <strong style="color: #f8fafc; font-size: 0.95rem;">{{ $bm->material->name }}</strong>
                            <div style="font-size: 0.775rem; color: var(--text-muted); margin-top: 2px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                @php
                                    $isRoofingMat = str_contains(strtolower($bm->material->category ?? ''), 'roof');
                                    $isWndrMat = str_contains(strtolower($bm->material->category ?? ''), 'door') || str_contains(strtolower($bm->material->category ?? ''), 'window');
                                    $catBg = $isRoofingMat ? 'rgba(249, 115, 22, 0.15)' : ($isWndrMat ? 'rgba(129, 140, 248, 0.15)' : 'rgba(255,255,255,0.06)');
                                    $catTxt = $isRoofingMat ? '#f97316' : ($isWndrMat ? '#818cf8' : 'var(--text-secondary)');
                                    $catBdr = $isRoofingMat ? 'rgba(249, 115, 22, 0.3)' : ($isWndrMat ? 'rgba(129, 140, 248, 0.3)' : 'rgba(255,255,255,0.1)');
                                @endphp
                                <span class="badge" style="padding: 2px 7px; font-size: 0.7rem; background: {{ $catBg }}; color: {{ $catTxt }}; border: 1px solid {{ $catBdr }};">{{ $bm->material->category }}</span>
                                <span style="font-family: var(--font-mono); font-size: 0.725rem;">{{ $bm->material->material_code }}</span>
                            </div>
                        </td>
                        <td style="text-align: right; color: var(--text-secondary);" class="col-num">
                            ₱{{ number_format($bm->unit_price, 2) }} / {{ $bm->material->unit }}
                        </td>
                        <td style="text-align: right;" class="col-num">
                            <strong>{{ number_format($bm->allocated_qty) }}</strong> {{ $bm->material->unit }}
                        </td>
                        <td style="text-align: right;" class="col-num">
                            <span style="color: #f59e0b; font-weight: 700;">{{ number_format($bm->used_qty) }}</span> {{ $bm->material->unit }}
                        </td>
                        <td style="text-align: right;" class="col-num">
                            @if($bm->excess_returned_qty > 0)
                                <span style="color: #10b981; font-weight: 700;">
                                    +{{ number_format($bm->excess_returned_qty) }} {{ $bm->material->unit }}
                                </span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td style="text-align: right;" class="col-num">
                            <strong style="font-size: 0.95rem; color: {{ $bm->remaining_qty <= ($bm->allocated_qty * 0.2) && $bm->remaining_qty > 0 ? '#ef4444' : ($bm->remaining_qty > 0 ? '#38bdf8' : 'var(--text-muted)') }};">
                                {{ number_format($bm->remaining_qty) }} {{ $bm->material->unit }}
                            </strong>
                            @if($bm->remaining_qty <= ($bm->allocated_qty * 0.2) && $bm->remaining_qty > 0)
                                <span class="badge badge-overdue" style="font-size: 0.65rem; margin-left: 4px;">Low</span>
                            @elseif($bm->remaining_qty <= 0)
                                <span class="badge badge-overdue" style="font-size: 0.65rem; margin-left: 4px;">Depleted</span>
                            @endif
                        </td>
                        <td style="text-align: right;" class="col-num">
                            <strong style="color: #10b981;">₱{{ number_format($bm->net_cost, 2) }}</strong>
                            <div style="font-size: 0.725rem; color: var(--text-muted);">Used: ₱{{ number_format($bm->used_cost, 2) }}</div>
                        </td>
                        <td style="text-align: center; min-width: 120px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                                <span>{{ $itemUsagePercent }}%</span>
                            </div>
                            <div class="progress-track" style="height: 6px;">
                                <div class="progress-bar progress-bar-structural" style="width: {{ $itemUsagePercent }}%;"></div>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; gap: 4px; flex-wrap: wrap; justify-content: center;">
                                @if($bm->remaining_qty > 0)
                                    <button class="btn-primary" style="font-size: 0.725rem; padding: 3px 6px; background: #f59e0b; border-color: #f59e0b;" onclick="openLogDailyUsageModal({{ $bm->id }}, '{{ addslashes($bm->material->name) }}', {{ $bm->remaining_qty }}, '{{ $bm->material->unit }}')">
                                        Log
                                    </button>
                                    <button class="btn-primary" style="font-size: 0.725rem; padding: 3px 6px; background: #38bdf8; border-color: #38bdf8;" onclick="openTransferMaterialModal({{ $bm->id }}, '{{ addslashes($bm->material->name) }}', {{ $bm->remaining_qty }}, '{{ $bm->material->unit }}')">
                                        Transfer
                                    </button>
                                    <button class="btn-secondary" style="font-size: 0.725rem; padding: 3px 6px; color: #10b981; border-color: rgba(16, 185, 129, 0.3);" onclick="openReturnExcessModal({{ $bm->id }}, '{{ addslashes($bm->material->name) }}', {{ $bm->remaining_qty }}, '{{ $bm->material->unit }}')">
                                        Return
                                    </button>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">Fully Consumed</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $selectedProject ? 9 : 10 }}" style="color: var(--text-muted); text-align: center; padding: 36px;">
                            No materials allocated in this Project's site tracker yet. Click "+ Allocate Material" or use "Sync Scope to Site Tracker" above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grid: Daily Usage Journal & Inter-Project Transfer Ledger -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px;">
        <!-- Panel 1: Daily Usage Journal -->
        <div class="glass-panel" style="border: 1px solid rgba(245, 158, 11, 0.3); padding: 20px;">
            <div class="panel-header" style="margin-bottom: 12px;">
                <div>
                    <h3 class="panel-title" style="font-size: 1.05rem; color: #f59e0b;">Daily Material Consumption Journal</h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Daily logs of materials used on active job site</span>
                </div>
            </div>
            <div style="overflow-x: auto; max-height: 320px;">
                <table class="bom-aligned-table" style="font-size: 0.825rem;">
                    <thead>
                        <tr>
                            <th style="width: 100px; text-align: left;">Date</th>
                            <th style="text-align: left;">Project & Material</th>
                            <th style="width: 120px; text-align: right;">Qty Used</th>
                            <th style="text-align: left;">Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDailyUsages as $usage)
                            <tr>
                                <td style="text-align: left; font-size: 0.775rem;" class="col-num">{{ $usage->usage_date->format('M d, Y') }}</td>
                                <td style="text-align: left;">
                                    <strong>{{ $usage->material->name ?? 'Material' }}</strong>
                                    <div style="font-size: 0.7rem; color: #38bdf8;">{{ $usage->project->project_code ?? '' }}</div>
                                </td>
                                <td style="text-align: right;" class="col-num">
                                    <strong style="color: #f59e0b;">
                                        -{{ number_format($usage->quantity_used) }} {{ $usage->material->unit ?? 'units' }}
                                    </strong>
                                </td>
                                <td style="text-align: left; font-size: 0.775rem; color: var(--text-secondary);">
                                    {{ $usage->activity_description }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">No daily usage entries logged for this project yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Panel 2: Inter-Project Transfers -->
        <div class="glass-panel" style="border: 1px solid rgba(56, 189, 248, 0.3); padding: 20px;">
            <div class="panel-header" style="margin-bottom: 12px;">
                <div>
                    <h3 class="panel-title" style="font-size: 1.05rem; color: #38bdf8;">Inter-Project Transfers & Restocking</h3>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Surplus materials transferred between projects or stocked to central warehouse</span>
                </div>
            </div>
            <div style="overflow-x: auto; max-height: 320px;">
                <table class="bom-aligned-table" style="font-size: 0.825rem;">
                    <thead>
                        <tr>
                            <th style="width: 120px; text-align: left;">Voucher Ref</th>
                            <th style="text-align: left;">Material & Qty</th>
                            <th style="text-align: left;">Source &rarr; Destination</th>
                            <th style="width: 80px; text-align: right;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransfers as $xfer)
                            <tr>
                                <td style="text-align: left; font-size: 0.75rem; color: #38bdf8;" class="col-num">{{ $xfer->transfer_reference_no }}</td>
                                <td style="text-align: left;">
                                    <strong>{{ $xfer->material->name ?? 'Material' }}</strong>
                                    <div class="col-num" style="color: #10b981; font-weight: 700; font-size: 0.8rem;">
                                        {{ number_format($xfer->quantity_transferred) }} {{ $xfer->material->unit ?? 'units' }}
                                    </div>
                                </td>
                                <td style="text-align: left; font-size: 0.775rem;">
                                    <span style="color: #ec4899;">{{ $xfer->sourceProject->project_code ?? 'PRJ' }}</span>
                                    &rarr;
                                    <span style="color: #38bdf8; font-weight: 700;">
                                        {{ $xfer->destinationProject ? $xfer->destinationProject->project_code : 'Central Inventory (Next Build)' }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-size: 0.75rem;" class="col-num">{{ $xfer->transfer_date->format('M d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">No inter-project transfers recorded for this project yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Excess Returns Movement Log -->
    <div class="glass-panel" style="border: 1px solid rgba(16, 185, 129, 0.3); padding: 20px;">
        <div class="panel-header" style="margin-bottom: 14px;">
            <div>
                <h3 class="panel-title" style="font-size: 1.1rem; color: #10b981;">Project Site Excess Material Returns & Reconciliation Log</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">Audited material stock returned from project construction site into central warehouse</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="bom-aligned-table">
                <thead>
                    <tr>
                        <th style="width: 120px; text-align: left;">Transaction Ref</th>
                        <th style="width: 110px; text-align: left;">Date</th>
                        <th style="text-align: left;">Project Site</th>
                        <th style="text-align: left;">Material Item</th>
                        <th style="width: 140px; text-align: right;">Returned Quantity</th>
                        <th style="width: 150px; text-align: right;">Reconciled Value (₱)</th>
                        <th style="min-width: 200px; text-align: left;">Audit Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReturns as $log)
                        <tr>
                            <td style="text-align: left; font-size: 0.8rem; color: #10b981; font-weight: 700;" class="col-num">
                                {{ $log->reference_no ?? 'RET-000' }}
                            </td>
                            <td style="text-align: left; font-size: 0.8rem;">{{ $log->created_at->format('M d, Y') }}</td>
                            <td style="text-align: left;">
                                <strong style="color: #f8fafc;">{{ $log->project->project_code ?? 'N/A' }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $log->project->title ?? 'Main Site' }}</div>
                            </td>
                            <td style="text-align: left;">
                                <strong style="color: #38bdf8;">{{ $log->material->name ?? 'Material' }}</strong>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $log->material->material_code ?? '' }}</div>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;" class="col-num">
                                +{{ number_format($log->quantity) }} {{ $log->material->unit ?? 'units' }}
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #f8fafc;" class="col-num">
                                ₱{{ number_format($log->total_cost, 2) }}
                            </td>
                            <td style="text-align: left; font-size: 0.8rem; color: var(--text-muted);">{{ $log->notes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                No excess material returns recorded for this project yet. When a project has unused materials, click "Return Stock" to reconcile inventory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ====================================================
     ALL MODAL DIALOGS
     ==================================================== -->

<!-- Modal 1: Add Scope Item -->
<div class="modal-overlay" id="addScopeItemModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #ef4444;">+ Add Scope of Work Item</h3>
            <button onclick="closeModal('addScopeItemModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        @if($selectedProject)
        <form action="{{ route('projects.scopeItems.store', $selectedProject->id) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 100px 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Item No.</label>
                    <input type="number" name="item_number" class="form-input" value="{{ $selectedProject->scopeItems->count() + 1 }}" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Scope Item Name</label>
                    <input type="text" name="item_name" class="form-input" placeholder="e.g. Foundation and Footings, Columns, Tile Works" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Volume / Dimension / Area (Optional)</label>
                <input type="text" name="volume_or_area" class="form-input" placeholder="e.g. V=9.4m³, A=45m², 120 l.m.">
            </div>

            <div class="form-group">
                <label class="form-label">Scope Summary / Work Notes</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="e.g. Layout, excavation, footings rebar, formwork and concrete pour"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Contingency %</label>
                    <input type="number" step="0.01" name="contingency_percent" class="form-input" value="15.00" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Taxes %</label>
                    <input type="number" step="0.01" name="taxes_percent" class="form-input" value="6.00" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Profit %</label>
                    <input type="number" step="0.01" name="profit_percent" class="form-input" value="10.00" min="0" max="100">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addScopeItemModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #ef4444; border-color: #ef4444;">Create Scope Item</button>
            </div>
        </form>
        @endif
    </div>
</div>

<!-- Modal 2: Edit Scope Item -->
<div class="modal-overlay" id="editScopeItemModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Edit Scope of Work Item</h3>
            <button onclick="closeModal('editScopeItemModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="editScopeItemForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 100px 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Item No.</label>
                    <input type="number" id="editScopeItemNumber" name="item_number" class="form-input" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Scope Item Name</label>
                    <input type="text" id="editScopeItemName" name="item_name" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Volume / Dimension / Area</label>
                <input type="text" id="editScopeVolume" name="volume_or_area" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Scope Summary / Work Notes</label>
                <textarea id="editScopeNotes" name="notes" class="form-textarea" rows="2"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Contingency %</label>
                    <input type="number" step="0.01" id="editScopeContingency" name="contingency_percent" class="form-input" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Taxes %</label>
                    <input type="number" step="0.01" id="editScopeTaxes" name="taxes_percent" class="form-input" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Profit %</label>
                    <input type="number" step="0.01" id="editScopeProfit" name="profit_percent" class="form-input" min="0" max="100">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editScopeItemModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update Scope Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add Line Item to Scope (Material, Labor, Equipment) -->
<div class="modal-overlay" id="addScopeLineModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700; color: #38bdf8;">+ Add Line Item Entry</h3>
                <div style="font-size: 0.8rem; color: var(--text-muted);" id="addScopeLineParentTitle">Item 1</div>
            </div>
            <button onclick="closeModal('addScopeLineModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="addScopeLineForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Breakdown Category</label>
                <select name="category" class="form-select" id="addScopeLineCategory" required>
                    <option value="material" selected>A. Material Line Item</option>
                    <option value="labor">B. Labor Sub-activity</option>
                    <option value="equipment">C. Equipment / Contingency Expense</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Item / Activity Description</label>
                <input type="text" name="description" class="form-input" placeholder="e.g. 12 mm Deformed Bar, Portland Cement, Masonry Labor" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" name="quantity" class="form-input" placeholder="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" class="form-input" placeholder="pcs, bags, m³, kgs, Lump Sum" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Price (@ ₱)</label>
                    <input type="number" step="0.01" name="unit_price" class="form-input" placeholder="0.00" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addScopeLineModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #38bdf8; border-color: #38bdf8;">Add Line Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit Line Item -->
<div class="modal-overlay" id="editScopeLineModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #38bdf8;">Edit Line Item Entry</h3>
            <button onclick="closeModal('editScopeLineModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="editScopeLineForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Breakdown Category</label>
                <select name="category" class="form-select" id="editScopeLineCategory" required>
                    <option value="material">A. Material Line Item</option>
                    <option value="labor">B. Labor Sub-activity</option>
                    <option value="equipment">C. Equipment Expense</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Item / Activity Description</label>
                <input type="text" id="editScopeLineDescription" name="description" class="form-input" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" id="editScopeLineQuantity" name="quantity" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <input type="text" id="editScopeLineUnit" name="unit" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Price (@ ₱)</label>
                    <input type="number" step="0.01" id="editScopeLineUnitPrice" name="unit_price" class="form-input" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editScopeLineModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Allocate Material to Site -->
<div class="modal-overlay" id="allocateBomModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Allocate Material to Project Site</h3>
            <button onclick="closeModal('allocateBomModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('bom.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Select Target Project</label>
                <select name="project_id" class="form-select" required>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ ($selectedProjectId == $p->id) ? 'selected' : '' }}>
                            {{ $p->project_code }} - {{ $p->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Select Material from Master Inventory</label>
                <select name="material_id" class="form-select" required onchange="updatePrice(this)">
                    <option value="">-- Choose Material --</option>
                    @foreach($materialsCatalog as $m)
                        <option value="{{ $m->id }}" data-cost="{{ $m->unit_cost }}" data-unit="{{ $m->unit }}">
                            {{ $m->name }} (₱{{ number_format($m->unit_cost, 2) }}/{{ $m->unit }}) - [Stock: {{ number_format($m->stock_quantity) }} {{ $m->unit }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Allocated Quantity</label>
                    <input type="number" name="allocated_qty" class="form-input" min="1" placeholder="e.g. 500" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit Price (₱)</label>
                    <input type="number" step="0.01" id="inputUnitPrice" name="unit_price" class="form-input" placeholder="0.00" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('allocateBomModal')">Cancel</button>
                <button type="submit" class="btn-primary">Allocate to Site</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 6: Log Today's Material Usage -->
<div class="modal-overlay" id="bomDailyUsageModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #f59e0b;">Log Today's Material Consumption</h3>
            <button onclick="closeModal('bomDailyUsageModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="bomDailyUsageForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Material Name</label>
                <input type="text" id="bomDailyUsageMatName" class="form-input" readonly style="background: rgba(255,255,255,0.05); font-weight: 700;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Usage Date</label>
                    <input type="date" name="usage_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity Used Today (<span id="bomDailyUsageUnitLabel">units</span>)</label>
                    <input type="number" step="0.01" id="bomDailyUsageQtyInput" name="quantity_used" class="form-input" min="0.01" required>
                    <div style="font-size: 0.775rem; color: var(--text-muted); margin-top: 4px;">Available On-Site: <strong id="bomDailyUsageMaxLabel" style="color: #38bdf8;"></strong></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Activity Description</label>
                <input type="text" name="activity_description" class="form-input" placeholder="e.g. Foundation pour, CHB laying, Conduit wiring" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('bomDailyUsageModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #f59e0b; border-color: #f59e0b;">Save Daily Consumption</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 7: Transfer Surplus to Another Project -->
<div class="modal-overlay" id="bomTransferModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #38bdf8;">Transfer Surplus Material to Another Project</h3>
            <button onclick="closeModal('bomTransferModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="bomTransferForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Material to Transfer</label>
                <input type="text" id="bomTransferMatName" class="form-input" readonly style="background: rgba(255,255,255,0.05); font-weight: 700; color: #38bdf8;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Transfer Destination Mode</label>
                    <select name="transfer_type" class="form-select" id="bomTransferTypeSelect" onchange="toggleBomTransferDest(this.value)" required>
                        <option value="inter_project" selected>Direct Inter-Project Transfer (To another project)</option>
                        <option value="warehouse_stock">Return & Stock in Central Warehouse (For future projects)</option>
                    </select>
                </div>

                <div class="form-group" id="bomDestProjectFormGroup">
                    <label class="form-label">Select Target Project</label>
                    <select name="destination_project_id" class="form-select" id="bomDestProjectSelect">
                        <option value="">-- Choose Target Project --</option>
                        @foreach($projects as $other)
                            <option value="{{ $other->id }}">
                                {{ $other->project_code }} - {{ $other->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Transfer Date</label>
                    <input type="date" name="transfer_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantity to Transfer (<span id="bomTransferUnitLabel">units</span>)</label>
                    <input type="number" step="0.01" name="transfer_qty" id="bomTransferQtyInput" class="form-input" min="1" required>
                    <div style="font-size: 0.775rem; color: var(--text-muted); margin-top: 4px;">Available Surplus: <strong id="bomTransferMaxLabel" style="color: #10b981;"></strong></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Transfer Reason & Notes</label>
                <textarea name="reason" class="form-textarea" rows="2" placeholder="e.g. Surplus rebar transferred to next project..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('bomTransferModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #38bdf8; border-color: #38bdf8;">Confirm Transfer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 8: Return Excess Material to Central Inventory -->
<div class="modal-overlay" id="bomReturnExcessModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">↩️ Return Excess Material to Central Inventory</h3>
            <button onclick="closeModal('bomReturnExcessModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="bomReturnExcessForm" action="" method="POST">
            @csrf

            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 16px;">
                <div style="font-size: 0.8rem; color: #10b981; font-weight: 700; text-transform: uppercase;">Inventory Reconciliation:</div>
                <div style="font-weight: 700; font-size: 1rem; color: #f8fafc; margin: 4px 0;" id="bomRetMaterialName">Material</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Unused Site Stock Available for Return: <strong id="bomRetMaxQty" style="color: #38bdf8; font-family: var(--font-mono);">0</strong>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Quantity to Return to Central Warehouse Stock</label>
                <input type="number" name="return_qty" id="bomRetInputQty" class="form-input" min="1" required>
            </div>

            <div class="form-group">
                <label class="form-label">Reconciliation Notes & Reason</label>
                <textarea name="return_notes" class="form-textarea" rows="2" placeholder="e.g. Returned unused leftover bags of cement to central warehouse stock..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('bomReturnExcessModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #10b981; border-color: #10b981;">Confirm Inventory Return</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Tab Switching Logic
    function switchBomTab(tabName) {
        document.querySelectorAll('.bom-tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.btn-tab').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = 'rgba(15, 23, 42, 0.5)';
            btn.style.color = 'var(--text-secondary)';
            btn.style.borderColor = 'transparent';
        });

        if (tabName === 'master') {
            document.getElementById('tabContentMaster').style.display = 'block';
            const btn = document.getElementById('tabBtnMaster');
            btn.classList.add('active');
            btn.style.background = 'rgba(56, 189, 248, 0.15)';
            btn.style.color = '#38bdf8';
            btn.style.borderColor = 'var(--border-color)';
        } else if (tabName === 'scope') {
            document.getElementById('tabContentScope').style.display = 'block';
            const btn = document.getElementById('tabBtnScope');
            btn.classList.add('active');
            btn.style.background = 'rgba(239, 68, 68, 0.15)';
            btn.style.color = '#ef4444';
            btn.style.borderColor = 'var(--border-color)';
        } else if (tabName === 'site') {
            document.getElementById('tabContentSite').style.display = 'block';
            const btn = document.getElementById('tabBtnSite');
            btn.classList.add('active');
            btn.style.background = 'rgba(16, 185, 129, 0.15)';
            btn.style.color = '#10b981';
            btn.style.borderColor = 'var(--border-color)';
        }
    }

    // Real-time Client-side Filter for Master Materials Table
    function filterMasterMaterials() {
        const query = document.getElementById('bomSearchInput').value.toLowerCase().trim();
        const activeCatBtn = document.querySelector('.cat-pill.active');
        const selectedCat = activeCatBtn ? activeCatBtn.getAttribute('data-cat-val') || 'all' : 'all';

        const rows = document.querySelectorAll('#masterMaterialsTable tbody tr.master-mat-row');
        rows.forEach(row => {
            const desc = row.getAttribute('data-desc') || '';
            const cat = row.getAttribute('data-cat') || '';
            const matchesQuery = desc.includes(query) || cat.toLowerCase().includes(query);
            const matchesCat = (selectedCat === 'all' || cat === selectedCat);

            if (matchesQuery && matchesCat) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Category Pill Filter
    function filterMasterCategory(catName, btnEl) {
        document.querySelectorAll('.cat-pill').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = 'rgba(255,255,255,0.05)';
            btn.style.color = 'var(--text-secondary)';
            btn.removeAttribute('data-cat-val');
        });

        btnEl.classList.add('active');
        btnEl.setAttribute('data-cat-val', catName);
        btnEl.style.background = 'rgba(56, 189, 248, 0.2)';
        btnEl.style.color = '#38bdf8';

        filterMasterMaterials();
    }

    // Export Master Materials Table to CSV
    function exportMasterBomToCsv() {
        const rows = Array.from(document.querySelectorAll('#masterMaterialsTable tr'));
        let csvContent = "data:text/csv;charset=utf-8,";

        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const cols = Array.from(row.querySelectorAll('th, td')).map(col => {
                    let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""').trim();
                    return `"${text}"`;
                });
                csvContent += cols.join(",") + "\r\n";
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "Bill_of_Materials_Master_Table_{{ $selectedProject ? $selectedProject->project_code : 'All' }}.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Modal helpers
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function openAddScopeLineModal(itemId, itemNumber, itemName) {
        @if($selectedProject)
            document.getElementById('addScopeLineForm').action = '/projects/{{ $selectedProject->id }}/scope-items/' + itemId + '/lines';
            document.getElementById('addScopeLineParentTitle').innerText = 'Item ' + itemNumber + ': ' + itemName;
            openModal('addScopeLineModal');
        @endif
    }

    function openEditScopeItemModal(itemId, number, name, volume, notes, contingency, taxes, profit) {
        document.getElementById('editScopeItemForm').action = '/projects/scope-items/' + itemId;
        document.getElementById('editScopeItemNumber').value = number;
        document.getElementById('editScopeItemName').value = name;
        document.getElementById('editScopeVolume').value = volume || '';
        document.getElementById('editScopeNotes').value = notes || '';
        document.getElementById('editScopeContingency').value = contingency;
        document.getElementById('editScopeTaxes').value = taxes;
        document.getElementById('editScopeProfit').value = profit;
        openModal('editScopeItemModal');
    }

    function openEditScopeLineModal(lineId, category, desc, qty, unit, price) {
        document.getElementById('editScopeLineForm').action = '/projects/scope-lines/' + lineId;
        document.getElementById('editScopeLineCategory').value = category;
        document.getElementById('editScopeLineDescription').value = desc;
        document.getElementById('editScopeLineQuantity').value = qty;
        document.getElementById('editScopeLineUnit').value = unit;
        document.getElementById('editScopeLineUnitPrice').value = price;
        openModal('editScopeLineModal');
    }

    function openLogDailyUsageModal(bomId, matName, remainingQty, unit) {
        document.getElementById('bomDailyUsageForm').action = '/bom/' + bomId + '/daily-usage';
        document.getElementById('bomDailyUsageMatName').value = matName;
        document.getElementById('bomDailyUsageUnitLabel').innerText = unit;
        document.getElementById('bomDailyUsageMaxLabel').innerText = remainingQty + ' ' + unit;
        document.getElementById('bomDailyUsageQtyInput').max = remainingQty;
        document.getElementById('bomDailyUsageQtyInput').value = Math.min(10, remainingQty);
        openModal('bomDailyUsageModal');
    }

    function openTransferMaterialModal(bomId, matName, remainingQty, unit) {
        document.getElementById('bomTransferForm').action = '/bom/' + bomId + '/transfer-project';
        document.getElementById('bomTransferMatName').value = matName;
        document.getElementById('bomTransferUnitLabel').innerText = unit;
        document.getElementById('bomTransferMaxLabel').innerText = remainingQty + ' ' + unit;
        document.getElementById('bomTransferQtyInput').max = remainingQty;
        document.getElementById('bomTransferQtyInput').value = remainingQty;
        openModal('bomTransferModal');
    }

    function toggleBomTransferDest(mode) {
        const destGroup = document.getElementById('bomDestProjectFormGroup');
        const destSelect = document.getElementById('bomDestProjectSelect');
        if (mode === 'inter_project') {
            destGroup.style.display = 'block';
            destSelect.required = true;
        } else {
            destGroup.style.display = 'none';
            destSelect.required = false;
        }
    }

    function openReturnExcessModal(bomId, matName, remainingQty, unit) {
        document.getElementById('bomReturnExcessForm').action = '/bom/' + bomId + '/return-excess';
        document.getElementById('bomRetMaterialName').innerText = matName;
        document.getElementById('bomRetMaxQty').innerText = remainingQty + ' ' + unit;
        document.getElementById('bomRetInputQty').max = remainingQty;
        document.getElementById('bomRetInputQty').value = remainingQty;
        openModal('bomReturnExcessModal');
    }

    function updatePrice(selectEl) {
        const option = selectEl.options[selectEl.selectedIndex];
        document.getElementById('inputUnitPrice').value = option.getAttribute('data-cost') || '';
    }
</script>
@endsection
