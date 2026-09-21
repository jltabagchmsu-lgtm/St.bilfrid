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
                    <strong style="color: var(--text-primary);">{{ $prj->client_name }}</strong>
                    <div style="font-size: 0.75rem; color: #0f172a; margin-top: 3px; font-weight: 600;">
                        {{ $completedTasks }} / {{ $totalTasks }} Tasks Done ({{ $prj->overall_progress }}%)
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="spec-chip" style="color: #0f172a;">Land: {{ number_format($prj->land_area_sqm) }} m²</span>
                        <span class="spec-chip" style="color: #0f172a;">Floor: {{ number_format($prj->floor_area_sqm) }} m²</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 3px;">
                        <div style="font-weight: 800; font-family: var(--font-mono); color: #0f172a; font-size: 0.95rem;">
                            {{ $prj->total_deployed_manpower }} Headcount
                        </div>
                        <div style="font-size: 0.725rem; color: #0f172a;">
                            {{ $prj->deployed_workers }} Workers &bull; {{ $prj->deployed_engineers }} Engr
                        </div>
                        <div style="font-size: 0.725rem; color: #0f172a;">
                            {{ $prj->deployed_architects }} Arch &bull; {{ $prj->deployed_operators }} Ops
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="spec-chip" style="background: {{ $schedHealth['bg'] }}; color: #0f172a; border-color: {{ $schedHealth['border'] }}; font-size: 0.7rem; font-weight: 700;">
                            {{ $schedHealth['label'] }}
                        </span>
                        <div style="font-size: 0.75rem; color: #0f172a; font-family: var(--font-mono);">
                            {{ $prj->remaining_days }}d left &bull; {{ $prj->end_date->format('M d, Y') }}
                        </div>
                    </div>
                </td>
                <td style="min-width: 170px;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                        <span style="font-weight: 800; font-family: var(--font-mono); font-size: 1.15rem; color: #0f172a;">{{ $prj->overall_progress }}%</span>
                        <span style="font-size: 0.7rem; color: #0f172a;">Weighted Total</span>
                    </div>
                    <div class="progress-track" style="height: 6px; margin-bottom: 6px;">
                        <div class="progress-bar progress-bar-structural" style="width: {{ $prj->overall_progress }}%; background: {{ $prj->overall_progress >= 100 ? '#10b981' : ($prj->overall_progress > 0 ? '#d97706' : '#64748b') }};"></div>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                        <span style="color: #38bdf8; font-weight: 600;">S: {{ $prj->structural_progress }}%</span>
                        <span style="color: #f59e0b; font-weight: 600;">E: {{ $prj->electrical_progress }}%</span>
                        <span style="color: #10b981; font-weight: 600;">P: {{ $prj->piping_progress }}%</span>
                        <span style="color: #ec4899; font-weight: 600;">F: {{ $prj->finishing_progress }}%</span>
                    </div>
                </td>
                <td>
                    <div style="font-size: 0.85rem;">
                        <div style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: #0f172a; letter-spacing: 0.04em;">Contract Budget (Sales):</div>
                        <div style="font-family: var(--font-mono); font-weight: 800; font-size: 1.15rem; color: #0f172a; line-height: 1.2;">
                            ₱{{ number_format($prj->contract_budget, 2) }}
                        </div>
                    </div>
                    @if($prj->client_budget && $prj->client_budget > 0)
                    <div style="font-size: 0.775rem; color: #0f172a; margin-top: 3px; font-weight: 600;">
                        Client: <span style="font-family: var(--font-mono); font-weight: 700;">₱{{ number_format($prj->client_budget, 2) }}</span>
                    </div>
                    @endif
                    @if($prj->estimated_cost && $prj->estimated_cost > 0)
                    <div style="font-size: 0.75rem; color: #0f172a; margin-top: 1px;">
                        Est: <span style="font-family: var(--font-mono); font-weight: 700;">₱{{ number_format($prj->estimated_cost, 2) }}</span>
                    </div>
                    @endif
                    <div style="font-size: 0.825rem; margin-top: 5px; border-top: 1px dashed var(--border-color); padding-top: 4px;">
                        <div style="font-size: 0.7rem; color: #0f172a; font-weight: 600;">Incurred Cost:</div>
                        <div style="font-family: var(--font-mono); color: #0f172a; font-weight: 800; font-size: 1.05rem;">
                            ₱{{ number_format($prj->total_incurred_cost, 2) }} <span style="font-size: 0.725rem; font-weight: 600;">({{ number_format($prj->budget_consumption_pct, 1) }}%)</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #0f172a; margin-top: 2px; font-weight: 700;">
                            Margin: <span style="font-family: var(--font-mono); font-weight: 800; font-size: 0.95rem;">₱{{ number_format($prj->gross_margin, 2) }}</span> ({{ $prj->gross_margin_percent }}%)
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <a href="{{ route('projects.show', $prj->id) }}" class="btn-primary" style="font-size: 0.75rem; padding: 4px 8px; text-align: center;">
                            Master View &rarr;
                        </a>
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

<!-- Modal 1: Initialize New Real Project (Multi-Step Wizard Flow) -->
<div class="modal-overlay" id="createProjectModal">
    <div class="modal-box modal-box-landscape" style="max-width: 1260px; width: 96vw; max-height: 92vh; display: flex; flex-direction: column; padding: 22px 28px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); margin-bottom: 14px;">
            <div>
                <h3 style="font-weight: 800; font-size: 1.25rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <span style="color: var(--primary-red);">+</span> Initialize Real Construction Project
                </h3>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                    Configure project identification, architectural floor plan model, engineering parameters, workforce headcounts, and live feasibility costing.
                </p>
            </div>
            <button type="button" onclick="closeModal('createProjectModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Sleek Step Progress Stepper Bar -->
        <div class="project-modal-stepper" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 14px; padding: 8px 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                <!-- Step 1 Tab -->
                <div id="createStepPill1" onclick="goToCreateProjectStep(1)" style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 6px 14px; border-radius: 8px; background: rgba(225, 29, 72, 0.08); border: 1.5px solid var(--primary-red); transition: all 0.2s ease;">
                    <div class="step-badge" style="width: 22px; height: 22px; border-radius: 50%; background: var(--primary-red); color: #fff; font-size: 0.75rem; font-weight: 800; display: flex; align-items: center; justify-content: center;">1</div>
                    <div>
                        <div class="step-title" style="font-size: 0.75rem; font-weight: 800; color: var(--primary-red); letter-spacing: 0.02em;">1. PROJECT IDENTIFICATION & ARCHITECTURAL PLAN</div>
                        <div style="font-size: 0.65rem; color: var(--text-secondary);">Identity, classification, floor plan preset & schedule</div>
                    </div>
                </div>

                <div style="color: var(--text-muted); font-size: 1rem; font-weight: 700;">➔</div>

                <!-- Step 2 Tab -->
                <div id="createStepPill2" onclick="goToCreateProjectStep(2)" style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 6px 14px; border-radius: 8px; background: #ffffff; border: 1.5px solid var(--border-color); opacity: 0.8; transition: all 0.2s ease;">
                    <div class="step-badge" style="width: 22px; height: 22px; border-radius: 50%; background: #e2e8f0; color: #64748b; font-size: 0.75rem; font-weight: 800; display: flex; align-items: center; justify-content: center;">2</div>
                    <div>
                        <div class="step-title" style="font-size: 0.75rem; font-weight: 800; color: var(--text-secondary); letter-spacing: 0.02em;">2. SIZING TARGETS, COSTING & MOBILIZATION</div>
                        <div style="font-size: 0.65rem; color: var(--text-muted);">Contract budget, feasibility engine & workforce</div>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <span id="stepCounterBadge" style="font-size: 0.7rem; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 20px; border: 1px solid #bae6fd;">
                    Step 1 of 2
                </span>
            </div>
        </div>

        <form id="createProjectForm" action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            
            <!-- =========================================================================
                 STEP 1: Project Identity, Classification, Architectural Layout & Schedule
                 ========================================================================= -->
            <div id="createProjectStep1" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                <div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 20px; overflow-y: auto; padding-right: 6px; padding-bottom: 6px; flex: 1; align-items: start;">
                    
                    <!-- LEFT COLUMN OF STEP 1: Identification & Schedule -->
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        
                        <!-- 1. Project Identification Card -->
                        <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary-red); display: inline-block;"></span>
                                1. Project Identification & Service Classification
                            </div>

                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Project Title <span style="color: var(--primary-red);">*</span></label>
                                    <input type="text" name="title" class="form-input" placeholder="e.g. Nexus Multi-Specialty Medical Complex" required style="font-size: 0.85rem;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Project Code (Optional)</label>
                                    <input type="text" name="project_code" class="form-input" placeholder="e.g. PRJ-2026-NEXUS" style="font-size: 0.85rem;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label class="form-label" style="font-size: 0.75rem;">Client / Developer Name <span style="color: var(--primary-red);">*</span></label>
                                <input type="text" name="client_name" class="form-input" placeholder="e.g. Apex Health Systems Inc." required style="font-size: 0.85rem;">
                            </div>

                            @include('partials.philippine_address_picker', [
                                'prefix' => 'create',
                                'label' => 'Site Location / Project Address'
                            ])

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Project Classification / Service Type <span style="color: var(--primary-red);">*</span></label>
                                    <select name="project_type" id="modalInputProjectType" class="form-select" onchange="recalculateProjectModalCosting()" required style="font-size: 0.8rem;">
                                        <option value="Residential Build" selected>Residential Build (₱1,100 / m² Floor Base)</option>
                                        <option value="Commercial Construction">Commercial Construction (₱1,400 / m² Floor Base)</option>
                                        <option value="Industrial Complex">Industrial Complex (₱1,600 / m² Floor Base)</option>
                                        <option value="High-Rise Development">High-Rise Development (₱1,800 / m² Floor Base)</option>
                                        <option value="Renovation & Overhaul">Renovation & Overhaul (₱800 / m² Floor Base)</option>
                                        <option value="Interior Fit-Out & Turnkey">Interior Fit-Out & Turnkey (₱950 / m² Floor Base)</option>
                                    </select>
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Finishing & Quality Tier</label>
                                    <select name="finish_tier" id="modalInputFinishTier" class="form-select" onchange="recalculateProjectModalCosting()" style="font-size: 0.8rem;">
                                        <option value="standard" selected>Standard / Basic Quality (1.00x Base)</option>
                                        <option value="executive">Semi-Custom / Executive (1.25x Multiplier)</option>
                                        <option value="luxury">Premium Luxury / High-End (1.50x Multiplier)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Project Schedule & Assigned Lead Personnel -->
                        <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                                3. Project Schedule & Assigned Lead Personnel
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Start Date <span style="color: var(--primary-red);">*</span></label>
                                    <input type="date" name="start_date" class="form-input" value="{{ date('Y-m-d') }}" required style="font-size: 0.85rem;">
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Target Completion Date <span style="color: var(--primary-red);">*</span></label>
                                    <input type="date" name="end_date" class="form-input" value="{{ date('Y-m-d', strtotime('+365 days')) }}" required style="font-size: 0.85rem;">
                                </div>
                            </div>

                            @if(isset($personnelList) && $personnelList->count() > 0)
                            <div>
                                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 6px;">Assign Lead Engineers & Architects</label>
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px 12px; max-height: 145px; overflow-y: auto; padding: 10px 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                    @foreach($personnelList as $pers)
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--text-primary); cursor: pointer; padding: 4px 6px; background: #ffffff; border-radius: 4px; border: 1px solid rgba(0,0,0,0.04);">
                                        <input type="checkbox" name="personnel_ids[]" value="{{ $pers->id }}">
                                        <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><strong>{{ $pers->name }}</strong> ({{ $pers->title }})</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                    </div>

                    <!-- RIGHT COLUMN OF STEP 1: Architectural Layout & Floor Plan Models -->
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        
                        <!-- 2. Floor Plan Model & Room Configuration -->
                        <div style="padding: 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 0.875rem; color: var(--primary-red); display: flex; align-items: center; gap: 6px;">
                                        <span>[PLAN]</span> Floor Plan Model & Room Configuration (Dropdown Driven)
                                    </div>
                                    <span style="font-size: 0.725rem; color: var(--text-muted);">
                                        Select a floor plan preset or customize room counts and dimensions from structured dropdowns.
                                    </span>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
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
                            <div style="font-size: 0.725rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px; text-transform: uppercase;">
                                3. Room Types & Dimension Brackets:
                            </div>
                            <div id="modalRoomRowsContainer" style="display: flex; flex-direction: column; gap: 8px; max-height: 180px; overflow-y: auto; padding-right: 4px; margin-bottom: 12px;">
                                <!-- Populated dynamically via JS -->
                            </div>

                            <!-- Dynamic Area Calculations Bar -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; padding: 10px 12px; background: #fafbfc; border-radius: var(--radius-sm); border: 1px solid var(--border-color); align-items: center;">
                                <div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Rooms:</div>
                                    <strong id="modalDispRoomCount" style="color: var(--text-primary); font-size: 0.95rem;">4</strong>
                                </div>
                                <div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Calculated Floor Area:</div>
                                    <strong id="modalDispFloorArea" style="color: var(--primary-red); font-size: 1.05rem; font-family: var(--font-mono);">65.00 m²</strong>
                                </div>
                                <div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Suggested Lot Footprint:</div>
                                    <strong id="modalDispLandArea" style="color: #059669; font-size: 1.05rem; font-family: var(--font-mono);">87.75 m²</strong>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Step 1 Bottom Action Bar (With Next Button) -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); background: #fafbfc;">
                    <button type="button" class="btn-secondary" onclick="closeModal('createProjectModal')" style="padding: 9px 20px;">Cancel</button>
                    
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 0.775rem; color: var(--text-muted); font-weight: 600;">Proceed to financial contract & sizing ➔</span>
                        <button type="button" id="btnNextToStep2" class="btn-primary" onclick="goToCreateProjectStep(2)" style="padding: 9px 28px; font-weight: 800; font-size: 0.875rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(225, 29, 72, 0.25);">
                            <span>Next</span>
                            <span style="font-size: 1.1rem; line-height: 1;">&rarr;</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- =========================================================================
                 STEP 2: Sizing Targets, Financial Feasibility, Weights & Workforce Scope
                 ========================================================================= -->
            <div id="createProjectStep2" style="display: none; flex-direction: column; flex: 1; overflow: hidden;">
                <div style="display: grid; grid-template-columns: 1.05fr 1fr; gap: 20px; overflow-y: auto; padding-right: 6px; padding-bottom: 6px; flex: 1; align-items: start;">
                    
                    <!-- LEFT COLUMN OF STEP 2: Sizing & Live Feasibility Engine -->
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        
                        <!-- 4. Sizing & Financial Specifications -->
                        <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                                2. Sizing Targets & Financial Contract
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div class="form-group" style="margin-bottom: 0; background: rgba(56, 189, 248, 0.06); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid rgba(56, 189, 248, 0.3);">
                                    <label class="form-label" style="color: #0284c7; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                        <span>Client Stated Budget (₱)</span>
                                        <span style="font-size: 0.65rem; color: #64748b; font-weight: 400;">Target Cap</span>
                                    </label>
                                    <input type="number" step="0.01" name="client_budget" id="modalInputClientBudget" class="form-input" placeholder="e.g. 3500000" oninput="recalculateProjectModalCosting()" style="border-color: rgba(56, 189, 248, 0.5); font-weight: 700; color: #0284c7; font-family: var(--font-mono); font-size: 0.85rem;">
                                </div>

                                <div class="form-group" style="margin-bottom: 0; background: rgba(16, 185, 129, 0.06); padding: 10px 12px; border-radius: var(--radius-sm); border: 1px solid rgba(16, 185, 129, 0.3);">
                                    <label class="form-label" style="color: #059669; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                        <span>Contract Budget (₱) <span style="color: var(--primary-red);">*</span></span>
                                        <span style="font-size: 0.65rem; color: #64748b; font-weight: 400;">Monitored Cap</span>
                                    </label>
                                    <input type="number" step="0.01" name="contract_budget" id="modalInputContractBudget" class="form-input" placeholder="₱ 0.00" style="border-color: rgba(16, 185, 129, 0.5); font-weight: 700; color: #059669; font-family: var(--font-mono); font-size: 0.85rem;" required>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Land Area (m²) <span style="color: var(--primary-red);">*</span></label>
                                    <input type="number" step="0.01" name="land_area_sqm" id="modalInputLandArea" class="form-input" placeholder="e.g. 150" oninput="recalculateProjectModalCosting()" required style="font-size: 0.85rem;">
                                </div>

                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.75rem;">Constructible Floor (m²) <span style="color: var(--primary-red);">*</span></label>
                                    <input type="number" step="0.01" name="floor_area_sqm" id="modalInputFloorArea" class="form-input" placeholder="e.g. 120" oninput="recalculateProjectModalCosting()" required style="font-size: 0.85rem;">
                                </div>
                            </div>
                        </div>

                        <!-- 5. Automated Costing & Financial Feasibility Engine -->
                        <div style="padding: 14px; background: #fafbfc; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                                <div>
                                    <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 6px;">
                                        <span style="color: #7c3aed;">⚡</span> 3. Automated Costing & Feasibility Engine
                                    </div>
                                </div>
                                <span id="modalCostingHealthBadge" class="spec-chip" style="font-size: 0.65rem; color: #059669; background: rgba(16, 185, 129, 0.15); border-color: rgba(16, 185, 129, 0.3);">
                                    SIZING ANALYSIS READY
                                </span>
                            </div>

                            <!-- 4 Cost Pillars Breakdown -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                                <div style="background: #f8fafc; padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">1. Floor Area Build:</div>
                                    <div id="costingBreakdownFloor" style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--primary-red); margin-top: 1px;">₱0.00</div>
                                    <div id="costingSubFloorRate" style="font-size: 0.625rem; color: var(--text-secondary); margin-top: 1px;">0 m² @ ₱1,100/m²</div>
                                </div>

                                <div style="background: #f8fafc; padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">2. Site / Land Prep:</div>
                                    <div id="costingBreakdownLand" style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: #059669; margin-top: 1px;">₱0.00</div>
                                    <div id="costingSubLandRate" style="font-size: 0.625rem; color: var(--text-secondary); margin-top: 1px;">0 m² @ ₱200/m²</div>
                                </div>

                                <div style="background: #f8fafc; padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">3. Spatial Fit-Out:</div>
                                    <div id="costingBreakdownRooms" style="font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: #d97706; margin-top: 1px;">₱0.00</div>
                                    <div id="costingSubRoomsCount" style="font-size: 0.625rem; color: var(--text-secondary); margin-top: 1px;">0 Rooms (1.00x Tier)</div>
                                </div>

                                <div style="background: #f8fafc; padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">4. Engineering Estimate:</div>
                                    <div id="costingTotalCalculated" style="font-family: var(--font-mono); font-size: 0.9rem; font-weight: 800; color: #7c3aed; margin-top: 1px;">₱0.00</div>
                                    <div style="font-size: 0.625rem; color: var(--text-muted); margin-top: 1px;">Sum of 1 + 2 + 3</div>
                                </div>
                            </div>

                            <!-- Live Client Budget vs Engineering Estimate Comparison Row -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; padding: 8px 10px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color); align-items: center;">
                                <div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Client Budget:</div>
                                    <div id="costingCompareClientBudget" style="font-family: var(--font-mono); font-weight: 700; font-size: 0.8rem; color: var(--primary-red);">₱ 0.00 (Unspecified)</div>
                                </div>

                                <div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Calculated Cost:</div>
                                    <div id="costingCompareEstCost" style="font-family: var(--font-mono); font-weight: 800; font-size: 0.85rem; color: #059669;">₱ 0.00</div>
                                </div>

                                <div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Budget Variance:</div>
                                    <div id="costingVarianceDisplay" style="font-family: var(--font-mono); font-weight: 800; font-size: 0.8rem; color: var(--text-muted);">
                                        ₱ 0.00 (0.0%)
                                    </div>
                                    <div id="costingFeasibilityNote" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN OF STEP 2: Weights, Headcount Mobilization & Scope -->
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        
                        <!-- 6. Trade Weighting & Initial Progress Bases -->
                        <div style="padding: 12px 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="font-weight: 700; font-size: 0.775rem; color: #d97706; margin-bottom: 6px;">Engineering Progression Formula Weights & Initial Progress</div>
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.7rem; color: #dc2626;">Structural %</label>
                                    <input type="number" name="structural_weight" class="form-input" value="40" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                    <label class="form-label" style="font-size: 0.625rem; color: var(--text-muted); margin-top: 2px;">Initial %</label>
                                    <input type="number" name="structural_progress" class="form-input" value="0" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.7rem; color: #d97706;">Electrical %</label>
                                    <input type="number" name="electrical_weight" class="form-input" value="25" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                    <label class="form-label" style="font-size: 0.625rem; color: var(--text-muted); margin-top: 2px;">Initial %</label>
                                    <input type="number" name="electrical_progress" class="form-input" value="0" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.7rem; color: #059669;">Piping %</label>
                                    <input type="number" name="piping_weight" class="form-input" value="20" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                    <label class="form-label" style="font-size: 0.625rem; color: var(--text-muted); margin-top: 2px;">Initial %</label>
                                    <input type="number" name="piping_progress" class="form-input" value="0" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.7rem; color: #7c3aed;">Finishing %</label>
                                    <input type="number" name="finishing_weight" class="form-input" value="15" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                    <label class="form-label" style="font-size: 0.625rem; color: var(--text-muted); margin-top: 2px;">Initial %</label>
                                    <input type="number" name="finishing_progress" class="form-input" value="0" min="0" max="100" style="font-size: 0.8rem; padding: 5px 6px;">
                                </div>
                            </div>
                        </div>

                        <!-- 7. Workforce Headcounts & Scope -->
                        <div style="padding: 12px 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="font-weight: 700; font-size: 0.775rem; color: var(--primary-red); margin-bottom: 6px;">Initial On-Site Workforce Mobilization (Headcount)</div>
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 8px;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.65rem;">General Laborers</label>
                                    <input type="number" name="deployed_workers" class="form-input" value="20" min="0" style="font-size: 0.775rem; padding: 4px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.65rem;">Skilled Trades</label>
                                    <input type="number" name="deployed_skilled_workers" class="form-input" value="12" min="0" style="font-size: 0.775rem; padding: 4px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.65rem;">Field Engineers</label>
                                    <input type="number" name="deployed_engineers" class="form-input" value="3" min="0" style="font-size: 0.775rem; padding: 4px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.65rem;">Architects</label>
                                    <input type="number" name="deployed_architects" class="form-input" value="1" min="0" style="font-size: 0.775rem; padding: 4px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.65rem;">Site Foremen</label>
                                    <input type="number" name="deployed_foremen" class="form-input" value="2" min="0" style="font-size: 0.775rem; padding: 4px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.65rem;">Equipment Ops</label>
                                    <input type="number" name="deployed_operators" class="form-input" value="2" min="0" style="font-size: 0.775rem; padding: 4px 6px;">
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label" style="font-size: 0.65rem;">Safety Officers</label>
                                    <input type="number" name="deployed_safety_officers" class="form-input" value="1" min="0" style="font-size: 0.775rem; padding: 4px 6px;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.7rem;">Project Scope & Technical Description</label>
                                <textarea name="description" class="form-textarea" rows="2" placeholder="Brief project scope, structural design details, deliverables..." style="font-size: 0.775rem;"></textarea>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Step 2 Bottom Action Bar (With Back and Submit Buttons) -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border-color); background: #fafbfc;">
                    <button type="button" class="btn-secondary" onclick="goToCreateProjectStep(1)" style="padding: 9px 20px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 1.1rem; line-height: 1;">&larr;</span>
                        <span>Back to Step 1</span>
                    </button>
                    
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <button type="button" class="btn-secondary" onclick="closeModal('createProjectModal')" style="padding: 9px 20px;">Cancel</button>
                        <button type="submit" class="btn-primary" style="padding: 9px 28px; font-weight: 800; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(225, 29, 72, 0.25);">
                            <span>+ Initialize Project</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hidden Fields for JSON Payloads & Estimated Cost -->
            <input type="hidden" name="room_program_json" id="modalInputRoomProgramJson" value="[]">
            <input type="hidden" name="material_takeoffs_json" id="modalInputMaterialTakeoffsJson" value="[]">
            <input type="hidden" name="estimated_cost" id="modalInputEstimatedCost" value="0">

        </form>
    </div>
</div>

<!-- Modal 2: Edit & Adjust Existing Project (Landscape 2-Column Layout) -->
<div class="modal-overlay" id="editProjectModal">
    <div class="modal-box modal-box-landscape" style="max-width: 1260px; width: 96vw; max-height: 92vh; display: flex; flex-direction: column; padding: 24px 28px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; border-bottom: 1px solid var(--border-color); margin-bottom: 18px;">
            <div>
                <h3 style="font-weight: 800; font-size: 1.25rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <span style="color: var(--primary-red);">✎</span> Edit Project Specifications & Adjust Settings
                </h3>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                    Update project details, financial contract, schedule, weights, blueprint hero, and workforce deployments.
                </p>
            </div>
            <button type="button" onclick="closeModal('editProjectModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; line-height: 1;">&times;</button>
        </div>

        <form id="editProjectForm" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 1.1fr 1fr; gap: 24px; overflow-y: auto; padding-right: 8px; padding-bottom: 8px; flex: 1; align-items: start;">
                
                <!-- LEFT COLUMN: Identity, Classification & Financials -->
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    
                    <!-- Basic Information Card -->
                    <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary-red); display: inline-block;"></span>
                            1. Core Information & Classification
                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Project Title <span style="color: var(--primary-red);">*</span></label>
                                <input type="text" name="title" id="edit_title" class="form-input" required style="font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Project Code <span style="color: var(--primary-red);">*</span></label>
                                <input type="text" name="project_code" id="edit_project_code" class="form-input" required style="font-size: 0.85rem;">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <label class="form-label" style="font-size: 0.75rem;">Client / Developer Name <span style="color: var(--primary-red);">*</span></label>
                            <input type="text" name="client_name" id="edit_client_name" class="form-input" required style="font-size: 0.85rem;">
                        </div>

                        @include('partials.philippine_address_picker', [
                            'prefix' => 'edit',
                            'label' => 'Site Location / Project Address'
                        ])

                        <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Project Classification <span style="color: var(--primary-red);">*</span></label>
                                <select name="project_type" id="edit_project_type" class="form-select" required style="font-size: 0.8rem;">
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

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Finishing Tier</label>
                                <select name="finish_tier" id="edit_finish_tier" class="form-select" style="font-size: 0.8rem;">
                                    <option value="standard">Standard (1.00x)</option>
                                    <option value="executive">Semi-Custom (1.25x)</option>
                                    <option value="luxury">Luxury Estate (1.50x)</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Status <span style="color: var(--primary-red);">*</span></label>
                                <select name="status" id="edit_status" class="form-select" onchange="toggleEditCompletionDate()" required style="font-size: 0.8rem;">
                                    <option value="in_progress">In Progress</option>
                                    <option value="approved">Approved / Planned</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="completed">Completed / Turned Over</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sizing & Financial Specs -->
                    <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 0.775rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                            2. Sizing & Financial Contracts
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Land Area (m²) <span style="color: var(--primary-red);">*</span></label>
                                <input type="number" step="0.01" name="land_area_sqm" id="edit_land_area_sqm" class="form-input" required style="font-size: 0.85rem;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Floor Area (m²) <span style="color: var(--primary-red);">*</span></label>
                                <input type="number" step="0.01" name="floor_area_sqm" id="edit_floor_area_sqm" class="form-input" required style="font-size: 0.85rem;">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1.2fr; gap: 10px;">
                            <div class="form-group" style="margin-bottom: 0; background: rgba(56, 189, 248, 0.06); padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid rgba(56, 189, 248, 0.3);">
                                <label class="form-label" style="color: #0284c7; font-size: 0.725rem; font-weight: 700; margin-bottom: 2px;">Client Budget (₱)</label>
                                <input type="number" step="0.01" name="client_budget" id="edit_client_budget" class="form-input" style="color: #0284c7; font-weight: 700; font-family: var(--font-mono); font-size: 0.825rem;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0; background: rgba(236, 72, 153, 0.06); padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid rgba(236, 72, 153, 0.3);">
                                <label class="form-label" style="color: #db2777; font-size: 0.725rem; font-weight: 700; margin-bottom: 2px;">Est. Cost (₱)</label>
                                <input type="number" step="0.01" name="estimated_cost" id="edit_estimated_cost" class="form-input" style="color: #db2777; font-weight: 700; font-family: var(--font-mono); font-size: 0.825rem;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0; background: rgba(16, 185, 129, 0.06); padding: 8px 10px; border-radius: var(--radius-sm); border: 1px solid rgba(16, 185, 129, 0.3);">
                                <label class="form-label" style="color: #059669; font-size: 0.725rem; font-weight: 700; margin-bottom: 2px;">Contract Budget (₱) <span style="color: var(--primary-red);">*</span></label>
                                <input type="number" step="0.01" name="contract_budget" id="edit_contract_budget" class="form-input" style="color: #059669; font-weight: 700; font-family: var(--font-mono); font-size: 0.825rem;" required>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline & Assigned Personnel -->
                    <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Start Date <span style="color: var(--primary-red);">*</span></label>
                                <input type="date" name="start_date" id="edit_start_date" class="form-input" required style="font-size: 0.85rem;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Target Completion Date <span style="color: var(--primary-red);">*</span></label>
                                <input type="date" name="end_date" id="edit_end_date" class="form-input" required style="font-size: 0.85rem;">
                            </div>
                        </div>

                        <div class="form-group" id="editActualCompletionGroup" style="display: none; margin-bottom: 12px;">
                            <label class="form-label" style="color: #10b981; font-size: 0.75rem;">Actual Handover / Turnover Date</label>
                            <input type="date" name="actual_completion_date" id="edit_actual_completion_date" class="form-input" style="font-size: 0.85rem;">
                        </div>

                        @if(isset($personnelList) && $personnelList->count() > 0)
                        <div>
                            <label class="form-label" style="font-size: 0.75rem; margin-bottom: 6px;">Assign Lead Engineers & Architects</label>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px 12px; max-height: 145px; overflow-y: auto; padding: 10px 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                @foreach($personnelList as $pers)
                                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.75rem; color: var(--text-primary); cursor: pointer; padding: 4px 6px; background: #ffffff; border-radius: 4px; border: 1px solid rgba(0,0,0,0.04);">
                                    <input type="checkbox" name="personnel_ids[]" class="edit-personnel-checkbox" value="{{ $pers->id }}">
                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><strong>{{ $pers->name }}</strong> ({{ $pers->title }})</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                </div>

                <!-- RIGHT COLUMN: CAD Blueprint, Progression Weights, Workforce -->
                <div style="display: flex; flex-direction: column; gap: 16px;">

                    <!-- Project Hero Blueprint & 3D Render Image Setup -->
                    <div style="padding: 16px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-weight: 700; font-size: 0.825rem; color: var(--primary-red); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <span>[CAD]</span> Update Project Hero Image / CAD Blueprint
                        </div>

                        <div id="indexEditPhotoPreviewWrap" style="display: flex; gap: 14px; margin-bottom: 12px; padding: 10px; background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); align-items: center;">
                            <div style="width: 90px; height: 68px; border-radius: 4px; overflow: hidden; background: #f8fafc; flex-shrink: 0; border: 1px solid var(--border-color);">
                                <img id="indexEditPhotoPreviewImg" src="" alt="Selected Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div style="font-size: 0.725rem; color: var(--text-muted);">
                                Current hero thumbnail. Select a new file or enter a URL to update.
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.725rem;">Upload New File</label>
                                <input type="file" name="project_photo_file" class="form-input" accept="image/*" onchange="previewIndexEditPhotoFile(this)" style="font-size: 0.75rem;">
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.725rem;">Or Image Web URL</label>
                                <input type="text" name="project_photo_url" id="edit_project_photo_url" class="form-input" placeholder="https://example.com/render.jpg" oninput="previewIndexEditPhotoUrl(this.value)" style="font-size: 0.75rem;">
                            </div>
                        </div>
                    </div>

                    <!-- Trade Weighting & Progress Adjustments -->
                    <div style="padding: 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-weight: 700; font-size: 0.775rem; color: #d97706; margin-bottom: 8px;">Engineering Progression Formula Weights & Completion Progress</div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.7rem; color: #dc2626;">Structural %</label>
                                <input type="number" name="structural_weight" id="edit_structural_weight" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                                <label class="form-label" style="font-size: 0.65rem; color: #dc2626; margin-top: 4px;">Progress %</label>
                                <input type="number" name="structural_progress" id="edit_structural_progress" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.7rem; color: #d97706;">Electrical %</label>
                                <input type="number" name="electrical_weight" id="edit_electrical_weight" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                                <label class="form-label" style="font-size: 0.65rem; color: #d97706; margin-top: 4px;">Progress %</label>
                                <input type="number" name="electrical_progress" id="edit_electrical_progress" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.7rem; color: #059669;">Piping %</label>
                                <input type="number" name="piping_weight" id="edit_piping_weight" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                                <label class="form-label" style="font-size: 0.65rem; color: #059669; margin-top: 4px;">Progress %</label>
                                <input type="number" name="piping_progress" id="edit_piping_progress" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.7rem; color: #7c3aed;">Finishing %</label>
                                <input type="number" name="finishing_weight" id="edit_finishing_weight" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                                <label class="form-label" style="font-size: 0.65rem; color: #7c3aed; margin-top: 4px;">Progress %</label>
                                <input type="number" name="finishing_progress" id="edit_finishing_progress" class="form-input" min="0" max="100" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                        </div>
                    </div>

                    <!-- Workforce Headcounts & Scope -->
                    <div style="padding: 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-weight: 700; font-size: 0.775rem; color: var(--primary-red); margin-bottom: 8px;">On-Site Workforce Deployment (Headcount)</div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.675rem;">General Laborers</label>
                                <input type="number" name="deployed_workers" id="edit_deployed_workers" class="form-input" min="0" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.675rem;">Skilled Trades</label>
                                <input type="number" name="deployed_skilled_workers" id="edit_deployed_skilled_workers" class="form-input" min="0" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.675rem;">Field Engineers</label>
                                <input type="number" name="deployed_engineers" id="edit_deployed_engineers" class="form-input" min="0" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.675rem;">Architects</label>
                                <input type="number" name="deployed_architects" id="edit_deployed_architects" class="form-input" min="0" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.675rem;">Site Foremen</label>
                                <input type="number" name="deployed_foremen" id="edit_deployed_foremen" class="form-input" min="0" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.675rem;">Equipment Ops</label>
                                <input type="number" name="deployed_operators" id="edit_deployed_operators" class="form-input" min="0" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.675rem;">Safety Officers</label>
                                <input type="number" name="deployed_safety_officers" id="edit_deployed_safety_officers" class="form-input" min="0" style="font-size: 0.8rem; padding: 5px 8px;">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 0.725rem;">Project Scope & Technical Description</label>
                            <textarea name="description" id="edit_description" class="form-textarea" rows="2" style="font-size: 0.8rem;"></textarea>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Footer Action Bar -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border-color); background: #fafbfc;">
                <button type="button" class="btn-secondary" onclick="closeModal('editProjectModal')" style="padding: 9px 20px;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 9px 24px; font-weight: 700;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Delete Project Confirmation Pop Up Window -->
<div class="modal-overlay" id="deleteProjectModal" onclick="if(event.target === this) closeModal('deleteProjectModal');">
    <div class="modal-box" style="max-width: 440px; border: 1px solid rgba(239, 68, 68, 0.4); box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.25); padding: 24px;">
        <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary-red-light); display: flex; align-items: center; justify-content: center; color: var(--primary-red); border: 1px solid var(--primary-red-border); flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"></path>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </div>
            <div style="flex: 1;">
                <h3 style="font-weight: 800; color: var(--text-primary); margin: 0 0 4px 0; font-size: 1.15rem;">
                    Are you sure you want to delete this?
                </h3>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    This action is permanent and cannot be undone.
                </div>
            </div>
            <button onclick="closeModal('deleteProjectModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; line-height: 1; padding: 2px;" title="Close">&times;</button>
        </div>

        <div style="background: var(--primary-red-light); border-left: 3px solid var(--primary-red); padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 20px;">
            <div id="deleteProjectCodeDisplay" style="font-family: var(--font-mono); font-weight: 700; color: var(--primary-red); font-size: 0.9rem;"></div>
            <div id="deleteProjectTitleDisplay" style="font-weight: 600; color: var(--text-primary); font-size: 0.85rem; margin-top: 2px;"></div>
        </div>

        <form id="deleteProjectForm" action="" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" name="confirmation" value="DELETE">
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteProjectModal')" style="padding: 8px 18px; font-weight: 600;">
                    Cancel
                </button>
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #ef4444, #dc2626); border-color: #dc2626; color: white; font-weight: 700; padding: 8px 20px;">
                    Yes, Delete
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
        if (id === 'createProjectModal') {
            goToCreateProjectStep(1);
        }
    }
    
    function closeModal(id) { 
        const el = document.getElementById(id);
        if (el) el.classList.remove('active'); 
    }

    function goToCreateProjectStep(step) {
        const step1El = document.getElementById('createProjectStep1');
        const step2El = document.getElementById('createProjectStep2');
        const pill1 = document.getElementById('createStepPill1');
        const pill2 = document.getElementById('createStepPill2');
        const badge = document.getElementById('stepCounterBadge');
        
        if (step === 2) {
            // Validate Step 1 required fields
            if (step1El) {
                const requiredFields = step1El.querySelectorAll('input[required], select[required]');
                for (let f of requiredFields) {
                    if (!f.value || !f.value.trim()) {
                        f.focus();
                        if (f.reportValidity) f.reportValidity();
                        return false;
                    }
                }
            }
            
            if (step1El) step1El.style.display = 'none';
            if (step2El) step2El.style.display = 'flex';
            
            if (pill1) {
                pill1.style.background = 'rgba(16, 185, 129, 0.08)';
                pill1.style.borderColor = '#10b981';
                pill1.style.opacity = '1';
                const title1 = pill1.querySelector('.step-title');
                if (title1) title1.style.color = '#10b981';
                const b1 = pill1.querySelector('.step-badge');
                if (b1) {
                    b1.style.background = '#10b981';
                    b1.style.color = '#fff';
                    b1.innerHTML = '&#10003;';
                }
            }
            
            if (pill2) {
                pill2.style.background = 'rgba(225, 29, 72, 0.08)';
                pill2.style.borderColor = 'var(--primary-red)';
                pill2.style.opacity = '1';
                const title2 = pill2.querySelector('.step-title');
                if (title2) title2.style.color = 'var(--primary-red)';
                const b2 = pill2.querySelector('.step-badge');
                if (b2) {
                    b2.style.background = 'var(--primary-red)';
                    b2.style.color = '#fff';
                    b2.innerText = '2';
                }
            }
            
            if (badge) {
                badge.innerText = 'Step 2 of 2';
                badge.style.background = '#fef3c7';
                badge.style.color = '#b45309';
                badge.style.borderColor = '#fde68a';
            }
            
            recalculateProjectModalCosting();
        } else {
            if (step1El) step1El.style.display = 'flex';
            if (step2El) step2El.style.display = 'none';
            
            if (pill1) {
                pill1.style.background = 'rgba(225, 29, 72, 0.08)';
                pill1.style.borderColor = 'var(--primary-red)';
                pill1.style.opacity = '1';
                const title1 = pill1.querySelector('.step-title');
                if (title1) title1.style.color = 'var(--primary-red)';
                const b1 = pill1.querySelector('.step-badge');
                if (b1) {
                    b1.style.background = 'var(--primary-red)';
                    b1.style.color = '#fff';
                    b1.innerText = '1';
                }
            }
            
            if (pill2) {
                pill2.style.background = '#ffffff';
                pill2.style.borderColor = 'var(--border-color)';
                pill2.style.opacity = '0.75';
                const title2 = pill2.querySelector('.step-title');
                if (title2) title2.style.color = 'var(--text-secondary)';
                const b2 = pill2.querySelector('.step-badge');
                if (b2) {
                    b2.style.background = '#e2e8f0';
                    b2.style.color = '#64748b';
                    b2.innerText = '2';
                }
            }
            
            if (badge) {
                badge.innerText = 'Step 1 of 2';
                badge.style.background = '#e0f2fe';
                badge.style.color = '#0369a1';
                badge.style.borderColor = '#bae6fd';
            }
        }
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
        if (typeof setPhAddress === 'function') {
            setPhAddress('edit', project.location || '');
        } else {
            const locInput = document.getElementById('edit_location');
            if (locInput) locInput.value = project.location || '';
        }
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
            row.style.cssText = 'display: grid; grid-template-columns: 32px 2fr 3fr 85px 30px; gap: 8px; align-items: center; background: #fafbfc; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--border-color);';

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
        if (typeof initPhAddressPicker === 'function') {
            initPhAddressPicker('create');
            initPhAddressPicker('edit');
        }
    });
</script>
@endsection
