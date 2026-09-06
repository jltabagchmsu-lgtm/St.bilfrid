@extends('layouts.app')

@section('title', 'Cross-Supplier Materials Catalog - St. Bilfrid Dev. Corp')
@section('header_title', 'Cross-Supplier Materials Matrix')
@section('header_subtitle', 'Compare material specifications, real-time inventory availability, and unit pricing across all trade supplier partners.')

@section('content')

<!-- Category Filter Tabs -->
<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px; flex-wrap: wrap;">
    <a href="{{ route('admin.suppliers.materials', request()->except(['category', 'subcategory', 'page'])) }}" class="filter-pill {{ !request('category') || request('category') === 'all' ? 'active' : '' }}">
        All Trade Categories ({{ \App\Models\SupplierMaterial::where('is_active', true)->count() }})
    </a>
    <a href="{{ route('admin.suppliers.materials', array_merge(request()->except(['subcategory', 'page']), ['category' => 'Windows & Doors'])) }}" class="filter-pill {{ request('category') === 'Windows & Doors' ? 'active' : '' }}" style="{{ request('category') === 'Windows & Doors' ? 'background: rgba(56, 189, 248, 0.15); color: #38bdf8; border-color: rgba(56, 189, 248, 0.4);' : '' }}">
        Windows & Doors ({{ \App\Models\SupplierMaterial::where('category', 'Windows & Doors')->where('is_active', true)->count() }})
    </a>
    <a href="{{ route('admin.suppliers.materials', array_merge(request()->except(['subcategory', 'page']), ['category' => 'Roofing'])) }}" class="filter-pill {{ request('category') === 'Roofing' ? 'active' : '' }}" style="{{ request('category') === 'Roofing' ? 'background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: rgba(239, 68, 68, 0.4);' : '' }}">
        Roofing Materials ({{ \App\Models\SupplierMaterial::where('category', 'Roofing')->where('is_active', true)->count() }})
    </a>
    <a href="{{ route('admin.suppliers.materials', array_merge(request()->except(['subcategory', 'page']), ['category' => 'Structural & Masonry'])) }}" class="filter-pill {{ request('category') === 'Structural & Masonry' ? 'active' : '' }}" style="{{ request('category') === 'Structural & Masonry' ? 'background: rgba(16, 185, 129, 0.15); color: #10b981; border-color: rgba(16, 185, 129, 0.4);' : '' }}">
        Structural & Masonry ({{ \App\Models\SupplierMaterial::where('category', 'Structural & Masonry')->where('is_active', true)->count() }})
    </a>
</div>

<!-- Search, Filter & Sorting Bar -->
<div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('admin.suppliers.materials') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif

        <!-- Left: Search Box -->
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px;">
            <div style="position: relative; width: 100%; max-width: 320px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search material name, specs, or code..." class="input-field" style="width: 100%; padding-left: 36px; font-size: 0.85rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <button type="submit" class="btn-secondary" style="padding: 8px 14px; font-size: 0.8rem;">
                Search
            </button>
            @if(request()->hasAny(['search', 'supplier_id', 'status', 'sort', 'subcategory']))
                <a href="{{ route('admin.suppliers.materials', request('category') ? ['category' => request('category')] : []) }}" class="btn-secondary" style="padding: 8px 12px; font-size: 0.8rem; color: var(--text-muted);">
                    Reset Filters
                </a>
            @endif
        </div>

        <!-- Right: Supplier & Status Selectors -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Supplier Filter -->
            <select name="supplier_id" onchange="this.form.submit()" class="input-field" style="font-size: 0.8rem; padding: 8px 12px;">
                <option value="all">All Suppliers</option>
                @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                        {{ $sup->name }}
                    </option>
                @endforeach
            </select>

            <!-- Availability Status -->
            <select name="status" onchange="this.form.submit()" class="input-field" style="font-size: 0.8rem; padding: 8px 12px;">
                <option value="all" {{ !request('status') || request('status') === 'all' ? 'selected' : '' }}>All Availability</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available in Stock</option>
                <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock (<= 10)</option>
                <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>

            <!-- Sorting -->
            <select name="sort" onchange="this.form.submit()" class="input-field" style="font-size: 0.8rem; padding: 8px 12px;">
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Sort: Name (A-Z)</option>
                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="stock_high" {{ request('sort') === 'stock_high' ? 'selected' : '' }}>Highest Stock</option>
                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest Cataloged</option>
            </select>
        </div>
    </form>

    <!-- Subcategory Pills -->
    @if(isset($subcategories) && $subcategories->count() > 0)
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.06);">
            <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Subcategory:</span>
            <a href="{{ route('admin.suppliers.materials', array_merge(request()->except('subcategory'), ['subcategory' => 'all'])) }}" class="filter-pill {{ !request('subcategory') || request('subcategory') === 'all' ? 'active' : '' }}" style="font-size: 0.75rem; padding: 4px 10px;">
                All
            </a>
            @foreach($subcategories as $subcat)
                <a href="{{ route('admin.suppliers.materials', array_merge(request()->except('subcategory'), ['subcategory' => $subcat])) }}" class="filter-pill {{ request('subcategory') === $subcat ? 'active' : '' }}" style="font-size: 0.75rem; padding: 4px 10px;">
                    {{ $subcat }}
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Materials Comparison Table -->
<div style="overflow-x: auto; margin-bottom: 24px;">
    <table class="grid-table">
        <thead>
            <tr>
                <th>Supplier Organization</th>
                <th>Material Code</th>
                <th>Material Details & Specifications</th>
                <th>Subcategory</th>
                <th>Unit</th>
                <th>Available Stock</th>
                <th>Unit Price (PHP)</th>
                <th>MOQ</th>
                <th>Stock Status</th>
                <th>Procurement</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $mat)
                @php 
                    $badge = $mat->status_badge;
                    $catColor = match($mat->category) {
                        'Windows & Doors' => '#38bdf8',
                        'Roofing' => '#ef4444',
                        'Structural & Masonry' => '#10b981',
                        default => '#818cf8',
                    };
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem;">
                            {{ $mat->supplier ? $mat->supplier->name : 'Supplier' }}
                        </div>
                        <span class="pill-badge" style="background: rgba(255, 255, 255, 0.06); color: {{ $catColor }}; font-size: 0.68rem; margin-top: 3px;">
                            {{ $mat->category }}
                        </span>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #38bdf8; font-size: 0.8rem;">
                            {{ $mat->material_code }}
                        </strong>
                    </td>
                    <td style="max-width: 280px;">
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;">
                            {{ $mat->name }}
                        </div>
                        @if($mat->specifications)
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; line-height: 1.3;">
                                {{ Str::limit($mat->specifications, 90) }}
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
                        <strong style="font-size: 0.95rem; font-family: var(--font-mono); color: {{ $mat->available_quantity <= 0 ? '#ef4444' : ($mat->available_quantity <= 10 ? '#f59e0b' : 'var(--text-primary)') }};">
                            {{ number_format($mat->available_quantity) }}
                        </strong>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: var(--text-primary); font-size: 0.95rem;">
                            ₱{{ number_format($mat->unit_price, 2) }}
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
                    <td>
                        <button type="button" onclick="orderSingleMaterial({{ json_encode($mat->load('supplier')) }})" class="btn-primary" style="padding: 6px 12px; font-size: 0.75rem;">
                            Order Item
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 48px; color: var(--text-muted);">
                        No materials found matching your category, supplier, and search criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Links -->
<div style="display: flex; justify-content: flex-end;">
    {{ $materials->links() }}
</div>

<!-- Modal: Quick Purchase Order for Selected Material -->
<div class="modal-backdrop" id="quickOrderMaterialModal">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Order Material from Supplier</h3>
                <p id="qomSupplierHeader" style="font-size: 0.75rem; color: #38bdf8; margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('quickOrderMaterialModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        <form action="{{ route('admin.suppliers.orders.store') }}" method="POST">
            @csrf
            <input type="hidden" name="supplier_id" id="qomSupplierId">
            <input type="hidden" name="items[0][material_id]" id="qomMaterialId">

            <div class="modal-body">
                <!-- Selected Material Card -->
                <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                    <div id="qomMaterialName" style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary);"></div>
                    <div id="qomMaterialSpecs" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;"></div>
                    <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 0.8rem;">
                        <div><span style="color: var(--text-muted);">Unit Price:</span> <strong id="qomUnitPriceDisplay" style="color: #38bdf8; font-family: var(--font-mono);"></strong></div>
                        <div><span style="color: var(--text-muted);">Current Stock:</span> <strong id="qomStockDisplay" style="color: #10b981; font-family: var(--font-mono);"></strong></div>
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

                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--text-secondary);">Total PO Valuation:</span>
                    <strong id="qomTotalValuation" style="font-size: 1.25rem; font-weight: 800; color: #38bdf8; font-family: var(--font-mono);">₱0.00</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('quickOrderMaterialModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Confirm Purchase Order</button>
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
        document.getElementById('qomUnitPriceDisplay').textContent = '₱' + Number(mat.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2}) + ' / ' + mat.unit;
        document.getElementById('qomStockDisplay').textContent = mat.available_quantity + ' ' + mat.unit;
        document.getElementById('qomUnitLabel').textContent = mat.unit;
        document.getElementById('qomQtyInput').value = mat.min_order_qty || 1;
        document.getElementById('qomQtyInput').min = mat.min_order_qty || 1;
        document.getElementById('qomDeliveryLocation').value = 'St. Bilfrid Central Warehouse Depot, Silay City';
        
        activeQomUnitPrice = parseFloat(mat.unit_price) || 0;
        calculateQomTotal();
        openModal('quickOrderMaterialModal');
    }

    function calculateQomTotal() {
        const qty = parseInt(document.getElementById('qomQtyInput').value) || 0;
        const total = qty * activeQomUnitPrice;
        document.getElementById('qomTotalValuation').textContent = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
