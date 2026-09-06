@extends('supplier.layout')

@section('title', $supplier->name . ' - Operations & Fulfillment Hub')
@section('page_title', $supplier->name)
@section('page_subtitle', $supplier->category . ' Supplier Operations & Order Fulfillment Center')

@section('content')

<!-- Metrics Row: Catalog Offerings & Purchase Order Pipeline -->
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
        <h3 style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
            Procurement & Order Fulfillment Overview
        </h3>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('supplier.materials') }}" style="font-size: 0.8rem; color: #38bdf8; text-decoration: none; font-weight: 600;">
                Product Catalog &rarr;
            </a>
            <span style="color: rgba(255,255,255,0.2);">&bull;</span>
            <a href="{{ route('supplier.orders') }}" style="font-size: 0.8rem; color: #38bdf8; text-decoration: none; font-weight: 600;">
                Purchase Orders &rarr;
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        <!-- Catalog Products -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Catalog Products</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(56, 189, 248, 0.1); display: grid; place-items: center; color: #38bdf8;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: var(--text-primary); margin-top: 10px;">
                {{ number_format($totalProducts) }}
            </div>
            <div style="font-size: 0.75rem; color: #10b981; margin-top: 4px;">
                {{ number_format($activeProducts) }} active offerings available to Admin
            </div>
        </div>

        <!-- Pending Confirmation -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Awaiting Acceptance</span>
                <span class="pill-badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">Action Required</span>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #f59e0b; margin-top: 10px;">
                {{ number_format($pendingOrders) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                New purchase orders from Admin
            </div>
        </div>

        <!-- In Production / Processing -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">In Fabrication / Dispatch</span>
                <span class="pill-badge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8;">In Progress</span>
            </div>
            <div style="font-size: 1.85rem; font-weight: 800; color: #38bdf8; margin-top: 10px;">
                {{ number_format($processingOrders + $readyOrders) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                {{ number_format($readyOrders) }} staged ready for delivery
            </div>
        </div>

        <!-- Completed Revenue -->
        <div class="stat-card" style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Fulfilled Value</span>
                <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">{{ number_format($completedOrders + $deliveredOrders) }} Orders</span>
            </div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #f8fafc; margin-top: 10px; font-family: var(--font-mono);">
                PHP {{ number_format($totalRevenue, 2) }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px;">
                Delivered procurement turnover
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Recent Purchase Orders & Catalog Highlights Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- Left Column: Recent Purchase Orders -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Incoming Purchase Orders</h3>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Client procurement orders requiring supply & dispatch</p>
            </div>
            <button type="button" onclick="openModal('addMaterialModal')" class="btn-primary" style="font-size: 0.8rem; padding: 8px 16px;">
                + Add Product
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table class="grid-table">
                <thead>
                    <tr>
                        <th>PO Code</th>
                        <th>Project Destination</th>
                        <th>Required Date</th>
                        <th>Order Items</th>
                        <th>Total Value</th>
                        <th>Fulfillment Status</th>
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
                                    PHP {{ number_format($ord->total_amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                <span class="pill-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('supplier.orders', ['search' => $ord->order_code]) }}" class="btn-secondary" style="padding: 6px 10px; font-size: 0.75rem;">
                                    Fulfill
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                No incoming purchase orders received yet. Admin will place orders against your product catalog.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Product Catalog Offerings & Supplier Profile -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary);">Catalog Offerings</h3>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Active supply products and price rates</p>
            </div>
            <a href="{{ route('supplier.materials') }}" style="font-size: 0.75rem; color: #38bdf8; text-decoration: none; font-weight: 600;">
                View All ({{ $totalProducts }})
            </a>
        </div>

        <div style="background: rgba(15, 23, 42, 0.65); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 16px;">
            @forelse($catalogHighlights as $mat)
                <div style="padding: 12px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.06); display: flex; align-items: center; justify-content: space-between;">
                    <div style="min-width: 0; flex: 1; padding-right: 12px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $mat->name }}
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                            <span style="font-family: var(--font-mono); color: #38bdf8;">{{ $mat->material_code }}</span>
                            <span>&bull;</span>
                            <span>{{ $mat->subcategory ?? 'General' }}</span>
                        </div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <div style="font-family: var(--font-mono); font-weight: 700; color: var(--text-primary); font-size: 0.85rem;">
                            PHP {{ number_format($mat->unit_price, 2) }}
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">
                            Per {{ $mat->unit }}
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 0.8rem;">
                    No catalog products registered yet. Click "+ Add Product" to publish items for Admin procurement.
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

            <div style="font-size: 0.8rem; color: var(--text-secondary);">
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
                <span style="font-size: 0.75rem; color: var(--text-muted);">Supplier Rating:</span>
                <span class="pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981;">
                    {{ number_format($supplier->rating, 2) }} / 5.0 Approved
                </span>
            </div>
        </div>
    </div>
</div>

@endsection
