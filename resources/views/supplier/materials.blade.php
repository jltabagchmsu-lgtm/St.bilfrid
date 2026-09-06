@extends('supplier.layout')

@section('title', 'My Materials Catalog - ' . $supplier->name)
@section('page_title', 'My Materials & Supplies Catalog')
@section('page_subtitle', 'Manage inventory availability, unit prices, technical specifications, and trade offerings.')

@section('content')

<!-- Action & Filter Bar -->
<div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('supplier.materials') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <!-- Left: Search Box -->
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px;">
            <div style="position: relative; width: 100%; max-width: 360px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, or specs..." class="input-field" style="width: 100%; padding-left: 36px; font-size: 0.85rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <button type="submit" class="btn-secondary" style="padding: 8px 14px; font-size: 0.8rem;">
                Search
            </button>
            @if(request()->hasAny(['search', 'subcategory', 'status']))
                <a href="{{ route('supplier.materials') }}" class="btn-secondary" style="padding: 8px 12px; font-size: 0.8rem; color: var(--text-muted);">
                    Reset
                </a>
            @endif
        </div>

        <!-- Right: Status Filter & Add Material Trigger -->
        <div style="display: flex; align-items: center; gap: 12px;">
            <select name="status" onchange="this.form.submit()" class="input-field" style="font-size: 0.8rem; padding: 8px 12px;">
                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Availability Statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available (In Stock)</option>
                <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock (<= 10)</option>
                <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
            </select>

            <button type="button" onclick="openModal('addMaterialModal')" class="btn-primary" style="font-size: 0.8rem; padding: 8px 16px;">
                + Add Material
            </button>
        </div>
    </form>

    <!-- Subcategory Pill Filters -->
    @if(isset($subcategories) && $subcategories->count() > 0)
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 16px; padding-top: 14px; border-top: 1px solid rgba(255, 255, 255, 0.06);">
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Categories:</span>
            <a href="{{ route('supplier.materials', array_merge(request()->except('subcategory'), ['subcategory' => 'all'])) }}" class="filter-pill {{ !request('subcategory') || request('subcategory') === 'all' ? 'active' : '' }}">
                All ({{ $supplier->materials()->count() }})
            </a>
            @foreach($subcategories as $subcat)
                <a href="{{ route('supplier.materials', array_merge(request()->except('subcategory'), ['subcategory' => $subcat])) }}" class="filter-pill {{ request('subcategory') === $subcat ? 'active' : '' }}">
                    {{ $subcat }}
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Materials Inventory Grid Table -->
<div style="overflow-x: auto; margin-bottom: 24px;">
    <table class="grid-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Material Details & Specifications</th>
                <th>Category / Type</th>
                <th>Unit</th>
                <th>Stock Quantity</th>
                <th>Unit Price (PHP)</th>
                <th>MOQ</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $mat)
                @php $badge = $mat->status_badge; @endphp
                <tr>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #38bdf8; font-size: 0.8rem;">{{ $mat->material_code }}</strong>
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
                        <span class="pill-badge" style="background: rgba(255, 255, 255, 0.06); color: var(--text-secondary);">
                            {{ $mat->subcategory ?? 'General' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; font-family: var(--font-mono);">{{ $mat->unit }}</span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="font-size: 0.95rem; font-family: var(--font-mono); color: {{ $mat->available_quantity <= 0 ? '#ef4444' : ($mat->available_quantity <= 10 ? '#f59e0b' : 'var(--text-primary)') }};">
                                {{ number_format($mat->available_quantity) }}
                            </strong>
                            <button type="button" onclick="openQuickStockModal('{{ $mat->id }}', '{{ addslashes($mat->name) }}', '{{ $mat->available_quantity }}', '{{ $mat->unit }}')" title="Quick Adjust Stock" style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); color: #38bdf8; border-radius: 4px; padding: 2px 6px; font-size: 0.7rem; cursor: pointer;">
                                &plusmn;
                            </button>
                        </div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: var(--text-primary); font-size: 0.9rem;">
                            ₱{{ number_format($mat->unit_price, 2) }}
                        </strong>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">{{ $mat->min_order_qty }} {{ $mat->unit }}</span>
                    </td>
                    <td>
                        <span class="pill-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                            {{ $badge['label'] }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">
                            {{ $mat->updated_at ? $mat->updated_at->format('M d, Y') : '-' }}
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <button type="button" onclick="openEditMaterialModal({{ json_encode($mat) }})" class="btn-secondary" style="padding: 5px 10px; font-size: 0.75rem;" title="Edit Specifications & Pricing">
                                Edit
                            </button>
                            <form action="{{ route('supplier.materials.destroy', $mat->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete or deactivate material {{ addslashes($mat->name) }}?');" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-secondary" style="padding: 5px 8px; font-size: 0.75rem; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" title="Delete / Deactivate">
                                    &times;
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 48px; color: var(--text-muted);">
                        No materials found matching the selected search and category filters.
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

<!-- Edit Material Modal -->
<div class="modal-backdrop" id="editMaterialModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Edit Material Specifications</h3>
                <p id="editMaterialCodeBadge" style="font-size: 0.75rem; color: #38bdf8; font-family: var(--font-mono); margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('editMaterialModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        <form id="editMaterialForm" method="POST">
            @csrf
            <div class="modal-body">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Material Name <span style="color: var(--primary-red);">*</span>
                    </label>
                    <input type="text" name="name" id="edit_name" required class="input-field" style="width: 100%;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Subcategory
                        </label>
                        <input type="text" name="subcategory" id="edit_subcategory" class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Unit of Measurement <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="text" name="unit" id="edit_unit" required class="input-field" style="width: 100%;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Available Stock <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="number" name="available_quantity" id="edit_available_quantity" min="0" required class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Unit Price (PHP) <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" name="unit_price" id="edit_unit_price" required class="input-field" style="width: 100%;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Min Order Qty (MOQ)
                        </label>
                        <input type="number" min="1" name="min_order_qty" id="edit_min_order_qty" class="input-field" style="width: 100%;">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Material Specifications
                    </label>
                    <textarea name="specifications" id="edit_specifications" rows="2" class="input-field" style="width: 100%; resize: vertical;"></textarea>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Description / Application Notes
                    </label>
                    <textarea name="description" id="edit_description" rows="2" class="input-field" style="width: 100%; resize: vertical;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Availability Status <span style="color: var(--primary-red);">*</span>
                        </label>
                        <select name="availability_status" id="edit_availability_status" required class="input-field" style="width: 100%;">
                            <option value="available">Available (In Stock)</option>
                            <option value="low_stock">Low Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="unavailable">Unavailable</option>
                        </select>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 24px;">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" style="width: 18px; height: 18px;">
                        <label for="edit_is_active" style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); cursor: pointer;">
                            Active in Public Catalog
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editMaterialModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Restock Modal -->
<div class="modal-backdrop" id="quickStockModal">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-primary);">Quick Stock Adjustment</h3>
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

    function openEditMaterialModal(mat) {
        document.getElementById('editMaterialCodeBadge').textContent = 'Code: ' + mat.material_code;
        document.getElementById('edit_name').value = mat.name;
        document.getElementById('edit_subcategory').value = mat.subcategory || '';
        document.getElementById('edit_unit').value = mat.unit;
        document.getElementById('edit_available_quantity').value = mat.available_quantity;
        document.getElementById('edit_unit_price').value = mat.unit_price;
        document.getElementById('edit_min_order_qty').value = mat.min_order_qty || 1;
        document.getElementById('edit_specifications').value = mat.specifications || '';
        document.getElementById('edit_description').value = mat.description || '';
        document.getElementById('edit_availability_status').value = mat.availability_status;
        document.getElementById('edit_is_active').checked = mat.is_active ? true : false;
        document.getElementById('editMaterialForm').action = '/supplier/materials/' + mat.id + '/update';
        openModal('editMaterialModal');
    }
</script>
@endpush
