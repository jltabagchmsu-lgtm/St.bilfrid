<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill of Materials and Cost Estimates - {{ $project->project_code }}</title>
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
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background-color: #f1f5f9;
            color: #000000;
            line-height: 1.35;
            font-size: 11.5px;
            padding: 24px;
        }

        .print-actions {
            max-width: 820px;
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

        .bom-page {
            max-width: 820px;
            margin: 0 auto 24px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            padding: 36px 44px;
            min-height: 1050px;
            position: relative;
        }

        .doc-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title {
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
        }

        .project-meta {
            margin-bottom: 18px;
            font-size: 11.5px;
            line-height: 1.5;
        }

        .meta-line {
            margin-bottom: 2px;
        }

        .scope-item-block {
            margin-bottom: 24px;
            page-break-inside: avoid;
        }

        .scope-item-header {
            font-weight: 800;
            font-size: 12px;
            text-transform: uppercase;
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            border-bottom: 1px solid #000000;
            padding-bottom: 3px;
        }

        .section-title {
            font-weight: 700;
            font-style: italic;
            margin: 6px 0 3px 0;
            font-size: 11px;
        }

        .line-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .line-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .col-qty {
            width: 45px;
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
        }

        .col-unit {
            width: 55px;
            padding-left: 6px !important;
        }

        .col-desc {
            padding-left: 6px !important;
        }

        .col-at {
            width: 25px;
            text-align: center;
        }

        .col-rate {
            width: 75px;
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
        }

        .col-p {
            width: 20px;
            text-align: right;
            font-weight: 700;
        }

        .col-total {
            width: 90px;
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }

        .subtotal-row {
            border-top: 1px solid #000000;
            font-weight: 700;
        }

        .cost-summary-box {
            margin-top: 8px;
            padding-left: 120px;
            font-size: 11px;
        }

        .cost-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            max-width: 320px;
            margin-left: auto;
        }

        .total-item-cost-row {
            display: flex;
            justify-content: space-between;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            padding: 3px 0;
            margin-top: 4px;
            font-weight: 800;
            font-size: 11.5px;
            max-width: 320px;
            margin-left: auto;
        }

        .page-footer-num {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            font-style: italic;
            color: #64748b;
        }

        .signature-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            padding-top: 20px;
        }

        .sig-block {
            width: 320px;
            font-size: 11px;
        }

        .sig-name {
            font-weight: 800;
            font-size: 12px;
            margin-top: 36px;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .sig-title {
            font-size: 10.5px;
            color: #334155;
            font-weight: 600;
        }

        .sig-info {
            font-size: 10px;
            color: #475569;
            margin-top: 2px;
            line-height: 1.4;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-actions {
                display: none !important;
            }
            .bom-page {
                border: none;
                box-shadow: none;
                max-width: 100%;
                margin: 0;
                padding: 20mm 20mm;
                page-break-after: always;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <a href="{{ route('projects.show', $project->id) }}" class="btn-back" onclick="navigateBack(event)">&larr; Back to Project Tracker</a>
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Bill of Materials (DUPA)
        </button>
    </div>

    <script>
        function navigateBack(e) {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                e.preventDefault();
                window.history.back();
                return;
            }
            if (window.opener && !window.opener.closed) {
                e.preventDefault();
                window.close();
                return;
            }
        }
    </script>

    <!-- Chunk Scope Items into Printable Pages (2-3 Scope Items per Page) -->
    @php
        $itemsList = $scopeItems->values();
        $chunks = $itemsList->chunk(2);
        $totalPages = $chunks->count() + 1;
        $pageCounter = 1;
    @endphp

    @foreach($chunks as $chunk)
    <div class="bom-page">
        <!-- Page Header -->
        @if($pageCounter === 1)
        <div class="doc-header">
            <h1 class="doc-title">BILL OF MATERIALS AND COST ESTIMATES</h1>
        </div>

        <div class="project-meta">
            <div class="meta-line"><strong>Project Title:</strong> {{ $project->title }}</div>
            <div class="meta-line"><strong>Project Location:</strong> {{ $project->location ?? 'Site Location' }}</div>
            <div class="meta-line"><strong>Project Area:</strong> {{ number_format($project->floor_area_sqm, 2) }} sq.m.</div>
            <div class="meta-line"><strong>Lot Area:</strong> {{ number_format($project->land_area_sqm, 2) }} sq.m.</div>
        </div>
        @endif

        <!-- Scope Items in this Page -->
        @foreach($chunk as $item)
        <div class="scope-item-block">
            <div class="scope-item-header">
                <span>ITEM {{ $item->item_number }}. &nbsp; {{ $item->item_name }}:</span>
                <span style="font-weight: 500; font-size: 11px;">{{ $item->volume_or_area }} {{ $item->notes ? '(' . $item->notes . ')' : '' }}</span>
            </div>

            <!-- A. Materials -->
            @if($item->materials->count() > 0)
            <div class="section-title">A. Materials</div>
            <table class="line-table">
                <tbody>
                    @foreach($item->materials as $mat)
                    <tr>
                        <td class="col-qty">{{ $mat->quantity > 0 ? (fmod($mat->quantity, 1) !== 0.0 ? number_format($mat->quantity, 2) : number_format($mat->quantity)) : '' }}</td>
                        <td class="col-unit">{{ $mat->unit }}</td>
                        <td class="col-desc">{{ $mat->description }}</td>
                        <td class="col-at">@</td>
                        <td class="col-rate">{{ number_format($mat->unit_price, 2) }}</td>
                        <td class="col-p">P</td>
                        <td class="col-total">{{ number_format($mat->total_cost, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="subtotal-row">
                        <td colspan="5"></td>
                        <td class="col-p">P</td>
                        <td class="col-total">{{ number_format($item->materials_subtotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            @endif

            <!-- B. Labors -->
            @if($item->labors->count() > 0)
            <div class="section-title">B. Labors</div>
            <table class="line-table">
                <tbody>
                    @foreach($item->labors as $lab)
                    <tr>
                        <td class="col-qty">{{ $lab->quantity > 1 ? (fmod($lab->quantity, 1) !== 0.0 ? number_format($lab->quantity, 2) : number_format($lab->quantity)) : '' }}</td>
                        <td class="col-unit">{{ $lab->unit !== 'Lump Sum' ? $lab->unit : '' }}</td>
                        <td class="col-desc">{{ $lab->description }}</td>
                        <td class="col-at">@</td>
                        <td class="col-rate">{{ $lab->unit_price > 0 ? number_format($lab->unit_price, 2) : '' }}</td>
                        <td class="col-p">P</td>
                        <td class="col-total">{{ number_format($lab->total_cost, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="subtotal-row">
                        <td colspan="5"></td>
                        <td class="col-p">P</td>
                        <td class="col-total">{{ number_format($item->labor_subtotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            @endif

            <!-- C. Equipment Epx. -->
            @if($item->equipments->count() > 0)
            <div class="section-title">C. Equipment Epx.</div>
            <table class="line-table">
                <tbody>
                    @foreach($item->equipments as $eq)
                    <tr>
                        <td class="col-qty"></td>
                        <td class="col-unit"></td>
                        <td class="col-desc">{{ $eq->description }}</td>
                        <td class="col-at"></td>
                        <td class="col-rate"></td>
                        <td class="col-p">P</td>
                        <td class="col-total">{{ number_format($eq->total_cost, 2) }}</td>
                    </tr>
                    @endforeach
                    @if($item->equipments->count() > 1)
                    <tr class="subtotal-row">
                        <td colspan="5"></td>
                        <td class="col-p">P</td>
                        <td class="col-total">{{ number_format($item->equipment_subtotal, 2) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @endif

            <!-- Direct Cost & Markup Summary -->
            <div class="cost-summary-box">
                <div class="cost-summary-row">
                    <span style="font-weight: 700;">DIRECT COST :</span>
                    <span style="font-family: 'JetBrains Mono'; font-weight: 700;">{{ number_format($item->direct_cost, 2) }}</span>
                </div>

                @if($item->contingency_percent > 0)
                <div class="cost-summary-row">
                    <span>Plus: &nbsp; Contingency &nbsp; {{ (int)$item->contingency_percent }}%</span>
                    <span style="font-family: 'JetBrains Mono';">{{ number_format($item->contingency_amount, 2) }}</span>
                </div>
                @endif

                @if($item->taxes_percent > 0)
                <div class="cost-summary-row">
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Taxes &nbsp; {{ (int)$item->taxes_percent }}%</span>
                    <span style="font-family: 'JetBrains Mono';">{{ number_format($item->taxes_amount, 2) }}</span>
                </div>
                @endif

                @if($item->profit_percent > 0)
                <div class="cost-summary-row">
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Profit &nbsp; {{ (int)$item->profit_percent }}%</span>
                    <span style="font-family: 'JetBrains Mono';">{{ number_format($item->profit_amount, 2) }}</span>
                </div>
                @endif

                <div class="total-item-cost-row">
                    <span>TOTAL ITEM COST</span>
                    <span style="font-family: 'JetBrains Mono';">P &nbsp; {{ number_format($item->total_item_cost, 2) }}</span>
                </div>
            </div>
        </div>
        @endforeach

        <div class="page-footer-num">
            Page {{ $pageCounter }} of {{ $totalPages }}
        </div>
    </div>
    @php $pageCounter++; @endphp
    @endforeach

    <!-- Final Page: Grand Summary Scope of Work Table with Signatures -->
    <div class="bom-page">
        <div class="doc-header">
            <h1 class="doc-title">BILL OF MATERIALS AND COST ESTIMATES</h1>
            <h2 style="font-size: 13px; font-weight: 700; margin-top: 4px; text-transform: uppercase;">SUMMARY SCOPE OF WORK & FINANCIAL CONSOLIDATION</h2>
        </div>

        <div class="project-meta">
            <div class="meta-line"><strong>Project Title:</strong> {{ $project->title }}</div>
            <div class="meta-line"><strong>Project Location:</strong> {{ $project->location ?? 'Site Location' }}</div>
        </div>

        <div style="font-weight: 800; font-size: 12px; margin-bottom: 12px; border-bottom: 1.5px solid #000000; padding-bottom: 4px;">
            SCOPE OF WORK:
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; margin-bottom: 24px;">
            <tbody>
                @foreach($scopeItems as $sItem)
                <tr>
                    <td style="width: 30px; padding: 3px 0; font-weight: 700;">{{ $sItem->item_number }} .</td>
                    <td style="padding: 3px 0;">
                        {{ $sItem->item_name }}
                        <span style="color: #64748b; font-size: 10px;">{{ str_repeat('.', max(10, 50 - strlen($sItem->item_name))) }}</span>
                    </td>
                    <td style="width: 25px; text-align: right; font-weight: 700; font-family: 'JetBrains Mono';">₱</td>
                    <td style="width: 120px; text-align: right; font-weight: 700; font-family: 'JetBrains Mono';">
                        {{ number_format($sItem->total_item_cost, 2) }}
                    </td>
                </tr>
                @endforeach
                <tr style="border-top: 2px solid #000000; border-bottom: 2px solid #000000;">
                    <td colspan="2" style="padding: 8px 0; font-weight: 800; font-size: 13px; text-transform: uppercase;">
                        Total Cost .............
                    </td>
                    <td style="padding: 8px 0; text-align: right; font-weight: 800; font-size: 13px; font-family: 'JetBrains Mono';">₱</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: 800; font-size: 14px; font-family: 'JetBrains Mono';">
                        {{ number_format($grandTotal ?: $project->contract_budget, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures Certification Block -->
        <div class="signature-grid">
            <div class="sig-block">
                <div>Certified by:</div>
                <div style="font-family: cursive; font-size: 16px; margin-top: 10px; color: #1e3a8a;">{{ $certName ?? 'Engr. Esabyl B. Mitra' }}</div>
                <div class="sig-name" style="margin-top: 2px;">{{ $certName ?? 'ENGR. ESABYL B. MITRA' }}</div>
                <div class="sig-title">{{ $certTitle ?? 'REGISTERED CIVIL ENGINEER' }}</div>
                <div class="sig-info">
                    PRC No: {{ $certLicense ?? '0180490' }}<br>
                    PTR: {{ $certPtr ?? '4531999' }}<br>
                    Date Issued: {{ $certDate ?? '01-07-2025' }}<br>
                    Place Issued: {{ $certPlace ?? 'SILAY CITY' }}
                </div>
            </div>

            <div class="sig-block" style="text-align: right;">
                <div>Approved by:</div>
                <div style="font-family: cursive; font-size: 18px; margin-top: 10px; color: #1e3a8a;">Ignacio S. Lonzaga</div>
                <div class="sig-name" style="margin-top: 2px;">{{ $ownerName ?? 'ENGR. IGNACIO S. LONZAGA' }}</div>
                <div class="sig-title">{{ $ownerTitle ?? 'PROJECT OWNER / DEVELOPER' }}</div>
                <div class="sig-info">
                    Conforme & Accepted for Construction
                </div>
            </div>
        </div>

        <div class="page-footer-num">
            Page {{ $totalPages }} of {{ $totalPages }}
        </div>
    </div>

</body>
</html>
