@extends('supplier.layout')

@section('title', 'Purchase Orders - ' . $supplier->name)
@section('page_title', 'Client Purchase Orders')
@section('page_subtitle', 'Review purchase orders placed by St. Bilfrid Dev. Corp, manage delivery dates, and advance fulfillment statuses.')

@section('content')

<!-- Orders Search & Filter Bar -->
<div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('supplier.orders') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 260px;">
            <div style="position: relative; width: 100%; max-width: 360px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Order ID, location, notes..." class="input-field" style="width: 100%; padding-left: 36px; font-size: 0.85rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <button type="submit" class="btn-secondary" style="padding: 8px 14px; font-size: 0.8rem;">
                Search
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('supplier.orders') }}" class="btn-secondary" style="padding: 8px 12px; font-size: 0.8rem; color: var(--text-muted);">
                    Reset
                </a>
            @endif
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <select name="status" onchange="this.form.submit()" class="input-field" style="font-size: 0.8rem; padding: 8px 12px;">
                <option value="all" {{ !request('status') || request('status') === 'all' ? 'selected' : '' }}>All Order Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Confirmation</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing / In-Fabrication</option>
                <option value="ready_for_delivery" {{ request('status') === 'ready_for_delivery' ? 'selected' : '' }}>Ready for Delivery</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered to Site</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed / Accepted</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
    </form>
</div>

<!-- Purchase Orders Table -->
<div style="overflow-x: auto; margin-bottom: 24px;">
    <table class="grid-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Client / Project Destination</th>
                <th>Materials Ordered</th>
                <th>Order Date</th>
                <th>Requested Delivery</th>
                <th>Total Value (PHP)</th>
                <th>Fulfillment Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $ord)
                @php $badge = $ord->status_badge; @endphp
                <tr>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #38bdf8; font-size: 0.85rem;">{{ $ord->order_code }}</strong>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">PO Reference</div>
                    </td>
                    <td style="max-width: 220px;">
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem;">
                            {{ $ord->project ? $ord->project->title : 'Central Warehouse Depot' }}
                        </div>
                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $ord->delivery_location }}
                        </div>
                    </td>
                    <td style="max-width: 260px;">
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">
                            {{ $ord->items->count() }} line item(s)
                        </div>
                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $ord->items->pluck('material_name')->implode(', ') }}
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; font-family: var(--font-mono);">
                            {{ $ord->created_at->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 0.8rem; font-family: var(--font-mono); font-weight: 700; color: #38bdf8;">
                            {{ $ord->requested_delivery_date->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); color: var(--text-primary); font-size: 0.95rem;">
                            PHP {{ number_format($ord->total_amount, 2) }}
                        </strong>
                    </td>
                    <td>
                        <span class="pill-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                            {{ $badge['label'] }}
                        </span>
                    </td>
                    <td>
                        <button type="button" onclick="openOrderModal({{ json_encode($ord->load(['items', 'logs.user', 'project', 'orderedBy'])) }})" class="btn-primary" style="padding: 6px 12px; font-size: 0.75rem;">
                            Details & Status
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 48px; color: var(--text-muted);">
                        No purchase orders matching the specified filter criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Links -->
<div style="display: flex; justify-content: flex-end;">
    {{ $orders->links() }}
</div>

<!-- Order Details & Workflow Management Modal -->
<div class="modal-backdrop" id="orderDetailsModal">
    <div class="modal-box" style="max-width: 760px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Purchase Order Details</h3>
                <p id="modalOrderCodeHeader" style="font-size: 0.8rem; color: #38bdf8; font-family: var(--font-mono); margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('orderDetailsModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>
        
        <div class="modal-body" style="padding-top: 18px;">
            <!-- Summary Overview Banner -->
            <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 16px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                <div>
                    <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Client / Project</div>
                    <div id="modalClientProject" style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-top: 4px;"></div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Requested Delivery</div>
                    <div id="modalRequestedDelivery" style="font-size: 0.85rem; font-weight: 700; color: #38bdf8; font-family: var(--font-mono); margin-top: 4px;"></div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Purchase Order</div>
                    <div id="modalTotalAmount" style="font-size: 1rem; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono); margin-top: 4px;"></div>
                </div>
            </div>

            <!-- Delivery Location & Contractor Notes -->
            <div style="margin-bottom: 20px; font-size: 0.85rem; background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 10px; padding: 14px;">
                <div style="margin-bottom: 6px;">
                    <strong style="color: var(--text-muted);">Delivery Address / Staging Site:</strong>
                    <span id="modalDeliveryLocation" style="color: var(--text-primary); margin-left: 6px;"></span>
                </div>
                <div id="modalNotesContainer" style="display: none;">
                    <strong style="color: var(--text-muted);">Contractor Instructions & Notes:</strong>
                    <div id="modalNotes" style="color: #cbd5e1; margin-top: 4px; font-style: italic; background: rgba(0,0,0,0.2); padding: 8px 12px; border-radius: 6px;"></div>
                </div>
            </div>

            <!-- Ordered Materials Items Table -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 10px;">
                    Ordered Materials Breakdown
                </h4>
                <div style="overflow-x: auto;">
                    <table class="grid-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th style="text-align: right;">Quantity</th>
                                <th>Unit</th>
                                <th style="text-align: right;">Unit Price (PHP)</th>
                                <th style="text-align: right;">Total Price (PHP)</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsTbody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Status Transition Form -->
            <form id="updateOrderStatusForm" method="POST" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                @csrf
                <h4 style="font-size: 0.9rem; font-weight: 700; color: #38bdf8; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Advance Order Fulfillment Status
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Workflow Status Transition <span style="color: var(--primary-red);">*</span>
                        </label>
                        <select name="status" id="modalStatusSelect" required class="input-field" style="width: 100%;">
                            <option value="pending">Pending Confirmation</option>
                            <option value="confirmed">Confirmed (Order Accepted)</option>
                            <option value="processing">Processing / In-Fabrication</option>
                            <option value="ready_for_delivery">Ready for Delivery / Staged</option>
                            <option value="delivered">Delivered to Site</option>
                            <option value="completed">Completed / Acknowledged</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Status Update Comment / Dispatch Notes
                        </label>
                        <input type="text" name="comment" placeholder="e.g. Fabrication completed, dispatching via truck 04" class="input-field" style="width: 100%;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary" style="font-size: 0.8rem; padding: 8px 18px;">
                        Update Order Status
                    </button>
                </div>
            </form>

            <!-- Status Transition History Audit Timeline -->
            <div>
                <h4 style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 10px;">
                    Status & Fulfillment Audit Log
                </h4>
                <div id="modalStatusLogs" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 10px; padding: 14px; max-height: 180px; overflow-y: auto;">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" onclick="closeModal('orderDetailsModal')" class="btn-secondary">Close Details</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openOrderModal(order) {
        document.getElementById('modalOrderCodeHeader').textContent = 'Order ID: ' + order.order_code;
        document.getElementById('modalClientProject').textContent = order.project ? order.project.title : 'St. Bilfrid Dev. Corp (Depot)';
        document.getElementById('modalRequestedDelivery').textContent = new Date(order.requested_delivery_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        document.getElementById('modalTotalAmount').textContent = 'PHP ' + Number(order.total_amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('modalDeliveryLocation').textContent = order.delivery_location || 'Not specified';
        
        if (order.notes && order.notes.trim() !== '') {
            document.getElementById('modalNotesContainer').style.display = 'block';
            document.getElementById('modalNotes').textContent = order.notes;
        } else {
            document.getElementById('modalNotesContainer').style.display = 'none';
        }

        // Set status dropdown
        document.getElementById('modalStatusSelect').value = order.status;
        document.getElementById('updateOrderStatusForm').action = '/supplier/orders/' + order.id + '/status';

        // Render Items Table
        const tbody = document.getElementById('modalItemsTbody');
        tbody.innerHTML = '';
        if (order.items && order.items.length > 0) {
            order.items.forEach(function(item) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${item.material_name}</strong></td>
                    <td style="text-align: right; font-family: var(--font-mono); font-weight: 700;">${item.quantity.toLocaleString()}</td>
                    <td>${item.unit}</td>
                    <td style="text-align: right; font-family: var(--font-mono);">PHP ${Number(item.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td style="text-align: right; font-family: var(--font-mono); font-weight: 700; color: #38bdf8;">PHP ${Number(item.total_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Render Audit Logs
        const logsDiv = document.getElementById('modalStatusLogs');
        logsDiv.innerHTML = '';
        if (order.logs && order.logs.length > 0) {
            order.logs.forEach(function(log) {
                const logItem = document.createElement('div');
                logItem.style.cssText = 'padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.8rem; display: flex; align-items: center; justify-content: space-between;';
                
                const timeStr = new Date(log.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                const userName = log.user ? log.user.name : 'System';
                
                logItem.innerHTML = `
                    <div>
                        <span style="font-weight: 700; color: #38bdf8;">${log.to_status.toUpperCase().replace(/_/g, ' ')}</span>: 
                        <span style="color: var(--text-primary);">${log.comment || 'Status modified.'}</span>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">Recorded by ${userName}</div>
                    </div>
                    <span style="font-size: 0.72rem; color: var(--text-muted); font-family: var(--font-mono);">${timeStr}</span>
                `;
                logsDiv.appendChild(logItem);
            });
        } else {
            logsDiv.innerHTML = '<div style="color: var(--text-muted); font-size: 0.8rem;">Initial order placement recorded.</div>';
        }

        openModal('orderDetailsModal');
    }
</script>
@endpush
