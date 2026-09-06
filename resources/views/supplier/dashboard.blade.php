@extends('supplier.layout')

@section('title', $supplier->name . ' - Operations Dashboard')
@section('page_title', $supplier->name)
@section('page_subtitle', $supplier->category . ' Supplier Operations & Inventory Control Center')

@section('content')

<!-- Metrics Row 1: Material Inventory KPIs -->
<div style="margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
        <h3 style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
            Material Inventory & Catalog Overview
        </h3>
        <a href="{{ route('supplier.materials') }}" style="font-size: 0.8rem; color: #38bdf8; text-decoration: none; font-weight: 600;">
            Manage All Materials &rarr;
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        <!-- Total Materials -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Total Materials</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(56, 189, 248, 0.1); display: grid; place-items: center; color: #38bdf8;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin-top: 10px;">
                {{ number_format($totalMaterials) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Cataloged items in trade registry
            </div>
        </div>

        <!-- Available Materials -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Available In Stock</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); display: grid; place-items: center; color: #10b981;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #10b981; margin-top: 10px;">
                {{ number_format($availableMaterials) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Ready for immediate procurement
            </div>
        </div>

        <!-- Low Stock Materials -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Low Stock Alerts</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); display: grid; place-items: center; color: #f59e0b;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #f59e0b; margin-top: 10px;">
                {{ number_format($lowStockMaterials) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Items with <= 10 units remaining
            </div>
        </div>

        <!-- Out of Stock Materials -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Out of Stock</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239, 68, 68, 0.1); display: grid; place-items: center; color: #ef4444;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #ef4444; margin-top: 10px;">
                {{ number_format($outOfStockMaterials) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Replenishment required
            </div>
        </div>
    </div>
</div>

<!-- Metrics Row 2: Purchase Order Workflow KPIs -->
<div style="margin-bottom: 32px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
        <h3 style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
            Purchase Orders & Fulfilment Workflow
        </h3>
        <a href="{{ route('supplier.orders') }}" style="font-size: 0.8rem; color: #38bdf8; text-decoration: none; font-weight: 600;">
            View All Orders &rarr;
        </a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        <!-- Pending Orders -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Pending Orders</span>
                <span class="pill-badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">Action Required</span>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #f59e0b; margin-top: 10px;">
                {{ number_format($pendingOrders) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Orders awaiting your confirmation
            </div>
        </div>

        <!-- Active Orders -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Active / In-Transit</span>
                <span class="pill-badge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">In Progress</span>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #38bdf8; margin-top: 10px;">
                {{ number_format($activeOrders) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Processing or staged for dispatch
            </div>
        </div>

        <!-- Completed Orders -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Completed Orders</span>
                <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">Delivered</span>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #10b981; margin-top: 10px;">
                {{ number_format($completedOrders) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Successfully fulfilled & acknowledged
            </div>
        </div>

        <!-- Total Fulfilled Value -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Fulfilled Value</span>
                <span class="pill-badge" style="background: rgba(129, 140, 248, 0.15); color: #818cf8;">PHP</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #f8fafc; margin-top: 10px; font-family: var(--font-mono);">
                ₱{{ number_format($totalRevenue, 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Delivered procurement revenue
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Recent Orders & Stock Alerts Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Left Column: Recent Purchase Orders -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Recent Purchase Orders</h3>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Incoming requests from St. Bilfrid Dev. Corp.</p>
            </div>
            <button type="button" onclick="openModal('addMaterialModal')" class="btn-primary" style="font-size: 0.8rem; padding: 8px 16px;">
                + Add Material
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table class="grid-table">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Project / Destination</th>
                        <th>Requested Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $ord)
                        @php $badge = $ord->status_badge; @endphp
                        <tr>
                            <td>
                                <strong style="font-family: var(--font-mono); color: #38bdf8;">{{ $ord->order_code }}</strong>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $ord->created_at->format('M d, Y') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);">
                                    {{ $ord->project ? $ord->project->title : 'Central Warehouse Depot' }}
                                </div>
                                <div style="font-size: 0.72rem; color: var(--text-muted); max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $ord->delivery_location }}
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; font-family: var(--font-mono);">
                                    {{ $ord->requested_delivery_date->format('M d, Y') }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.8rem; font-weight: 600;">{{ $ord->items->count() }} item(s)</span>
                            </td>
                            <td>
                                <strong style="font-family: var(--font-mono); color: var(--text-primary);">
                                    ₱{{ number_format($ord->total_amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                <span class="pill-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('supplier.orders', ['search' => $ord->order_code]) }}" class="btn-secondary" style="padding: 6px 10px; font-size: 0.75rem;">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                                No purchase orders placed yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Low Stock Warnings & Quick Stock Management -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Inventory Attention</h3>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Items requiring stock replenishment</p>
            </div>
            <a href="{{ route('supplier.materials', ['status' => 'low_stock']) }}" style="font-size: 0.75rem; color: #f59e0b; text-decoration: none; font-weight: 600;">
                Filter Low Stock
            </a>
        </div>

        <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 16px;">
            @forelse($lowStockAlerts as $mat)
                <div style="padding: 12px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.06); display: flex; align-items: center; justify-content: space-between;">
                    <div style="min-width: 0; flex: 1; padding-right: 12px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $mat->name }}
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                            <span style="font-family: var(--font-mono);">{{ $mat->material_code }}</span>
                            <span>&bull;</span>
                            <span>₱{{ number_format($mat->unit_price, 2) }} / {{ $mat->unit }}</span>
                        </div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <span class="pill-badge" style="background: {{ $mat->available_quantity <= 0 ? 'rgba(239, 68, 68, 0.15)' : 'rgba(245, 158, 11, 0.15)' }}; color: {{ $mat->available_quantity <= 0 ? '#ef4444' : '#f59e0b' }};">
                            {{ $mat->available_quantity }} {{ $mat->unit }}
                        </span>
                        <div style="margin-top: 4px;">
                            <button type="button" onclick="openQuickStockModal('{{ $mat->id }}', '{{ addslashes($mat->name) }}', '{{ $mat->available_quantity }}', '{{ $mat->unit }}')" style="background: none; border: none; font-size: 0.72rem; color: #38bdf8; cursor: pointer; text-decoration: underline;">
                                Quick Restock
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 0.8rem;">
                    All materials have healthy inventory levels.
                </div>
            @endforelse
        </div>

        <!-- Supplier Organization Summary Card -->
        <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 18px; margin-top: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(56, 189, 248, 0.15); display: grid; place-items: center; color: #38bdf8;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <div>
                    <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary);">{{ $supplier->name }}</h4>
                    <div style="font-size: 0.72rem; color: var(--text-muted);">Trade Category: {{ $supplier->category }}</div>
                </div>
            </div>

            <div style="font-size: 0.8rem; color: var(--text-secondary); space-y: 6px;">
                <div style="margin-bottom: 6px;">
                    <strong style="color: var(--text-muted);">Representative:</strong> {{ $supplier->contact_person ?? 'Not specified' }}
                </div>
                <div style="margin-bottom: 6px;">
                    <strong style="color: var(--text-muted);">Hotline:</strong> {{ $supplier->phone ?? 'Not specified' }}
                </div>
                <div style="margin-bottom: 6px;">
                    <strong style="color: var(--text-muted);">Email:</strong> {{ $supplier->email }}
                </div>
                <div>
                    <strong style="color: var(--text-muted);">Logistics Hub:</strong> {{ $supplier->address ?? 'Silay City / Bacolod Logistics Depot' }}
                </div>
            </div>

            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.75rem; color: var(--text-muted);">Supplier Performance:</span>
                <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    {{ number_format($supplier->rating, 2) }} / 5.0 Rating
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Restock Modal -->
<div class="modal-backdrop" id="quickStockModal">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary);">Update Stock Inventory</h3>
                <p id="quickStockMaterialName" style="font-size: 0.75rem; color: #38bdf8; margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('quickStockModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        <form id="quickStockForm" method="POST">
            @csrf
            <div class="modal-body">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        New Available Quantity (<span id="quickStockUnit"></span>)
                    </label>
                    <input type="number" name="available_quantity" id="quickStockQty" min="0" required class="input-field" style="width: 100%; font-size: 1.1rem; font-weight: 700;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('quickStockModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Stock</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openQuickStockModal(id, name, qty, unit) {
        document.getElementById('quickStockMaterialName').textContent = name;
        document.getElementById('quickStockQty').value = qty;
        document.getElementById('quickStockUnit').textContent = unit;
        document.getElementById('quickStockForm').action = '/supplier/materials/' + id + '/quick-stock';
        openModal('quickStockModal');
    }
</script>
@endpush
