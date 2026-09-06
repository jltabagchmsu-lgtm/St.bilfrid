@extends('layouts.app')

@section('title', 'Active Project Tracker - St. Bilfrid Development Corporation')
@section('page_title', 'Active Project Tracker & Progress Monitor')

@section('top_actions')
    <button class="btn-primary" onclick="openModal('createProjectModal')">
        <span>+</span> Add New Project
    </button>
@endsection

@section('content')

<div class="glass-panel">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Approved Projects Monitoring Matrix</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Tracking Blueprints, Task Milestones, Workforce Deployment, Weighted Progression, & Incurred Cost vs Budget</span>
        </div>
        <span class="badge badge-in_progress">{{ $projects->count() }} Active Sites Monitored</span>
    </div>

    <table class="custom-table">
        <thead>
            <tr>
                <th>Design / Specs</th>
                <th>Client & Tasks</th>
                <th>Land & Floor Area</th>
                <th>Workforce Deployed</th>
                <th>Schedule Health</th>
                <th>Weighted Progression</th>
                <th>Financials & Margins</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $prj)
            @php 
                $schedHealth = $prj->schedule_health_status; 
                $heroPhoto = $prj->primaryPhoto ?? $prj->photos->first();
                $completedTasks = $prj->tasks()->where(function($q) { $q->where('progress', '>=', 100)->orWhere('status', 'completed'); })->count();
                $totalTasks = $prj->tasks()->count();
            @endphp
            <tr>
                <td>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        @if($heroPhoto)
                            <div style="width: 54px; height: 54px; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border-accent); flex-shrink: 0; background: #000;">
                                <img src="{{ $heroPhoto->file_path }}" alt="{{ $prj->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @else
                            <div style="width: 54px; height: 54px; border-radius: var(--radius-sm); background: rgba(239, 68, 68, 0.1); border: 1px dashed var(--border-accent); display: grid; place-items: center; color: #ef4444; font-size: 0.8rem; font-weight: bold; flex-shrink: 0;">
                                CAD
                            </div>
                        @endif
                        <div>
                            <strong style="font-size: 1rem; color: var(--text-primary);">{{ $prj->title }}</strong>
                            <div style="font-family: var(--font-mono); font-size: 0.775rem; color: #ef4444; margin-top: 2px;">{{ $prj->project_code }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $prj->project_type }} &bull; {{ $prj->location }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <strong>{{ $prj->client_name }}</strong>
                    <div style="font-size: 0.75rem; color: #38bdf8; margin-top: 3px; font-weight: 600;">
                        {{ $completedTasks }} / {{ $totalTasks }} Tasks Done ({{ $prj->overall_progress }}%)
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="spec-chip">Land: {{ number_format($prj->land_area_sqm) }} m²</span>
                        <span class="spec-chip">Floor: {{ number_format($prj->floor_area_sqm) }} m²</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 3px;">
                        <div style="font-weight: 800; font-family: var(--font-mono); color: #38bdf8; font-size: 0.95rem;">
                            {{ $prj->total_deployed_manpower }} Headcount
                        </div>
                        <div style="font-size: 0.725rem; color: var(--text-muted);">
                            {{ $prj->deployed_workers }} Workers &bull; {{ $prj->deployed_engineers }} Engr
                        </div>
                        <div style="font-size: 0.725rem; color: var(--text-muted);">
                            {{ $prj->deployed_architects }} Arch &bull; {{ $prj->deployed_operators }} Ops
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="spec-chip" style="background: {{ $schedHealth['bg'] }}; color: {{ $schedHealth['color'] }}; border-color: {{ $schedHealth['border'] }}; font-size: 0.7rem;">
                            {{ $schedHealth['label'] }}
                        </span>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); font-family: var(--font-mono);">
                            {{ $prj->remaining_days }}d left &bull; {{ $prj->end_date->format('M d, Y') }}
                        </div>
                    </div>
                </td>
                <td style="min-width: 170px;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                        <span style="font-weight: 800; font-family: var(--font-mono); font-size: 1.1rem; color: #ef4444;">{{ $prj->overall_progress }}%</span>
                        <span style="font-size: 0.7rem; color: var(--text-muted);">Weighted Total</span>
                    </div>
                    <div class="progress-track" style="height: 6px; margin-bottom: 6px;">
                        <div class="progress-bar progress-bar-structural" style="width: {{ $prj->overall_progress }}%;"></div>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                        <span style="color: #38bdf8;">S: {{ $prj->structural_progress }}%</span>
                        <span style="color: #f59e0b;">E: {{ $prj->electrical_progress }}%</span>
                        <span style="color: #10b981;">P: {{ $prj->piping_progress }}%</span>
                        <span style="color: #ec4899;">F: {{ $prj->finishing_progress }}%</span>
                    </div>
                <td>
                    <div style="font-size: 0.85rem;">
                        <div style="color: var(--text-secondary);">Contract Budget:</div>
                        <div style="font-family: var(--font-mono); font-weight: 700; color: #fff;">
                            ₱{{ number_format($prj->contract_budget, 2) }}
                        </div>
                    </div>
                    @if($prj->client_budget && $prj->client_budget > 0)
                    <div style="font-size: 0.75rem; color: #38bdf8; margin-top: 2px;">
                        Client Budget: ₱{{ number_format($prj->client_budget, 2) }}
                    </div>
                    @endif
                    @if($prj->estimated_cost && $prj->estimated_cost > 0)
                    <div style="font-size: 0.725rem; color: #ec4899; margin-top: 1px;">
                        Est. Cost: ₱{{ number_format($prj->estimated_cost, 2) }}
                    </div>
                    @endif
                    <div style="font-size: 0.8rem; margin-top: 4px;">
                        <div style="color: var(--text-secondary);">Incurred Cost:</div>
                        <div style="font-family: var(--font-mono); color: {{ $prj->total_incurred_cost > $prj->contract_budget ? '#ef4444' : '#38bdf8' }}; font-weight: 600;">
                            ₱{{ number_format($prj->total_incurred_cost, 2) }} ({{ number_format($prj->budget_consumption_pct, 1) }}%)
                        </div>
                        <div style="font-size: 0.725rem; color: #10b981; margin-top: 2px;">
                            Margin: ₱{{ number_format($prj->gross_margin, 2) }} ({{ $prj->gross_margin_percent }}%)
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <a href="{{ route('projects.show', $prj->id) }}" class="btn-primary" style="font-size: 0.75rem; padding: 4px 8px; text-align: center;">
                            Master View &rarr;
                        </a>
                        <button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; text-align: center; color: #38bdf8;" onclick="openEditProjectModal({{ json_encode($prj) }})">
                            Edit
                        </button>
                        <a href="{{ route('projects.printReport', $prj->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.725rem; padding: 3px 6px; text-align: center; color: #ef4444;">
                            Report
                        </a>
                        <button type="button" class="btn-secondary" style="font-size: 0.725rem; padding: 3px 6px; text-align: center; color: #f87171; width: 100%; border-color: rgba(239,68,68,0.35); cursor: pointer;" onclick="openDeleteProjectModal({{ $prj->id }}, '{{ addslashes($prj->project_code) }}', '{{ addslashes($prj->title) }}')">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal 1: Initialize New Real Project -->
<div class="modal-overlay" id="createProjectModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">+ Initialize Real Construction Project</h3>
            <button onclick="closeModal('createProjectModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Project Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Nexus Multi-Specialty Medical Complex" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Project Code (Optional)</label>
                    <input type="text" name="project_code" class="form-input" placeholder="e.g. PRJ-2026-NEXUS">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Client / Developer Name</label>
                    <input type="text" name="client_name" class="form-input" placeholder="e.g. Apex Health Systems Inc." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Site Location / Address</label>
                    <input type="text" name="location" class="form-input" placeholder="e.g. North Triangle Commercial District, QC">
                </div>
            </div>

            <!-- Project Classification & Finishing Tier Dropdowns -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Project Classification / Service Type</label>
                    <select name="project_type" id="modalInputProjectType" class="form-select" onchange="recalculateProjectModalCosting()" required>
                        <option value="Residential Build" selected>Residential Build (₱1,100 / m² Floor Base)</option>
                        <option value="Commercial Construction">Commercial Construction (₱1,400 / m² Floor Base)</option>
                        <option value="Industrial Complex">Industrial Complex (₱1,600 / m² Floor Base)</option>
                        <option value="High-Rise Development">High-Rise Development (₱1,800 / m² Floor Base)</option>
                        <option value="Renovation & Overhaul">Renovation & Overhaul (₱800 / m² Floor Base)</option>
                        <option value="Interior Fit-Out & Turnkey">Interior Fit-Out & Turnkey (₱950 / m² Floor Base)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Finishing & Quality Specification Tier</label>
                    <select name="finish_tier" id="modalInputFinishTier" class="form-select" onchange="recalculateProjectModalCosting()">
                        <option value="standard" selected>Standard / Basic Quality (1.00x Base Multiplier)</option>
                        <option value="executive">Semi-Custom / Executive Quality (1.25x Multiplier)</option>
                        <option value="luxury">Premium Luxury / Turnkey High-End (1.50x Multiplier)</option>
                    </select>
                </div>
            </div>

            <!-- Guided Spatial Layout & Dropdown Room Setup -->
            <div style="margin-top: 16px; padding: 16px; background: rgba(15, 23, 42, 0.7); border-radius: var(--radius-md); border: 1px solid rgba(56, 189, 248, 0.35);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                            <span>[PLAN]</span> Floor Plan Model & Room Configuration (Dropdown Driven)
                        </div>
                        <span style="font-size: 0.775rem; color: var(--text-muted);">
                            Select a floor plan preset or customize room counts and dimensions from structured dropdowns.
                        </span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.75rem;">1. Suggested Floor Plan Model Preset</label>
                        <select id="modalFloorPlanModelSelect" class="form-select" style="font-size: 0.8rem;" onchange="applyModalFloorPlanPreset(this.value)">
                            <option value="custom">-- Custom Room-by-Room Build --</option>
                            <option value="studio">1-Room Studio / Micro-Loft (36 m²)</option>
                            <option value="2br_bungalow" selected>2-Bedroom Single-Storey Bungalow (65 m²)</option>
                            <option value="3br_standard">3-Bedroom Standard Two-Storey Residence (120 m²)</option>
                            <option value="4br_executive">4-Bedroom Executive Villa (220 m²)</option>
                            <option value="5br_luxury">5-Bedroom Luxury Estate with En-Suites (350 m²)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.75rem;">2. Total Room Count</label>
                        <select id="modalTotalRoomCountSelect" class="form-select" style="font-size: 0.8rem;" onchange="setModalTotalRoomsCount(this.value)">
                            <option value="1">1 Room Total</option>
                            <option value="2">2 Rooms Total</option>
                            <option value="3">3 Rooms Total</option>
                            <option value="4" selected>4 Rooms Total</option>
                            <option value="5">5 Rooms Total</option>
                            <option value="6">6 Rooms Total</option>
                            <option value="7">7 Rooms Total</option>
                            <option value="8">8 Rooms Total</option>
                            <option value="10">10 Rooms Total</option>
                            <option value="12">12 Rooms Total</option>
                        </select>
                    </div>
                </div>

                <!-- Container for Individual Room Dropdown Rows -->
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                    3. Room Types & Dimension Brackets:
                </div>
                <div id="modalRoomRowsContainer" style="display: flex; flex-direction: column; gap: 8px; max-height: 240px; overflow-y: auto; padding-right: 4px; margin-bottom: 14px;">
                    <!-- Populated dynamically via JS -->
                </div>

                <!-- Dynamic Area Calculations & Material Summary Bar -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; padding: 10px 14px; background: rgba(0,0,0,0.4); border-radius: var(--radius-sm); border: 1px solid var(--border-color); align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Rooms:</div>
                        <strong id="modalDispRoomCount" style="color: #f8fafc; font-size: 1rem;">4</strong>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Calculated Floor Area:</div>
                        <strong id="modalDispFloorArea" style="color: #38bdf8; font-size: 1.1rem; font-family: var(--font-mono);">65.00 m²</strong>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Suggested Lot Footprint:</div>
                        <strong id="modalDispLandArea" style="color: #10b981; font-size: 1.1rem; font-family: var(--font-mono);">87.75 m²</strong>
                    </div>
                </div>

            </div>

            <!-- Areas & Financial Specifications with Client Stated Budget -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr 1.2fr; gap: 14px; margin-top: 14px;">
                <div class="form-group" style="background: rgba(56, 189, 248, 0.06); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid rgba(56, 189, 248, 0.3);">
                    <label class="form-label" style="color: #38bdf8; font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                        <span>Client Stated Budget (₱)</span>
                        <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 400;">Target Cap</span>
                    </label>
                    <input type="number" step="0.01" name="client_budget" id="modalInputClientBudget" class="form-input" placeholder="e.g. 3500000" oninput="recalculateProjectModalCosting()" style="border-color: rgba(56, 189, 248, 0.5); font-weight: 700; color: #38bdf8; font-family: var(--font-mono);">
                </div>

                <div class="form-group">
                    <label class="form-label">Land Area (m²)</label>
                    <input type="number" step="0.01" name="land_area_sqm" id="modalInputLandArea" class="form-input" placeholder="e.g. 150" oninput="recalculateProjectModalCosting()" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Constructible Floor (m²)</label>
                    <input type="number" step="0.01" name="floor_area_sqm" id="modalInputFloorArea" class="form-input" placeholder="e.g. 120" oninput="recalculateProjectModalCosting()" required>
                </div>

                <div class="form-group" style="background: rgba(16, 185, 129, 0.06); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid rgba(16, 185, 129, 0.3);">
                    <label class="form-label" style="color: #10b981; font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                        <span>Contract Budget (₱)</span>
                        <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 400;">Monitored Cap</span>
                    </label>
                    <input type="number" step="0.01" name="contract_budget" id="modalInputContractBudget" class="form-input" placeholder="₱ 0.00" style="border-color: rgba(16, 185, 129, 0.5); font-weight: 700; color: #10b981; font-family: var(--font-mono);" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Completion Date</label>
                    <input type="date" name="end_date" class="form-input" value="{{ date('Y-m-d', strtotime('+365 days')) }}" required>
                </div>
            </div>

            <!-- Hidden Fields for JSON Payloads & Estimated Cost -->
            <input type="hidden" name="room_program_json" id="modalInputRoomProgramJson" value="[]">
            <input type="hidden" name="material_takeoffs_json" id="modalInputMaterialTakeoffsJson" value="[]">
            <input type="hidden" name="estimated_cost" id="modalInputEstimatedCost" value="0">

            <!-- Trade Weighting & Initial Progress Bases -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #f59e0b; margin-bottom: 10px;">Engineering Progression Formula Weights & Initial Progress</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #38bdf8;">Structural Weight %</label>
                        <input type="number" name="structural_weight" class="form-input" value="40" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Initial Progress %</label>
                        <input type="number" name="structural_progress" class="form-input" value="0" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #f59e0b;">Electrical Weight %</label>
                        <input type="number" name="electrical_weight" class="form-input" value="25" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Initial Progress %</label>
                        <input type="number" name="electrical_progress" class="form-input" value="0" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #10b981;">Piping Weight %</label>
                        <input type="number" name="piping_weight" class="form-input" value="20" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Initial Progress %</label>
                        <input type="number" name="piping_progress" class="form-input" value="0" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #ec4899;">Finishing Weight %</label>
                        <input type="number" name="finishing_weight" class="form-input" value="15" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">Initial Progress %</label>
                        <input type="number" name="finishing_progress" class="form-input" value="0" min="0" max="100">
                    </div>
                </div>
            </div>

            <!-- Workforce Headcounts -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #38bdf8; margin-bottom: 10px;">Initial On-Site Workforce Mobilization (Headcount)</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">General Laborers</label>
                        <input type="number" name="deployed_workers" class="form-input" value="20" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Skilled Trades</label>
                        <input type="number" name="deployed_skilled_workers" class="form-input" value="12" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Field Engineers</label>
                        <input type="number" name="deployed_engineers" class="form-input" value="3" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Architects</label>
                        <input type="number" name="deployed_architects" class="form-input" value="1" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Site Foremen</label>
                        <input type="number" name="deployed_foremen" class="form-input" value="2" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Equipment Ops</label>
                        <input type="number" name="deployed_operators" class="form-input" value="2" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Safety Officers</label>
                        <input type="number" name="deployed_safety_officers" class="form-input" value="1" min="0">
                    </div>
                </div>
            </div>

            <!-- Assigned Lead Personnel -->
            @if(isset($personnelList) && $personnelList->count() > 0)
            <div style="margin-top: 14px;">
                <label class="form-label">Assign Lead Engineers & Architects</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px; max-height: 120px; overflow-y: auto; padding: 10px; background: rgba(0,0,0,0.3); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    @foreach($personnelList as $pers)
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="personnel_ids[]" value="{{ $pers->id }}">
                        <span><strong>{{ $pers->name }}</strong> ({{ $pers->title }})</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="form-group" style="margin-top: 14px;">
                <label class="form-label">Project Scope & Technical Description</label>
                <textarea name="description" class="form-textarea" rows="2" placeholder="Brief project scope, structural design details, deliverables..."></textarea>
            </div>

            <!-- Automated Costing & Financial Feasibility Engine (Merged Estimator) -->
            <div style="margin-top: 20px; padding: 18px 20px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9)); border-radius: var(--radius-md); border: 1px solid rgba(56, 189, 248, 0.35); box-shadow: 0 10px 25px rgba(0,0,0,0.4);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
                    <div>
                        <div style="font-size: 1rem; font-weight: 800; color: #f8fafc; display: flex; align-items: center; gap: 8px;">
                            <span>Automated Costing & Financial Feasibility Engine</span>
                        </div>
                        <div style="font-size: 0.775rem; color: var(--text-muted); margin-top: 2px;">
                            Dynamically calculated from Service Rate, Floor Area, Land Preparation, Fit-out, & Room Configuration.
                        </div>
                    </div>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <span class="spec-chip" style="font-size: 0.7rem; color: #38bdf8; background: rgba(56, 189, 248, 0.15); border-color: rgba(56, 189, 248, 0.3);">
                            UNIT COST BENCHMARK
                        </span>
                        <span id="modalCostingHealthBadge" class="spec-chip" style="font-size: 0.7rem; color: #10b981; background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3);">
                            SIZING ANALYSIS READY
                        </span>
                    </div>
                </div>

                <!-- 4 Cost Pillars Breakdown -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px;">
                    <div style="background: rgba(0,0,0,0.35); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.675rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">1. Floor Area Build:</div>
                        <div id="costingBreakdownFloor" style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; color: #38bdf8; margin-top: 2px;">₱0.00</div>
                        <div id="costingSubFloorRate" style="font-size: 0.65rem; color: var(--text-secondary); margin-top: 2px;">0 m² @ ₱1,100/m²</div>
                    </div>

                    <div style="background: rgba(0,0,0,0.35); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.675rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">2. Site / Land Prep:</div>
                        <div id="costingBreakdownLand" style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; color: #10b981; margin-top: 2px;">₱0.00</div>
                        <div id="costingSubLandRate" style="font-size: 0.65rem; color: var(--text-secondary); margin-top: 2px;">0 m² @ ₱200/m²</div>
                    </div>

                    <div style="background: rgba(0,0,0,0.35); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.675rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">3. Spatial Fit-Out:</div>
                        <div id="costingBreakdownRooms" style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 700; color: #f59e0b; margin-top: 2px;">₱0.00</div>
                        <div id="costingSubRoomsCount" style="font-size: 0.65rem; color: var(--text-secondary); margin-top: 2px;">0 Rooms (1.00x Tier)</div>
                    </div>

                    <div style="background: rgba(0,0,0,0.35); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="font-size: 0.675rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">4. Engineering Estimate:</div>
                        <div id="costingTotalCalculated" style="font-family: var(--font-mono); font-size: 1.05rem; font-weight: 800; color: #ec4899; margin-top: 2px;">₱0.00</div>
                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;">Sum of 1 + 2 + 3</div>
                    </div>
                </div>

                <!-- Live Client Budget vs Engineering Estimate Comparison Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1.2fr; gap: 12px; padding: 12px 14px; background: rgba(0,0,0,0.45); border-radius: var(--radius-sm); border: 1px solid rgba(255,255,255,0.1); align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Client Stated Budget:</div>
                        <div id="costingCompareClientBudget" style="font-family: var(--font-mono); font-weight: 700; font-size: 1.05rem; color: #38bdf8;">₱ 0.00 (Unspecified)</div>
                    </div>

                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Calculated Cost:</div>
                        <div id="costingCompareEstCost" style="font-family: var(--font-mono); font-weight: 800; font-size: 1.15rem; color: #10b981;">₱ 0.00</div>
                    </div>

                    <div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Budget Margin / Variance:</div>
                        <div id="costingVarianceDisplay" style="font-family: var(--font-mono); font-weight: 800; font-size: 1.05rem; color: var(--text-muted);">
                            ₱ 0.00 (0.0%)
                        </div>
                        <div id="costingFeasibilityNote" style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">
                            Enter client stated budget to evaluate financial health
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('createProjectModal')">Cancel</button>
                <button type="submit" class="btn-primary">Initialize Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit & Adjust Existing Project -->
<div class="modal-overlay" id="editProjectModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700;">Edit Project Specifications & Adjust Settings</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Update project details, financial contract, schedule, weights, and workforce</span>
            </div>
            <button onclick="closeModal('editProjectModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="editProjectForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Project Title</label>
                    <input type="text" name="title" id="edit_title" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Project Code</label>
                    <input type="text" name="project_code" id="edit_project_code" class="form-input" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Client / Developer Name</label>
                    <input type="text" name="client_name" id="edit_client_name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Site Location / Address</label>
                    <input type="text" name="location" id="edit_location" class="form-input">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Project Classification</label>
                    <select name="project_type" id="edit_project_type" class="form-select" required>
                        <option value="Commercial Construction">Commercial Construction</option>
                        <option value="Residential Build">Residential Build</option>
                        <option value="Industrial Complex">Industrial Complex</option>
                        <option value="High-Rise Development">High-Rise Development</option>
                        <option value="Institutional Facility">Institutional Facility</option>
                        <option value="Infrastructure & Roadwork">Infrastructure & Roadwork</option>
                        <option value="Renovation & Overhaul">Renovation & Overhaul</option>
                        <option value="Interior Fit-Out & Turnkey">Interior Fit-Out & Turnkey</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Finishing Tier</label>
                    <select name="finish_tier" id="edit_finish_tier" class="form-select">
                        <option value="standard">Standard / Basic (1.00x)</option>
                        <option value="executive">Semi-Custom (1.25x)</option>
                        <option value="luxury">Luxury Estate (1.50x)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-select" onchange="toggleEditCompletionDate()" required>
                        <option value="in_progress">In Progress</option>
                        <option value="approved">Approved / Planned</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed / Turned Over</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1.2fr 1.2fr 1.2fr; gap: 12px;">
                <div class="form-group">
                    <label class="form-label">Land (m²)</label>
                    <input type="number" step="0.01" name="land_area_sqm" id="edit_land_area_sqm" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Floor (m²)</label>
                    <input type="number" step="0.01" name="floor_area_sqm" id="edit_floor_area_sqm" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" style="color: #38bdf8;">Client Budget (₱)</label>
                    <input type="number" step="0.01" name="client_budget" id="edit_client_budget" class="form-input" style="color: #38bdf8; font-weight: 700; font-family: var(--font-mono);">
                </div>

                <div class="form-group">
                    <label class="form-label" style="color: #ec4899;">Est. Cost (₱)</label>
                    <input type="number" step="0.01" name="estimated_cost" id="edit_estimated_cost" class="form-input" style="color: #ec4899; font-weight: 700; font-family: var(--font-mono);">
                </div>

                <div class="form-group">
                    <label class="form-label" style="color: #10b981;">Contract Budget (₱)</label>
                    <input type="number" step="0.01" name="contract_budget" id="edit_contract_budget" class="form-input" style="color: #10b981; font-weight: 700; font-family: var(--font-mono);" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" id="edit_start_date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Completion Date</label>
                    <input type="date" name="end_date" id="edit_end_date" class="form-input" required>
                </div>
            </div>

            <div class="form-group" id="editActualCompletionGroup" style="display: none;">
                <label class="form-label" style="color: #10b981;">Actual Handover / Turnover Date</label>
                <input type="date" name="actual_completion_date" id="edit_actual_completion_date" class="form-input">
            </div>

            <!-- Project Hero Blueprint & 3D Render Image Setup -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid rgba(236, 72, 153, 0.35);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #ec4899; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <span>[CAD]</span> Update Project Hero Image / CAD Blueprint
                </div>

                <div id="indexEditPhotoPreviewWrap" style="display: flex; gap: 14px; margin-bottom: 12px; padding: 10px; background: rgba(0,0,0,0.4); border-radius: var(--radius-sm); align-items: center;">
                    <div style="width: 100px; height: 75px; border-radius: 4px; overflow: hidden; background: #000; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.2);">
                        <img id="indexEditPhotoPreviewImg" src="" alt="Selected Preview" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="font-size: 0.75rem; color: #38bdf8;">
                        Current hero thumbnail. Select a new file or type a URL below to change it.
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.75rem;">Upload New File (JPG, PNG, WebP up to 10MB)</label>
                        <input type="file" name="project_photo_file" class="form-input" accept="image/*" onchange="previewIndexEditPhotoFile(this)">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.75rem;">Or Enter New Image Web URL</label>
                        <input type="text" name="project_photo_url" id="edit_project_photo_url" class="form-input" placeholder="https://example.com/render.jpg" oninput="previewIndexEditPhotoUrl(this.value)">
                    </div>
                </div>
            </div>

            <!-- Trade Weighting & Progress Adjustments -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #f59e0b; margin-bottom: 10px;">Engineering Progression Formula Weights & Completion Progress</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #38bdf8;">Structural Weight %</label>
                        <input type="number" name="structural_weight" id="edit_structural_weight" class="form-input" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #38bdf8; margin-top: 4px;">Progress %</label>
                        <input type="number" name="structural_progress" id="edit_structural_progress" class="form-input" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #f59e0b;">Electrical Weight %</label>
                        <input type="number" name="electrical_weight" id="edit_electrical_weight" class="form-input" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #f59e0b; margin-top: 4px;">Progress %</label>
                        <input type="number" name="electrical_progress" id="edit_electrical_progress" class="form-input" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #10b981;">Piping Weight %</label>
                        <input type="number" name="piping_weight" id="edit_piping_weight" class="form-input" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #10b981; margin-top: 4px;">Progress %</label>
                        <input type="number" name="piping_progress" id="edit_piping_progress" class="form-input" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #ec4899;">Finishing Weight %</label>
                        <input type="number" name="finishing_weight" id="edit_finishing_weight" class="form-input" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #ec4899; margin-top: 4px;">Progress %</label>
                        <input type="number" name="finishing_progress" id="edit_finishing_progress" class="form-input" min="0" max="100">
                    </div>
                </div>
            </div>

            <!-- Workforce Headcounts -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #38bdf8; margin-bottom: 10px;">On-Site Workforce Deployment (Headcount)</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">General Laborers</label>
                        <input type="number" name="deployed_workers" id="edit_deployed_workers" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Skilled Trades</label>
                        <input type="number" name="deployed_skilled_workers" id="edit_deployed_skilled_workers" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Field Engineers</label>
                        <input type="number" name="deployed_engineers" id="edit_deployed_engineers" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Architects</label>
                        <input type="number" name="deployed_architects" id="edit_deployed_architects" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Site Foremen</label>
                        <input type="number" name="deployed_foremen" id="edit_deployed_foremen" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Equipment Ops</label>
                        <input type="number" name="deployed_operators" id="edit_deployed_operators" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Safety Officers</label>
                        <input type="number" name="deployed_safety_officers" id="edit_deployed_safety_officers" class="form-input" min="0">
                    </div>
                </div>
            </div>

            <!-- Assigned Personnel Checkboxes -->
            @if(isset($personnelList) && $personnelList->count() > 0)
            <div style="margin-top: 14px;">
                <label class="form-label">Assign Lead Engineers & Architects</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px; max-height: 120px; overflow-y: auto; padding: 10px; background: rgba(0,0,0,0.3); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    @foreach($personnelList as $pers)
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="personnel_ids[]" class="edit-personnel-checkbox" value="{{ $pers->id }}">
                        <span><strong>{{ $pers->name }}</strong> ({{ $pers->title }})</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="form-group" style="margin-top: 14px;">
                <label class="form-label">Project Scope & Technical Description</label>
                <textarea name="description" id="edit_description" class="form-textarea" rows="2"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editProjectModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Delete Project Confirmation -->
<div class="modal-overlay" id="deleteProjectModal">
    <div class="modal-box" style="max-width: 480px; border: 1px solid rgba(239, 68, 68, 0.4); box-shadow: 0 20px 30px rgba(0,0,0,0.6), 0 0 20px rgba(239, 68, 68, 0.2);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: bold; color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">
                    DEL
                </div>
                <div>
                    <h3 style="font-weight: 700; color: #f87171; margin: 0; font-size: 1.15rem;">Delete Project</h3>
                    <div style="font-size: 0.775rem; color: var(--text-muted);">St. Bilfrid Development Corporation</div>
                </div>
            </div>
            <button onclick="closeModal('deleteProjectModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; line-height: 1;">&times;</button>
        </div>

        <div style="background: rgba(239, 68, 68, 0.08); border-left: 3px solid #ef4444; padding: 12px 14px; border-radius: 4px; margin-bottom: 16px;">
            <div style="font-size: 0.8rem; color: #94a3b8;">Are you sure you want to permanently delete:</div>
            <div id="deleteProjectCodeDisplay" style="font-family: var(--font-mono); font-weight: 700; color: #ef4444; font-size: 0.95rem; margin-top: 2px;"></div>
            <div id="deleteProjectTitleDisplay" style="font-weight: 600; color: #f1f5f9; font-size: 0.9rem; margin-top: 2px;"></div>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 20px; font-size: 0.78rem; color: var(--text-secondary); line-height: 1.5; border: 1px solid var(--border-color);">
            <strong>Action Details:</strong> This will permanently delete this project, including its 53-item checklist, material allocations, BOM estimates, incurred expense logs, CAD blueprints/photos, and billing payment history.
        </div>

        <form id="deleteProjectForm" action="" method="POST">
            @csrf
            @method('DELETE')
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteProjectModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626); border-color: #dc2626; color: white; font-weight: 700;">
                    Permanently Delete Project
                </button>
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

    function openDeleteProjectModal(id, code, title) {
        const form = document.getElementById('deleteProjectForm');
        if (form) {
            form.action = '/projects/' + id;
        }
        const codeEl = document.getElementById('deleteProjectCodeDisplay');
        if (codeEl) {
            codeEl.innerText = code || ('PRJ-#' + id);
        }
        const titleEl = document.getElementById('deleteProjectTitleDisplay');
        if (titleEl) {
            titleEl.innerText = title || '';
        }
        openModal('deleteProjectModal');
    }

    function toggleEditCompletionDate() {
        const status = document.getElementById('edit_status').value;
        const grp = document.getElementById('editActualCompletionGroup');
        if (grp) {
            grp.style.display = (status === 'completed') ? 'block' : 'none';
        }
    }

    function openEditProjectModal(project) {
        document.getElementById('editProjectForm').action = '/projects/' + project.id;
        document.getElementById('edit_title').value = project.title || '';
        document.getElementById('edit_project_code').value = project.project_code || '';
        document.getElementById('edit_client_name').value = project.client_name || '';
        document.getElementById('edit_location').value = project.location || '';
        document.getElementById('edit_project_type').value = project.project_type || 'Commercial Construction';
        document.getElementById('edit_finish_tier').value = project.finish_tier || 'standard';
        document.getElementById('edit_status').value = project.status || 'in_progress';
        document.getElementById('edit_land_area_sqm').value = project.land_area_sqm || '';
        document.getElementById('edit_floor_area_sqm').value = project.floor_area_sqm || '';
        document.getElementById('edit_client_budget').value = project.client_budget || '';
        document.getElementById('edit_estimated_cost').value = project.estimated_cost || '';
        document.getElementById('edit_contract_budget').value = project.contract_budget || '';
        document.getElementById('edit_start_date').value = project.start_date ? project.start_date.substring(0, 10) : '';
        document.getElementById('edit_end_date').value = project.end_date ? project.end_date.substring(0, 10) : '';
        document.getElementById('edit_actual_completion_date').value = project.actual_completion_date ? project.actual_completion_date.substring(0, 10) : '';
        document.getElementById('edit_description').value = project.description || '';
        
        document.getElementById('edit_financing_type').value = project.financing_type || 'bank_loan';
        document.getElementById('edit_financing_institution').value = project.financing_institution || 'BDO Unibank';
        document.getElementById('edit_loan_account_no').value = project.loan_account_no || '';
        document.getElementById('edit_approved_loan_amount').value = project.approved_loan_amount || (project.contract_budget ? (project.contract_budget * 0.80) : 0);
        document.getElementById('edit_client_equity_amount').value = project.client_equity_amount || (project.contract_budget ? (project.contract_budget * 0.20) : 0);
        document.getElementById('edit_payment_first_policy').checked = (project.payment_first_policy !== false && project.payment_first_policy !== 0);

        document.getElementById('edit_structural_weight').value = project.structural_weight || 40;
        document.getElementById('edit_electrical_weight').value = project.electrical_weight || 25;
        document.getElementById('edit_piping_weight').value = project.piping_weight || 20;
        document.getElementById('edit_finishing_weight').value = project.finishing_weight || 15;

        document.getElementById('edit_structural_progress').value = project.structural_progress || 0;
        document.getElementById('edit_electrical_progress').value = project.electrical_progress || 0;
        document.getElementById('edit_piping_progress').value = project.piping_progress || 0;
        document.getElementById('edit_finishing_progress').value = project.finishing_progress || 0;

        document.getElementById('edit_deployed_workers').value = project.deployed_workers || 0;
        document.getElementById('edit_deployed_skilled_workers').value = project.deployed_skilled_workers || 0;
        document.getElementById('edit_deployed_engineers').value = project.deployed_engineers || 0;
        document.getElementById('edit_deployed_architects').value = project.deployed_architects || 0;
        document.getElementById('edit_deployed_foremen').value = project.deployed_foremen || 0;
        document.getElementById('edit_deployed_operators').value = project.deployed_operators || 0;
        document.getElementById('edit_deployed_safety_officers').value = project.deployed_safety_officers || 0;

        toggleEditCompletionDate();

        const heroPhoto = project.primary_photo || (project.photos && project.photos.length > 0 ? project.photos[0] : null);
        const editImg = document.getElementById('indexEditPhotoPreviewImg');
        if (editImg) {
            editImg.src = heroPhoto ? heroPhoto.file_path : '';
        }
        const urlInput = document.getElementById('edit_project_photo_url');
        if (urlInput) urlInput.value = '';

        const assignedIds = (project.personnel || []).map(p => p.id);
        document.querySelectorAll('.edit-personnel-checkbox').forEach(cb => {
            cb.checked = assignedIds.includes(parseInt(cb.value));
        });

        openModal('editProjectModal');
    }

    function previewIndexCreatePhotoFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('indexCreatePhotoPreviewImg');
                const wrap = document.getElementById('indexCreatePhotoPreviewWrap');
                if (previewImg) previewImg.src = e.target.result;
                if (wrap) wrap.style.display = 'flex';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewIndexCreatePhotoUrl(url) {
        if (url && url.trim()) {
            const previewImg = document.getElementById('indexCreatePhotoPreviewImg');
            const wrap = document.getElementById('indexCreatePhotoPreviewWrap');
            if (previewImg) previewImg.src = url.trim();
            if (wrap) wrap.style.display = 'flex';
        }
    }

    function previewIndexEditPhotoFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('indexEditPhotoPreviewImg');
                if (previewImg) previewImg.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewIndexEditPhotoUrl(url) {
        if (url && url.trim()) {
            const previewImg = document.getElementById('indexEditPhotoPreviewImg');
            if (previewImg) previewImg.src = url.trim();
        }
    }

    // ====================================================
    // STRUCTURED ROOM & MATERIAL MAPPING ENGINE
    // ====================================================
    const MODAL_ROOM_PRESETS = {
        master_bedroom: {
            label: "Master Bedroom Suite",
            category: "dry_living",
            icon: "",
            brackets: {
                small:     { label: "Compact Suite (16 m² — 4.0m × 4.0m)", sqm: 16.0 },
                medium:    { label: "Standard Suite (22 m² — 4.5m × 4.9m)", sqm: 22.0 },
                large:     { label: "Executive Master (30 m² — 5.0m × 6.0m)", sqm: 30.0 },
                executive: { label: "Penthouse Master (42 m² — 6.0m × 7.0m)", sqm: 42.0 }
            }
        },
        standard_bedroom: {
            label: "Standard Bedroom",
            category: "dry_living",
            icon: "",
            brackets: {
                small:  { label: "Compact Bedroom (10 m² — 3.0m × 3.3m)", sqm: 10.0 },
                medium: { label: "Standard Bedroom (14 m² — 3.5m × 4.0m)", sqm: 14.0 },
                large:  { label: "Spacious Bedroom (18 m² — 4.0m × 4.5m)", sqm: 18.0 }
            }
        },
        guest_bedroom: {
            label: "Guest Bedroom",
            category: "dry_living",
            icon: "",
            brackets: {
                small:  { label: "Compact Guest Room (10 m² — 3.0m × 3.3m)", sqm: 10.0 },
                medium: { label: "Standard Guest Room (14 m² — 3.5m × 4.0m)", sqm: 14.0 }
            }
        },
        master_bathroom: {
            label: "Full Master Bathroom (En-Suite)",
            category: "wet_area",
            icon: "",
            brackets: {
                small:  { label: "Compact Full Bath (5 m² — 2.0m × 2.5m)", sqm: 5.0 },
                medium: { label: "Standard Master Bath (8 m² — 2.5m × 3.2m)", sqm: 8.0 },
                large:  { label: "Luxury Spa Bath (12 m² — 3.0m × 4.0m)", sqm: 12.0 }
            }
        },
        powder_room: {
            label: "Common Bathroom / Powder Room",
            category: "wet_area",
            icon: "",
            brackets: {
                small:  { label: "Compact Powder Room (2.5 m² — 1.5m × 1.7m)", sqm: 2.5 },
                medium: { label: "Standard Powder Room (4.0 m² — 2.0m × 2.0m)", sqm: 4.0 }
            }
        },
        kitchen: {
            label: "Kitchen & Pantry",
            category: "culinary",
            icon: "",
            brackets: {
                small:  { label: "Galley Kitchen (9 m² — 2.5m × 3.6m)", sqm: 9.0 },
                medium: { label: "Open Concept Kitchen (15 m² — 3.5m × 4.3m)", sqm: 15.0 },
                large:  { label: "Chef's Kitchen with Island (24 m² — 4.0m × 6.0m)", sqm: 24.0 }
            }
        },
        living_dining: {
            label: "Living & Dining Great Room",
            category: "dry_living",
            icon: "",
            brackets: {
                small:  { label: "Compact Living-Dining (24 m² — 4.0m × 6.0m)", sqm: 24.0 },
                medium: { label: "Standard Great Room (36 m² — 6.0m × 6.0m)", sqm: 36.0 },
                large:  { label: "Grand Open Living (50 m² — 7.0m × 7.1m)", sqm: 50.0 }
            }
        },
        home_office: {
            label: "Home Office / Study",
            category: "workspace",
            icon: "",
            brackets: {
                small:  { label: "Work Nook (8 m² — 2.5m × 3.2m)", sqm: 8.0 },
                medium: { label: "Standard Executive Office (14 m² — 3.5m × 4.0m)", sqm: 14.0 }
            }
        },
        utility_laundry: {
            label: "Laundry & Utility Room",
            category: "wet_area",
            icon: "",
            brackets: {
                small:  { label: "Utility Closet (4 m² — 2.0m × 2.0m)", sqm: 4.0 },
                medium: { label: "Full Laundry & Storage (8 m² — 2.5m × 3.2m)", sqm: 8.0 }
            }
        },
        balcony: {
            label: "Balcony / Covered Terrace",
            category: "outdoor",
            icon: "",
            brackets: {
                small:  { label: "Veranda Balcony (6 m² — 2.0m × 3.0m)", sqm: 6.0 },
                medium: { label: "Spacious Terrace (12 m² — 3.0m × 4.0m)", sqm: 12.0 }
            }
        }
    };

    let activeModalRooms = [];

    function initProjectModalRooms() {
        applyModalFloorPlanPreset('2br_bungalow');
    }

    function applyModalFloorPlanPreset(model) {
        if (model === 'studio') {
            activeModalRooms = [
                { id: 'rm_1', type: 'master_bedroom', bracket: 'small', sqm: 16.0 },
                { id: 'rm_2', type: 'powder_room', bracket: 'small', sqm: 2.5 },
                { id: 'rm_3', type: 'kitchen', bracket: 'small', sqm: 9.0 },
                { id: 'rm_4', type: 'living_dining', bracket: 'small', sqm: 24.0 }
            ];
        } else if (model === '2br_bungalow') {
            activeModalRooms = [
                { id: 'rm_1', type: 'master_bedroom', bracket: 'medium', sqm: 22.0 },
                { id: 'rm_2', type: 'standard_bedroom', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_3', type: 'master_bathroom', bracket: 'small', sqm: 5.0 },
                { id: 'rm_4', type: 'kitchen', bracket: 'medium', sqm: 15.0 },
                { id: 'rm_5', type: 'living_dining', bracket: 'small', sqm: 24.0 }
            ];
        } else if (model === '3br_standard') {
            activeModalRooms = [
                { id: 'rm_1', type: 'master_bedroom', bracket: 'large', sqm: 30.0 },
                { id: 'rm_2', type: 'standard_bedroom', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_3', type: 'guest_bedroom', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_4', type: 'master_bathroom', bracket: 'medium', sqm: 8.0 },
                { id: 'rm_5', type: 'powder_room', bracket: 'medium', sqm: 4.0 },
                { id: 'rm_6', type: 'kitchen', bracket: 'medium', sqm: 15.0 },
                { id: 'rm_7', type: 'living_dining', bracket: 'medium', sqm: 36.0 }
            ];
        } else if (model === '4br_executive') {
            activeModalRooms = [
                { id: 'rm_1', type: 'master_bedroom', bracket: 'executive', sqm: 42.0 },
                { id: 'rm_2', type: 'standard_bedroom', bracket: 'large', sqm: 18.0 },
                { id: 'rm_3', type: 'standard_bedroom', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_4', type: 'guest_bedroom', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_5', type: 'master_bathroom', bracket: 'large', sqm: 12.0 },
                { id: 'rm_6', type: 'powder_room', bracket: 'medium', sqm: 4.0 },
                { id: 'rm_7', type: 'kitchen', bracket: 'large', sqm: 24.0 },
                { id: 'rm_8', type: 'living_dining', bracket: 'large', sqm: 50.0 },
                { id: 'rm_9', type: 'home_office', bracket: 'standard', sqm: 14.0 },
                { id: 'rm_10', type: 'utility_laundry', bracket: 'medium', sqm: 8.0 }
            ];
        } else if (model === '5br_luxury') {
            activeModalRooms = [
                { id: 'rm_1', type: 'master_bedroom', bracket: 'executive', sqm: 42.0 },
                { id: 'rm_2', type: 'standard_bedroom', bracket: 'large', sqm: 18.0 },
                { id: 'rm_3', type: 'standard_bedroom', bracket: 'large', sqm: 18.0 },
                { id: 'rm_4', type: 'guest_bedroom', bracket: 'large', sqm: 18.0 },
                { id: 'rm_5', type: 'guest_bedroom', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_6', type: 'master_bathroom', bracket: 'large', sqm: 12.0 },
                { id: 'rm_7', type: 'master_bathroom', bracket: 'medium', sqm: 8.0 },
                { id: 'rm_8', type: 'powder_room', bracket: 'medium', sqm: 4.0 },
                { id: 'rm_9', type: 'kitchen', bracket: 'large', sqm: 24.0 },
                { id: 'rm_10', type: 'living_dining', bracket: 'large', sqm: 50.0 },
                { id: 'rm_11', type: 'home_office', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_12', type: 'utility_laundry', bracket: 'medium', sqm: 8.0 },
                { id: 'rm_13', type: 'balcony', bracket: 'medium', sqm: 12.0 }
            ];
        } else if (activeModalRooms.length === 0) {
            activeModalRooms = [
                { id: 'rm_1', type: 'master_bedroom', bracket: 'medium', sqm: 22.0 },
                { id: 'rm_2', type: 'standard_bedroom', bracket: 'medium', sqm: 14.0 },
                { id: 'rm_3', type: 'kitchen', bracket: 'medium', sqm: 15.0 },
                { id: 'rm_4', type: 'living_dining', bracket: 'medium', sqm: 36.0 }
            ];
        }

        const roomCountSelect = document.getElementById('modalTotalRoomCountSelect');
        if (roomCountSelect) {
            roomCountSelect.value = activeModalRooms.length > 12 ? '12' : (activeModalRooms.length > 8 ? '10' : activeModalRooms.length);
        }

        renderModalRoomRows();
    }

    function setModalTotalRoomsCount(targetCount) {
        targetCount = parseInt(targetCount) || 1;
        const defaultSequence = [
            'master_bedroom', 'master_bathroom', 'kitchen', 'living_dining',
            'standard_bedroom', 'powder_room', 'guest_bedroom', 'home_office',
            'utility_laundry', 'balcony', 'standard_bedroom', 'master_bathroom'
        ];

        while (activeModalRooms.length < targetCount) {
            const nextType = defaultSequence[activeModalRooms.length] || 'standard_bedroom';
            const defaultBracket = Object.keys(MODAL_ROOM_PRESETS[nextType].brackets)[0];
            activeModalRooms.push({
                id: 'rm_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
                type: nextType,
                bracket: defaultBracket,
                sqm: MODAL_ROOM_PRESETS[nextType].brackets[defaultBracket].sqm
            });
        }

        if (activeModalRooms.length > targetCount) {
            activeModalRooms = activeModalRooms.slice(0, targetCount);
        }

        const presetSelect = document.getElementById('modalFloorPlanModelSelect');
        if (presetSelect) presetSelect.value = 'custom';

        renderModalRoomRows();
    }

    function addModalRoomRow() {
        const defaultBracket = Object.keys(MODAL_ROOM_PRESETS.standard_bedroom.brackets)[0];
        activeModalRooms.push({
            id: 'rm_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
            type: 'standard_bedroom',
            bracket: defaultBracket,
            sqm: MODAL_ROOM_PRESETS.standard_bedroom.brackets[defaultBracket].sqm
        });

        const presetSelect = document.getElementById('modalFloorPlanModelSelect');
        if (presetSelect) presetSelect.value = 'custom';

        const roomCountSelect = document.getElementById('modalTotalRoomCountSelect');
        if (roomCountSelect) {
            roomCountSelect.value = activeModalRooms.length > 12 ? '12' : (activeModalRooms.length > 8 ? '10' : activeModalRooms.length);
        }

        renderModalRoomRows();
    }

    function removeModalRoomRow(roomId) {
        if (activeModalRooms.length <= 1) {
            alert('A project layout must contain at least 1 room.');
            return;
        }
        activeModalRooms = activeModalRooms.filter(r => r.id !== roomId);

        const presetSelect = document.getElementById('modalFloorPlanModelSelect');
        if (presetSelect) presetSelect.value = 'custom';

        const roomCountSelect = document.getElementById('modalTotalRoomCountSelect');
        if (roomCountSelect) {
            roomCountSelect.value = activeModalRooms.length > 12 ? '12' : (activeModalRooms.length > 8 ? '10' : activeModalRooms.length);
        }

        renderModalRoomRows();
    }

    function onModalRoomTypeChange(roomId, newType) {
        const room = activeModalRooms.find(r => r.id === roomId);
        if (!room) return;
        room.type = newType;
        const defaultBracket = Object.keys(MODAL_ROOM_PRESETS[newType].brackets)[0];
        room.bracket = defaultBracket;
        room.sqm = MODAL_ROOM_PRESETS[newType].brackets[defaultBracket].sqm;

        const presetSelect = document.getElementById('modalFloorPlanModelSelect');
        if (presetSelect) presetSelect.value = 'custom';

        renderModalRoomRows();
    }

    function onModalRoomBracketChange(roomId, newBracket) {
        const room = activeModalRooms.find(r => r.id === roomId);
        if (!room) return;
        room.bracket = newBracket;
        room.sqm = MODAL_ROOM_PRESETS[room.type].brackets[newBracket].sqm;

        const presetSelect = document.getElementById('modalFloorPlanModelSelect');
        if (presetSelect) presetSelect.value = 'custom';

        renderModalRoomRows();
    }

    function renderModalRoomRows() {
        const container = document.getElementById('modalRoomRowsContainer');
        if (!container) return;
        container.innerHTML = '';

        let totalFloorSqm = 0;

        activeModalRooms.forEach((room, index) => {
            totalFloorSqm += room.sqm;

            const row = document.createElement('div');
            row.style.cssText = 'display: grid; grid-template-columns: 32px 2fr 3fr 85px 30px; gap: 8px; align-items: center; background: rgba(0,0,0,0.3); padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border-color);';

            let typeOpts = '';
            for (const [key, preset] of Object.entries(MODAL_ROOM_PRESETS)) {
                typeOpts += `<option value="${key}" ${room.type === key ? 'selected' : ''}>${preset.icon} ${preset.label}</option>`;
            }

            const currentPreset = MODAL_ROOM_PRESETS[room.type] || MODAL_ROOM_PRESETS.standard_bedroom;
            let bracketOpts = '';
            for (const [bKey, bVal] of Object.entries(currentPreset.brackets)) {
                bracketOpts += `<option value="${bKey}" ${room.bracket === bKey ? 'selected' : ''}>${bVal.label}</option>`;
            }

            row.innerHTML = `
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">#${index + 1}</span>
                <select class="form-select" style="font-size: 0.775rem; height: 30px; padding: 2px 6px;" onchange="onModalRoomTypeChange('${room.id}', this.value)">
                    ${typeOpts}
                </select>
                <select class="form-select" style="font-size: 0.775rem; height: 30px; padding: 2px 6px;" onchange="onModalRoomBracketChange('${room.id}', this.value)">
                    ${bracketOpts}
                </select>
                <div style="font-family: var(--font-mono); font-size: 0.8rem; color: #38bdf8; text-align: right; font-weight: 700;">
                    ${room.sqm.toFixed(1)} m²
                </div>
                <button type="button" onclick="removeModalRoomRow('${room.id}')" style="background: none; border: none; color: #ef4444; font-size: 1.2rem; cursor: pointer; text-align: center;">&times;</button>
            `;

            container.appendChild(row);
        });

        const totalLandSqm = Math.round(totalFloorSqm * 1.35 * 100) / 100;

        // Update Display Badges
        document.getElementById('modalDispRoomCount').innerText = activeModalRooms.length;
        document.getElementById('modalDispFloorArea').innerText = totalFloorSqm.toFixed(2) + ' m²';
        document.getElementById('modalDispLandArea').innerText = totalLandSqm.toFixed(2) + ' m²';

        // Auto-Populate Inputs
        const inputFloor = document.getElementById('modalInputFloorArea');
        const inputLand = document.getElementById('modalInputLandArea');
        const inputBudget = document.getElementById('modalInputContractBudget');

        if (inputFloor) inputFloor.value = totalFloorSqm.toFixed(2);
        if (inputLand) inputLand.value = totalLandSqm.toFixed(2);

        document.getElementById('modalInputRoomProgramJson').value = JSON.stringify(activeModalRooms);

        // Run Real-Time Cost Estimation & Budget Feasibility Analysis
        recalculateProjectModalCosting();
    }

    function recalculateProjectModalCosting() {
        const projectTypeEl = document.getElementById('modalInputProjectType');
        const finishTierEl = document.getElementById('modalInputFinishTier');
        const floorAreaEl = document.getElementById('modalInputFloorArea');
        const landAreaEl = document.getElementById('modalInputLandArea');
        const clientBudgetEl = document.getElementById('modalInputClientBudget');
        const contractBudgetEl = document.getElementById('modalInputContractBudget');
        const estCostHiddenEl = document.getElementById('modalInputEstimatedCost');

        const projectType = projectTypeEl ? projectTypeEl.value : 'Residential Build';
        const finishTier = finishTierEl ? finishTierEl.value : 'standard';
        const floorArea = parseFloat(floorAreaEl ? floorAreaEl.value : 0) || 0;
        const landArea = parseFloat(landAreaEl ? landAreaEl.value : 0) || 0;
        const clientBudget = parseFloat(clientBudgetEl ? clientBudgetEl.value : 0) || 0;

        // Base rates per project type from benchmark
        let baseRatePerSqm = 1100;
        if (projectType === 'Commercial Construction') baseRatePerSqm = 1400;
        else if (projectType === 'Industrial Complex') baseRatePerSqm = 1600;
        else if (projectType === 'High-Rise Development') baseRatePerSqm = 1800;
        else if (projectType === 'Renovation & Overhaul') baseRatePerSqm = 800;
        else if (projectType === 'Interior Fit-Out & Turnkey') baseRatePerSqm = 950;

        // Finish multiplier
        let finishMultiplier = 1.0;
        let finishLabel = '1.00x Base';
        if (finishTier === 'executive') {
            finishMultiplier = 1.25;
            finishLabel = '1.25x Exec';
        } else if (finishTier === 'luxury') {
            finishMultiplier = 1.50;
            finishLabel = '1.50x Lux';
        }

        // Cost Breakdown components
        const floorCost = Math.round(floorArea * baseRatePerSqm * finishMultiplier);
        const landPrepCost = Math.round(landArea * 200);
        const roomCount = (typeof activeModalRooms !== 'undefined' && activeModalRooms) ? activeModalRooms.length : 0;
        const roomFitoutCost = Math.round(roomCount * 8500 * finishMultiplier);

        const totalEstimatedCost = floorCost + landPrepCost + roomFitoutCost;

        // Update Hidden Input for backend storage
        if (estCostHiddenEl) estCostHiddenEl.value = totalEstimatedCost;

        // If contract budget is empty, default it to estimated cost
        if (contractBudgetEl && (!contractBudgetEl.value || contractBudgetEl.value == 0)) {
            contractBudgetEl.value = totalEstimatedCost;
        }

        // Update Breakdown Displays
        const dispFloor = document.getElementById('costingBreakdownFloor');
        const dispLand = document.getElementById('costingBreakdownLand');
        const dispRooms = document.getElementById('costingBreakdownRooms');
        const dispTotal = document.getElementById('costingTotalCalculated');

        const subFloorRate = document.getElementById('costingSubFloorRate');
        const subLandRate = document.getElementById('costingSubLandRate');
        const subRoomsCount = document.getElementById('costingSubRoomsCount');

        if (dispFloor) dispFloor.innerText = '₱' + floorCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (subFloorRate) subFloorRate.innerText = `${floorArea.toFixed(1)} m² @ ₱${baseRatePerSqm}/m² (${finishMultiplier}x)`;

        if (dispLand) dispLand.innerText = '₱' + landPrepCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (subLandRate) subLandRate.innerText = `${landArea.toFixed(1)} m² @ ₱200/m²`;

        if (dispRooms) dispRooms.innerText = '₱' + roomFitoutCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (subRoomsCount) subRoomsCount.innerText = `${roomCount} Rooms (${finishLabel})`;

        if (dispTotal) dispTotal.innerText = '₱' + totalEstimatedCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Comparison Display
        const compClient = document.getElementById('costingCompareClientBudget');
        const compEst = document.getElementById('costingCompareEstCost');
        const compVariance = document.getElementById('costingVarianceDisplay');
        const compNote = document.getElementById('costingFeasibilityNote');
        const healthBadge = document.getElementById('modalCostingHealthBadge');

        if (compClient) compClient.innerText = clientBudget > 0 ? ('₱ ' + clientBudget.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) : '₱ 0.00 (Unspecified)';
        if (compEst) compEst.innerText = '₱ ' + totalEstimatedCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (clientBudget > 0) {
            const variance = clientBudget - totalEstimatedCost;
            const marginPct = (variance / clientBudget) * 100;
            const formattedVariance = (variance >= 0 ? '+₱ ' : '-₱ ') + Math.abs(variance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            if (compVariance) {
                compVariance.innerText = `${formattedVariance} (${marginPct.toFixed(1)}%)`;
                compVariance.style.color = variance >= 0 ? '#10b981' : '#ef4444';
            }

            if (variance >= 0) {
                if (marginPct >= 10) {
                    if (healthBadge) {
                        healthBadge.innerText = 'EXCELLENT BUDGET MARGIN';
                        healthBadge.style.color = '#10b981';
                        healthBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                        healthBadge.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                    }
                    if (compNote) compNote.innerText = 'Client budget has comfortable margin over engineering estimate.';
                } else {
                    if (healthBadge) {
                        healthBadge.innerText = 'TIGHT BUDGET BUFFER';
                        healthBadge.style.color = '#f59e0b';
                        healthBadge.style.background = 'rgba(245, 158, 11, 0.15)';
                        healthBadge.style.borderColor = 'rgba(245, 158, 11, 0.3)';
                    }
                    if (compNote) compNote.innerText = 'Budget covers baseline with slim margin (<10%). Monitor expenses.';
                }
            } else {
                if (healthBadge) {
                    healthBadge.innerText = 'BUDGET DEFICIT / DEFICIENT';
                    healthBadge.style.color = '#ef4444';
                    healthBadge.style.background = 'rgba(239, 68, 68, 0.15)';
                    healthBadge.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                }
                if (compNote) compNote.innerText = 'Client budget is below calculated engineering cost. Overrun risk.';
            }
        } else {
            if (compVariance) {
                compVariance.innerText = '₱ 0.00 (0.0%)';
                compVariance.style.color = 'var(--text-muted)';
            }
            if (compNote) compNote.innerText = 'Enter client stated budget to calculate live margin & feasibility.';
            if (healthBadge) {
                healthBadge.innerText = 'ESTIMATE COMPUTED';
                healthBadge.style.color = '#38bdf8';
                healthBadge.style.background = 'rgba(56, 189, 248, 0.15)';
                healthBadge.style.borderColor = 'rgba(56, 189, 248, 0.3)';
            }
        }
    }

    function applyCalculatedToContractBudget() {
        const estVal = document.getElementById('modalInputEstimatedCost').value;
        const contractBudgetInput = document.getElementById('modalInputContractBudget');
        if (contractBudgetInput && estVal) {
            contractBudgetInput.value = estVal;
            contractBudgetInput.style.transition = 'all 0.3s ease';
            contractBudgetInput.style.boxShadow = '0 0 12px rgba(16, 185, 129, 0.8)';
            setTimeout(() => contractBudgetInput.style.boxShadow = 'none', 1000);
        }
    }

    function applyClientBudgetToContractBudget() {
        const clientVal = document.getElementById('modalInputClientBudget').value;
        const contractBudgetInput = document.getElementById('modalInputContractBudget');
        if (contractBudgetInput && clientVal && parseFloat(clientVal) > 0) {
            contractBudgetInput.value = clientVal;
            contractBudgetInput.style.transition = 'all 0.3s ease';
            contractBudgetInput.style.boxShadow = '0 0 12px rgba(56, 189, 248, 0.8)';
            setTimeout(() => contractBudgetInput.style.boxShadow = 'none', 1000);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initProjectModalRooms();
    });
</script>
@endsection
