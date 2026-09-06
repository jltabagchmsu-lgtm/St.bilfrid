@extends('layouts.app')

@section('title', 'Centralized Supplier Hub - St. Bilfrid Dev. Corp')
@section('header_title', 'Supplier Network & Procurement Control')
@section('header_subtitle', 'Centralized management of trade suppliers, cross-supplier product catalogs, and purchase order fulfillment.')

@section('content')

<!-- KPI Summary Cards (Section 11) -->
<div class="metrics-grid" style="grid-template-columns: repeat(6, 1fr); gap: 14px; margin-bottom: 24px;">
    <!-- 1. Total Suppliers -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Active Suppliers</span>
            <div class="stat-icon" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            </div>
        </div>
        <div class="stat-value" style="color: #38bdf8;">{{ number_format($totalSuppliers) }}</div>
        <div class="stat-sub">3 Trade Partners</div>
    </div>

    <!-- 2. Total Available Materials -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Available Materials</span>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
            </div>
        </div>
        <div class="stat-value" style="color: #10b981;">{{ number_format($totalAvailableMaterials) }}</div>
        <div class="stat-sub">Ready for PO Generation</div>
    </div>

    <!-- 3. Pending Confirmation Orders -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Pending Orders</span>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
        </div>
        <div class="stat-value" style="color: #f59e0b;">{{ number_format($pendingOrders) }}</div>
        <div class="stat-sub">Supplier confirmation pending</div>
    </div>

    <!-- 4. Active Orders -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Active Orders</span>
            <div class="stat-icon" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            </div>
        </div>
        <div class="stat-value" style="color: #38bdf8;">{{ number_format($activeOrders) }}</div>
        <div class="stat-sub">In fabrication & transit</div>
    </div>

    <!-- 5. Completed Orders -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Completed Orders</span>
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
        </div>
        <div class="stat-value" style="color: #22c55e;">{{ number_format($completedOrders) }}</div>
        <div class="stat-sub">Successfully fulfilled</div>
    </div>

    <!-- 6. Total Procurement Expenditure -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-label">Fulfilled Volume</span>
            <div class="stat-icon" style="background: rgba(129, 140, 248, 0.1); color: #818cf8;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>
        <div class="stat-value" style="color: #818cf8; font-size: 1.25rem;">PHP {{ number_format($totalProcurementCost, 0) }}</div>
        <div class="stat-sub">Delivered expenditure</div>
    </div>
</div>

<!-- Quick Navigation & Procurement Actions Header -->
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ route('admin.suppliers.materials') }}" class="btn-secondary" style="font-size: 0.85rem; padding: 10px 18px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
            Cross-Supplier Product Matrix
        </a>
        <a href="{{ route('admin.suppliers.orders') }}" class="btn-secondary" style="font-size: 0.85rem; padding: 10px 18px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            Purchase Order Master Tracker
        </a>
    </div>

    <button type="button" onclick="openModal('createPurchaseOrderModal')" class="btn-primary" style="font-size: 0.85rem; padding: 10px 22px;">
        + Place New Purchase Order
    </button>
</div>

<!-- Three Core Supplier Cards -->
<div style="margin-bottom: 32px;">
    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 14px;">
        Trade Suppliers & Authorized Partners
    </h3>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
        @foreach($suppliers as $sup)
            @php
                $accentColor = match($sup->category) {
                    'Windows & Doors' => '#38bdf8',
                    'Roofing' => '#ef4444',
                    'Structural & Masonry' => '#10b981',
                    default => '#818cf8',
                };
            @endphp
            <div class="card" style="border-top: 3px solid {{ $accentColor }}; padding: 22px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                        <div>
                            <span class="pill-badge" style="background: rgba(255, 255, 255, 0.06); color: {{ $accentColor }}; border: 1px solid {{ $accentColor }}40; font-size: 0.72rem;">
                                {{ $sup->category }}
                            </span>
                            <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); margin-top: 8px;">
                                {{ $sup->name }}
                            </h4>
                        </div>
                        <span class="pill-badge" style="background: {{ $sup->status === 'active' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; color: {{ $sup->status === 'active' ? '#10b981' : '#ef4444' }};">
                            {{ ucfirst($sup->status) }}
                        </span>
                    </div>

                    <div style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 16px; line-height: 1.6;">
                        <div><strong style="color: var(--text-muted);">Contact:</strong> {{ $sup->contact_person ?? 'Not specified' }}</div>
                        <div><strong style="color: var(--text-muted);">Phone:</strong> {{ $sup->phone ?? 'Not specified' }}</div>
                        <div><strong style="color: var(--text-muted);">Email:</strong> {{ $sup->email }}</div>
                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><strong style="color: var(--text-muted);">Hub:</strong> {{ $sup->address ?? 'Silay City / Bacolod Logistics' }}</div>
                    </div>

                    <!-- Mini Stat Highlights -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 10px; margin-bottom: 18px;">
                        <div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Catalog Items</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">
                                {{ $sup->materials_count }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Rating</div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: #10b981; font-family: var(--font-mono);">
                                {{ number_format($sup->rating, 2) }} / 5.0
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 14px;">
                    <a href="{{ route('admin.suppliers.materials', ['supplier_id' => $sup->id]) }}" class="btn-secondary" style="flex: 1; font-size: 0.75rem; justify-content: center; padding: 6px;">
                        Browse Catalog
                    </a>
                    <button type="button" onclick="openEditSupplierModal({{ json_encode($sup) }})" class="btn-secondary" style="padding: 6px 10px; font-size: 0.75rem;">
                        Edit Info
                    </button>
                    <form action="{{ route('admin.suppliers.toggleStatus', $sup->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-secondary" style="padding: 6px 8px; font-size: 0.75rem; color: {{ $sup->status === 'active' ? '#ef4444' : '#10b981' }};" title="{{ $sup->status === 'active' ? 'Deactivate Supplier Account' : 'Activate Supplier Account' }}">
                            {{ $sup->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Recent Purchase Orders Table -->
<div style="margin-bottom: 32px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Recent Procurement Orders</h3>
            <p style="font-size: 0.75rem; color: var(--text-muted);">Live status synchronization with trade suppliers</p>
        </div>
        <a href="{{ route('admin.suppliers.orders') }}" style="font-size: 0.8rem; color: #38bdf8; text-decoration: none; font-weight: 600;">
            View Master Tracker &rarr;
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="grid-table">
            <thead>
                <tr>
                    <th>Order Code</th>
                    <th>Supplier Organization</th>
                    <th>Destination Site / Project</th>
                    <th>Requested Delivery</th>
                    <th>Items</th>
                    <th>Total (PHP)</th>
                    <th>Live Status</th>
                    <th>Actions</th>
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
                            <div style="font-weight: 700; color: var(--text-primary);">{{ $ord->supplier ? $ord->supplier->name : 'Supplier' }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $ord->supplier ? $ord->supplier->category : '' }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $ord->project ? $ord->project->title : 'Central Warehouse Depot' }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-muted); max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $ord->delivery_location }}</div>
                        </td>
                        <td>
                            <span style="font-size: 0.8rem; font-family: var(--font-mono);">
                                {{ $ord->requested_delivery_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 0.8rem; font-weight: 600;">{{ $ord->items->count() }} line(s)</span>
                        </td>
                        <td>
                            <strong style="font-family: var(--font-mono); color: var(--text-primary);">
                                PHP {{ number_format($ord->total_amount, 2) }}
                            </strong>
                        </td>
                        <td>
                            <span class="pill-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <a href="{{ route('admin.suppliers.orders', ['search' => $ord->order_code]) }}" class="btn-secondary" style="padding: 5px 10px; font-size: 0.75rem;">
                                    View Log
                                </a>
                                @if($ord->status === 'delivered')
                                    <form action="{{ route('admin.suppliers.orders.receive', $ord->id) }}" method="POST" onsubmit="return confirm('Officially receive order materials and integrate into project BOM stock?');">
                                        @csrf
                                        <button type="submit" class="btn-primary" style="padding: 5px 10px; font-size: 0.75rem; background: #10b981;">
                                            Accept & BOM Sync
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 36px; color: var(--text-muted);">
                            No purchase orders created yet. Click "+ Place New Purchase Order" to generate one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create New Purchase Order -->
<div class="modal-backdrop" id="createPurchaseOrderModal">
    <div class="modal-box" style="max-width: 720px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Generate Purchase Order to Supplier</h3>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Procure products directly from supplier catalog for a project site or central warehouse.</p>
            </div>
            <button type="button" onclick="closeModal('createPurchaseOrderModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        <form action="{{ route('admin.suppliers.orders.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <!-- Supplier Selection -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Select Target Supplier Organization <span style="color: var(--primary-red);">*</span>
                    </label>
                    <select name="supplier_id" id="poSupplierSelect" required onchange="onPoSupplierChange()" class="input-field" style="width: 100%;">
                        <option value="">-- Choose Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-category="{{ $s->category }}">{{ $s->name }} ({{ $s->category }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Project & Delivery Location -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Destination Project Site (Optional)
                        </label>
                        <select name="project_id" id="poProjectSelect" onchange="onPoProjectChange()" class="input-field" style="width: 100%;">
                            <option value="">-- Central Warehouse Depot --</option>
                            @foreach($activeProjects as $prj)
                                <option value="{{ $prj->id }}" data-location="{{ $prj->location }}">{{ $prj->title }} ({{ $prj->project_code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Requested Delivery Date <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="date" name="requested_delivery_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="input-field" style="width: 100%;">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Delivery Location Address <span style="color: var(--primary-red);">*</span>
                    </label>
                    <input type="text" name="delivery_location" id="poDeliveryLocation" required placeholder="e.g. Villa Rosario Phase 2 Construction Site, Silay City" class="input-field" style="width: 100%;">
                </div>

                <!-- Line Items Container -->
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <label style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary);">
                            Material Order Line Items <span style="color: var(--primary-red);">*</span>
                        </label>
                        <button type="button" onclick="addPoItemRow()" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 10px;">
                            + Add Another Item
                        </button>
                    </div>

                    <div id="poItemsContainer">
                        <div class="po-item-row" style="display: grid; grid-template-columns: 3fr 1fr 1fr 36px; gap: 10px; margin-bottom: 8px; align-items: center;">
                            <div>
                                <select name="items[0][material_id]" class="input-field po-material-select" required onchange="calculatePoTotal()" style="width: 100%; font-size: 0.8rem;">
                                    <option value="">-- Select Product Item --</option>
                                    @foreach($featuredMaterials as $fm)
                                        <option value="{{ $fm->id }}" data-supplier-id="{{ $fm->supplier_id }}" data-price="{{ $fm->unit_price }}" data-unit="{{ $fm->unit }}">
                                            {{ $fm->name }} [{{ $fm->supplier ? $fm->supplier->name : '' }}] (PHP {{ number_format($fm->unit_price, 2) }} / {{ $fm->unit }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="number" name="items[0][quantity]" class="input-field po-qty-input" min="1" value="1" required placeholder="Qty" oninput="calculatePoTotal()" style="width: 100%; font-size: 0.85rem;">
                            </div>
                            <div>
                                <input type="text" class="input-field po-subtotal-display" readonly placeholder="PHP 0.00" style="width: 100%; font-size: 0.8rem; font-family: var(--font-mono); opacity: 0.8;">
                            </div>
                            <div>
                                <button type="button" onclick="removePoItemRow(this)" class="btn-secondary" style="padding: 6px; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">&times;</button>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 10px; padding-top: 8px; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                        <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary);">
                            Estimated Total: <span id="poGrandTotalDisplay" style="color: #38bdf8; font-family: var(--font-mono); font-size: 1.1rem;">PHP 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Notes & Special Instructions -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Special Instructions / Delivery Remarks
                    </label>
                    <textarea name="notes" rows="2" placeholder="e.g. Please coordinate with Site Engineer upon arrival for boom truck staging." class="input-field" style="width: 100%; resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('createPurchaseOrderModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Submit Purchase Order</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Supplier Organization -->
<div class="modal-backdrop" id="editSupplierModal">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Manage Supplier Record</h3>
                <p id="editSupplierCodeBadge" style="font-size: 0.75rem; color: #38bdf8; font-family: var(--font-mono); margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('editSupplierModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        <form id="editSupplierForm" method="POST">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Company Name <span style="color: var(--primary-red);">*</span></label>
                    <input type="text" name="name" id="edit_sup_name" required class="input-field" style="width: 100%;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Representative</label>
                        <input type="text" name="contact_person" id="edit_sup_contact" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Phone</label>
                        <input type="text" name="phone" id="edit_sup_phone" class="input-field" style="width: 100%;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Email <span style="color: var(--primary-red);">*</span></label>
                    <input type="email" name="email" id="edit_sup_email" required class="input-field" style="width: 100%;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Logistics Address</label>
                    <textarea name="address" id="edit_sup_address" rows="2" class="input-field" style="width: 100%; resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Quality Rating (1-5)</label>
                        <input type="number" step="0.01" min="1" max="5" name="rating" id="edit_sup_rating" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Status <span style="color: var(--primary-red);">*</span></label>
                        <select name="status" id="edit_sup_status" required class="input-field" style="width: 100%;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editSupplierModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Supplier Details</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let poItemIndex = 1;

    function onPoProjectChange() {
        const select = document.getElementById('poProjectSelect');
        const selected = select.options[select.selectedIndex];
        const location = selected.getAttribute('data-location');
        if (location && location.trim() !== '') {
            document.getElementById('poDeliveryLocation').value = location;
        } else if (select.value === '') {
            document.getElementById('poDeliveryLocation').value = 'St. Bilfrid Central Warehouse Depot, Silay City';
        }
    }

    function onPoSupplierChange() {
        const supId = document.getElementById('poSupplierSelect').value;
        const selects = document.querySelectorAll('.po-material-select');
        selects.forEach(function(sel) {
            let hasValidSelection = false;
            Array.from(sel.options).forEach(function(opt) {
                if (!opt.value) return;
                const optSupId = opt.getAttribute('data-supplier-id');
                if (!supId || optSupId === supId) {
                    opt.style.display = '';
                    if (sel.value === opt.value) hasValidSelection = true;
                } else {
                    opt.style.display = 'none';
                }
            });
            if (!hasValidSelection && sel.value !== '') {
                sel.value = '';
            }
        });
        calculatePoTotal();
    }

    function addPoItemRow() {
        const container = document.getElementById('poItemsContainer');
        const firstRow = container.querySelector('.po-item-row');
        const clone = firstRow.cloneNode(true);
        
        // Update names
        clone.querySelector('.po-material-select').name = `items[${poItemIndex}][material_id]`;
        clone.querySelector('.po-material-select').value = '';
        clone.querySelector('.po-qty-input').name = `items[${poItemIndex}][quantity]`;
        clone.querySelector('.po-qty-input').value = '1';
        clone.querySelector('.po-subtotal-display').value = 'PHP 0.00';

        container.appendChild(clone);
        poItemIndex++;
        onPoSupplierChange();
        calculatePoTotal();
    }

    function removePoItemRow(btn) {
        const rows = document.querySelectorAll('.po-item-row');
        if (rows.length > 1) {
            btn.closest('.po-item-row').remove();
            calculatePoTotal();
        }
    }

    function calculatePoTotal() {
        let grandTotal = 0;
        const rows = document.querySelectorAll('.po-item-row');
        rows.forEach(function(row) {
            const matSelect = row.querySelector('.po-material-select');
            const qtyInput = row.querySelector('.po-qty-input');
            const subtotalDisp = row.querySelector('.po-subtotal-display');

            const selectedOpt = matSelect.options[matSelect.selectedIndex];
            const price = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-price') || 0) : 0;
            const qty = parseInt(qtyInput.value) || 0;
            const subtotal = price * qty;
            
            subtotalDisp.value = 'PHP ' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            grandTotal += subtotal;
        });

        document.getElementById('poGrandTotalDisplay').textContent = 'PHP ' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function openEditSupplierModal(sup) {
        document.getElementById('editSupplierCodeBadge').textContent = 'Code: ' + sup.code;
        document.getElementById('edit_sup_name').value = sup.name;
        document.getElementById('edit_sup_contact').value = sup.contact_person || '';
        document.getElementById('edit_sup_phone').value = sup.phone || '';
        document.getElementById('edit_sup_email').value = sup.email;
        document.getElementById('edit_sup_address').value = sup.address || '';
        document.getElementById('edit_sup_rating').value = sup.rating || 5.0;
        document.getElementById('edit_sup_status').value = sup.status;
        document.getElementById('editSupplierForm').action = '/suppliers/' + sup.id + '/update';
        openModal('editSupplierModal');
    }
</script>
@endpush
