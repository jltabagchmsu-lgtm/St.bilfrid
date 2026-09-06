<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Material Transfer Voucher - {{ $transfer->transfer_reference_no }}</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: var(--font-sans);
            display: flex;
            justify-content: center;
            padding: 30px 15px;
            margin: 0;
        }

        .voucher-sheet {
            width: 100%;
            max-width: 820px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 45px 50px;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            position: relative;
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 18px;
            margin-bottom: 24px;
        }

        .firm-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .firm-logo {
            width: 48px;
            height: 48px;
            background: #ef4444;
            border-radius: 8px;
            display: grid;
            place-items: center;
        }

        .firm-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .firm-title span {
            color: #ef4444;
        }

        .firm-sub {
            font-size: 0.775rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .voucher-badge-box {
            text-align: right;
        }

        .voucher-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .voucher-ref-no {
            font-family: var(--font-mono);
            font-size: 0.95rem;
            font-weight: 700;
            color: #ef4444;
            margin-top: 3px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .meta-label {
            font-size: 0.725rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
        }

        .meta-val {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .item-table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 14px;
            text-align: left;
        }

        .item-table td {
            border: 1px solid #e2e8f0;
            padding: 12px 14px;
            font-size: 0.85rem;
            color: #1e293b;
        }

        .item-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }

        .sig-block {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 90px;
        }

        .sig-line {
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 6px;
        }

        .sig-name {
            font-size: 0.825rem;
            font-weight: 800;
            color: #0f172a;
        }

        .sig-role {
            font-size: 0.725rem;
            color: #64748b;
        }

        .no-print-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .voucher-sheet {
                box-shadow: none;
                border: none;
                padding: 20px 0;
                max-width: 100%;
            }
            .no-print-toolbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <button onclick="window.print()" style="background: #0f172a; color: #ffffff; border: none; padding: 10px 18px; border-radius: 6px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <span>Print Official Slip</span>
        </button>
        <button onclick="window.close()" style="background: #e2e8f0; color: #0f172a; border: 1px solid #cbd5e1; padding: 10px 16px; border-radius: 6px; font-weight: 600; cursor: pointer;">
            Close
        </button>
    </div>

    <div class="voucher-sheet">
        
        <!-- Header -->
        <div class="voucher-header">
            <div class="firm-brand">
                <div class="firm-logo">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4v18"></path>
                        <path d="M19 21V11l-6-3"></path>
                        <path d="M9 9h1"></path>
                        <path d="M9 13h1"></path>
                    </svg>
                </div>
                <div>
                    <div class="firm-title">St. Bilfrid <span>Development Corporation</span></div>
                    <div class="firm-sub">{{ $departmentName ?? 'Materials Logistics & Supply Division' }}</div>
                </div>
            </div>
            <div class="voucher-badge-box">
                <div class="voucher-title">Material Transfer Slip</div>
                <div class="voucher-ref-no">{{ $transfer->transfer_reference_no }}</div>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                    Date: <strong>{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('F d, Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Transfer Type</span>
                <span class="meta-val">
                    @if($transfer->transfer_type === 'warehouse_dispatch')
                        Warehouse Dispatch to Project Site
                    @elseif($transfer->transfer_type === 'inter_project')
                        Inter-Project Surplus Relocation
                    @else
                        Site Excess Return to Central Stock
                    @endif
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Authorized Dispatched By</span>
                <span class="meta-val">{{ $transfer->authorized_by ?? $departmentOfficer ?? 'Transfer Officer' }}</span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Origin / Source Facility</span>
                <span class="meta-val">
                    @if($transfer->transfer_type === 'warehouse_dispatch')
                        Central Warehouse Logistics Hub (Negros Central)
                    @else
                        {{ $transfer->sourceProject->project_code ?? 'PRJ' }} - {{ $transfer->sourceProject->title ?? 'Site' }}
                    @endif
                </span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Receiving Destination / Project Site</span>
                <span class="meta-val">
                    @if($transfer->transfer_type === 'warehouse_stock')
                        Central Warehouse Storage Catalog
                    @else
                        {{ $transfer->destinationProject->project_code ?? 'PRJ' }} - {{ $transfer->destinationProject->title ?? 'Site' }}
                        <div style="font-size: 0.75rem; color: #64748b; font-weight: normal;">
                            Location: {{ $transfer->destinationProject->location ?? 'Main Build Site' }}
                        </div>
                    @endif
                </span>
            </div>
        </div>

        <!-- Transfer Itemized Manifest -->
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">Item</th>
                    <th style="width: 120px;">Material Code</th>
                    <th>Material Description & Specifications</th>
                    <th style="width: 80px; text-align: center;">Unit</th>
                    <th style="width: 100px; text-align: right;">Quantity</th>
                    <th style="width: 110px; text-align: right;">Unit Rate</th>
                    <th style="width: 120px; text-align: right;">Total Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; font-weight: 700;">1</td>
                    <td style="font-family: var(--font-mono); font-weight: 700; color: #ef4444;">
                        {{ $transfer->material->material_code ?? 'MAT-ITEM' }}
                    </td>
                    <td>
                        <strong>{{ $transfer->material->name ?? 'Material Item' }}</strong>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                            Category: {{ $transfer->material->category ?? 'General' }}
                        </div>
                    </td>
                    <td style="text-align: center;">{{ $transfer->material->unit ?? 'units' }}</td>
                    <td style="text-align: right; font-weight: 800; font-size: 0.95rem;">
                        {{ number_format($transfer->quantity_transferred) }}
                    </td>
                    <td style="text-align: right; font-family: var(--font-mono);">
                        ₱{{ number_format($transfer->material->unit_cost ?? 0, 2) }}
                    </td>
                    <td style="text-align: right; font-family: var(--font-mono); font-weight: 800;">
                        ₱{{ number_format(($transfer->quantity_transferred) * ($transfer->material->unit_cost ?? 0), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Engineering Reason & Notes -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 24px; font-size: 0.825rem;">
            <strong style="color: #0f172a;">Purpose of Transfer:</strong>
            <span style="color: #475569; margin-left: 6px;">{{ $transfer->reason ?? 'Materials dispatched for scheduled trade installation.' }}</span>
        </div>

        <!-- 3 Signature Verification Blocks -->
        <div class="signatures-grid">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $transfer->authorized_by ?? 'Materials Officer' }}</div>
                <div class="sig-role">Dispatched By (Department Officer)</div>
            </div>

            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-name">Engr. Esabyl B. Mitra / Site Engineer</div>
                <div class="sig-role">Received & Verified On-Site</div>
            </div>

            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-name">Engr. Sophia Martinez, PMP</div>
                <div class="sig-role">Audited (Project Director & Controller)</div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center; font-size: 0.7rem; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px;">
            Document generated electronically via St. Bilfrid Development Corporation Enterprise Monitoring System &bull; Voucher ID: {{ $transfer->transfer_reference_no }} &bull; Timestamp: {{ now()->format('Y-m-d H:i:s') }}
        </div>

    </div>

</body>
</html>
