@extends('supplier.layout')

@section('title', 'Material Inquiries & Quotations - ' . $supplier->name)
@section('page_title', 'Material Inquiries & RFQs')
@section('page_subtitle', 'Review incoming material inquiries and request for quotations from St. Bilfrid Dev. Corp, and submit custom pricing responses.')

@section('content')

<!-- Inquiries Table -->
<div style="overflow-x: auto; margin-bottom: 24px;">
    <table class="grid-table">
        <thead>
            <tr>
                <th style="white-space: nowrap; min-width: 140px;">Product / Material</th>
                <th style="min-width: 180px;">Subject & Inquiry</th>
                <th style="white-space: nowrap; min-width: 100px;">Req. Quantity</th>
                <th style="white-space: nowrap; min-width: 110px;">Date Received</th>
                <th style="white-space: nowrap; min-width: 120px;">Status</th>
                <th style="white-space: nowrap; min-width: 130px;">Quoted Price (PHP)</th>
                <th style="white-space: nowrap; min-width: 110px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inquiries as $inq)
                @php 
                    $badgeBg = match($inq->status) {
                        'open' => 'rgba(234, 179, 8, 0.15)',
                        'quoted' => 'rgba(16, 185, 129, 0.15)',
                        'closed' => 'rgba(100, 116, 139, 0.15)',
                        default => 'rgba(255, 255, 255, 0.05)',
                    };
                    $badgeColor = match($inq->status) {
                        'open' => '#eab308',
                        'quoted' => '#10b981',
                        'closed' => '#94a3b8',
                        default => '#ffffff',
                    };
                    $badgeBorder = match($inq->status) {
                        'open' => 'rgba(234, 179, 8, 0.35)',
                        'quoted' => 'rgba(16, 185, 129, 0.35)',
                        'closed' => 'rgba(100, 116, 139, 0.35)',
                        default => 'rgba(255, 255, 255, 0.1)',
                    };
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem;">
                            {{ $inq->material ? $inq->material->name : 'General Inquiry' }}
                        </div>
                        @if($inq->material)
                            <div style="font-size: 0.72rem; color: #38bdf8; font-family: var(--font-mono); margin-top: 2px;">
                                {{ $inq->material->material_code }} (Catalog: PHP {{ number_format($inq->material->unit_price, 2) }}/{{ $inq->material->unit }})
                            </div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.85rem;">
                            {{ $inq->subject }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $inq->message }}
                        </div>
                    </td>
                    <td style="white-space: nowrap;">
                        <span style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--text-primary);">
                            {{ $inq->requested_quantity ? number_format($inq->requested_quantity) : 'N/A' }}
                        </span>
                        @if($inq->material)
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $inq->material->unit }}</span>
                        @endif
                    </td>
                    <td style="white-space: nowrap;">
                        <span style="font-size: 0.8rem; font-family: var(--font-mono); color: var(--text-secondary);">
                            {{ $inq->created_at->format('M d, Y') }}
                        </span>
                    </td>
                    <td style="white-space: nowrap;">
                        <span class="pill-badge" style="background: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeBorder }};">
                            {{ ucfirst($inq->status) }}
                        </span>
                    </td>
                    <td style="white-space: nowrap;">
                        @if($inq->quoted_unit_price)
                            <strong style="font-family: var(--font-mono); color: #10b981; font-size: 0.9rem;">
                                PHP {{ number_format($inq->quoted_unit_price, 2) }}
                            </strong>
                        @else
                            <span style="color: var(--text-muted); font-size: 0.8rem;">Pending Quote</span>
                        @endif
                    </td>
                    <td style="white-space: nowrap; text-align: center;">
                        <button type="button" onclick="openRespondModal({{ json_encode($inq->load(['material', 'user'])) }})" class="btn-primary" style="padding: 6px 12px; font-size: 0.75rem;">
                            {{ $inq->status === 'open' ? 'Respond / Quote' : 'View Details' }}
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 48px; color: var(--text-muted);">
                        No inquiries or quotation requests received yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination Links -->
<div style="display: flex; justify-content: flex-end;">
    {{ $inquiries->links() }}
</div>

<!-- Inquiry Response / Quotation Modal -->
<div class="modal-backdrop" id="respondInquiryModal">
    <div class="modal-box" style="max-width: 640px;">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary);">Material Inquiry & RFQ Response</h3>
                <p id="inqSubjectHeader" style="font-size: 0.8rem; color: #38bdf8; margin-top: 2px;"></p>
            </div>
            <button type="button" onclick="closeModal('respondInquiryModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
        </div>

        <form id="respondInquiryForm" method="POST">
            @csrf
            <div class="modal-body">
                <!-- Inquiry Details Overview -->
                <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Target Material</div>
                            <div id="inqMaterialName" style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-top: 2px;"></div>
                        </div>
                        <div>
                            <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Requested Quantity</div>
                            <div id="inqQuantity" style="font-size: 0.85rem; font-weight: 700; color: #38bdf8; font-family: var(--font-mono); margin-top: 2px;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Inquiry from St. Bilfrid Admin</div>
                        <div id="inqMessageText" style="font-size: 0.85rem; color: #cbd5e1; margin-top: 4px; background: rgba(0,0,0,0.25); padding: 10px 12px; border-radius: 6px; line-height: 1.4;"></div>
                    </div>
                </div>

                <!-- Response Fields -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Quoted Unit Price (PHP) <span style="font-size: 0.72rem; color: var(--text-muted);">(Leave blank if offering standard catalog rate)</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="quoted_unit_price" id="inqQuotedPriceInput" placeholder="0.00" class="input-field" style="width: 100%; font-family: var(--font-mono); font-size: 1rem;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Supplier Quotation Response & Lead Time Notes <span style="color: var(--primary-red);">*</span>
                    </label>
                    <textarea name="supplier_response" id="inqResponseTextarea" rows="4" required placeholder="Detail unit pricing discounts, lead times, batch availability, or technical clarifications..." class="input-field" style="width: 100%; resize: vertical;"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" onclick="closeModal('respondInquiryModal')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Send Response to Admin</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openRespondModal(inquiry) {
        document.getElementById('inqSubjectHeader').textContent = inquiry.subject;
        document.getElementById('inqMaterialName').textContent = inquiry.material ? inquiry.material.name : 'General Material Request';
        document.getElementById('inqQuantity').textContent = (inquiry.requested_quantity ? Number(inquiry.requested_quantity).toLocaleString() : 'Not specified') + (inquiry.material ? ' ' + inquiry.material.unit : '');
        document.getElementById('inqMessageText').textContent = inquiry.message;
        
        document.getElementById('inqQuotedPriceInput').value = inquiry.quoted_unit_price || '';
        document.getElementById('inqResponseTextarea').value = inquiry.supplier_response || '';

        document.getElementById('respondInquiryForm').action = '/supplier/inquiries/' + inquiry.id + '/respond';

        openModal('respondInquiryModal');
    }
</script>
@endpush
