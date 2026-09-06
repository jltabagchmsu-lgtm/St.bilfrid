@extends('supplier.layout')

@section('title', $supplier->name . ' - Operations & Fulfillment Hub')
@section('page_title', $supplier->name)
@section('page_subtitle', $supplier->category . ' Supplier Operations & Order Fulfillment Center')

@section('content')

<!-- Metrics Row: Catalog Offerings & Purchase Order Pipeline (Section 7) -->
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
        <h3 style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
            Material Catalog & Order Statistics
        </h3>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('supplier.materials') }}" class="btn-secondary" style="font-size: 0.8rem; padding: 6px 12px; text-decoration: none;">
                Product Catalog &rarr;
            </a>
            <a href="{{ route('supplier.orders') }}" class="btn-secondary" style="font-size: 0.8rem; padding: 6px 12px; text-decoration: none;">
                Purchase Orders &rarr;
            </a>
        </div>
    </div>

    <!-- 6 KPI Cards Grid -->
    <div class="supplier-kpi-grid">
        <!-- 1. Total Materials -->
        <div class="supplier-kpi-card" style="--card-accent: linear-gradient(90deg, #38bdf8, #0284c7);">
            <div class="supplier-kpi-header">
                <span class="supplier-kpi-label">Total Materials</span>
                <div class="supplier-kpi-icon" style="background: rgba(56, 189, 248, 0.12); color: #38bdf8; border-color: rgba(56, 189, 248, 0.25);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
            </div>
            <div class="supplier-kpi-val" style="color: #38bdf8;">
                {{ number_format($totalMaterials) }}
            </div>
            <div class="supplier-kpi-sub">
                Cataloged offerings
            </div>
        </div>

        <!-- 2. Available Materials -->
        <div class="supplier-kpi-card" style="--card-accent: linear-gradient(90deg, #10b981, #059669);">
            <div class="supplier-kpi-header">
                <span class="supplier-kpi-label">Available</span>
                <div class="supplier-kpi-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981; border-color: rgba(16, 185, 129, 0.25);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
            <div class="supplier-kpi-val" style="color: #10b981;">
                {{ number_format($availableMaterials) }}
            </div>
            <div class="supplier-kpi-sub" style="color: #10b981;">
                Ready for order
            </div>
        </div>

        <!-- 3. Unavailable Materials -->
        <div class="supplier-kpi-card" style="--card-accent: linear-gradient(90deg, #94a3b8, #64748b);">
            <div class="supplier-kpi-header">
                <span class="supplier-kpi-label">Unavailable</span>
                <div class="supplier-kpi-icon" style="background: rgba(148, 163, 184, 0.12); color: #94a3b8; border-color: rgba(148, 163, 184, 0.25);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                </div>
            </div>
            <div class="supplier-kpi-val" style="color: #94a3b8;">
                {{ number_format($unavailableMaterials) }}
            </div>
            <div class="supplier-kpi-sub">
                Suspended / off-catalog
            </div>
        </div>

        <!-- 4. Pending Orders -->
        <div class="supplier-kpi-card" style="--card-accent: linear-gradient(90deg, #f59e0b, #d97706);">
            <div class="supplier-kpi-header">
                <span class="supplier-kpi-label">Pending Orders</span>
                <div class="supplier-kpi-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b; border-color: rgba(245, 158, 11, 0.25);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
            <div class="supplier-kpi-val" style="color: #f59e0b;">
                {{ number_format($pendingOrders) }}
            </div>
            <div class="supplier-kpi-sub">
                Awaiting confirmation
            </div>
        </div>

        <!-- 5. Active Orders -->
        <div class="supplier-kpi-card" style="--card-accent: linear-gradient(90deg, #38bdf8, #818cf8);">
            <div class="supplier-kpi-header">
                <span class="supplier-kpi-label">Active Orders</span>
                <div class="supplier-kpi-icon" style="background: rgba(56, 189, 248, 0.12); color: #38bdf8; border-color: rgba(56, 189, 248, 0.25);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                </div>
            </div>
            <div class="supplier-kpi-val" style="color: #38bdf8;">
                {{ number_format($activeOrders) }}
            </div>
            <div class="supplier-kpi-sub">
                Processing & dispatch
            </div>
        </div>

        <!-- 6. Completed Orders -->
        <div class="supplier-kpi-card" style="--card-accent: linear-gradient(90deg, #22c55e, #16a34a);">
            <div class="supplier-kpi-header">
                <span class="supplier-kpi-label">Completed</span>
                <div class="supplier-kpi-icon" style="background: rgba(34, 197, 94, 0.12); color: #22c55e; border-color: rgba(34, 197, 94, 0.25);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
            <div class="supplier-kpi-val" style="color: #22c55e;">
                {{ number_format($completedOrders) }}
            </div>
            <div class="supplier-kpi-sub">
                Fulfilled requests
            </div>
        </div>
    </div>
</div>

<!-- 2. Full-Width Section: Incoming Purchase Orders Master Tracker -->
<div style="margin-bottom: 32px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Incoming Purchase Orders</h3>
            <p style="font-size: 0.75rem; color: var(--text-muted);">Client procurement orders requiring supply confirmation, staging, & site dispatch</p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('supplier.orders') }}" class="btn-secondary" style="font-size: 0.8rem; padding: 7px 16px; text-decoration: none;">
                View All Purchase Orders &rarr;
            </a>
        </div>
    </div>

    <div style="overflow-x: auto; background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px;">
        <table class="grid-table" style="border: none; background: transparent; width: 100%;">
            <thead>
                <tr>
                    <th style="white-space: nowrap; min-width: 140px;">PO Code</th>
                    <th style="min-width: 220px;">Project Destination & Address</th>
                    <th style="white-space: nowrap; min-width: 130px;">Required Schedule</th>
                    <th style="white-space: nowrap; min-width: 110px;">Order Items</th>
                    <th style="white-space: nowrap; min-width: 140px;">Total Value</th>
                    <th style="white-space: nowrap; min-width: 140px;">Fulfillment Status</th>
                    <th style="white-space: nowrap; min-width: 110px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $ord)
                    @php $badge = $ord->status_badge; @endphp
                    <tr>
                        <td style="white-space: nowrap;">
                            <div style="font-family: var(--font-mono); color: #38bdf8; font-size: 0.875rem; font-weight: 700; white-space: nowrap;">{{ $ord->order_code }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; margin-top: 2px;">{{ $ord->created_at->format('M d, Y - h:i A') }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;">
                                {{ $ord->project ? $ord->project->title : 'Central Warehouse Depot' }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                {{ $ord->delivery_location }}
                            </div>
                        </td>
                        <td style="white-space: nowrap;">
                            <span style="font-size: 0.825rem; font-family: var(--font-mono); color: var(--text-primary); font-weight: 600;">
                                {{ $ord->requested_delivery_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="pill-badge" style="background: rgba(255, 255, 255, 0.06); color: var(--text-primary); border: 1px solid rgba(255, 255, 255, 0.1);">
                                {{ $ord->items->count() }} line item(s)
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span style="font-family: var(--font-mono); color: var(--text-primary); font-size: 0.95rem; font-weight: 800;">
                                PHP {{ number_format($ord->total_amount, 2) }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="pill-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="white-space: nowrap; text-align: center;">
                            <a href="{{ route('supplier.orders', ['search' => $ord->order_code]) }}" class="btn-primary" style="padding: 6px 14px; font-size: 0.78rem; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                Fulfill & Chat
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">
                            No incoming purchase orders received yet. Admin will place orders against your published catalog.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 3. Lower Balanced Section: Catalog Offerings (2-Col Grid) & Operations Hub -->
<div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; align-items: start;">

    <!-- Left Column: Catalog Offerings Matrix -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Product Catalog Offerings</h3>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Active supply products and contracted rates</p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="{{ route('supplier.materials') }}" style="font-size: 0.78rem; color: #38bdf8; text-decoration: none; font-weight: 600;">
                    View All ({{ $totalMaterials }}) &rarr;
                </a>
                <button type="button" onclick="openModal('addMaterialModal')" class="btn-primary" style="font-size: 0.78rem; padding: 6px 14px;">
                    + Add Product
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
            @forelse($catalogHighlights as $mat)
                <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between; min-height: 110px; transition: transform 0.2s ease, border-color 0.2s ease;" onmouseover="this.style.borderColor='rgba(56, 189, 248, 0.35)'" onmouseout="this.style.borderColor='rgba(255, 255, 255, 0.08)'">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-family: var(--font-mono); font-size: 0.7rem; color: #38bdf8; font-weight: 700;">
                                {{ $mat->material_code }}
                            </span>
                            <span class="pill-badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); font-size: 0.68rem; padding: 2px 8px;">
                                {{ $mat->subcategory ?? 'General' }}
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); line-height: 1.35; margin-bottom: 8px;">
                            {{ $mat->name }}
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-end; justify-content: space-between; border-top: 1px solid rgba(255, 255, 255, 0.06); padding-top: 8px; margin-top: 6px;">
                        <div>
                            <span style="font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase;">Unit Rate:</span>
                            <div style="font-family: var(--font-mono); font-weight: 800; color: #38bdf8; font-size: 0.95rem;">
                                PHP {{ number_format($mat->unit_price, 2) }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.72rem; color: var(--text-secondary);">Per {{ $mat->unit }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: span 2; text-align: center; padding: 32px; background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; color: var(--text-muted); font-size: 0.8rem;">
                    No catalog products registered yet. Click "+ Add Product" to publish items for Admin procurement.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Supplier Operations Hub & Profile Summary -->
    <div>
        <div style="margin-bottom: 14px;">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Supplier Operations Hub</h3>
            <p style="font-size: 0.75rem; color: var(--text-muted);">Organization profile & verified logistics details</p>
        </div>

        <!-- Supplier Organization Summary Card -->
        <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px; margin-bottom: 18px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(56, 189, 248, 0.15); display: grid; place-items: center; color: #38bdf8;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div>
                        <h4 style="font-size: 0.98rem; font-weight: 800; color: var(--text-primary);">{{ $supplier->name }}</h4>
                        <div style="font-size: 0.74rem; color: #38bdf8; font-weight: 600;">{{ $supplier->category }} Trade Partner</div>
                    </div>
                </div>
                <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                    {{ number_format($supplier->rating, 2) }} / 5.0 Approved
                </span>
            </div>

            <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.7; background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                <div><strong style="color: var(--text-muted);">Representative:</strong> {{ $supplier->contact_person ?? 'Engr. Danilo V. Tan' }}</div>
                <div><strong style="color: var(--text-muted);">Phone Hotline:</strong> {{ $supplier->phone ?? '+63 (34) 495-7744' }}</div>
                <div><strong style="color: var(--text-muted);">Email:</strong> {{ $supplier->email }}</div>
                <div><strong style="color: var(--text-muted);">Logistics Depot:</strong> {{ $supplier->address ?? 'Silay City / Bacolod Logistics Hub' }}</div>
            </div>

            <!-- Quick Management Shortcuts -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <a href="{{ route('supplier.inquiries') }}" class="btn-secondary" style="font-size: 0.75rem; justify-content: center; padding: 8px; text-decoration: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    RFQs & Quotes
                </a>
                <a href="{{ route('supplier.profile') }}" class="btn-secondary" style="font-size: 0.75rem; justify-content: center; padding: 8px; text-decoration: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
