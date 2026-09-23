@extends('layouts.app')

@section('title', 'Materials Inventory (INV) - St. Bilfrid Development Corporation')
@section('page_title', 'Materials Inventory (INV) - Master Warehouse Catalog')

@section('top_actions')
    <button class="btn-primary" style="background: #10b981; border-color: #10b981; font-weight: 700; display: flex; align-items: center; gap: 6px;" onclick="openSelectProjectForExcessModal()">
        <span>📦</span> Reconcile Project Excess Materials
    </button>
    <button class="btn-secondary" onclick="openModal('addMaterialModal')">
        <span>+</span> Add New Material to Catalog
    </button>
@endsection

@section('content')

<!-- Inventory Valuation KPI Cards -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title" style="color: #000000; font-weight: 700;">Warehouse Valuation</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #000000; font-weight: 700; border-color: #cbd5e1;">VALUATION</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #000000;">₱{{ number_format($totalValuation, 2) }}</div>
        <div class="kpi-sub" style="color: #000000;">Total Capital in Stock</div>
    </div>

    <div class="kpi-card" style="border-top: 3px solid #000000;">
        <div class="kpi-header">
            <span class="kpi-title" style="color: #000000; font-weight: 700;">Reclaimed Excess to Stock</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #000000; background: #f1f5f9; border-color: #cbd5e1; font-weight: 700;">RECOVERED</span>
        </div>
        <div class="kpi-val" style="color: #000000; font-family: var(--font-mono);">+₱{{ number_format($totalReturnedValuation, 2) }}</div>
        <div class="kpi-sub" style="color: #000000;">{{ number_format($totalReturnedUnits) }} units returned from completed builds</div>
    </div>

    <div class="kpi-card" style="border-top: 3px solid #000000;">
        <div class="kpi-header">
            <span class="kpi-title" style="color: #000000; font-weight: 700;">Purchased New Products</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #000000; background: #f1f5f9; border-color: #cbd5e1; font-weight: 700;">NEW ARRIVALS</span>
        </div>
        <div class="kpi-val" style="color: #000000; font-family: var(--font-mono);">{{ $newProductsCount }}</div>
        <div class="kpi-sub" style="color: #000000;">Procured from Trade Partners</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title" style="color: #000000; font-weight: 700;">Catalog Item Types</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #000000; font-weight: 700; border-color: #cbd5e1;">TYPES</span>
        </div>
        <div class="kpi-val" style="color: #000000;">{{ $totalItemsCount }}</div>
        <div class="kpi-sub" style="color: #000000;">Distinct Construction Materials</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title" style="color: #000000; font-weight: 700;">Total Warehouse Units</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #000000; font-weight: 700; border-color: #cbd5e1;">UNITS</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #000000;">{{ number_format($totalStockUnits) }}</div>
        <div class="kpi-sub" style="color: #000000;">Gross On-Hand Stock</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title" style="color: #000000; font-weight: 700;">Low Stock Warnings</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #000000; font-weight: 700; border-color: #cbd5e1;">ALERT</span>
        </div>
        <div class="kpi-val" style="color: #000000;">{{ $lowStockCount }}</div>
        <div class="kpi-sub" style="color: #000000;">Items below safety threshold</div>
    </div>
</div>

<!-- Procurement-Governed Inventory Control Briefing Banner -->
<div style="background: #fafbfc; border: 1px solid var(--border-color); border-left: 4px solid var(--primary-red); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div style="display: flex; align-items: center; gap: 14px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: #fef2f2; display: flex; align-items: center; justify-content: center; color: var(--primary-red); flex-shrink: 0; border: 1px solid rgba(220, 38, 38, 0.2);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
        <div>
            <h4 style="font-size: 0.95rem; font-weight: 800; color: #000000; margin-bottom: 2px;">Procurement-Governed Inventory System</h4>
            <p style="font-size: 0.8rem; color: #000000; margin: 0;">Warehouse inventory is replenished exclusively through verified outside Trade Suppliers (Mils Glass, Colorsteel, Titan Structural). In-stock quantities automatically increment upon PO delivery receipts and cannot be manually overridden.</p>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ route('admin.suppliers.materials') }}" class="btn-secondary" style="font-size: 0.8rem; padding: 8px 14px; text-decoration: none; color: #000000; font-weight: 600;">
            Supplier Product Matrix &rarr;
        </a>
        <a href="{{ route('admin.suppliers.index') }}" class="btn-primary" style="font-size: 0.8rem; padding: 8px 16px; text-decoration: none;">
            Central Supplier Hub &rarr;
        </a>
    </div>
</div>

<!-- Search & Filter Controls -->
<div class="glass-panel" style="padding: 18px 24px; margin-bottom: 24px;">
    <form action="{{ route('inventory.index') }}" method="GET" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
            <input type="text" name="search" class="form-input" placeholder="Search by material code or name..." value="{{ $search }}" style="max-width: 380px; color: #000000;">
            <button type="submit" class="btn-primary" style="padding: 8px 16px;">Search</button>
            @if($search || $selectedCategory || $filter)
                <a href="{{ route('inventory.index') }}" class="btn-secondary" style="padding: 8px 14px; font-size: 0.85rem; color: #000000;">Reset Filters</a>
            @endif
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; font-weight: 700; color: #000000;">Stations & Categories:</span>
            <a href="{{ route('inventory.index') }}" class="spec-chip {{ empty($selectedCategory) && empty($filter) ? 'spec-chip-active' : '' }}" style="text-decoration: none; cursor: pointer; color: #000000; font-weight: 700;">
                All ({{ $totalItemsCount }})
            </a>
            <a href="{{ route('inventory.index', ['filter' => 'new', 'search' => $search]) }}" class="spec-chip {{ $filter === 'new' ? 'spec-chip-active' : '' }}" style="text-decoration: none; cursor: pointer; color: #000000; border-color: rgba(16, 185, 129, 0.4); background: {{ $filter === 'new' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(16, 185, 129, 0.08)' }}; font-weight: 700;">
                New Products ({{ $newProductsCount }})
            </a>
            @foreach($allCategories as $cat)
                <a href="{{ route('inventory.index', ['category' => $cat, 'search' => $search]) }}" class="spec-chip {{ $selectedCategory == $cat ? 'spec-chip-active' : '' }}" style="text-decoration: none; cursor: pointer; color: #000000; font-weight: 600;">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    </form>
</div>

<!-- Master Inventory Table -->
<div class="glass-panel" style="margin-bottom: 28px;">
    <div class="panel-header">
        <div>
            <h3 class="panel-title" style="color: #000000; font-weight: 800;">Master Materials Inventory & Warehouse Stock</h3>
            <span style="font-size: 0.85rem; color: #000000;">Central warehouse stock levels synchronized automatically with Trade Supplier purchase order deliveries</span>
        </div>
        <button class="btn-secondary" style="font-size: 0.85rem; padding: 6px 14px; color: #000000; font-weight: 600;" onclick="openModal('addMaterialModal')">+ New Catalog Item</button>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="color: #000000; font-weight: 700;">Code & Material Name</th>
                    <th style="color: #000000; font-weight: 700;">Trade Category</th>
                    <th style="color: #000000; font-weight: 700;">Unit of Measure</th>
                    <th style="color: #000000; font-weight: 700;">Contract Cost (₱)</th>
                    <th style="color: #000000; font-weight: 700;">In-Stock Quantity</th>
                    <th style="color: #000000; font-weight: 700;">Total Value (₱)</th>
                    <th style="color: #000000; font-weight: 700;">Stock Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $mat)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <strong style="color: #000000; font-size: 1rem; font-weight: 700;">{{ $mat->name }}</strong>
                            @if($mat->is_new_product)
                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #000000; border: 1px solid rgba(16, 185, 129, 0.45); font-size: 0.68rem; font-weight: 800; letter-spacing: 0.04em; padding: 2px 8px; border-radius: 6px;">
                                    NEW PRODUCT
                                </span>
                            @endif
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 3px;">
                            <span style="font-family: var(--font-mono); font-size: 0.8rem; color: #000000; font-weight: 600;">{{ $mat->material_code }}</span>
                            @if($mat->last_purchased_at)
                                <span style="font-size: 0.72rem; color: #000000;">&bull; Purchased {{ $mat->last_purchased_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background: #f1f5f9; color: #000000; border: 1px solid #cbd5e1; font-weight: 700;">
                            {{ $mat->category }}
                        </span>
                    </td>
                    <td style="font-weight: 600; color: #000000;">{{ $mat->unit }}</td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #000000; font-weight: 700;">₱{{ number_format($mat->unit_cost, 2) }}</strong>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); font-size: 1.05rem; color: #000000; font-weight: 700;">
                            {{ number_format($mat->stock_quantity) }} {{ $mat->unit }}
                        </strong>
                        <div style="font-size: 0.68rem; color: #000000; display: flex; align-items: center; gap: 4px; margin-top: 2px; font-weight: 600;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Supplier-Synchronized
                        </div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #000000; font-weight: 700;">
                            ₱{{ number_format($mat->stock_quantity * $mat->unit_cost, 2) }}
                        </strong>
                    </td>
                    <td>
                        @if($mat->stock_quantity <= 0)
                            <span class="badge badge-overdue" style="color: #000000; font-weight: 700;">Out of Stock</span>
                        @elseif($mat->stock_quantity <= 500)
                            <span class="badge badge-pending" style="color: #000000; font-weight: 700;">Low Stock</span>
                        @else
                            <span class="badge badge-completed" style="color: #000000; font-weight: 700;">Well Stocked</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="color: #000000; text-align: center; padding: 36px; font-weight: 600;">
                        No material catalog items found matching filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Inventory Movement & Project Excess Returns Log -->
<div class="glass-panel" id="inventoryLogsPanel" style="border: 1px solid rgba(16, 185, 129, 0.3);">
    <div class="panel-header" style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 class="panel-title" style="font-size: 1.1rem; color: #000000; font-weight: 800;">Central Warehouse Movement & Supplier Delivery Receipts Log</h3>
            <span style="font-size: 0.85rem; color: #000000;">Audit log of supplier procurement deliveries, stock allocations, and site excess returns</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="btn-secondary" id="invLogTopPrevBtn" onclick="changeLogPage(-1)" style="padding: 6px 14px; font-size: 0.8rem; font-weight: 700; color: #000000; border-color: #cbd5e1; display: inline-flex; align-items: center; gap: 4px;">
                &larr; Prev
            </button>
            <span id="invLogTopPageIndicator" style="font-size: 0.8rem; font-weight: 700; color: #000000; font-family: var(--font-mono); padding: 4px 10px; background: #f1f5f9; border-radius: 6px; border: 1px solid #cbd5e1;">
                Page 1 of 1
            </span>
            <button type="button" class="btn-primary" id="invLogTopNextBtn" onclick="changeLogPage(1)" style="padding: 6px 16px; font-size: 0.8rem; font-weight: 700; background: #10b981; border-color: #10b981; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                Next &rarr;
            </button>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="color: #000000; font-weight: 700;">Ref / TXN</th>
                    <th style="color: #000000; font-weight: 700;">Date & Time</th>
                    <th style="color: #000000; font-weight: 700;">Type</th>
                    <th style="color: #000000; font-weight: 700;">Material Item</th>
                    <th style="color: #000000; font-weight: 700;">Destination / Source</th>
                    <th style="color: #000000; font-weight: 700;">Quantity Change</th>
                    <th style="color: #000000; font-weight: 700;">Unit Cost</th>
                    <th style="color: #000000; font-weight: 700;">Audit Verification</th>
                </tr>
            </thead>
            <tbody id="inventoryLogsTableBody">
                @forelse($inventoryLogs as $log)
                @php $tBadge = $log->transaction_badge; @endphp
                <tr class="inv-log-row">
                    <td style="font-family: var(--font-mono); color: #000000; font-weight: 700;">
                        {{ $log->reference_no ?? ('TXN-' . $log->id) }}
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 0.8rem; color: #000000;">
                        {{ $log->created_at->format('M d, Y H:i') }}
                    </td>
                    <td>
                        <span class="badge" style="background: {{ $tBadge['bg'] }}; color: #000000; border: 1px solid {{ $tBadge['border'] }}; font-size: 0.75rem; font-weight: 700;">
                            {{ $tBadge['label'] }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: #000000;">{{ $log->material->name ?? 'Material' }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #000000;">{{ $log->material->material_code ?? '' }}</div>
                    </td>
                    <td>
                        @if($log->project)
                            <a href="{{ route('projects.show', $log->project->id) }}" style="color: #000000; font-weight: 700; text-decoration: underline;">
                                {{ $log->project->title }}
                            </a>
                            <div style="font-size: 0.75rem; color: #000000;">{{ $log->project->project_code }}</div>
                        @else
                            <span style="color: #000000;">Central Warehouse Stock</span>
                        @endif
                    </td>
                    <td style="font-family: var(--font-mono); font-weight: 800; color: #000000;">
                        {{ in_array($log->transaction_type, ['excess_return', 'restock']) ? '+' : '-' }}{{ number_format($log->quantity) }} {{ $log->material->unit ?? 'units' }}
                    </td>
                    <td style="font-family: var(--font-mono); color: #000000; font-weight: 600;">
                        ₱{{ number_format($log->unit_cost, 2) }}
                    </td>
                    <td style="font-size: 0.8rem; color: #000000;">
                        {{ $log->notes ?? 'Verified inventory movement' }}
                    </td>
                </tr>
                @empty
                <tr id="invLogEmptyRow">
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        No recent inventory movements recorded.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination Controls -->
    @if(count($inventoryLogs) > 0)
    <div class="custom-pagination-container" id="invLogPaginationContainer" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-top: 16px; padding: 12px 18px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px;">
        <div class="pagination-info" style="font-size: 0.85rem; color: #334155; font-weight: 600;">
            Showing <span class="pagination-highlight" id="invLogRangeStart" style="color: #0f172a; font-weight: 700; font-family: var(--font-mono);">1</span> to <span class="pagination-highlight" id="invLogRangeEnd" style="color: #0f172a; font-weight: 700; font-family: var(--font-mono);">{{ min(7, count($inventoryLogs)) }}</span> of <span class="pagination-highlight" id="invLogTotalItems" style="color: #0f172a; font-weight: 700; font-family: var(--font-mono);">{{ count($inventoryLogs) }}</span> movement records
        </div>
        
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="btn-secondary" id="invLogPrevBtn" onclick="changeLogPage(-1)" style="padding: 7px 16px; font-size: 0.85rem; font-weight: 700; color: #334155; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
                &larr; Previous
            </button>
            
            <div id="invLogPageNumbers" style="display: flex; align-items: center; gap: 5px;">
                <!-- Dynamically generated page pills -->
            </div>

            <button type="button" class="btn-primary" id="invLogNextBtn" onclick="changeLogPage(1)" style="padding: 7px 20px; font-size: 0.85rem; font-weight: 700; background: #10b981; border: 1px solid #10b981; color: #ffffff; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25); transition: all 0.2s ease;">
                Next &rarr;
            </button>
        </div>
    </div>
    @endif
</div>

<!-- Modal 1: Add New Master Material to Catalog -->
<div class="modal-overlay" id="addMaterialModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Add Material Item to Catalog</h3>
            <button onclick="closeModal('addMaterialModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Material Name</label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Heavy Duty Structural Rebar 25mm" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="Windows & Doors">Windows & Doors</option>
                        <option value="Roofing">Roofing</option>
                        <option value="Structural & Masonry">Structural & Masonry</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Piping/Plumbing">Piping/Plumbing</option>
                        <option value="Finishing">Finishing</option>
                        <option value="General">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" name="unit" class="form-input" placeholder="e.g. bags, pcs, ln.m., sets, sheets" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Contracted Unit Cost (₱)</label>
                    <input type="number" step="0.01" name="unit_cost" class="form-input" placeholder="₱ 0.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Warehouse Stock</label>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px; font-size: 0.78rem; color: var(--text-secondary); display: flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <span>0 units (Auto-increments upon PO delivery)</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addMaterialModal')">Cancel</button>
                <button type="submit" class="btn-primary">Register in Warehouse Catalog</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Select Completed / Active Project for Excess Reconciliation -->
<div class="modal-overlay" id="selectProjectExcessModal">
    <div class="modal-box" style="max-width: 520px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: #10b981;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <h3 style="font-weight: 800; margin: 0; font-size: 1.15rem;">Reconcile Project Excess Materials</h3>
            </div>
            <button onclick="closeModal('selectProjectExcessModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        
        <div style="margin-bottom: 16px; font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">
            Select a construction project to audit, identify, and return remaining unused building materials back into Central Warehouse Inventory stock:
        </div>

        <div class="form-group">
            <label class="form-label" style="font-weight: 700;">Select Originating Project</label>
            <select id="projectToReconcileSelect" class="form-select" style="font-size: 0.9rem;">
                @if($completedProjects->count() > 0)
                <optgroup label="Completed Projects">
                    @foreach($completedProjects as $cp)
                        <option value="{{ $cp->id }}" data-code="{{ $cp->project_code }}" data-title="{{ $cp->title }}" data-status="{{ $cp->status }}">
                            ✓ {{ $cp->title }} ({{ $cp->project_code }}) — Completed ({{ $cp->projectMaterials->count() }} materials)
                        </option>
                    @endforeach
                </optgroup>
                @endif
                @if($allProjects->where('status', '!=', 'completed')->count() > 0)
                <optgroup label="Active Projects">
                    @foreach($allProjects->where('status', '!=', 'completed') as $ap)
                        <option value="{{ $ap->id }}" data-code="{{ $ap->project_code }}" data-title="{{ $ap->title }}" data-status="{{ $ap->status }}">
                            &bull; {{ $ap->title }} ({{ $ap->project_code }}) — {{ ucfirst(str_replace('_', ' ', $ap->status)) }}
                        </option>
                    @endforeach
                </optgroup>
                @endif
                @if($allProjects->count() === 0)
                    <option value="">-- No projects registered in system --</option>
                @endif
            </select>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button" class="btn-secondary" onclick="closeModal('selectProjectExcessModal')">Cancel</button>
            <button type="button" class="btn-primary" style="background: #10b981; border-color: #10b981; font-weight: 700;" onclick="proceedToProjectExcessReconcile()">
                Open Excess Materials Reconciler &rarr;
            </button>
        </div>
    </div>
</div>

@include('projects.partials.excess_materials_modal')

@endsection

@section('scripts')
<script>
    function openModal(id) { 
        const el = document.getElementById(id);
        if (el) el.classList.add('active'); 
    }
    function closeModal(id) { 
        const el = document.getElementById(id);
        if (el) el.classList.remove('active'); 
    }

    function openSelectProjectForExcessModal() {
        openModal('selectProjectExcessModal');
    }

    function proceedToProjectExcessReconcile() {
        const select = document.getElementById('projectToReconcileSelect');
        if (!select || select.selectedIndex < 0) {
            alert('Please select a project first.');
            return;
        }
        const opt = select.options[select.selectedIndex];
        if (!opt || !opt.value) {
            alert('Please select a valid project first.');
            return;
        }

        closeModal('selectProjectExcessModal');
        if (typeof openReconcileExcessModalForProject === 'function') {
            openReconcileExcessModalForProject(opt.value, opt.dataset.code, opt.dataset.title, opt.dataset.status);
        }
    }

    // Client-side pagination for Central Warehouse Movement & Delivery Receipts Log
    let currentLogPage = 1;
    const logsPerPage = 7;

    function renderLogPagination() {
        const rows = Array.from(document.querySelectorAll('.inv-log-row'));
        const total = rows.length;
        if (total === 0) return;
        
        const totalPages = Math.ceil(total / logsPerPage) || 1;
        if (currentLogPage > totalPages) currentLogPage = totalPages;
        if (currentLogPage < 1) currentLogPage = 1;
        
        const startIndex = (currentLogPage - 1) * logsPerPage;
        const endIndex = Math.min(startIndex + logsPerPage, total);
        
        rows.forEach((row, idx) => {
            row.style.display = (idx >= startIndex && idx < endIndex) ? '' : 'none';
        });
        
        // Update range labels
        const rangeStart = document.getElementById('invLogRangeStart');
        const rangeEnd = document.getElementById('invLogRangeEnd');
        const totalItems = document.getElementById('invLogTotalItems');
        if (rangeStart) rangeStart.textContent = total === 0 ? 0 : (startIndex + 1);
        if (rangeEnd) rangeEnd.textContent = endIndex;
        if (totalItems) totalItems.textContent = total;
        
        // Top page indicator
        const topIndicator = document.getElementById('invLogTopPageIndicator');
        if (topIndicator) topIndicator.textContent = `Page ${currentLogPage} of ${totalPages}`;
        
        // Top buttons state
        const topPrev = document.getElementById('invLogTopPrevBtn');
        const topNext = document.getElementById('invLogTopNextBtn');
        if (topPrev) {
            topPrev.disabled = (currentLogPage <= 1);
            topPrev.style.opacity = (currentLogPage <= 1) ? '0.45' : '1';
            topPrev.style.cursor = (currentLogPage <= 1) ? 'not-allowed' : 'pointer';
        }
        if (topNext) {
            topNext.disabled = (currentLogPage >= totalPages);
            topNext.style.opacity = (currentLogPage >= totalPages) ? '0.45' : '1';
            topNext.style.cursor = (currentLogPage >= totalPages) ? 'not-allowed' : 'pointer';
        }
        
        // Bottom buttons state
        const prevBtn = document.getElementById('invLogPrevBtn');
        const nextBtn = document.getElementById('invLogNextBtn');
        if (prevBtn) {
            prevBtn.disabled = (currentLogPage <= 1);
            prevBtn.style.opacity = (currentLogPage <= 1) ? '0.45' : '1';
            prevBtn.style.cursor = (currentLogPage <= 1) ? 'not-allowed' : 'pointer';
        }
        if (nextBtn) {
            nextBtn.disabled = (currentLogPage >= totalPages);
            nextBtn.style.opacity = (currentLogPage >= totalPages) ? '0.45' : '1';
            nextBtn.style.cursor = (currentLogPage >= totalPages) ? 'not-allowed' : 'pointer';
        }
        
        // Bottom page number pills
        const pageNumbersContainer = document.getElementById('invLogPageNumbers');
        if (pageNumbersContainer) {
            pageNumbersContainer.innerHTML = '';
            
            let startPage = Math.max(1, currentLogPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }
            
            if (startPage > 1) {
                pageNumbersContainer.appendChild(createPageBtn(1));
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.style.padding = '0 4px';
                    ellipsis.style.color = '#94a3b8';
                    ellipsis.textContent = '…';
                    pageNumbersContainer.appendChild(ellipsis);
                }
            }
            
            for (let p = startPage; p <= endPage; p++) {
                pageNumbersContainer.appendChild(createPageBtn(p));
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.style.padding = '0 4px';
                    ellipsis.style.color = '#94a3b8';
                    ellipsis.textContent = '…';
                    pageNumbersContainer.appendChild(ellipsis);
                }
                pageNumbersContainer.appendChild(createPageBtn(totalPages));
            }
        }
    }

    function createPageBtn(page) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = page;
        btn.style.minWidth = '34px';
        btn.style.height = '34px';
        btn.style.padding = '0 8px';
        btn.style.fontSize = '0.85rem';
        btn.style.fontWeight = (page === currentLogPage) ? '800' : '600';
        btn.style.borderRadius = '6px';
        btn.style.cursor = 'pointer';
        btn.style.transition = 'all 0.15s ease';
        
        if (page === currentLogPage) {
            btn.style.background = '#10b981';
            btn.style.color = '#ffffff';
            btn.style.border = '1px solid #10b981';
            btn.style.boxShadow = '0 2px 5px rgba(16, 185, 129, 0.3)';
        } else {
            btn.style.background = '#ffffff';
            btn.style.color = '#334155';
            btn.style.border = '1px solid #cbd5e1';
        }
        
        btn.onclick = () => {
            currentLogPage = page;
            renderLogPagination();
        };
        return btn;
    }

    function changeLogPage(delta) {
        currentLogPage += delta;
        renderLogPagination();
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderLogPagination();
    });
</script>
@endsection
