@extends('layouts.app')

@section('title', 'Materials Inventory (INV) - St. Bilfrid Development Corporation')
@section('page_title', 'Materials Inventory (INV) - Master Warehouse Catalog')

@section('top_actions')
    <button class="btn-primary" onclick="openModal('addMaterialModal')">
        <span>+</span> Add New Material to Catalog
    </button>
@endsection

@section('content')

<!-- Inventory Valuation KPI Cards -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Warehouse Inventory Valuation</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">VALUATION</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981;">₱{{ number_format($totalValuation, 2) }}</div>
        <div class="kpi-sub">Total Capital in Stock</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Catalog Item Types</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #38bdf8;">TYPES</span>
        </div>
        <div class="kpi-val" style="color: #38bdf8;">{{ $totalItemsCount }}</div>
        <div class="kpi-sub">Distinct Construction Materials</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Warehouse Units</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b;">UNITS</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #f59e0b;">{{ number_format($totalStockUnits) }}</div>
        <div class="kpi-sub">Gross On-Hand Stock</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Reconciled Site Returns</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981;">EXCESS</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981;">+{{ number_format($totalReturnedUnits) }}</div>
        <div class="kpi-sub">Units Returned from Projects</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Low Stock Warnings</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #ef4444;">ALERT</span>
        </div>
        <div class="kpi-val" style="color: #ef4444;">{{ $lowStockCount }}</div>
        <div class="kpi-sub">Items below safety threshold</div>
    </div>
</div>

<!-- Procurement-Governed Inventory Control Briefing Banner -->
<div style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.9) 100%); border: 1px solid rgba(56, 189, 248, 0.3); border-left: 4px solid #38bdf8; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.25);">
    <div style="display: flex; align-items: center; gap: 14px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(56, 189, 248, 0.15); display: flex; align-items: center; justify-content: center; color: #38bdf8; flex-shrink: 0; border: 1px solid rgba(56, 189, 248, 0.25);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
        <div>
            <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--text-primary); margin-bottom: 2px;">Procurement-Governed Inventory System</h4>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">Warehouse inventory is replenished exclusively through verified outside Trade Suppliers (Mils Glass, Colorsteel, Titan Structural). In-stock quantities automatically increment upon PO delivery receipts and cannot be manually overridden.</p>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ route('admin.suppliers.materials') }}" class="btn-secondary" style="font-size: 0.8rem; padding: 8px 14px; text-decoration: none;">
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
            <input type="text" name="search" class="form-input" placeholder="Search by material code or name..." value="{{ $search }}" style="max-width: 380px;">
            <button type="submit" class="btn-primary" style="padding: 8px 16px;">Search</button>
            @if($search || $selectedCategory)
                <a href="{{ route('inventory.index') }}" class="btn-secondary" style="padding: 8px 14px; font-size: 0.85rem;">Reset Filters</a>
            @endif
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-muted);">Categories:</span>
            <a href="{{ route('inventory.index') }}" class="spec-chip {{ empty($selectedCategory) ? 'spec-chip-active' : '' }}" style="text-decoration: none; cursor: pointer;">
                All ({{ $totalItemsCount }})
            </a>
            @foreach($allCategories as $cat)
                <a href="{{ route('inventory.index', ['category' => $cat, 'search' => $search]) }}" class="spec-chip {{ $selectedCategory == $cat ? 'spec-chip-active' : '' }}" style="text-decoration: none; cursor: pointer;">
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
            <h3 class="panel-title">Master Materials Inventory & Warehouse Stock</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Central warehouse stock levels synchronized automatically with Trade Supplier purchase order deliveries</span>
        </div>
        <button class="btn-secondary" style="font-size: 0.85rem; padding: 6px 14px;" onclick="openModal('addMaterialModal')">+ New Catalog Item</button>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Code & Material Name</th>
                    <th>Trade Category</th>
                    <th>Unit of Measure</th>
                    <th>Contract Cost (₱)</th>
                    <th>In-Stock Quantity</th>
                    <th>Total Value (₱)</th>
                    <th>Stock Health</th>
                    <th>Procurement Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materials as $mat)
                <tr>
                    <td>
                        <strong style="color: var(--text-primary); font-size: 1rem;">{{ $mat->name }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.8rem; color: #38bdf8; margin-top: 2px;">{{ $mat->material_code }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25);">
                            {{ $mat->category }}
                        </span>
                    </td>
                    <td style="font-weight: 600; color: var(--text-secondary);">{{ $mat->unit }}</td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #f8fafc;">₱{{ number_format($mat->unit_cost, 2) }}</strong>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); font-size: 1.05rem; color: {{ $mat->stock_quantity <= 500 ? '#ef4444' : '#10b981' }};">
                            {{ number_format($mat->stock_quantity) }} {{ $mat->unit }}
                        </strong>
                        <div style="font-size: 0.68rem; color: #38bdf8; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Supplier-Synchronized
                        </div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #10b981;">
                            ₱{{ number_format($mat->stock_quantity * $mat->unit_cost, 2) }}
                        </strong>
                    </td>
                    <td>
                        @if($mat->stock_quantity <= 0)
                            <span class="badge badge-overdue">Out of Stock</span>
                        @elseif($mat->stock_quantity <= 500)
                            <span class="badge badge-pending">Low Stock</span>
                        @else
                            <span class="badge badge-completed">Well Stocked</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.suppliers.materials', ['search' => $mat->name]) }}" class="btn-primary" style="font-size: 0.78rem; padding: 6px 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                            + Procure from Supplier
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="color: var(--text-muted); text-align: center; padding: 36px;">
                        No material catalog items found matching filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Inventory Movement & Project Excess Returns Log -->
<div class="glass-panel" style="border: 1px solid rgba(16, 185, 129, 0.3);">
    <div class="panel-header" style="margin-bottom: 14px;">
        <div>
            <h3 class="panel-title" style="font-size: 1.1rem; color: #10b981;">Central Warehouse Movement & Supplier Delivery Receipts Log</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Audit log of supplier procurement deliveries, stock allocations, and site excess returns</span>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Ref / TXN</th>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Material Item</th>
                    <th>Destination / Source</th>
                    <th>Quantity Change</th>
                    <th>Unit Cost</th>
                    <th>Audit Verification</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryLogs as $log)
                @php $tBadge = $log->transaction_badge; @endphp
                <tr>
                    <td style="font-family: var(--font-mono); color: #38bdf8; font-weight: 700;">
                        {{ $log->reference_no ?? ('TXN-' . $log->id) }}
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 0.8rem;">
                        {{ $log->created_at->format('M d, Y H:i') }}
                    </td>
                    <td>
                        <span class="badge" style="background: {{ $tBadge['bg'] }}; color: {{ $tBadge['color'] }}; border: 1px solid {{ $tBadge['border'] }}; font-size: 0.75rem;">
                            {{ $tBadge['label'] }}
                        </span>
                    </td>
                    <td>
                        <strong style="color: #f8fafc;">{{ $log->material->name ?? 'Material' }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted);">{{ $log->material->material_code ?? '' }}</div>
                    </td>
                    <td>
                        @if($log->project)
                            <a href="{{ route('projects.show', $log->project->id) }}" style="color: #38bdf8; font-weight: 600; text-decoration: none;">
                                {{ $log->project->title }}
                            </a>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $log->project->project_code }}</div>
                        @else
                            <span style="color: var(--text-muted);">Central Warehouse Stock</span>
                        @endif
                    </td>
                    <td style="font-family: var(--font-mono); font-weight: 800; color: {{ in_array($log->transaction_type, ['excess_return', 'restock']) ? '#10b981' : '#f59e0b' }};">
                        {{ in_array($log->transaction_type, ['excess_return', 'restock']) ? '+' : '-' }}{{ number_format($log->quantity) }} {{ $log->material->unit ?? 'units' }}
                    </td>
                    <td style="font-family: var(--font-mono);">
                        ₱{{ number_format($log->unit_cost, 2) }}
                    </td>
                    <td style="font-size: 0.8rem; color: var(--text-muted);">
                        {{ $log->notes ?? 'Verified inventory movement' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        No recent inventory movements recorded.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
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
                    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: 8px; padding: 10px 12px; font-size: 0.78rem; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
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

@endsection

@section('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>
@endsection
