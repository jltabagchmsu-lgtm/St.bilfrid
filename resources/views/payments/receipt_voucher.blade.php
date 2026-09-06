<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Payment Receipt Voucher - {{ $payment->effective_or_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            line-height: 1.5;
            font-size: 13px;
            padding: 24px;
        }

        .receipt-container {
            max-width: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            padding: 36px 44px;
            position: relative;
        }

        .print-actions {
            max-width: 760px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print {
            background: #10b981;
            color: #ffffff;
            border: none;
            padding: 10px 22px;
            font-weight: 700;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-back {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 10px 18px;
            font-weight: 600;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #ef4444;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .brand h1 {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        .brand span {
            color: #ef4444;
        }

        .brand p {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .or-badge {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
        }

        .or-badge span {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .or-number {
            font-size: 16px;
            font-weight: 800;
            color: #ef4444;
        }

        .receipt-title {
            text-align: center;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 8px;
            border-radius: 4px;
            border: 1px dashed #cbd5e1;
        }

        .receipt-body {
            margin-bottom: 24px;
        }

        .row-item {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .row-label {
            font-weight: 600;
            color: #475569;
            width: 220px;
        }

        .row-value {
            font-weight: 600;
            color: #0f172a;
            flex: 1;
            text-align: right;
        }

        .amount-highlight-box {
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 6px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 24px 0;
        }

        .amount-figure {
            font-family: 'JetBrains Mono', monospace;
            font-size: 24px;
            font-weight: 800;
            color: #047857;
        }

        .stamp-box {
            border: 2px solid #10b981;
            color: #047857;
            padding: 6px 14px;
            font-weight: 800;
            font-size: 12px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            transform: rotate(-3deg);
        }

        .proof-attachment {
            margin: 18px 0;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
            padding-top: 20px;
        }

        .sig-box {
            text-align: center;
        }

        .sig-line {
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 8px;
            height: 45px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-actions {
                display: none;
            }
            .receipt-container {
                border: none;
                box-shadow: none;
                padding: 20px 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <a href="{{ route('payments.index', ['project_id' => $payment->project_id]) }}" class="btn-back" onclick="navigateBack(event)">&larr; Back to System</a>
        <button class="btn-print" onclick="window.print()">
            Print Official Receipt Voucher
        </button>
    </div>

    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <h1>St. Bilfrid <span>Development Corporation</span></h1>
                <p>Architectural Design & General Construction Contracting</p>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                    Taxpayer Identification: 402-998-120-000 &bull; Licensed PCAB Contractor
                </div>
            </div>
            <div class="or-badge">
                <span>Official Receipt No.</span>
                <div class="or-number">{{ $payment->effective_or_number }}</div>
                <div style="font-size: 11px; color: #64748b;">Ref: {{ $payment->invoice_no }}</div>
            </div>
        </div>

        <div class="receipt-title">
            Official Receipt & Billing Settlement Voucher
        </div>

        <div class="receipt-body">
            <div class="row-item">
                <span class="row-label">Date of Payment:</span>
                <span class="row-value" style="font-family: 'JetBrains Mono';">{{ $payment->payment_date->format('F d, Y') }}</span>
            </div>
            <div class="row-item">
                <span class="row-label">Client / Payer Name:</span>
                <span class="row-value">{{ $payment->payer_name ?? $payment->project->client_name }}</span>
            </div>
            <div class="row-item">
                <span class="row-label">Project Code & Title:</span>
                <span class="row-value"><strong>{{ $payment->project->project_code }}</strong> &bull; {{ $payment->project->title }}</span>
            </div>
            <div class="row-item">
                <span class="row-label">Site Address / Location:</span>
                <span class="row-value">{{ $payment->project->location ?? 'Main Construction Site' }}</span>
            </div>
            <div class="row-item">
                <span class="row-label">Billing Milestone / Stage:</span>
                <span class="row-value" style="font-weight: 800; color: #0f172a;">{{ $payment->payment_stage }}</span>
            </div>
            <div class="row-item">
                <span class="row-label">Payment Method:</span>
                <span class="row-value">{{ $payment->payment_method }}@if($payment->bank_reference) &bull; Ref/Check: {{ $payment->bank_reference }}@endif</span>
            </div>
            @if($payment->notes)
            <div class="row-item">
                <span class="row-label">Financial Remarks:</span>
                <span class="row-value" style="color: #475569; font-weight: 500;">{{ $payment->notes }}</span>
            </div>
            @endif
        </div>

        <!-- Amount Box -->
        <div class="amount-highlight-box">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #065f46; text-transform: uppercase;">Total Amount Paid to Firm</div>
                <div class="amount-figure">₱{{ number_format($payment->amount, 2) }}</div>
            </div>
            <div class="stamp-box">
                {{ strtoupper($payment->status) === 'PAID' ? 'OFFICIALLY CLEARED' : strtoupper($payment->status) }}
            </div>
        </div>

        @if($payment->receipt_file)
        <div class="proof-attachment">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">
                Attached Proof of Settlement:
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 0.85rem; font-weight: bold; color: var(--text-muted, #64748b);">[FILE]</span>
                <div>
                    <div style="font-weight: 700; font-size: 12px; color: #0f172a;">{{ $payment->receipt_file }}</div>
                    <a href="{{ $payment->receipt_url }}" target="_blank" style="font-size: 11px; color: #ef4444; font-weight: 600; text-decoration: none;">Click to view original proof file &rarr;</a>
                </div>
            </div>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signature-section">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div style="font-size: 12px; font-weight: 700; color: #0f172a;">{{ $payment->payer_name ?? $payment->project->client_name }}</div>
                <div style="font-size: 11px; color: #64748b;">Client Authorized Signatory</div>
            </div>

            <div class="sig-box">
                <div class="sig-line"></div>
                <div style="font-size: 12px; font-weight: 700; color: #0f172a;">{{ $payment->received_by ?? 'Engr. Sophia Martinez, PMP' }}</div>
                <div style="font-size: 11px; color: #64748b;">Authorized Financial Comptroller &bull; St. Bilfrid Development Corporation</div>
            </div>
        </div>
    </div>

    <script>
        function navigateBack(e) {
            // Check if opened from within the application history
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                e.preventDefault();
                window.history.back();
                return;
            }
            
            // If opened in a popup/child window
            if (window.opener && !window.opener.closed) {
                e.preventDefault();
                window.close();
                return;
            }
            
            // Otherwise, let the fallback href execute normally
        }
    </script>
</body>
</html>
