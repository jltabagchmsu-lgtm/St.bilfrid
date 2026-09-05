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
            <span style="font-size: 0.85rem; color: var(--text-muted);">Central warehouse stock levels, unit valuations, and supply health</span>
        </div>
        <button class="btn-secondary" style="font-size: 0.85rem; padding: 6px 14px;" onclick="openModal('addMaterialModal')">+ New Material</button>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Code & Material Name</th>
                    <th>Trade Category</th>
                    <th>Unit of Measure</th>
                    <th>Unit Cost (₱)</th>
                    <th>In-Stock Quantity</th>
                    <th>Total Value (₱)</th>
                    <th>Stock Health</th>
                    <th>Action</th>
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
                        <button class="btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;" onclick="openRestockModal({{ $mat->id }}, '{{ addslashes($mat->name) }}', {{ $mat->stock_quantity }}, {{ $mat->unit_cost }}, '{{ $mat->unit }}')">
                            Adjust / Restock
                        </button>
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
            <h3 class="panel-title" style="font-size: 1.1rem; color: #10b981;">Central Warehouse Movement & Site Excess Returns Audit Log</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Real-time audit log of stock allocations, project excess returns, and warehouse deliveries</span>
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
                    <th>Project Site Link</th>
                    <th>Quantity Change</th>
                    <th>Unit Cost</th>
                    <th>Notes</th>
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
                            {{ $tBadge['icon'] }} {{ $tBadge['label'] }}
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
                        {{ $log->notes ?? 'Standard warehouse inventory movement' }}
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

<!-- Modal 1: Add New Master Material -->
<div class="modal-overlay" id="addMaterialModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Add New Material to Master Inventory</h3>
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
                        <option value="Structural">Structural</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Piping/Plumbing">Piping/Plumbing</option>
                        <option value="Finishing">Finishing</option>
                        <option value="General">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" name="unit" class="form-input" placeholder="e.g. bags, pcs, meters, tons, sheets" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Unit Cost (₱)</label>
                    <input type="number" step="0.01" name="unit_cost" class="form-input" placeholder="₱ 0.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Warehouse Stock</label>
                    <input type="number" name="stock_quantity" class="form-input" placeholder="e.g. 5000" min="0" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addMaterialModal')">Cancel</button>
                <button type="submit" class="btn-primary">Add to Warehouse Catalog</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Adjust Stock & Pricing -->
<div class="modal-overlay" id="restockModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Adjust Inventory Stock & Pricing</h3>
            <button onclick="closeModal('restockModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="restockForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Material</label>
                <input type="text" id="restockMatName" class="form-input" readonly style="background: rgba(255,255,255,0.05); font-weight: 700;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Updated Warehouse Stock (<span id="restockUnitLbl"></span>)</label>
                    <input type="number" id="restockQuantity" name="stock_quantity" class="form-input" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Updated Unit Cost (₱)</label>
                    <input type="number" step="0.01" id="restockCost" name="unit_cost" class="form-input" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('restockModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Inventory Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function openRestockModal(matId, name, currentStock, currentCost, unit) {
        document.getElementById('restockForm').action = '/inventory/' + matId + '/update-stock';
        document.getElementById('restockMatName').value = name;
        document.getElementById('restockQuantity').value = currentStock;
        document.getElementById('restockCost').value = currentCost;
        document.getElementById('restockUnitLbl').innerText = unit;
        openModal('restockModal');
    }
</script>
@endsection
