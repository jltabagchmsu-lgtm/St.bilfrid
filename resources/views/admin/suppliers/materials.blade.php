@extends('layouts.app')

@section('title', 'Cross-Supplier Product Catalog - St. Bilfrid Dev. Corp')
@section('header_title', 'Cross-Supplier Product Matrix')
@section('header_subtitle', 'Compare product specifications, trade rates, and minimum order requirements across all supplier partners.')

@section('content')

<style>
    /* Category Filter Tabs */
    .mat-cat-tabs {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    .mat-cat-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a !important;
        text-decoration: none !important;
        border-radius: 9px;
        border: 1.5px solid transparent;
        transition: all 0.2s ease;
        background: transparent;
    }
    .mat-cat-tab:hover {
        background: #ffffff;
        color: #000000 !important;
        border-color: #cbd5e1;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .mat-cat-tab.active {
        background: #ffffff;
        color: #0f172a !important;
        font-weight: 800;
        border-color: #0f172a;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
    }
    .mat-cat-count {
        background: #e2e8f0;
        color: #0f172a;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 800;
    }
    .mat-cat-tab.active .mat-cat-count {
        background: #0f172a;
        color: #ffffff;
    }

    /* Search & Filter Card */
    .mat-filter-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .mat-filter-grid {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
    }
    .mat-search-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 300px;
    }
    .mat-search-wrapper {
        position: relative;
        flex: 1;
        max-width: 420px;
    }
    .mat-search-input {
        width: 100%;
        height: 44px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 0 14px 0 42px;
        font-size: 0.875rem;
        font-weight: 600;
        color: #0f172a !important;
        background: #f8fafc;
        outline: none;
        transition: all 0.2s ease;
    }
    .mat-search-input:focus {
        border-color: #0f172a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
    }
    .mat-search-input::placeholder {
        color: #64748b;
        font-weight: 500;
    }
    .mat-select-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .mat-select-box {
        height: 44px;
        min-width: 175px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a !important;
        background: #f8fafc;
        outline: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .mat-select-box:focus {
        border-color: #0f172a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
    }
    .mat-select-box option {
        color: #0f172a;
        font-weight: 600;
        background: #ffffff;
    }
    .mat-btn-search {
        height: 44px;
        padding: 0 22px;
        font-size: 0.875rem;
        font-weight: 700;
        color: #ffffff !important;
        background: #0f172a;
        border: 1.5px solid #0f172a;
        border-radius: 10px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
    }
    .mat-btn-search:hover {
        background: #1e293b;
        border-color: #1e293b;
        transform: translateY(-1px);
    }
    .mat-btn-reset {
        height: 44px;
        padding: 0 16px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #dc2626 !important;
        background: #fef2f2;
        border: 1.5px solid #fca5a5;
        border-radius: 10px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .mat-btn-reset:hover {
        background: #fee2e2;
        border-color: #f87171;
    }

    /* Subcategory Chips */
    .mat-subcat-bar {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
    }
    .mat-subcat-label {
        font-size: 0.75rem;
        font-weight: 800;
        color: #0f172a !important;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-right: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .mat-subcat-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #0f172a !important;
        text-decoration: none !important;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        transition: all 0.15s ease;
        line-height: 1.3;
    }
    .mat-subcat-chip:hover {
        background: #ffffff;
        border-color: #0f172a;
        color: #000000 !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .mat-subcat-chip.active {
        background: #0f172a !important;
        color: #ffffff !important;
        border-color: #0f172a !important;
        box-shadow: 0 2px 8px rgba(15,23,42,0.2);
    }
</style>

<!-- Category Filter Tabs -->
<div class="mat-cat-tabs">
    <a href="{{ route('admin.suppliers.materials', request()->except(['category', 'subcategory', 'page'])) }}" class="mat-cat-tab {{ !request('category') || request('category') === 'all' ? 'active' : '' }}">
        All Trade Categories <span class="mat-cat-count">{{ \App\Models\SupplierMaterial::where('is_active', true)->count() }}</span>
    </a>
    <a href="{{ route('admin.suppliers.materials', array_merge(request()->except(['subcategory', 'page']), ['category' => 'Windows & Doors'])) }}" class="mat-cat-tab {{ request('category') === 'Windows & Doors' ? 'active' : '' }}">
        Windows & Doors <span class="mat-cat-count">{{ \App\Models\SupplierMaterial::where('category', 'Windows & Doors')->where('is_active', true)->count() }}</span>
    </a>
    <a href="{{ route('admin.suppliers.materials', array_merge(request()->except(['subcategory', 'page']), ['category' => 'Roofing'])) }}" class="mat-cat-tab {{ request('category') === 'Roofing' ? 'active' : '' }}">
        Roofing Materials <span class="mat-cat-count">{{ \App\Models\SupplierMaterial::where('category', 'Roofing')->where('is_active', true)->count() }}</span>
    </a>
    <a href="{{ route('admin.suppliers.materials', array_merge(request()->except(['subcategory', 'page']), ['category' => 'Structural & Masonry'])) }}" class="mat-cat-tab {{ request('category') === 'Structural & Masonry' ? 'active' : '' }}">
        Structural & Masonry <span class="mat-cat-count">{{ \App\Models\SupplierMaterial::where('category', 'Structural & Masonry')->where('is_active', true)->count() }}</span>
    </a>
</div>

<!-- Search, Filter & Sorting Bar -->
<div class="mat-filter-card">
    <form method="GET" action="{{ route('admin.suppliers.materials') }}">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif

        <div class="mat-filter-grid">
            <!-- Left: Search Box & Reset -->
            <div class="mat-search-group">
                <div class="mat-search-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product name, specs, or code..." class="mat-search-input">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <button type="submit" class="mat-btn-search">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    Search
                </button>
                @if(request()->hasAny(['search', 'supplier_id', 'status', 'sort', 'subcategory']))
                    <a href="{{ route('admin.suppliers.materials', request('category') ? ['category' => request('category')] : []) }}" class="mat-btn-reset" title="Clear all filters">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        Reset
                    </a>
                @endif
            </div>

            <!-- Right: Supplier & Sorting Selectors -->
            <div class="mat-select-group">
                <!-- Supplier Filter -->
                <select name="supplier_id" onchange="this.form.submit()" class="mat-select-box" title="Filter by Supplier">
                    <option value="all">All Suppliers</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Availability Status -->
                <select name="status" onchange="this.form.submit()" class="mat-select-box" title="Filter by Status">
                    <option value="all" {{ !request('status') || request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available for Order</option>
                    <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                </select>

                <!-- Sorting -->
                <select name="sort" onchange="this.form.submit()" class="mat-select-box" title="Sort Order">
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Name (A-Z)</option>
                    <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest Cataloged</option>
                </select>
            </div>
        </div>

        <!-- Subcategory Chips -->
        @if(isset($subcategories) && $subcategories->count() > 0)
            <div class="mat-subcat-bar">
                <span class="mat-subcat-label">Subcategory:</span>
                <a href="{{ route('admin.suppliers.materials', array_merge(request()->except('subcategory'), ['subcategory' => 'all'])) }}" class="mat-subcat-chip {{ !request('subcategory') || request('subcategory') === 'all' ? 'active' : '' }}">
                    All
                </a>
                @foreach($subcategories as $subcat)
                    <a href="{{ route('admin.suppliers.materials', array_merge(request()->except('subcategory'), ['subcategory' => $subcat])) }}" class="mat-subcat-chip {{ request('subcategory') === $subcat ? 'active' : '' }}">
                        {{ $subcat }}
                    </a>
                @endforeach
            </div>
        @endif
    </form>
</div>

<!-- Product Matrix Table -->
<div style="overflow-x: auto; margin-bottom: 24px;">
    <table class="grid-table">
        <thead>
            <tr>
                <th>Supplier Organization</th>
                <th>Product Code</th>
                <th>Product Details & Specifications</th>
                <th>Subcategory</th>
                <th>Unit</th>
                <th>Unit Price (PHP)</th>
                <th>MOQ</th>
                <th>Status</th>
                <th>Procurement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $mat)
                @php 
                    $badge = $mat->status_badge;
                    $catColor = match($mat->category) {
                        'Windows & Doors' => '#dc2626',
                        'Roofing' => '#ef4444',
                        'Structural & Masonry' => '#059669',
                        default => '#dc2626',
                    };
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem;">
                            {{ $mat->supplier ? $mat->supplier->name : 'Supplier' }}
                        </div>
                        <span class="pill-badge" style="background: var(--bg-surface-alt); color: {{ $catColor }}; font-size: 0.68rem; margin-top: 3px;">
                            {{ $mat->category }}
                        </span>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: var(--primary-red); font-size: 0.8rem;">
                            {{ $mat->material_code }}
                        </strong>
                    </td>
                    <td style="max-width: 300px;">
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;">
                            {{ $mat->name }}
                        </div>
                        @if($mat->specifications)
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; line-height: 1.3;">
                                {{ Str::limit($mat->specifications, 110) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">
                            {{ $mat->subcategory ?? 'General' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; font-family: var(--font-mono);">{{ $mat->unit }}</span>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: var(--text-primary); font-size: 0.95rem;">
                            PHP {{ number_format($mat->unit_price, 2) }}
                        </strong>
                    </td>
                    <td>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ $mat->min_order_qty }} {{ $mat->unit }}
                        </span>
                    </td>
                    <td>
                        <span class="pill-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                            {{ $badge['label'] }}
                        </span>
                    </td>
                    <td style="white-space: nowrap;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <button type="button" onclick="orderSingleMaterial({{ json_encode($mat->load('supplier')) }})" class="btn-primary" style="padding: 6px 10px; font-size: 0.75rem; white-space: nowrap;">
                                Order Item
                            </button>
                            <button type="button" onclick="inquireSingleMaterial({{ json_encode($mat->load('supplier')) }})" class="btn-secondary" style="padding: 6px 10px; font-size: 0.75rem; color: var(--primary-red); border-color: rgba(220, 38, 38, 0.3); white-space: nowrap;">
                                Inquire / RFQ
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 48px; color: var(--text-muted);">
                        No products found matching your category, supplier, and search criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Links -->
<div style="margin-top: 14px;">
    {{ $materials->links() }}
</div>

<!-- Modal: Quick Purchase Order for Selected Product -->
<div class="modal-backdrop" id="quickOrderMaterialModal">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Generate Purchase Order to Supplier</h3>
                <p id="qomSupplierHeader" style="font-size: 0.75rem; color: var(--primary-red); margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('quickOrderMaterialModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        <form action="{{ route('admin.suppliers.orders.store') }}" method="POST">
            @csrf
            <input type="hidden" name="supplier_id" id="qomSupplierId">
            <input type="hidden" name="items[0][material_id]" id="qomMaterialId">

            <div class="modal-body">
                <!-- Selected Material Card -->
                <div style="background: var(--bg-surface-alt); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                    <div id="qomMaterialName" style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);"></div>
                    <div id="qomMaterialSpecs" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;"></div>
                    <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 0.8rem;">
                        <div><span style="color: var(--text-muted);">Unit Rate:</span> <strong id="qomUnitPriceDisplay" style="color: var(--primary-red); font-family: var(--font-mono);"></strong></div>
                        <div><span style="color: var(--text-muted);">Minimum Order:</span> <strong id="qomMoqDisplay" style="color: #059669; font-family: var(--font-mono);"></strong></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Order Quantity (<span id="qomUnitLabel"></span>) <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="number" name="items[0][quantity]" id="qomQtyInput" min="1" value="1" required oninput="calculateQomTotal()" class="input-field" style="width: 100%; font-size: 1.1rem; font-weight: 700;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Requested Delivery Date <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="date" name="requested_delivery_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="input-field" style="width: 100%;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Destination Project Site
                        </label>
                        <select name="project_id" id="qomProjectSelect" onchange="onQomProjectChange()" class="input-field" style="width: 100%;">
                            <option value="">-- Central Warehouse Depot --</option>
                            @foreach($activeProjects as $prj)
                                <option value="{{ $prj->id }}" data-location="{{ $prj->location }}">{{ $prj->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Delivery Address / Staging Site <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="text" name="delivery_location" id="qomDeliveryLocation" required class="input-field" style="width: 100%;">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Special Instructions / Delivery Remarks
                    </label>
                    <textarea name="notes" rows="2" placeholder="e.g. Call Site Engineer upon arrival" class="input-field" style="width: 100%; resize: vertical;"></textarea>
                </div>

                <div style="background: var(--bg-surface-alt); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-secondary);">Total PO Valuation:</span>
                    <strong id="qomTotalValuation" style="font-size: 1.25rem; font-weight: 800; color: var(--primary-red); font-family: var(--font-mono);">PHP 0.00</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('quickOrderMaterialModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Confirm Purchase Order</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Send Material Inquiry / RFQ to Supplier -->
<div class="modal-backdrop" id="inquireMaterialModal">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Send Material Inquiry / RFQ</h3>
                <p id="inqSupplierHeader" style="font-size: 0.75rem; color: var(--primary-red); margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('inquireMaterialModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        <form action="{{ route('admin.suppliers.inquiries.store') }}" method="POST">
            @csrf
            <input type="hidden" name="supplier_id" id="inqSupplierId">
            <input type="hidden" name="supplier_material_id" id="inqMaterialId">

            <div class="modal-body">
                <!-- Selected Material Overview -->
                <div style="background: var(--bg-surface-alt); border: 1px solid var(--border-color); border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                    <div id="inqMaterialName" style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);"></div>
                    <div id="inqMaterialSpecs" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;"></div>
                    <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 0.8rem;">
                        <div><span style="color: var(--text-muted);">Standard Rate:</span> <strong id="inqUnitPriceDisplay" style="color: var(--primary-red); font-family: var(--font-mono);"></strong></div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Inquiry Subject <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="text" name="subject" id="inqSubjectInput" required placeholder="e.g. Bulk discount quote for 500 units" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Estimated Qty (<span id="inqUnitLabel"></span>)
                        </label>
                        <input type="number" name="requested_quantity" id="inqQtyInput" min="1" placeholder="Optional" class="input-field" style="width: 100%; font-family: var(--font-mono);">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Message & Inquiries for Supplier <span style="color: var(--primary-red);">*</span>
                    </label>
                    <textarea name="message" id="inqMessageInput" rows="4" required placeholder="Ask the supplier about custom dimensions, batch availability, bulk pricing tiers, or delivery lead times..." class="input-field" style="width: 100%; resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('inquireMaterialModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    Send Inquiry to Supplier
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let activeQomUnitPrice = 0;

    function orderSingleMaterial(mat) {
        document.getElementById('qomSupplierId').value = mat.supplier_id;
        document.getElementById('qomMaterialId').value = mat.id;
        document.getElementById('qomSupplierHeader').textContent = 'Supplier: ' + (mat.supplier ? mat.supplier.name : '') + ' (' + mat.category + ')';
        document.getElementById('qomMaterialName').textContent = mat.name;
        document.getElementById('qomMaterialSpecs').textContent = mat.specifications || 'Standard manufacturer specifications';
        document.getElementById('qomUnitPriceDisplay').textContent = 'PHP ' + Number(mat.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' / ' + mat.unit;
        document.getElementById('qomMoqDisplay').textContent = (mat.min_order_qty || 1) + ' ' + mat.unit;
        document.getElementById('qomUnitLabel').textContent = mat.unit;
        document.getElementById('qomQtyInput').value = mat.min_order_qty || 1;
        document.getElementById('qomQtyInput').min = mat.min_order_qty || 1;
        document.getElementById('qomDeliveryLocation').value = 'St. Bilfrid Central Warehouse Depot, Silay City';
        
        activeQomUnitPrice = parseFloat(mat.unit_price) || 0;
        calculateQomTotal();
        openModal('quickOrderMaterialModal');
    }

    function inquireSingleMaterial(mat) {
        document.getElementById('inqSupplierId').value = mat.supplier_id;
        document.getElementById('inqMaterialId').value = mat.id;
        document.getElementById('inqSupplierHeader').textContent = 'Supplier: ' + (mat.supplier ? mat.supplier.name : '') + ' (' + mat.category + ')';
        document.getElementById('inqMaterialName').textContent = mat.name;
        document.getElementById('inqMaterialSpecs').textContent = mat.specifications || 'Standard manufacturer specifications';
        document.getElementById('inqUnitPriceDisplay').textContent = 'PHP ' + Number(mat.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' / ' + mat.unit;
        document.getElementById('inqUnitLabel').textContent = mat.unit;
        document.getElementById('inqSubjectInput').value = 'Pricing & Lead Time Inquiry: ' + mat.name;
        document.getElementById('inqQtyInput').value = mat.min_order_qty || '';
        document.getElementById('inqMessageInput').value = '';

        openModal('inquireMaterialModal');
    }

    function calculateQomTotal() {
        const qty = parseInt(document.getElementById('qomQtyInput').value) || 0;
        const total = qty * activeQomUnitPrice;
        document.getElementById('qomTotalValuation').textContent = 'PHP ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function onQomProjectChange() {
        const select = document.getElementById('qomProjectSelect');
        const selected = select.options[select.selectedIndex];
        const location = selected.getAttribute('data-location');
        if (location && location.trim() !== '') {
            document.getElementById('qomDeliveryLocation').value = location;
        } else {
            document.getElementById('qomDeliveryLocation').value = 'St. Bilfrid Central Warehouse Depot, Silay City';
        }
    }
</script>
@endpush
