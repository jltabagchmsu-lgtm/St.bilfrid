<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Accomplishment & Monitoring Report - {{ $project->project_code }}</title>
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
            line-height: 1.45;
            font-size: 13px;
            padding: 24px;
        }

        .report-page {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            padding: 40px 48px;
        }

        .print-actions {
            max-width: 900px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print {
            background: #ef4444;
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
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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

        /* Letterhead */
        .letterhead {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #ef4444;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .company-brand h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .company-brand span {
            color: #ef4444;
        }

        .company-brand p {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        .report-meta {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: #475569;
        }

        .report-title-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #ef4444;
            padding: 14px 18px;
            border-radius: 4px;
            margin-bottom: 24px;
        }

        .report-title-box h2 {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .report-title-box p {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Section Headings */
        .section-heading {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 24px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-heading .badge {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 4px;
            background: #e0f2fe;
            color: #0369a1;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Data Grids & Tables */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 24px;
            margin-bottom: 16px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 4px;
            font-size: 12px;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }

        .metric-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .metric-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }

        .metric-card .title {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .metric-card .value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }

        .metric-card .sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Progress Breakdown Bar */
        .prog-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 16px;
        }

        .prog-table th, .prog-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            text-align: left;
        }

        .prog-table th {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            font-size: 11px;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Sign-off Blocks */
        .signatures-container {
            margin-top: 36px;
            padding-top: 20px;
            border-top: 2px solid #0f172a;
            page-break-inside: avoid;
        }

        .signatures-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0f172a;
            margin-bottom: 20px;
            text-align: center;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px 40px;
        }

        .sig-block {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 16px;
            background: #f8fafc;
        }

        .sig-role {
            font-size: 11px;
            font-weight: 800;
            color: #ef4444;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 30px;
        }

        .sig-line {
            border-bottom: 1.5px solid #0f172a;
            margin-bottom: 6px;
        }

        .sig-name {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }

        .sig-detail {
            font-size: 11px;
            color: #64748b;
        }

        /* Print Media Queries */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
                color: #000000;
            }
            .print-actions {
                display: none !important;
            }
            .report-page {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .signatures-container {
                page-break-inside: avoid;
            }
            @page {
                margin: 15mm 15mm 15mm 15mm;
                size: A4 portrait;
            }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <a href="{{ route('projects.show', $project->id) }}" class="btn-back">&larr; Return to Master View</a>
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Official Report (PDF / Hardcopy)
        </button>
    </div>

    <div class="report-page">
        <!-- Letterhead Header -->
        <div class="letterhead">
            <div class="company-brand">
                <h1>St. Bilfrid <span>Development Corporation</span></h1>
                <p>Engineering, Architecture & Construction Management</p>
                <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                    SEC Reg. No. CS-2024-99120 &bull; PCAB License No. 49812-AAA &bull; ISO 9001:2015 Certified
                </div>
            </div>
            <div class="report-meta">
                <div>DOC REF: <strong>REP-{{ $project->project_code }}-{{ date('Ymd') }}</strong></div>
                <div>DATE ISSUED: <strong>{{ date('M d, Y') }}</strong></div>
                <div>PROJECT STATUS: <strong style="text-transform: uppercase; color: #ef4444;">{{ str_replace('_', ' ', $project->status) }}</strong></div>
            </div>
        </div>

        <!-- Title Box -->
        <div class="report-title-box">
            <h2>Executive Project Accomplishment & Monitoring Certificate</h2>
            <p>Official periodic monitoring, trade work progression audit, financial variance ledger, and materials inventory reconciliation report.</p>
        </div>

        <!-- Section 1: Project Identification & Physical Scope -->
        <div class="section-heading">
            <span>1. Project Identification & Physical Specifications</span>
            <span class="badge">{{ $project->project_code }}</span>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Project Title:</span>
                <span class="info-value">{{ $project->title }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Client Entity:</span>
                <span class="info-value">{{ $project->client_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Project Classification:</span>
                <span class="info-value">{{ $project->project_type }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Site Location:</span>
                <span class="info-value">{{ $project->location ?? 'Metropolitan District' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Land Plot Area:</span>
                <span class="info-value mono">{{ number_format($project->land_area_sqm, 2) }} m²</span>
            </div>
            <div class="info-item">
                <span class="info-label">Constructible Floor Area:</span>
                <span class="info-value mono">{{ number_format($project->floor_area_sqm, 2) }} m²</span>
            </div>
            <div class="info-item">
                <span class="info-label">Contract Timeline:</span>
                <span class="info-value mono">{{ $project->start_date->format('M d, Y') }} &rarr; {{ $project->end_date->format('M d, Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Current Execution Phase:</span>
                <span class="info-value" style="color: #ef4444;">{{ $project->current_phase ?? 'Phase 2: Substructure & Frame' }}</span>
            </div>
        </div>

        <!-- Section 2: Financial Audit & Margin Analysis -->
        <div class="section-heading">
            <span>2. Financial Statement & Commercial Audit</span>
            <span class="badge">CURRENCY: PHP (₱)</span>
        </div>
        <div class="metric-cards">
            <div class="metric-card">
                <div class="title">Contract Budget</div>
                <div class="value">₱{{ number_format($project->contract_budget, 2) }}</div>
                <div class="sub">Agreed Scope</div>
            </div>
            <div class="metric-card">
                <div class="title">Actual Incurred Cost</div>
                <div class="value" style="color: #ef4444;">₱{{ number_format($totalIncurredCost, 2) }}</div>
                <div class="sub">₱{{ number_format($project->cost_per_floor_sqm, 2) }}/m²</div>
            </div>
            <div class="metric-card">
                <div class="title">Cleared Inflow</div>
                <div class="value" style="color: #10b981;">₱{{ number_format($totalPaid, 2) }}</div>
                <div class="sub">Settled Milestones</div>
            </div>
            <div class="metric-card">
                <div class="title">Gross Margin</div>
                <div class="value" style="color: #0369a1;">₱{{ number_format($grossMargin, 2) }}</div>
                <div class="sub">{{ $grossMarginPercent }}% Profit Margin</div>
            </div>
        </div>

        <!-- Section 3: Weighted Engineering Progression Bases -->
        <div class="section-heading">
            <span>3. Multi-Trade Progression & Weighted Engineering Bases</span>
            <span class="badge">OVERALL: {{ $project->overall_progress }}%</span>
        </div>
        <p style="font-size: 11px; color: #64748b; margin-bottom: 8px;">
            Overall accomplishment is calculated using standard weighted engineering bases: 
            <strong>Overall % = (Structural &times; {{ $project->structural_weight }}%) + (Electrical &times; {{ $project->electrical_weight }}%) + (Plumbing &times; {{ $project->piping_weight }}%) + (Finishing &times; {{ $project->finishing_weight }}%)</strong>
        </p>
        <table class="prog-table">
            <thead>
                <tr>
                    <th>Trade Discipline</th>
                    <th>Engineering Basis Weight</th>
                    <th>Actual Trade Progress</th>
                    <th>Weighted Contribution</th>
                    <th>Discipline Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Structural Works & Foundation</strong></td>
                    <td class="mono">{{ $project->structural_weight }}% weight</td>
                    <td class="mono"><strong>{{ $project->structural_progress }}%</strong></td>
                    <td class="mono">{{ round(($project->structural_progress * $project->structural_weight) / 100, 1) }}%</td>
                    <td>{{ $project->structural_progress >= 100 ? 'Completed' : ($project->structural_progress > 0 ? 'In Progress' : 'Not Started') }}</td>
                </tr>
                <tr>
                    <td><strong>Electrical Conduits & High Voltage</strong></td>
                    <td class="mono">{{ $project->electrical_weight }}% weight</td>
                    <td class="mono"><strong>{{ $project->electrical_progress }}%</strong></td>
                    <td class="mono">{{ round(($project->electrical_progress * $project->electrical_weight) / 100, 1) }}%</td>
                    <td>{{ $project->electrical_progress >= 100 ? 'Completed' : ($project->electrical_progress > 0 ? 'In Progress' : 'Not Started') }}</td>
                </tr>
                <tr>
                    <td><strong>Piping, Sanitary & Plumbing</strong></td>
                    <td class="mono">{{ $project->piping_weight }}% weight</td>
                    <td class="mono"><strong>{{ $project->piping_progress }}%</strong></td>
                    <td class="mono">{{ round(($project->piping_progress * $project->piping_weight) / 100, 1) }}%</td>
                    <td>{{ $project->piping_progress >= 100 ? 'Completed' : ($project->piping_progress > 0 ? 'In Progress' : 'Not Started') }}</td>
                </tr>
                <tr>
                    <td><strong>Architectural & Turnkey Finishes</strong></td>
                    <td class="mono">{{ $project->finishing_weight }}% weight</td>
                    <td class="mono"><strong>{{ $project->finishing_progress }}%</strong></td>
                    <td class="mono">{{ round(($project->finishing_progress * $project->finishing_weight) / 100, 1) }}%</td>
                    <td>{{ $project->finishing_progress >= 100 ? 'Completed' : ($project->finishing_progress > 0 ? 'In Progress' : 'Not Started') }}</td>
                </tr>
                <tr style="background: #f8fafc; font-weight: 800;">
                    <td>TOTAL CONSOLIDATED</td>
                    <td class="mono">100%</td>
                    <td class="mono" colspan="2" style="color: #ef4444; font-size: 13px;">{{ $project->overall_progress }}% Weighted Completion</td>
                    <td>{{ $scheduleHealth['label'] }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Section 4: Schedule, Materials & Excess Reconciliation -->
        <div class="section-heading">
            <span>4. Schedule Health & Materials Inventory Reconciliation</span>
            <span class="badge">{{ $totalDeployedManpower }} Active Workforce</span>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Total Scheduled Duration:</span>
                <span class="info-value mono">{{ $totalScheduleDays }} Calendar Days</span>
            </div>
            <div class="info-item">
                <span class="info-label">Elapsed Days vs Remaining:</span>
                <span class="info-value mono">{{ $elapsedDays }} Days Elapsed &bull; {{ $remainingDays }} Days Left</span>
            </div>
            <div class="info-item">
                <span class="info-label">BOM Materials Allocated:</span>
                <span class="info-value mono">₱{{ number_format($project->projectMaterials->sum('total_cost'), 2) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Excess Returned to Warehouse:</span>
                <span class="info-value mono" style="color: #10b981;">+₱{{ number_format($project->total_returned_excess_value, 2) }} ({{ $project->total_returned_excess_units }} Units)</span>
            </div>
            <div class="info-item">
                <span class="info-label">Net Materials Expended:</span>
                <span class="info-value mono">₱{{ number_format($project->projectMaterials->sum('net_cost'), 2) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Scheduled Tasks Execution:</span>
                <span class="info-value mono">{{ $project->tasks->where('status', 'completed')->count() }} / {{ $project->tasks->count() }} Tasks Done</span>
            </div>
        </div>

        <!-- Section 5: Official Sign-off & Certification Block -->
        <div class="signatures-container">
            <div class="signatures-title">
                Official Certifications, Inspection & Client Acceptance Sign-Off
            </div>
            <div class="signature-grid">
                <!-- Prepared By -->
                <div class="sig-block">
                    <div class="sig-role">Prepared & Certified By:</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $leadEngineer->name ?? 'Engr. Elena Rostova' }}</div>
                    <div class="sig-detail">{{ $leadEngineer->title ?? 'Site / Structural Engineer' }}</div>
                    <div class="sig-detail">PRC License No.: <strong>{{ $leadEngineer->license_no ?? 'PE-330412' }}</strong></div>
                    <div class="sig-detail" style="margin-top: 4px;">Date Signed: ________________________</div>
                </div>

                <!-- Verified By -->
                <div class="sig-block">
                    <div class="sig-role">Checked & Quality Verified By:</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $leadArchitect->name ?? 'Arch. Marcus Vance' }}</div>
                    <div class="sig-detail">{{ $leadArchitect->title ?? 'Lead Principal Architect' }}</div>
                    <div class="sig-detail">PRC License No.: <strong>{{ $leadArchitect->license_no ?? 'ARC-991204' }}</strong></div>
                    <div class="sig-detail" style="margin-top: 4px;">Date Signed: ________________________</div>
                </div>

                <!-- Approved By -->
                <div class="sig-block">
                    <div class="sig-role">Approved By Firm Management:</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">Engr. Sophia Martinez, PMP</div>
                    <div class="sig-detail">Managing Director & Operations Principal</div>
                    <div class="sig-detail">St. Bilfrid Development Corporation Head Office</div>
                    <div class="sig-detail" style="margin-top: 4px;">Date Signed: ________________________</div>
                </div>

                <!-- Client Acceptance -->
                <div class="sig-block">
                    <div class="sig-role">Conforme & Client Acceptance:</div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $project->client_name }}</div>
                    <div class="sig-detail">Authorized Client Representative / Owner</div>
                    <div class="sig-detail">Subject to final inspection terms</div>
                    <div class="sig-detail" style="margin-top: 4px;">Date Signed: ________________________</div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
