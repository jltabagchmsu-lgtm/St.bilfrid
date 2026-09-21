@extends('layouts.app')

@section('title', 'Financial Payments Ledger & Billing - St. Bilfrid Development Corporation')
@section('page_title', 'Financial Payments & Milestone Billing Ledger')

@section('top_actions')
    <button class="btn-primary" onclick="openModal('addPaymentModal')">
        <span>+</span> Record Payment & Issue OR
    </button>
@endsection

@section('content')

<!-- Financial Summary KPIs in Philippine Peso -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Settled Receipts</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981; border-color: rgba(16, 185, 129, 0.4);">CLEARED</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981; font-weight: 800; font-size: 1.6rem;">₱{{ number_format($totalPaid, 2) }}</div>
        <div class="kpi-sub">Total Cleared & Paid to Firm</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Total Invoiced Billing</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #059669; border-color: rgba(16, 185, 129, 0.4);">GROSS BILLINGS</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #059669; font-weight: 800; font-size: 1.6rem;">₱{{ number_format($totalInvoiced, 2) }}</div>
        <div class="kpi-sub">Total Billed Invoices</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Pending Invoices</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #f59e0b; border-color: rgba(245, 158, 11, 0.4);">AWAITING CLEARANCE</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #f59e0b; font-weight: 800; font-size: 1.6rem;">₱{{ number_format($totalPending, 2) }}</div>
        <div class="kpi-sub">Unsettled Milestone Invoices</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Overdue Receivables</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #ef4444; border-color: rgba(239, 68, 68, 0.4);">OVERDUE</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #ef4444; font-weight: 800; font-size: 1.6rem;">₱{{ number_format($totalOverdue, 2) }}</div>
        <div class="kpi-sub">Past Due Invoices</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-header">
            <span class="kpi-title">Collection Rate</span>
            <span class="spec-chip" style="font-size: 0.65rem; color: #10b981; border-color: rgba(16, 185, 129, 0.4);">EFFICIENCY</span>
        </div>
        <div class="kpi-val" style="font-family: var(--font-mono); color: #10b981; font-weight: 800; font-size: 1.6rem;">{{ $collectionRate }}%</div>
        <div class="kpi-sub">Paid vs Gross Billed</div>
    </div>
</div>

<!-- Search & Project Filter Bar -->
<div class="glass-panel" style="padding: 18px 24px; margin-bottom: 24px;">
    <form action="{{ route('payments.index') }}" method="GET" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
            <label style="font-weight: 700; color: var(--text-secondary); font-size: 0.85rem; white-space: nowrap;">Project:</label>
            <select name="project_id" class="form-select" style="max-width: 320px;" onchange="this.form.submit()">
                <option value="">-- All Projects --</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ ($selectedProjectId ?? '') == $p->id ? 'selected' : '' }}>
                        {{ $p->project_code }} - {{ $p->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Payment Method Filter -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">Method:</span>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'status' => $selectedStatus ?? '']) }}" class="spec-chip {{ empty($selectedMethod) ? 'spec-chip-active' : '' }}" style="text-decoration: none;">All</a>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'status' => $selectedStatus ?? '', 'payment_method' => 'Bank Transfer']) }}" class="spec-chip {{ ($selectedMethod ?? '') == 'Bank Transfer' ? 'spec-chip-active' : '' }}" style="text-decoration: none;">Bank Transfer</a>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'status' => $selectedStatus ?? '', 'payment_method' => 'Cheque']) }}" class="spec-chip {{ ($selectedMethod ?? '') == 'Cheque' ? 'spec-chip-active' : '' }}" style="text-decoration: none;">Cheque</a>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'status' => $selectedStatus ?? '', 'payment_method' => 'Cash']) }}" class="spec-chip {{ ($selectedMethod ?? '') == 'Cash' ? 'spec-chip-active' : '' }}" style="text-decoration: none;">Cash</a>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'status' => $selectedStatus ?? '', 'payment_method' => 'Online Banking']) }}" class="spec-chip {{ ($selectedMethod ?? '') == 'Online Banking' ? 'spec-chip-active' : '' }}" style="text-decoration: none;">Online</a>
        </div>

        <!-- Status Filter -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">Status:</span>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'payment_method' => $selectedMethod ?? '']) }}" class="spec-chip {{ empty($selectedStatus) ? 'spec-chip-active' : '' }}" style="text-decoration: none;">All</a>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'payment_method' => $selectedMethod ?? '', 'status' => 'paid']) }}" class="spec-chip {{ ($selectedStatus ?? '') == 'paid' ? 'spec-chip-active' : '' }}" style="text-decoration: none;">Paid</a>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'payment_method' => $selectedMethod ?? '', 'status' => 'pending']) }}" class="spec-chip {{ ($selectedStatus ?? '') == 'pending' ? 'spec-chip-active' : '' }}" style="text-decoration: none;">Pending</a>
            <a href="{{ route('payments.index', ['project_id' => $selectedProjectId ?? '', 'payment_method' => $selectedMethod ?? '', 'status' => 'overdue']) }}" class="spec-chip {{ ($selectedStatus ?? '') == 'overdue' ? 'spec-chip-active' : '' }}" style="text-decoration: none;">Overdue</a>
        </div>
    </form>
</div>

<!-- Internal Payments Ledger Matrix -->
<div class="glass-panel">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Official Financial Payments & Billing Ledger</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Official Receipt (OR) generation, milestone progress billings, settlement tracking, and payment verification</span>
        </div>
        <button class="btn-primary" style="font-size: 0.825rem; padding: 6px 14px;" onclick="openModal('addPaymentModal')">+ Record Payment & OR</button>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Official Receipt (OR)</th>
                    <th>Project & Client</th>
                    <th>Billing Milestone / Stage</th>
                    <th>Payment Date & Method</th>
                    <th>Amount Paid (₱)</th>
                    <th>Remaining Balance After Payment</th>
                    <th>Reference / Check No.</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pay)
                @php
                    $remAfter = $pay->remaining_balance_after_payment;
                @endphp
                <tr>
                    <td>
                        <strong style="font-family: var(--font-mono); color: #ef4444; font-size: 0.95rem;">{{ $pay->effective_or_number }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.725rem; color: var(--text-muted);">Invoice: {{ $pay->invoice_no }}</div>
                    </td>
                    <td>
                        <a href="{{ route('projects.show', $pay->project->id) }}" style="text-decoration: none; color: inherit;">
                            <strong style="color: var(--text-primary); font-size: 0.875rem;">{{ $pay->project->title }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                Client: <strong>{{ $pay->payer_name ?? $pay->project->client_name }}</strong> &bull; <span style="font-family: var(--font-mono); color: #ef4444;">{{ $pay->project->project_code }}</span>
                            </div>
                        </a>
                    </td>
                    <td>
                        <span style="font-weight: 700; color: var(--text-primary);">{{ $pay->payment_stage }}</span>
                    </td>
                    <td>
                        <div style="font-family: var(--font-mono); font-size: 0.85rem;">{{ $pay->payment_date->format('M d, Y') }}</div>
                        <span class="spec-chip" style="font-size: 0.7rem; margin-top: 3px;">
                            {{ $pay->payment_method }}
                        </span>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); font-size: 1.15rem; font-weight: 800; color: {{ $pay->status === 'paid' ? '#059669' : '#d97706' }}; letter-spacing: -0.015em;">
                            ₱{{ number_format($pay->amount, 2) }}
                        </strong>
                    </td>
                    <td style="font-family: var(--font-mono);">
                        @if($remAfter <= 0)
                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.75rem; font-weight: 800; color: #047857; background: #dcfce7; padding: 3px 8px; border-radius: 4px; border: 1px solid #86efac;">
                                ✓ ₱0.00 Fully Settled
                            </span>
                        @else
                            <div style="font-size: 0.95rem; font-weight: 800; color: #b45309;">
                                ₱{{ number_format($remAfter, 2) }}
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b;">
                                {{ round(($pay->cumulative_paid_up_to_this / max(1, $pay->project->contract_budget)) * 100, 1) }}% total settled
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($pay->bank_reference)
                            <span style="font-family: var(--font-mono); font-size: 0.8rem; color: #38bdf8;">{{ $pay->bank_reference }}</span>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted);">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $pay->status }}">{{ strtoupper($pay->status) }}</span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 6px; align-items: center;">
                            <a href="{{ route('payments.printReceipt', $pay->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; color: #10b981; border-color: rgba(16, 185, 129, 0.4);" title="Print Official Receipt Voucher">
                                Print OR
                            </a>
                            @if($pay->status !== 'paid')
                                <form action="{{ route('payments.updateStatus', $pay->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="btn-primary" style="font-size: 0.75rem; padding: 4px 8px; background: #10b981; border-color: #10b981;">
                                        Mark Paid
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('payments.destroy', $pay->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Permanently remove this payment receipt record ({{ addslashes($pay->effective_or_number) }})?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 6px; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" title="Delete Payment Record">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 28px;">
                        No financial payments recorded yet. Click "+ Record Payment & OR" to create a new client billing receipt.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Record Official Payment & Generate OR -->
<div class="modal-overlay" id="addPaymentModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700; margin: 0;">+ Record Client Payment & Issue Official Receipt (OR)</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Generate official payment voucher, record milestone settlement, and issue printable invoice</span>
            </div>
            <button onclick="closeModal('addPaymentModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label">Target Construction Project <span style="color:#ef4444;">*</span></label>
                <select name="project_id" class="form-select" required id="selectPaymentProj" onchange="updateProjectClientName(this)">
                    <option value="">-- Choose Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" 
                                data-client="{{ $p->client_name }}"
                                data-phase="{{ $p->current_phase }}"
                                data-contract="{{ (float) $p->contract_budget }}"
                                data-paid="{{ (float) $p->total_paid_sales }}"
                                data-balance="{{ (float) $p->uncollected_balance }}"
                                {{ ($selectedProjectId ?? '') == $p->id ? 'selected' : '' }}>
                            {{ $p->project_code }} - {{ $p->title }} ({{ $p->client_name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Client Account & Remaining Balance Dynamic Card -->
            <div id="genModalProjectInfoCard" style="display: none; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px;">
                    <span style="font-size: 0.775rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.04em;">Client Account & Contract Breakdown</span>
                    <span id="genModalClientBadge" style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: 700;"></span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <div style="font-size: 0.725rem; color: #64748b;">Total Contract Value:</div>
                        <div id="genModalContractVal" style="font-size: 0.95rem; font-weight: 800; color: #0f172a; font-family: var(--font-mono);">₱0.00</div>
                    </div>
                    <div>
                        <div style="font-size: 0.725rem; color: #64748b;">Total Already Settled:</div>
                        <div id="genModalPaidVal" style="font-size: 0.95rem; font-weight: 800; color: #059669; font-family: var(--font-mono);">₱0.00</div>
                    </div>
                    <div>
                        <div style="font-size: 0.725rem; color: #64748b;">Current Unpaid Balance:</div>
                        <div id="genModalBalanceVal" style="font-size: 0.95rem; font-weight: 800; color: #d97706; font-family: var(--font-mono);">₱0.00</div>
                    </div>
                </div>

                <!-- Dynamic Live Calculation Card -->
                <div id="genModalLiveBalanceCard" style="background: #ffffff; border: 1.5px solid #0284c7; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase;">Remaining Balance After This Payment:</div>
                        <div id="genModalLiveRemainingVal" style="font-size: 1.3rem; font-weight: 900; color: #0284c7; font-family: var(--font-mono);">
                            ₱0.00
                        </div>
                    </div>
                    <div id="genModalLiveStatusBadge" style="font-size: 0.775rem; font-weight: 700; color: #0369a1; background: #f0f9ff; padding: 4px 10px; border-radius: 6px; border: 1px solid #bae6fd;">
                        Type payment amount below
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Official Receipt (OR) Number</label>
                    <input type="text" name="official_receipt_no" class="form-input" placeholder="e.g. OR-{{ date('Ym') }}-{{ rand(1000, 9999) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" name="invoice_no" class="form-input" placeholder="e.g. INV-{{ date('Ym') }}-{{ rand(1000, 9999) }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Client / Payer Entity Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="payer_name" id="inputPayerName" class="form-input" placeholder="e.g. Apex Health Systems Inc." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Amount Given by Client (₱) <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount" id="inputGenPaymentAmount" class="form-input" placeholder="₱ 0.00" oninput="calcGeneralPaymentRemainingBalance()" required style="font-weight: 800; font-size: 1.05rem; color: #0f172a;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Payment Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="payment_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Milestone / Billing Stage <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="payment_stage" id="inputPaymentStage" class="form-input" placeholder="e.g. Downpayment (20%), 30% Structural Frame..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method <span style="color:#ef4444;">*</span></label>
                    <select name="payment_method" class="form-select" required>
                        <option value="Bank Transfer">Bank Direct Wire / Transfer</option>
                        <option value="Cheque">Cheque / Manager's Check</option>
                        <option value="Cash">Cash Settlement</option>
                        <option value="Online Banking">Online Banking (GCash / Maya / Instapay)</option>
                        <option value="Credit / Debit Card">Credit / Debit Card</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Bank Reference / Check / Transaction Number</label>
                    <input type="text" name="bank_reference" class="form-input" placeholder="e.g. BDO-REF-99214 / CHK-002194">
                </div>

                <div class="form-group">
                    <label class="form-label">Received By (Authorized Comptroller)</label>
                    <input type="text" name="received_by" class="form-input" value="Engr. Sophia Martinez, PMP">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Payment Status <span style="color:#ef4444;">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="paid" selected>Paid / Cleared & Received</option>
                        <option value="pending">Pending Settlement / Verification</option>
                        <option value="overdue">Overdue / Delayed Payment</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Attach Proof of Payment (Voucher / Slip)</label>
                    <input type="file" name="receipt_file" class="form-input" accept="image/*,application/pdf">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Financial Notes & Settlement Remarks</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="Official settlement terms, check clearing notes, remarks..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addPaymentModal')">Cancel</button>
                <button type="submit" class="btn-primary">Record Payment & Generate OR</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openModal(id) { 
        const el = document.getElementById(id);
        if (el) el.classList.add('active'); 
    }
    function closeModal(id) { 
        const el = document.getElementById(id);
        if (el) el.classList.remove('active'); 
    }

    function updateProjectClientName(select) {
        const option = select.options[select.selectedIndex];
        const card = document.getElementById('genModalProjectInfoCard');
        if (option && option.value) {
            document.getElementById('inputPayerName').value = option.dataset.client || '';
            if (option.dataset.phase) {
                document.getElementById('inputPaymentStage').value = option.dataset.phase;
            }
            if (card) {
                card.style.display = 'block';
                document.getElementById('genModalClientBadge').innerText = option.dataset.client || '';
                
                const contract = parseFloat(option.dataset.contract) || 0;
                const paid = parseFloat(option.dataset.paid) || 0;
                const balance = parseFloat(option.dataset.balance) || 0;

                document.getElementById('genModalContractVal').innerText = '₱' + contract.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('genModalPaidVal').innerText = '₱' + paid.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('genModalBalanceVal').innerText = '₱' + balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                calcGeneralPaymentRemainingBalance();
            }
        } else {
            if (card) card.style.display = 'none';
        }
    }

    function calcGeneralPaymentRemainingBalance() {
        const select = document.getElementById('selectPaymentProj');
        if (!select || !select.value) return;
        const option = select.options[select.selectedIndex];
        if (!option) return;

        const contract = parseFloat(option.dataset.contract) || 0;
        const paid = parseFloat(option.dataset.paid) || 0;
        const balance = parseFloat(option.dataset.balance) || 0;

        const amountInput = document.getElementById('inputGenPaymentAmount');
        const amountVal = parseFloat(amountInput ? amountInput.value : 0) || 0;

        const remainingAfter = Math.max(0, balance - amountVal);
        const liveRemainingVal = document.getElementById('genModalLiveRemainingVal');
        const liveCard = document.getElementById('genModalLiveBalanceCard');
        const liveBadge = document.getElementById('genModalLiveStatusBadge');

        if (!liveRemainingVal) return;

        liveRemainingVal.innerText = '₱' + remainingAfter.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (amountVal <= 0) {
            liveCard.style.borderColor = '#0284c7';
            liveRemainingVal.style.color = '#0284c7';
            liveBadge.innerHTML = 'Type payment amount below';
            liveBadge.style.color = '#0369a1';
            liveBadge.style.background = '#f0f9ff';
            liveBadge.style.borderColor = '#bae6fd';
        } else if (amountVal >= balance && balance > 0) {
            liveCard.style.borderColor = '#059669';
            liveRemainingVal.style.color = '#059669';
            const overpaid = amountVal - balance;
            if (overpaid > 0) {
                liveBadge.innerHTML = '✓ Fully Paid (Advance Credit: ₱' + overpaid.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ')';
            } else {
                liveBadge.innerHTML = '✓ 100% Fully Settled & Cleared';
            }
            liveBadge.style.color = '#047857';
            liveBadge.style.background = '#dcfce7';
            liveBadge.style.borderColor = '#86efac';
        } else {
            liveCard.style.borderColor = '#d97706';
            liveRemainingVal.style.color = '#d97706';
            const newTotalPaid = paid + amountVal;
            const pct = contract > 0 ? Math.min(100, Math.round((newTotalPaid / contract) * 100)) : 0;
            liveBadge.innerHTML = 'Partial Payment (' + pct + '% Settled)';
            liveBadge.style.color = '#b45309';
            liveBadge.style.background = '#fef3c7';
            liveBadge.style.borderColor = '#fde68a';
        }
    }

    // Auto trigger on page load if project preselected
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('selectPaymentProj');
        if (select && select.value) {
            updateProjectClientName(select);
        }
    });
</script>
@endsection
