@extends('layouts.app')

@section('title', $project->title . ' - Project Master Monitor & Control Hub')
@section('page_title', $project->title)

@section('top_actions')
    <button class="btn-secondary" style="font-size: 0.85rem; color: #38bdf8; border-color: rgba(56, 189, 248, 0.4);" onclick="openModal('editProjectModal')">
        ✏️ Edit Project Specs
    </button>
    <a href="{{ route('projects.printReport', $project->id) }}" target="_blank" class="btn-primary" style="font-size: 0.85rem; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
        🖨️ Official Accomplishment Report
    </a>
    <a href="{{ route('projects.printBom', $project->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.85rem; color: #10b981; border-color: rgba(16, 185, 129, 0.4);">
        📑 Print 8-Page BOM (DUPA)
    </a>
    <button class="btn-secondary" style="font-size: 0.85rem;" onclick="openModal('updateScheduleModal')">
        📅 Set Schedule ({{ $remainingDays }}d left)
    </button>
    <button class="btn-secondary" style="font-size: 0.85rem;" onclick="openModal('uploadPhotoModal')">
        📸 + Blueprint / Photo
    </button>
    <button class="btn-secondary" style="font-size: 0.85rem;" onclick="openModal('updateManpowerModal')">
        👷 Manpower ({{ $totalDeployedManpower }})
    </button>
    <button class="btn-secondary" style="font-size: 0.85rem;" onclick="openModal('addCostItemModal')">
        + Cost Item
    </button>
    <button class="btn-secondary" style="font-size: 0.85rem;" onclick="openModal('addProjectPaymentModal')">
        + Payment / OR
    </button>
    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to permanently delete this project ({{ addslashes($project->project_code) }} - {{ addslashes($project->title) }}) and all associated records? This cannot be undone.');" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-secondary" style="font-size: 0.85rem; color: #f87171; border-color: rgba(239,68,68,0.35);" title="Permanently Delete Project">
            🗑️ Delete
        </button>
    </form>
    @if($project->status === 'completed')
        <a href="/history" class="btn-secondary" style="font-size: 0.85rem;">&larr; History</a>
    @else
        <a href="/projects" class="btn-secondary" style="font-size: 0.85rem;">&larr; Tracker</a>
    @endif
@endsection

@section('content')

<!-- Master Project Hero Banner -->
<div class="glass-panel" style="padding: 24px 30px; margin-bottom: 24px; background: rgba(239, 68, 68, 0.04); border: 1px solid var(--border-accent); position: relative; overflow: hidden;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 24px; position: relative; z-index: 2;">
        
        <!-- Primary Photo Thumbnail if available -->
        <div style="display: flex; gap: 20px; align-items: flex-start;">
            @if($project->primaryPhoto || $project->photos->first())
                @php $heroPhoto = $project->primaryPhoto ?? $project->photos->first(); @endphp
                <div style="position: relative; width: 140px; height: 110px; border-radius: var(--radius-md); overflow: hidden; border: 2px solid var(--border-accent); box-shadow: 0 4px 15px rgba(0,0,0,0.5); flex-shrink: 0; cursor: pointer;" onclick="openLightbox('{{ $heroPhoto->file_path }}', '{{ addslashes($heroPhoto->title) }}', '{{ $heroPhoto->type_badge['label'] }}', {{ $heroPhoto->id }}, '{{ $heroPhoto->photo_type }}', '{{ addslashes($heroPhoto->description ?? '') }}', '{{ $heroPhoto->taken_at ? $heroPhoto->taken_at->format('Y-m-d') : '' }}', {{ $heroPhoto->is_primary ? 1 : 0 }})">
                    <img src="{{ $heroPhoto->file_path }}" alt="{{ $heroPhoto->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                    <span style="position: absolute; bottom: 4px; left: 4px; font-size: 0.65rem; background: rgba(0,0,0,0.8); color: #fff; padding: 2px 6px; border-radius: 4px; font-weight: 700;">
                        {{ $heroPhoto->type_badge['icon'] }} {{ $heroPhoto->photo_type === 'blueprint' ? 'BLUEPRINT' : ($heroPhoto->photo_type === '3d_render' ? '3D DESIGN' : 'SITE PHOTO') }}
                    </span>
                    <button type="button" onclick="event.stopPropagation(); openEditPhotoModal({{ $heroPhoto->id }}, '{{ addslashes($heroPhoto->title) }}', '{{ $heroPhoto->photo_type }}', '{{ addslashes($heroPhoto->description ?? '') }}', '{{ addslashes($heroPhoto->file_path) }}', '{{ $heroPhoto->taken_at ? $heroPhoto->taken_at->format('Y-m-d') : '' }}', {{ $heroPhoto->is_primary ? 1 : 0 }})" style="position: absolute; top: 4px; right: 4px; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.3); color: #fff; border-radius: 4px; padding: 2px 6px; font-size: 0.65rem; cursor: pointer;" title="Edit this primary hero image">
                        ✏️ Edit
                    </button>
                </div>
            @else
                <div style="width: 140px; height: 110px; border-radius: var(--radius-md); border: 2px dashed var(--border-accent); background: rgba(239, 68, 68, 0.05); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; cursor: pointer; flex-shrink: 0;" onclick="openModal('uploadPhotoModal')">
                    <span style="font-size: 1.5rem;">📷</span>
                    <span style="font-size: 0.7rem; color: #ef4444; font-weight: 700;">+ Add Image</span>
                </div>
            @endif

            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap;">
                    <span class="badge badge-{{ $project->status }}">{{ str_replace('_', ' ', $project->status) }}</span>
                    <span style="font-family: var(--font-mono); font-size: 0.9rem; color: #ef4444; font-weight: 700;">{{ $project->project_code }}</span>
                    <span class="spec-chip" style="font-size: 0.775rem;">{{ $project->project_type }}</span>
                    <span class="spec-chip" style="font-size: 0.775rem; background: {{ $scheduleHealth['bg'] }}; color: {{ $scheduleHealth['color'] }}; border-color: {{ $scheduleHealth['border'] }};">
                        {{ $scheduleHealth['icon'] }} {{ $scheduleHealth['label'] }}
                    </span>
                    <span class="spec-chip" style="font-size: 0.775rem; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border-color: rgba(56, 189, 248, 0.3);">
                        👷 {{ $totalDeployedManpower }} Deployed
                    </span>
                </div>
                <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">{{ $project->title }}</h1>
                <div style="font-size: 0.925rem; color: var(--text-secondary); margin-top: 6px; display: flex; gap: 20px; flex-wrap: wrap;">
                    <span>Client: <strong style="color: var(--text-primary);">{{ $project->client_name }}</strong></span>
                    <span>Location: <strong style="color: var(--text-primary);">{{ $project->location ?? 'Main Construction Site' }}</strong></span>
                    <span>Phase: <strong style="color: #38bdf8;">{{ $project->current_phase ?? 'Phase 1: Mobilization & Earthworks' }}</strong></span>
                    <span>Timeline: <strong style="color: var(--text-primary);">{{ $project->start_date->format('M d, Y') }} &rarr; {{ $project->end_date->format('M d, Y') }}</strong></span>
                </div>
                @if($project->description)
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 10px; line-height: 1.6; max-width: 850px;">
                        {{ $project->description }}
                    </p>
                @endif
            </div>
        </div>

        <div style="text-align: right; display: flex; flex-direction: column; gap: 8px;">
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Weighted Accomplishment</div>
            <div style="font-family: var(--font-mono); font-size: 2.75rem; font-weight: 800; color: var(--primary-red); line-height: 1;">
                {{ $project->overall_progress }}%
            </div>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">
                @if($project->status === 'completed')
                    Turned over on {{ $project->actual_completion_date ? $project->actual_completion_date->format('M d, Y') : $project->end_date->format('M d, Y') }}
                @else
                    {{ $remainingDays }} calendar days remaining
                @endif
            </div>
            <div style="margin-top: 4px; font-size: 0.75rem; color: var(--text-muted); font-family: var(--font-mono);">
                Formula: ({{ $project->structural_weight }}% / {{ $project->electrical_weight }}% / {{ $project->piping_weight }}% / {{ $project->finishing_weight }}%)
            </div>
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 1: COMPREHENSIVE PROJECT EXECUTIVE MASTER SUMMARY
     ==================================================== -->
<div class="summary-briefing-card" style="margin-bottom: 28px;">
    <div class="summary-header-row">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <h2 style="font-size: 1.3rem; font-weight: 800; color: #f8fafc;">Project Executive Master Summary & Status Briefing</h2>
                <span class="badge badge-in_progress" style="font-size: 0.75rem;">Consolidated Snapshot</span>
            </div>
            <span style="font-size: 0.85rem; color: var(--text-muted);">
                Unified high-level executive report covering project scope, financial health, trade progress, materials, and manpower
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('projects.printReport', $project->id) }}" target="_blank" class="btn-primary" style="font-size: 0.8rem; padding: 6px 14px; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                🖨️ Print Accomplishment Report (Signed) &rarr;
            </a>
        </div>
    </div>

    <!-- 8 Executive Summary Blocks Grid -->
    <div class="summary-metric-blocks">
        <div class="summary-block" style="border-left: 3px solid #38bdf8;">
            <div class="summary-block-label">Total Contract Value</div>
            <div class="summary-block-val" style="color: #f8fafc;">₱{{ number_format($project->contract_budget, 2) }}</div>
            <div class="summary-block-sub">
                @if($project->client_budget && $project->client_budget > 0)
                    Client Budget: ₱{{ number_format($project->client_budget, 2) }}
                @elseif($project->estimated_cost && $project->estimated_cost > 0)
                    Est. Cost: ₱{{ number_format($project->estimated_cost, 2) }}
                @else
                    Gross Booked Sales
                @endif
            </div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #10b981;">
            <div class="summary-block-label">Cleared Cash Inflow</div>
            <div class="summary-block-val" style="color: #10b981;">₱{{ number_format($totalPaid, 2) }}</div>
            <div class="summary-block-sub">{{ $salesCollectionRate }}% Collection Rate</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #f59e0b;">
            <div class="summary-block-label">Actual Incurred Cost</div>
            <div class="summary-block-val" style="color: #f59e0b;">₱{{ number_format($totalIncurredCost, 2) }}</div>
            <div class="summary-block-sub">Rate: ₱{{ number_format($costPerFloorSqm, 2) }}/m²</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #38bdf8;">
            <div class="summary-block-label">Projected Gross Margin</div>
            <div class="summary-block-val" style="color: {{ $grossMargin >= 0 ? '#10b981' : '#ef4444' }};">
                ₱{{ number_format($grossMargin, 2) }}
            </div>
            <div class="summary-block-sub" style="color: #10b981; font-weight: 700;">{{ $grossMarginPercent }}% Profit Margin</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #ec4899;">
            <div class="summary-block-label">Workforce Deployed</div>
            <div class="summary-block-val" style="color: #ec4899;">{{ $totalDeployedManpower }} Headcount</div>
            <div class="summary-block-sub">{{ $project->deployed_workers }} Workers, {{ $project->deployed_engineers }} Engr, {{ $project->deployed_operators }} Ops</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #818cf8;">
            <div class="summary-block-label">Constructible Floor Space</div>
            <div class="summary-block-val" style="color: #818cf8;">{{ number_format($project->floor_area_sqm) }} m²</div>
            <div class="summary-block-sub">Land Area: {{ number_format($project->land_area_sqm) }} m²</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #ef4444;">
            <div class="summary-block-label">Trade Progression</div>
            <div class="summary-block-val" style="color: #ef4444;">{{ $project->overall_progress }}%</div>
            <div class="summary-block-sub">Struct {{ $project->structural_progress }}% | Elec {{ $project->electrical_progress }}% | Pipe {{ $project->piping_progress }}%</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #14b8a6;">
            <div class="summary-block-label">BOM Materials Allocated</div>
            <div class="summary-block-val" style="color: #14b8a6;">₱{{ number_format($bomAllocatedValue, 2) }}</div>
            <div class="summary-block-sub">Consumed: ₱{{ number_format($bomConsumedValue, 2) }} | Ret: +₱{{ number_format($bomReturnedExcessValue, 2) }}</div>
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 2: PROJECT BLUEPRINTS, 3D ARCHITECTURAL RENDERS & SITE PHOTOS GALLERY
     ==================================================== -->
<div class="glass-panel" style="border: 1px solid rgba(236, 72, 153, 0.35); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(236, 72, 153, 0.2); display: grid; place-items: center; font-size: 1rem; font-weight: 800; color: #ec4899;">
                🎨
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="panel-title" style="font-size: 1.15rem;">Project Design, Technical Blueprints & Site Photos</h3>
                    <span class="badge badge-in_progress">{{ $project->photos->count() }} Media Files</span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Architectural CAD blueprints, 3D design concept renders (what the client wants), structural framing, and on-site actual progress photos
                </span>
            </div>
        </div>
        <button class="btn-primary" style="font-size: 0.825rem; padding: 6px 14px; background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); border-color: #ec4899;" onclick="openModal('uploadPhotoModal')">
            + Upload Blueprint / Design Photo
        </button>
    </div>

    @if($project->photos->count() > 0)
        <!-- Photo Gallery Category Filter Tabs -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <button type="button" class="spec-chip active-gallery-filter" onclick="filterGalleryCategory('all', this)" style="cursor: pointer; font-size: 0.775rem; padding: 5px 12px; background: rgba(236, 72, 153, 0.2); color: #ec4899; border-color: #ec4899;">
                All Media ({{ $project->photos->count() }})
            </button>
            <button type="button" class="spec-chip" onclick="filterGalleryCategory('blueprint', this)" style="cursor: pointer; font-size: 0.775rem; padding: 5px 12px;">
                📐 Blueprints ({{ $project->photos->where('photo_type', 'blueprint')->count() }})
            </button>
            <button type="button" class="spec-chip" onclick="filterGalleryCategory('3d_render', this)" style="cursor: pointer; font-size: 0.775rem; padding: 5px 12px;">
                🎨 3D Renders ({{ $project->photos->where('photo_type', '3d_render')->count() }})
            </button>
            <button type="button" class="spec-chip" onclick="filterGalleryCategory('actual_site', this)" style="cursor: pointer; font-size: 0.775rem; padding: 5px 12px;">
                📸 Site Progress ({{ $project->photos->where('photo_type', 'actual_site')->count() }})
            </button>
            <button type="button" class="spec-chip" onclick="filterGalleryCategory('structural', this)" style="cursor: pointer; font-size: 0.775rem; padding: 5px 12px;">
                🏗️ Structural ({{ $project->photos->where('photo_type', 'structural')->count() }})
            </button>
            <button type="button" class="spec-chip" onclick="filterGalleryCategory('finishing', this)" style="cursor: pointer; font-size: 0.775rem; padding: 5px 12px;">
                ✨ Finishing ({{ $project->photos->where('photo_type', 'finishing')->count() }})
            </button>
            <button type="button" class="spec-chip" onclick="filterGalleryCategory('client_want', this)" style="cursor: pointer; font-size: 0.775rem; padding: 5px 12px;">
                💡 Client Want ({{ $project->photos->where('photo_type', 'client_want')->count() }})
            </button>
        </div>

        <div id="projectPhotosGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px;">
            @foreach($project->photos as $photo)
                @php $badge = $photo->type_badge; @endphp
                <div class="gallery-photo-card" data-category="{{ $photo->photo_type }}" style="background: rgba(15, 23, 42, 0.85); border: 1px solid {{ $photo->is_primary ? '#ef4444' : 'var(--border-color)' }}; border-radius: var(--radius-md); overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; box-shadow: 0 4px 15px rgba(0,0,0,0.3);" class="hover-lift">
                    <!-- Photo Image Preview Container -->
                    <div style="position: relative; height: 175px; background: #000; cursor: pointer; overflow: hidden;" onclick="openLightbox('{{ $photo->file_path }}', '{{ addslashes($photo->title) }}', '{{ $badge['label'] }}', {{ $photo->id }}, '{{ $photo->photo_type }}', '{{ addslashes($photo->description ?? '') }}', '{{ $photo->taken_at ? $photo->taken_at->format('Y-m-d') : '' }}', {{ $photo->is_primary ? 1 : 0 }})">
                        <img src="{{ $photo->file_path }}" alt="{{ $photo->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                        <span style="position: absolute; top: 8px; left: 8px; font-size: 0.65rem; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }}; padding: 3px 8px; border-radius: 4px; font-weight: 700;">
                            {{ $badge['icon'] }} {{ strtoupper(str_replace('_', ' ', $photo->photo_type)) }}
                        </span>
                        @if($photo->is_primary)
                            <span style="position: absolute; top: 8px; right: 8px; font-size: 0.65rem; background: #ef4444; color: #fff; padding: 3px 8px; border-radius: 4px; font-weight: 800; box-shadow: 0 2px 8px rgba(0,0,0,0.6);">
                                ⭐ PRIMARY BANNER
                            </span>
                        @endif
                    </div>

                    <!-- Photo Details -->
                    <div style="padding: 14px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem; color: #f8fafc; margin-bottom: 4px;">{{ $photo->title }}</div>
                            <div style="font-size: 0.775rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 8px;">
                                {{ $photo->description ?? 'Project architectural reference media.' }}
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid var(--border-color); font-size: 0.75rem;">
                            <span style="color: var(--text-secondary); font-family: var(--font-mono);">
                                📅 {{ $photo->taken_at ? $photo->taken_at->format('M d, Y') : $photo->created_at->format('M d, Y') }}
                            </span>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <!-- Edit Photo Button -->
                                <button type="button" class="btn-secondary" style="font-size: 0.725rem; padding: 3px 8px; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-color: rgba(56, 189, 248, 0.3);" onclick="openEditPhotoModal({{ $photo->id }}, '{{ addslashes($photo->title) }}', '{{ $photo->photo_type }}', '{{ addslashes($photo->description ?? '') }}', '{{ addslashes($photo->file_path) }}', '{{ $photo->taken_at ? $photo->taken_at->format('Y-m-d') : '' }}', {{ $photo->is_primary ? 1 : 0 }})" title="Edit photo details, classification, or replace image">
                                    ✏️ Edit
                                </button>

                                @if(!$photo->is_primary)
                                    <form action="{{ route('projects.photos.primary', $photo->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-secondary" style="font-size: 0.725rem; padding: 3px 8px; color: #f59e0b; border-color: rgba(245, 158, 11, 0.3);" title="Set as primary project hero photo">
                                            ⭐ Set Banner
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('projects.photos.delete', $photo->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Remove photo {{ addslashes($photo->title) }} from project gallery?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="font-size: 0.725rem; padding: 3px 6px; color: #ef4444; border-color: rgba(239, 68, 68, 0.3);" title="Delete photo">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 36px 20px; background: rgba(0, 0, 0, 0.2); border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
            <div style="font-size: 2.5rem; margin-bottom: 10px;">📐</div>
            <div style="font-size: 1.05rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">No Blueprints or Architectural Photos Uploaded Yet</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); max-width: 480px; margin: 0 auto 16px auto;">
                Upload technical CAD drawings, 3D concept renders, client wish-list inspirations, or on-site actual progress photographs to monitor visual fidelity.
            </div>
            <button class="btn-primary" style="font-size: 0.85rem;" onclick="openModal('uploadPhotoModal')">
                + Upload First Blueprint / Render Photo
            </button>
        </div>
    @endif
</div>

<!-- ====================================================
     SECTION 3: CLARIFIED PROJECT SCHEDULING & PHASE MATRIX PLANNER
     ==================================================== -->
<div class="glass-panel" style="border: 1px solid rgba(56, 189, 248, 0.35); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 18px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(56, 189, 248, 0.2); display: grid; place-items: center; font-size: 1rem; font-weight: 800; color: #38bdf8;">
                📅
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="panel-title" style="font-size: 1.15rem;">Project Master Scheduling & Execution Timeline</h3>
                    <span class="spec-chip" style="background: {{ $scheduleHealth['bg'] }}; color: {{ $scheduleHealth['color'] }}; border-color: {{ $scheduleHealth['border'] }};">
                        {{ $scheduleHealth['icon'] }} {{ $scheduleHealth['label'] }}
                    </span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Clarified schedule planning, calendar duration, elapsed vs remaining days, and critical-path construction phase gates
                </span>
            </div>
        </div>
        <button class="btn-primary" style="font-size: 0.825rem; padding: 6px 14px;" onclick="openModal('updateScheduleModal')">
            📅 Set / Adjust Project Schedule
        </button>
    </div>

    <!-- 4 Schedule Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="summary-block" style="border-left: 3px solid #38bdf8; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Total Scheduled Window</div>
            <div class="summary-block-val" style="color: #f8fafc;">{{ $totalScheduleDays }} Days</div>
            <div class="summary-block-sub">{{ $project->start_date->format('M d, Y') }} &rarr; {{ $project->end_date->format('M d, Y') }}</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #f59e0b; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Elapsed Site Days</div>
            <div class="summary-block-val" style="color: #f59e0b;">{{ $elapsedDays }} Days</div>
            <div class="summary-block-sub">{{ $scheduleProgressRatio }}% Timeline Consumed</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #10b981; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Days to Target Handover</div>
            <div class="summary-block-val" style="color: #10b981;">{{ $remainingDays }} Days Left</div>
            <div class="summary-block-sub">Target: {{ $project->end_date->format('M d, Y') }}</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #ec4899; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Active Construction Phase</div>
            <div class="summary-block-val" style="color: #ec4899; font-size: 1.1rem; line-height: 1.3;">
                {{ $project->current_phase ?? 'Phase 1: Mobilization' }}
            </div>
            <div class="summary-block-sub">{{ $project->overall_progress }}% Accomplished</div>
        </div>
    </div>

    <!-- 5 Standard Construction Phase Gates Matrix -->
    <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
        <div style="font-size: 0.85rem; font-weight: 700; color: #f8fafc; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
            <span>Standard Construction Phase Gates & Schedule Status</span>
            <span style="font-size: 0.75rem; color: var(--text-muted);">Sequential Milestone Progression</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px;">
            @php
                $phases = [
                    ['num' => 'Phase 1', 'name' => 'Mobilization & Earthworks', 'target' => 'Month 1-2', 'status' => $project->overall_progress >= 20 ? 'completed' : 'active'],
                    ['num' => 'Phase 2', 'name' => 'Substructure & Framing', 'target' => 'Month 2-5', 'status' => $project->structural_progress >= 80 ? 'completed' : ($project->overall_progress >= 20 ? 'active' : 'pending')],
                    ['num' => 'Phase 3', 'name' => 'MEP Rough-in & Conduits', 'target' => 'Month 5-8', 'status' => ($project->electrical_progress >= 80 && $project->piping_progress >= 80) ? 'completed' : ($project->structural_progress >= 40 ? 'active' : 'pending')],
                    ['num' => 'Phase 4', 'name' => 'Architectural Finishes', 'target' => 'Month 8-11', 'status' => $project->finishing_progress >= 90 ? 'completed' : (($project->electrical_progress >= 50) ? 'active' : 'pending')],
                    ['num' => 'Phase 5', 'name' => 'Testing & Client Handover', 'target' => 'Month 12', 'status' => $project->status === 'completed' ? 'completed' : 'pending'],
                ];
            @endphp

            @foreach($phases as $ph)
                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid {{ $ph['status'] === 'completed' ? '#10b981' : ($ph['status'] === 'active' ? '#38bdf8' : 'var(--border-color)') }}; border-radius: var(--radius-sm); padding: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-size: 0.7rem; font-weight: 800; color: {{ $ph['status'] === 'completed' ? '#10b981' : ($ph['status'] === 'active' ? '#38bdf8' : 'var(--text-muted)') }};">
                            {{ $ph['num'] }}
                        </span>
                        <span style="font-size: 0.65rem; font-weight: 700; color: {{ $ph['status'] === 'completed' ? '#10b981' : ($ph['status'] === 'active' ? '#38bdf8' : 'var(--text-muted)') }};">
                            {{ strtoupper($ph['status']) }}
                        </span>
                    </div>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">{{ $ph['name'] }}</div>
                    <div style="font-size: 0.7rem; color: var(--text-muted);">Target: {{ $ph['target'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 4: PROGRESSION BASES & ENGINEERING WEIGHT FORMULA
     ==================================================== -->
<!-- ====================================================
     SECTION 4: PROJECT MONITORING & TRADE PROGRESSION (CHECKLIST METHOD)
     ==================================================== -->
<div class="glass-panel" style="border: 1px solid rgba(56, 189, 248, 0.35); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: var(--radius-sm); background: rgba(56, 189, 248, 0.2); display: grid; place-items: center; font-size: 1.1rem; font-weight: 800; color: #38bdf8;">
                ☑️
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <h3 class="panel-title" style="font-size: 1.15rem;">Project Monitoring & Trade Progression (Checklist Method)</h3>
                    <span class="badge badge-in_progress">{{ $completedTasksCount }} / {{ $totalTasksCount }} Tasks Completed ({{ $project->overall_progress }}%)</span>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.725rem; font-weight: 700; padding: 3px 8px;">🔒 Forward-Only Monotonic Progress</span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Checklist execution for Structural, Electrical, Piping & Plumbing, and Design-Build. Tasks and progress advance strictly forward-only; completed milestones are permanent and irreversible.
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <button type="button" class="btn-secondary" style="font-size: 0.8rem; height: 36px; color: #10b981; border-color: rgba(16, 185, 129, 0.3); display: inline-flex; align-items: center; gap: 6px;" onclick="openChecklistJsonModal()">
                📋 Structured JSON State
            </button>
            <form action="{{ route('projects.resetChecklist', $project->id) }}" method="POST" onsubmit="return confirm('Reset and load the standard 53-item engineering checklist for Structural, Electrical, Piping, and Design-Build?');" style="display: inline;">
                @csrf
                <button type="submit" class="btn-secondary" style="font-size: 0.8rem; height: 36px; color: #38bdf8; border-color: rgba(56, 189, 248, 0.3); display: inline-flex; align-items: center; gap: 6px;">
                    ⚡ Reset Standard Checklist (53 Tasks)
                </button>
            </form>
            <button class="btn-primary" style="font-size: 0.8rem; height: 36px; background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); border-color: #38bdf8; display: inline-flex; align-items: center; gap: 6px;" onclick="openAddSpecificTaskModal('Structural')">
                + Add Checklist Task
            </button>
        </div>
    </div>

    <!-- Mathematical Formula Box -->
    <div style="background: rgba(0,0,0,0.4); border: 1px solid var(--border-accent); border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Standard Engineering Weighting Formula (Calculated from Checklists):</div>
            <div style="font-family: var(--font-mono); font-size: 0.95rem; color: #f8fafc; font-weight: 600;">
                <span style="color: #ef4444;">Overall %</span> = (<span style="color: #38bdf8;">Structural</span> &times; {{ $project->structural_weight }}%) + (<span style="color: #f59e0b;">Electrical</span> &times; {{ $project->electrical_weight }}%) + (<span style="color: #10b981;">Plumbing</span> &times; {{ $project->piping_weight }}%) + (<span style="color: #ec4899;">Design-Build</span> &times; {{ $project->finishing_weight }}%)
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Automated Overall Progress</div>
            <div style="font-family: var(--font-mono); font-size: 1.6rem; font-weight: 800; color: #10b981;">
                {{ $project->overall_progress }}%
            </div>
        </div>
    </div>

    <!-- 4 Trade Summary Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 22px;">
        <!-- Structural Card -->
        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: var(--radius-md); padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-weight: 700; font-size: 0.9rem; color: #38bdf8;">🏗️ Structural Works</span>
                <span class="spec-chip" style="font-size: 0.65rem;">{{ $project->structural_weight }}% WEIGHT</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                <span style="font-family: var(--font-mono); font-size: 1.5rem; font-weight: 800; color: #f8fafc;" id="kpiStructVal">{{ $project->structural_progress }}%</span>
                <span style="font-size: 0.75rem; color: #38bdf8; font-weight: 600;" id="kpiStructDone">{{ $structuralDone }} / {{ $structuralTasks->count() }} Tasks Done</span>
            </div>
            <div style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; margin-bottom: 6px;">
                <div id="kpiStructBar" style="width: {{ $project->structural_progress }}%; height: 100%; background: #38bdf8; transition: width 0.4s ease;"></div>
            </div>
            <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                <span>Contribution to Total:</span>
                <strong style="color: #38bdf8;" id="kpiStructContrib">+{{ round(($project->structural_progress * $project->structural_weight) / 100, 1) }}%</strong>
            </div>
        </div>

        <!-- Electrical Card -->
        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: var(--radius-md); padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-weight: 700; font-size: 0.9rem; color: #f59e0b;">⚡ Electrical Works</span>
                <span class="spec-chip" style="font-size: 0.65rem;">{{ $project->electrical_weight }}% WEIGHT</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                <span style="font-family: var(--font-mono); font-size: 1.5rem; font-weight: 800; color: #f8fafc;" id="kpiElecVal">{{ $project->electrical_progress }}%</span>
                <span style="font-size: 0.75rem; color: #f59e0b; font-weight: 600;" id="kpiElecDone">{{ $electricalDone }} / {{ $electricalTasks->count() }} Tasks Done</span>
            </div>
            <div style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; margin-bottom: 6px;">
                <div id="kpiElecBar" style="width: {{ $project->electrical_progress }}%; height: 100%; background: #f59e0b; transition: width 0.4s ease;"></div>
            </div>
            <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                <span>Contribution to Total:</span>
                <strong style="color: #f59e0b;" id="kpiElecContrib">+{{ round(($project->electrical_progress * $project->electrical_weight) / 100, 1) }}%</strong>
            </div>
        </div>

        <!-- Piping Card -->
        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-weight: 700; font-size: 0.9rem; color: #10b981;">🚰 Piping & Plumbing</span>
                <span class="spec-chip" style="font-size: 0.65rem;">{{ $project->piping_weight }}% WEIGHT</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                <span style="font-family: var(--font-mono); font-size: 1.5rem; font-weight: 800; color: #f8fafc;" id="kpiPipeVal">{{ $project->piping_progress }}%</span>
                <span style="font-size: 0.75rem; color: #10b981; font-weight: 600;" id="kpiPipeDone">{{ $pipingDone }} / {{ $pipingTasks->count() }} Tasks Done</span>
            </div>
            <div style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; margin-bottom: 6px;">
                <div id="kpiPipeBar" style="width: {{ $project->piping_progress }}%; height: 100%; background: #10b981; transition: width 0.4s ease;"></div>
            </div>
            <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                <span>Contribution to Total:</span>
                <strong style="color: #10b981;" id="kpiPipeContrib">+{{ round(($project->piping_progress * $project->piping_weight) / 100, 1) }}%</strong>
            </div>
        </div>

        <!-- Finishing Card -->
        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(236, 72, 153, 0.3); border-radius: var(--radius-md); padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-weight: 700; font-size: 0.9rem; color: #ec4899;">✨ Design-Build / Turnkey</span>
                <span class="spec-chip" style="font-size: 0.65rem;">{{ $project->finishing_weight }}% WEIGHT</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                <span style="font-family: var(--font-mono); font-size: 1.5rem; font-weight: 800; color: #f8fafc;" id="kpiFinishVal">{{ $project->finishing_progress }}%</span>
                <span style="font-size: 0.75rem; color: #ec4899; font-weight: 600;" id="kpiFinishDone">{{ $finishingDone }} / {{ $finishingTasks->count() }} Tasks Done</span>
            </div>
            <div style="height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; margin-bottom: 6px;">
                <div id="kpiFinishBar" style="width: {{ $project->finishing_progress }}%; height: 100%; background: #ec4899; transition: width 0.4s ease;"></div>
            </div>
            <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                <span>Contribution to Total:</span>
                <strong style="color: #ec4899;" id="kpiFinishContrib">+{{ round(($project->finishing_progress * $project->finishing_weight) / 100, 1) }}%</strong>
            </div>
        </div>
    </div>

    <!-- Trade Checklist Tabs & Timeline Dropdown Choices Bar -->
    <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            
            <!-- Left: Discipline Filter Tabs -->
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="btn-tab active" onclick="switchChecklistTab('all', this)" style="padding: 7px 14px; font-size: 0.825rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid var(--border-color); background: rgba(56, 189, 248, 0.15); color: #38bdf8; cursor: pointer;">
                    All Disciplines (<span id="tabCountAll">{{ $project->tasks->count() }}</span>)
                </button>
                <button type="button" class="btn-tab" onclick="switchChecklistTab('structural', this)" style="padding: 7px 14px; font-size: 0.825rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid transparent; background: rgba(15, 23, 42, 0.5); color: var(--text-secondary); cursor: pointer;">
                    🏗️ Structural (<span id="tabCountStruct">{{ $structuralTasks->count() }}</span>)
                </button>
                <button type="button" class="btn-tab" onclick="switchChecklistTab('electrical', this)" style="padding: 7px 14px; font-size: 0.825rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid transparent; background: rgba(15, 23, 42, 0.5); color: var(--text-secondary); cursor: pointer;">
                    ⚡ Electrical (<span id="tabCountElec">{{ $electricalTasks->count() }}</span>)
                </button>
                <button type="button" class="btn-tab" onclick="switchChecklistTab('piping', this)" style="padding: 7px 14px; font-size: 0.825rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid transparent; background: rgba(15, 23, 42, 0.5); color: var(--text-secondary); cursor: pointer;">
                    🚰 Piping (<span id="tabCountPipe">{{ $pipingTasks->count() }}</span>)
                </button>
                <button type="button" class="btn-tab" onclick="switchChecklistTab('finishing', this)" style="padding: 7px 14px; font-size: 0.825rem; font-weight: 700; border-radius: var(--radius-sm); border: 1px solid transparent; background: rgba(15, 23, 42, 0.5); color: var(--text-secondary); cursor: pointer;">
                    ✨ Design-Build (<span id="tabCountFinish">{{ $finishingTasks->count() }}</span>)
                </button>
            </div>

            <!-- Right: Dropdown Choices by Timeline -->
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                
                <!-- Timeline Phase Filter Dropdown -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 0.775rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; white-space: nowrap;">
                        📅 Timeline Phase:
                    </label>
                    <select id="timelinePhaseFilter" class="form-select" onchange="filterChecklistByTimeline(this.value)" style="padding: 6px 12px; font-size: 0.825rem; font-weight: 600; min-width: 220px; height: 34px; background: rgba(15, 23, 42, 0.9); border-color: rgba(56, 189, 248, 0.4); color: #38bdf8;">
                        <option value="all">📅 All Timeline Phases ({{ $project->tasks->count() }} Tasks)</option>
                        <option value="phase1">Phase 1: Mobilization & Substructure (M1-2)</option>
                        <option value="phase2">Phase 2: Superstructure & Framing (M3-5)</option>
                        <option value="phase3">Phase 3: MEP Rough-Ins & Enclosures (M6-8)</option>
                        <option value="phase4">Phase 4: Architectural Fit-Out & Finishes (M9-11)</option>
                        <option value="phase5">Phase 5: Commissioning & Handover (M11-12)</option>
                        <option value="active">🟢 Active Timeline Window (In Progress)</option>
                        <option value="completed">✅ Completed Milestones (100%)</option>
                        <option value="upcoming">⏳ Upcoming Sprints (Not Started)</option>
                    </select>
                </div>

                <!-- Dropdown Choice: Jump to Specific Task by Timeline -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 0.775rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; white-space: nowrap;">
                        🎯 Timeline Task Jump:
                    </label>
                    <select id="taskTimelineJumpSelect" class="form-select" onchange="jumpToTaskByTimeline(this.value)" style="padding: 6px 12px; font-size: 0.825rem; font-weight: 600; max-width: 290px; height: 34px; background: rgba(15, 23, 42, 0.9); border-color: rgba(245, 158, 11, 0.4); color: #f8fafc;">
                        <option value="">-- Choose Task by Timeline --</option>
                        
                        @php
                            $groupedByPhase = $project->tasks->sortBy('sort_order')->groupBy(function($t) {
                                return $t->timeline_phase ?? 'Phase 1: Mobilization & Substructure';
                            });
                        @endphp

                        @foreach($groupedByPhase as $phaseTitle => $pTasks)
                            <optgroup label="{{ strtoupper($phaseTitle) }}">
                                @foreach($pTasks as $pt)
                                    <option value="{{ $pt->id }}">
                                        {{ $pt->task_name }} [{{ $pt->progress }}%] ({{ $pt->timeline_window_label }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>
    </div>

    <!-- 4 Checklist Tables Container -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- 1. Structural Checklist Table -->
        <div class="checklist-section" id="chkSectionStructural" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(56, 189, 248, 0.25); border-radius: var(--radius-md); overflow: hidden;">
            <div style="padding: 12px 18px; background: rgba(56, 189, 248, 0.08); border-bottom: 1px solid rgba(56, 189, 248, 0.2); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 800; font-size: 1rem; color: #38bdf8;">🏗️ Structural Works Checklist</span>
                    <span class="badge badge-in_progress" style="font-size: 0.7rem;" id="structSectionDoneBadge">{{ $structuralDone }} / {{ $structuralTasks->count() }} Tasks Done</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Trade Progress:</span>
                    <strong style="font-family: var(--font-mono); font-size: 1.1rem; color: #38bdf8;" id="structSectionProgVal">{{ $project->structural_progress }}%</strong>
                    <button class="btn-primary" style="font-size: 0.725rem; padding: 4px 8px; background: #38bdf8; border-color: #38bdf8;" onclick="openAddSpecificTaskModal('Structural')">+ Add Structural Task</button>
                </div>
            </div>
            @include('projects.partials.checklist_table', ['tasks' => $structuralTasks, 'tradeName' => 'Structural Task', 'tradeColor' => '#38bdf8'])
        </div>

        <!-- 2. Electrical Checklist Table -->
        <div class="checklist-section" id="chkSectionElectrical" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: var(--radius-md); overflow: hidden;">
            <div style="padding: 12px 18px; background: rgba(245, 158, 11, 0.08); border-bottom: 1px solid rgba(245, 158, 11, 0.2); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 800; font-size: 1rem; color: #f59e0b;">⚡ Electrical Works Checklist</span>
                    <span class="badge badge-in_progress" style="font-size: 0.7rem;" id="elecSectionDoneBadge">{{ $electricalDone }} / {{ $electricalTasks->count() }} Tasks Done</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Trade Progress:</span>
                    <strong style="font-family: var(--font-mono); font-size: 1.1rem; color: #f59e0b;" id="elecSectionProgVal">{{ $project->electrical_progress }}%</strong>
                    <button class="btn-primary" style="font-size: 0.725rem; padding: 4px 8px; background: #f59e0b; border-color: #f59e0b;" onclick="openAddSpecificTaskModal('Electrical')">+ Add Electrical Task</button>
                </div>
            </div>
            @include('projects.partials.checklist_table', ['tasks' => $electricalTasks, 'tradeName' => 'Electrical Task', 'tradeColor' => '#f59e0b'])
        </div>

        <!-- 3. Piping & Plumbing Checklist Table -->
        <div class="checklist-section" id="chkSectionPiping" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: var(--radius-md); overflow: hidden;">
            <div style="padding: 12px 18px; background: rgba(16, 185, 129, 0.08); border-bottom: 1px solid rgba(16, 185, 129, 0.2); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 800; font-size: 1rem; color: #10b981;">🚰 Piping & Plumbing Checklist</span>
                    <span class="badge badge-in_progress" style="font-size: 0.7rem;" id="pipeSectionDoneBadge">{{ $pipingDone }} / {{ $pipingTasks->count() }} Tasks Done</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Trade Progress:</span>
                    <strong style="font-family: var(--font-mono); font-size: 1.1rem; color: #10b981;" id="pipeSectionProgVal">{{ $project->piping_progress }}%</strong>
                    <button class="btn-primary" style="font-size: 0.725rem; padding: 4px 8px; background: #10b981; border-color: #10b981;" onclick="openAddSpecificTaskModal('Piping & Plumbing')">+ Add Plumbing Task</button>
                </div>
            </div>
            @include('projects.partials.checklist_table', ['tasks' => $pipingTasks, 'tradeName' => 'Piping & Plumbing Task', 'tradeColor' => '#10b981'])
        </div>

        <!-- 4. Design-Build / Turnkey Finishing Checklist Table -->
        <div class="checklist-section" id="chkSectionFinishing" style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(236, 72, 153, 0.25); border-radius: var(--radius-md); overflow: hidden;">
            <div style="padding: 12px 18px; background: rgba(236, 72, 153, 0.08); border-bottom: 1px solid rgba(236, 72, 153, 0.2); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 800; font-size: 1rem; color: #ec4899;">✨ Design-Build / Turnkey Finishing Checklist</span>
                    <span class="badge badge-in_progress" style="font-size: 0.7rem;" id="finishSectionDoneBadge">{{ $finishingDone }} / {{ $finishingTasks->count() }} Tasks Done</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 0.8rem; color: var(--text-muted);">Trade Progress:</span>
                    <strong style="font-family: var(--font-mono); font-size: 1.1rem; color: #ec4899;" id="finishSectionProgVal">{{ $project->finishing_progress }}%</strong>
                    <button class="btn-primary" style="font-size: 0.725rem; padding: 4px 8px; background: #ec4899; border-color: #ec4899;" onclick="openAddSpecificTaskModal('Design-Build / Turnkey Finishing')">+ Add Finishing Task</button>
                </div>
            </div>
            @include('projects.partials.checklist_table', ['tasks' => $finishingTasks, 'tradeName' => 'Design-Build Task', 'tradeColor' => '#ec4899'])
        </div>

    </div>

    <!-- ====================================================
         ACTIVE PROJECT MATERIALS & REAL-TIME ACCUMULATION PANEL
         ==================================================== -->
    <div id="activeProjectMaterialsSection" class="glass-panel" style="margin-top: 24px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-md); padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 18px; border-bottom: 1px solid rgba(16, 185, 129, 0.2); padding-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; border-radius: var(--radius-sm); background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); display: grid; place-items: center; font-size: 1.25rem;">
                    📦
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h4 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #f8fafc;">Active Project Materials & On-Site Resource Consumption</h4>
                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.75rem;">
                            ⚡ Dynamically Accumulated from Active Tasks
                        </span>
                    </div>
                    <span style="font-size: 0.825rem; color: var(--text-muted);">
                        Real-time material aggregation strictly linked to active & completed construction tasks across Structural, Electrical, Plumbing, and Finishing.
                    </span>
                </div>
            </div>

            <!-- Search Filter for Active Materials -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <input type="text" id="activeMaterialSearchInput" onkeyup="filterActiveMaterialsTable(this.value)" placeholder="🔍 Filter active materials..." class="form-input" style="padding: 6px 12px; font-size: 0.825rem; width: 220px; height: 34px; background: rgba(0,0,0,0.4); border-color: rgba(255,255,255,0.15);">
                <button type="button" class="btn-secondary" onclick="refreshActiveMaterialsAjax()" style="font-size: 0.775rem; padding: 6px 12px; height: 34px;" title="Refresh Active Materials">
                    🔄 Sync
                </button>
            </div>
        </div>

        <!-- 4 Active Materials Summary KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-sm); padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Active Material Items</div>
                <div style="font-family: var(--font-mono); font-size: 1.4rem; font-weight: 800; color: #38bdf8;" id="activeMatCount">
                    {{ $activeMaterialsData['total_active_items'] }} Items
                </div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">Distinct specifications active</div>
            </div>

            <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-sm); padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Total Active Quantity</div>
                <div style="font-family: var(--font-mono); font-size: 1.4rem; font-weight: 800; color: #f59e0b;" id="activeMatUnits">
                    {{ number_format($activeMaterialsData['total_active_units']) }} Units
                </div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">Cumulative units mobilized</div>
            </div>

            <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-sm); padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: #10b981; text-transform: uppercase; font-weight: 700;">Accumulated Active Value</div>
                <div style="font-family: var(--font-mono); font-size: 1.4rem; font-weight: 800; color: #10b981;" id="activeMatValue">
                    ₱{{ number_format($activeMaterialsData['total_active_value'], 2) }}
                </div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">Total cost of active materials</div>
            </div>

            <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-sm); padding: 12px 16px;">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Active Task Sources</div>
                <div style="font-family: var(--font-mono); font-size: 1.4rem; font-weight: 800; color: #f8fafc;" id="activeTasksCount">
                    {{ $activeMaterialsData['active_tasks_count'] }} Tasks Active
                </div>
                <div style="font-size: 0.7rem; color: var(--text-muted);">
                    <span id="activeTasksCompletedCount">{{ $activeMaterialsData['completed_tasks_count'] }}</span> Completed • <span id="activeTasksInProgCount">{{ $activeMaterialsData['in_progress_tasks_count'] }}</span> In Progress
                </div>
            </div>
        </div>

        <!-- Active Materials Table -->
        <div style="overflow-x: auto; max-height: 420px; overflow-y: auto; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
            <table class="data-table" id="activeMaterialsTable" style="margin-bottom: 0; font-size: 0.825rem; width: 100%;">
                <thead style="position: sticky; top: 0; background: #0f172a; z-index: 5;">
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th style="min-width: 220px;">Material Specification</th>
                        <th style="width: 140px;">Discipline</th>
                        <th style="width: 150px; text-align: right;">Accumulated Qty</th>
                        <th style="width: 120px; text-align: right;">Unit Cost</th>
                        <th style="width: 150px; text-align: right;">Total Cost</th>
                        <th style="min-width: 250px;">Aligned Construction Task(s)</th>
                        <th style="width: 150px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody id="activeMaterialsTableBody">
                    @forelse($activeMaterialsData['materials'] as $index => $mat)
                        <tr class="active-mat-row" data-name="{{ strtolower($mat['material_name']) }}" data-category="{{ strtolower($mat['category']) }}">
                            <td style="text-align: center; color: var(--text-muted); font-family: var(--font-mono);">
                                {{ $index + 1 }}
                            </td>
                            <td>
                                <strong style="color: #f8fafc; font-size: 0.875rem;">
                                    🧱 {{ $mat['material_name'] }}
                                </strong>
                            </td>
                            <td>
                                @php
                                    $catColor = match($mat['category']) {
                                        'Structural' => '#38bdf8',
                                        'Electrical' => '#f59e0b',
                                        'Piping & Plumbing', 'Piping' => '#10b981',
                                        default => '#ec4899',
                                    };
                                @endphp
                                <span class="spec-chip" style="font-size: 0.7rem; color: {{ $catColor }}; border-color: {{ $catColor }}44;">
                                    {{ $mat['category'] }}
                                </span>
                            </td>
                            <td style="text-align: right; font-family: var(--font-mono); font-weight: 700; color: #f8fafc;">
                                {{ number_format($mat['total_quantity']) }} {{ $mat['unit'] }}
                            </td>
                            <td style="text-align: right; font-family: var(--font-mono); color: var(--text-muted);">
                                ₱{{ number_format($mat['unit_cost'], 2) }}
                            </td>
                            <td style="text-align: right; font-family: var(--font-mono); font-weight: 800; color: #10b981;">
                                ₱{{ number_format($mat['total_cost'], 2) }}
                            </td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($mat['task_names'] as $tName)
                                        <span class="badge" style="font-size: 0.675rem; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25);">
                                            📍 {{ $tName }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($mat['is_all_completed'])
                                    <span class="badge badge-completed" style="font-size: 0.725rem;">
                                        🔒 Installed & Finalized
                                    </span>
                                @else
                                    <span class="badge badge-in_progress" style="font-size: 0.725rem;">
                                        ⚡ In Consumption
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyActiveMaterialsRow">
                            <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                No active materials accumulated yet. Check off tasks or advance tasks to "In Progress" in the checklist above to dynamically activate construction materials.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ====================================================
     SECTION 5: ON-SITE WORKFORCE & RESOURCE DEPLOYMENT HUB
     ==================================================== -->
<div class="glass-panel" style="border: 1px solid rgba(56, 189, 248, 0.25); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(56, 189, 248, 0.2); display: grid; place-items: center; font-size: 0.75rem; font-weight: 800; color: #38bdf8;">
                SITE
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="panel-title" style="font-size: 1.15rem;">On-Site Workforce & Resource Deployment Hub</h3>
                    <span class="badge badge-in_progress">{{ $totalDeployedManpower }} Total On-Site Manpower</span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Real-time headcount tracking across construction laborers, licensed engineers, architects, equipment operators, and supervisors
                </span>
            </div>
        </div>
        <button class="btn-primary" style="font-size: 0.825rem; padding: 6px 14px;" onclick="openModal('updateManpowerModal')">
            Update Manpower Deployment
        </button>
    </div>

    <!-- 7 Deployment Category Cards Grid -->
    <div class="manpower-grid" style="margin-bottom: 24px;">
        @foreach($manpowerBreakdown as $key => $item)
            <div class="manpower-card" style="border-left: 3px solid {{ $item['color'] }};">
                <div class="manpower-head">
                    <span class="spec-chip" style="font-size: 0.65rem; color: {{ $item['color'] }};">{{ strtoupper($key) }}</span>
                    <span class="manpower-count" style="color: {{ $item['color'] }};">{{ $item['count'] }}</span>
                </div>
                <div class="manpower-title">{{ $item['title'] }}</div>
                <div class="manpower-role">{{ $item['role'] }}</div>
                <div class="manpower-bar">
                    <div class="manpower-bar-fill" style="width: {{ $item['percent'] }}%; background: {{ $item['color'] }};"></div>
                </div>
                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 6px; display: flex; justify-content: space-between;">
                    <span>Share of Site:</span>
                    <strong style="color: {{ $item['color'] }}; font-family: var(--font-mono);">{{ $item['percent'] }}%</strong>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Assigned Licensed Engineers & Architects Roster -->
    <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <div style="font-weight: 700; font-size: 0.95rem; color: #f8fafc;">Assigned Licensed Engineers & Lead Architects</div>
            <button class="btn-secondary" style="font-size: 0.75rem; padding: 4px 10px;" onclick="openModal('assignPersonnelModal')">
                + Assign Personnel
            </button>
        </div>

        @if($project->personnel->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                @foreach($project->personnel as $person)
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px; display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); display: grid; place-items: center; font-weight: 800; color: #ef4444; font-size: 0.85rem;">
                            {{ substr($person->name, 0, 2) }}
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 0.9rem; color: #f8fafc;">{{ $person->name }}</div>
                            <div style="font-size: 0.75rem; color: #38bdf8;">{{ $person->pivot->assignment_role ?? $person->title }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-muted);">PRC Lic: {{ $person->license_no ?? 'N/A' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="font-size: 0.85rem; color: var(--text-muted); text-align: center; padding: 14px;">
                No licensed engineers assigned yet. Click "+ Assign Personnel" to assign lead team members.
            </div>
        @endif
    </div>
</div>



<!-- ====================================================
     SECTION 6B: ITEMIZED BILL OF MATERIALS & DETAILED COST ESTIMATES (DUPA ENGINE)
     ==================================================== -->
<div class="glass-panel" style="border: 1px solid rgba(239, 68, 68, 0.35); margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 18px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(239, 68, 68, 0.2); display: grid; place-items: center; font-size: 1.1rem; font-weight: 800; color: #ef4444;">
                📐
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="panel-title" style="font-size: 1.15rem;">Itemized Bill of Materials & Detailed Unit Price Analysis (DUPA)</h3>
                    <span class="badge badge-paid" style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                        {{ $project->scopeItems->count() }} Scope Items
                    </span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Official Philippine engineering breakdown: Materials (A) + Labor (B) + Equipment (C) &bull; Direct Cost + 15% Contingency + 6% Taxes + 10% Profit
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <form action="{{ route('projects.loadBungalowTemplate', $project->id) }}" method="POST" onsubmit="return confirm('Load the standard 18-item 2-Bedroom Bungalow Bill of Materials template (₱1,831,613.80)?');">
                @csrf
                <button type="submit" class="btn-secondary" style="font-size: 0.8rem; background: rgba(56, 189, 248, 0.1); border-color: rgba(56, 189, 248, 0.3); color: #38bdf8;" title="Populate standard 18-item template">
                    ⚡ 1-Click Load 2BR Bungalow BOM Template
                </button>
            </form>
            <a href="{{ route('projects.printBom', $project->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.8rem; color: #10b981; border-color: rgba(16, 185, 129, 0.3);" title="Print official multi-page engineering document">
                🖨️ Print Official BOM Document
            </a>
            <button class="btn-primary" style="font-size: 0.8rem; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-color: #ef4444;" onclick="openModal('addScopeItemModal')">
                + Add Scope Item
            </button>
        </div>
    </div>

    <!-- DUPA Financial Metrics Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 22px;">
        <div class="summary-block" style="border-left: 3px solid #38bdf8; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">A. Materials Subtotal</div>
            <div class="summary-block-val" style="color: #38bdf8;">₱{{ number_format($project->total_scope_materials_cost, 2) }}</div>
            <div class="summary-block-sub">Itemized Materials Sum</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #f59e0b; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">B. Labor Subtotal</div>
            <div class="summary-block-val" style="color: #f59e0b;">₱{{ number_format($project->total_scope_labor_cost, 2) }}</div>
            <div class="summary-block-sub">Excavation, Formwork, Trades</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #ec4899; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">C. Equipment Expense</div>
            <div class="summary-block-val" style="color: #ec4899;">₱{{ number_format($project->total_scope_equipment_cost, 2) }}</div>
            <div class="summary-block-sub">Machinery & Tools Overhead</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #64748b; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Total Direct Cost (A+B+C)</div>
            <div class="summary-block-val" style="color: #f8fafc;">₱{{ number_format($project->total_scope_direct_cost, 2) }}</div>
            <div class="summary-block-sub">Base Project Expenditure</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #10b981; background: rgba(15, 23, 42, 0.6);">
            <div class="summary-block-label">Grand Total Scope Cost</div>
            <div class="summary-block-val" style="color: #10b981; font-size: 1.3rem;">
                ₱{{ number_format($project->grand_scope_cost ?: $project->contract_budget, 2) }}
            </div>
            <div class="summary-block-sub">With Contingency + Taxes + Profit</div>
        </div>
    </div>

    <!-- Scope Items Accordion / Itemized Breakdown -->
    @if($project->scopeItems->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($project->scopeItems as $item)
                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                    <!-- Scope Item Header -->
                    <div style="padding: 14px 18px; background: rgba(0, 0, 0, 0.35); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: 800; color: #ef4444; font-family: var(--font-mono); font-size: 1rem;">ITEM {{ $item->item_number }}.</span>
                                <span style="font-weight: 800; font-size: 1.05rem; color: #f8fafc; text-transform: uppercase;">{{ $item->item_name }}</span>
                                @if($item->volume_or_area)
                                    <span class="spec-chip" style="font-size: 0.75rem; color: #38bdf8;">{{ $item->volume_or_area }}</span>
                                @endif
                            </div>
                            @if($item->notes)
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">{{ $item->notes }}</div>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="text-align: right;">
                                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Total Item Cost (with Markups)</div>
                                <div style="font-family: var(--font-mono); font-weight: 800; font-size: 1.15rem; color: #10b981;">
                                    ₱{{ number_format($item->total_item_cost, 2) }}
                                </div>
                            </div>
                            <div style="display: inline-flex; gap: 6px;">
                                <button class="btn-primary" style="font-size: 0.75rem; padding: 5px 10px; background: #38bdf8; border-color: #38bdf8;" onclick="openAddScopeLineModal({{ $item->id }}, {{ $item->item_number }}, '{{ addslashes($item->item_name) }}')">
                                    + Add Line
                                </button>
                                <form action="{{ route('projects.scopeItems.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete Item {{ $item->item_number }} ({{ $item->item_name }}) and all its line items?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="font-size: 0.75rem; padding: 5px 8px; color: #ef4444;" title="Delete Item">
                                        &times;
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 16px;">
                        <!-- A. Materials Table -->
                        @if($item->materials->count() > 0)
                            <div style="font-weight: 700; font-size: 0.85rem; color: #38bdf8; margin-bottom: 6px; display: flex; justify-content: space-between;">
                                <span>A. Materials Breakdown</span>
                                <span style="font-family: var(--font-mono);">Subtotal: ₱{{ number_format($item->materials_subtotal, 2) }}</span>
                            </div>
                            <table class="data-table" style="margin-bottom: 14px; font-size: 0.825rem;">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Qty</th>
                                        <th style="width: 80px;">Unit</th>
                                        <th>Material Item Description</th>
                                        <th style="width: 120px; text-align: right;">Unit Price (@ ₱)</th>
                                        <th style="width: 130px; text-align: right;">Total Amount (₱)</th>
                                        <th style="width: 60px; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->materials as $mat)
                                        <tr>
                                            <td style="font-family: var(--font-mono); font-weight: 700;">{{ $mat->quantity }}</td>
                                            <td style="color: var(--text-secondary);">{{ $mat->unit }}</td>
                                            <td><strong style="color: #f8fafc;">{{ $mat->description }}</strong></td>
                                            <td style="font-family: var(--font-mono); text-align: right;">₱{{ number_format($mat->unit_price, 2) }}</td>
                                            <td style="font-family: var(--font-mono); font-weight: 700; text-align: right; color: #38bdf8;">₱{{ number_format($mat->total_cost, 2) }}</td>
                                            <td style="text-align: right;">
                                                <div style="display: inline-flex; gap: 4px; justify-content: flex-end;">
                                                    <button type="button" style="background:none; border:none; color:#38bdf8; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Edit Line" onclick="openEditScopeLineModal({{ $mat->id }}, 'material', '{{ addslashes($mat->description) }}', {{ $mat->quantity }}, '{{ addslashes($mat->unit) }}', {{ $mat->unit_price }})">✏️</button>
                                                    <form action="{{ route('projects.scopeLines.destroy', $mat->id) }}" method="POST" onsubmit="return confirm('Delete this material line?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Delete Line">&times;</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <!-- B. Labors Table -->
                        @if($item->labors->count() > 0)
                            <div style="font-weight: 700; font-size: 0.85rem; color: #f59e0b; margin-bottom: 6px; display: flex; justify-content: space-between;">
                                <span>B. Labor Breakdown</span>
                                <span style="font-family: var(--font-mono);">Subtotal: ₱{{ number_format($item->labor_subtotal, 2) }}</span>
                            </div>
                            <table class="data-table" style="margin-bottom: 14px; font-size: 0.825rem;">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Qty</th>
                                        <th style="width: 80px;">Unit</th>
                                        <th>Labor Sub-activity Description</th>
                                        <th style="width: 120px; text-align: right;">Unit Rate (@ ₱)</th>
                                        <th style="width: 130px; text-align: right;">Total Amount (₱)</th>
                                        <th style="width: 60px; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->labors as $lab)
                                        <tr>
                                            <td style="font-family: var(--font-mono);">{{ $lab->quantity > 1 ? $lab->quantity : '' }}</td>
                                            <td style="color: var(--text-secondary);">{{ $lab->unit }}</td>
                                            <td><strong style="color: #f8fafc;">{{ $lab->description }}</strong></td>
                                            <td style="font-family: var(--font-mono); text-align: right;">{{ $lab->unit_price > 0 ? '₱' . number_format($lab->unit_price, 2) : '-' }}</td>
                                            <td style="font-family: var(--font-mono); font-weight: 700; text-align: right; color: #f59e0b;">₱{{ number_format($lab->total_cost, 2) }}</td>
                                            <td style="text-align: right;">
                                                <div style="display: inline-flex; gap: 4px; justify-content: flex-end;">
                                                    <button type="button" style="background:none; border:none; color:#38bdf8; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Edit Line" onclick="openEditScopeLineModal({{ $lab->id }}, 'labor', '{{ addslashes($lab->description) }}', {{ $lab->quantity }}, '{{ addslashes($lab->unit) }}', {{ $lab->unit_price }})">✏️</button>
                                                    <form action="{{ route('projects.scopeLines.destroy', $lab->id) }}" method="POST" onsubmit="return confirm('Delete this labor line?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Delete Line">&times;</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <!-- C. Equipment Expenses Table -->
                        @if($item->equipments->count() > 0)
                            <div style="font-weight: 700; font-size: 0.85rem; color: #ec4899; margin-bottom: 6px; display: flex; justify-content: space-between;">
                                <span>C. Equipment Expense</span>
                                <span style="font-family: var(--font-mono);">Subtotal: ₱{{ number_format($item->equipment_subtotal, 2) }}</span>
                            </div>
                            <table class="data-table" style="margin-bottom: 14px; font-size: 0.825rem;">
                                <thead>
                                    <tr>
                                        <th>Equipment / Machinery Description</th>
                                        <th style="width: 130px; text-align: right;">Total Amount (₱)</th>
                                        <th style="width: 60px; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->equipments as $eq)
                                        <tr>
                                            <td><strong style="color: #f8fafc;">{{ $eq->description }}</strong></td>
                                            <td style="font-family: var(--font-mono); font-weight: 700; text-align: right; color: #ec4899;">₱{{ number_format($eq->total_cost, 2) }}</td>
                                            <td style="text-align: right;">
                                                <div style="display: inline-flex; gap: 4px; justify-content: flex-end;">
                                                    <button type="button" style="background:none; border:none; color:#38bdf8; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Edit Line" onclick="openEditScopeLineModal({{ $eq->id }}, 'equipment', '{{ addslashes($eq->description) }}', {{ $eq->quantity }}, '{{ addslashes($eq->unit) }}', {{ $eq->unit_price }})">✏️</button>
                                                    <form action="{{ route('projects.scopeLines.destroy', $eq->id) }}" method="POST" onsubmit="return confirm('Delete this equipment line?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.8rem; padding: 2px 4px;" title="Delete Line">&times;</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif

                        <!-- Direct Cost & Markups Formula Calculation Strip -->
                        <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                            <div style="background: rgba(0, 0, 0, 0.4); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px 18px; min-width: 320px; font-size: 0.825rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <strong>DIRECT COST (A+B+C):</strong>
                                    <strong style="font-family: var(--font-mono);">₱{{ number_format($item->direct_cost, 2) }}</strong>
                                </div>
                                @if($item->contingency_percent > 0)
                                <div style="display: flex; justify-content: space-between; color: var(--text-muted); margin-bottom: 2px;">
                                    <span>Plus: Contingency ({{ (int)$item->contingency_percent }}%):</span>
                                    <span style="font-family: var(--font-mono);">₱{{ number_format($item->contingency_amount, 2) }}</span>
                                </div>
                                @endif
                                @if($item->taxes_percent > 0)
                                <div style="display: flex; justify-content: space-between; color: var(--text-muted); margin-bottom: 2px;">
                                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Taxes ({{ (int)$item->taxes_percent }}%):</span>
                                    <span style="font-family: var(--font-mono);">₱{{ number_format($item->taxes_amount, 2) }}</span>
                                </div>
                                @endif
                                @if($item->profit_percent > 0)
                                <div style="display: flex; justify-content: space-between; color: var(--text-muted); margin-bottom: 4px;">
                                    <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Profit ({{ (int)$item->profit_percent }}%):</span>
                                    <span style="font-family: var(--font-mono);">₱{{ number_format($item->profit_amount, 2) }}</span>
                                </div>
                                @endif
                                <div style="display: flex; justify-content: space-between; border-top: 1px solid #10b981; padding-top: 4px; font-weight: 800; color: #10b981; font-size: 0.95rem;">
                                    <span>TOTAL ITEM COST:</span>
                                    <span style="font-family: var(--font-mono);">₱{{ number_format($item->total_item_cost, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align: center; padding: 36px 20px; background: rgba(0, 0, 0, 0.2); border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
            <div style="font-size: 2.5rem; margin-bottom: 10px;">📐</div>
            <div style="font-size: 1.05rem; font-weight: 700; color: #f8fafc; margin-bottom: 4px;">No Scope of Work Bill of Materials Items Created Yet</div>
            <div style="font-size: 0.85rem; color: var(--text-muted); max-width: 520px; margin: 0 auto 16px auto;">
                Create itemized scope items (Foundation, Columns, Beams, Walls, Roofing, Plumbing, Electrical) with itemized Materials (A), Labor (B), and Equipment (C), or load the standard 18-item ₱1.83M template with 1 click.
            </div>
            <div style="display: inline-flex; gap: 10px;">
                <form action="{{ route('projects.loadBungalowTemplate', $project->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); border-color: #38bdf8;">
                        ⚡ 1-Click Load 2BR Bungalow BOM Template (₱1.83M)
                    </button>
                </form>
                <button class="btn-secondary" onclick="openModal('addScopeItemModal')">
                    + Add Custom Scope Item
                </button>
            </div>
        </div>
    @endif
</div>

<!-- ====================================================
     SECTION 7: SCHEDULED TASKS & MILESTONES TIMELINE
     ==================================================== -->
<div class="glass-panel" style="margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(56, 189, 248, 0.2); display: grid; place-items: center; font-size: 1rem; font-weight: 800; color: #38bdf8;">
                📋
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="panel-title" style="font-size: 1.15rem;">Task Tracking & Milestone Execution Matrix</h3>
                    <span class="badge badge-in_progress">{{ $completedTasksCount }} / {{ $totalTasksCount }} Completed</span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Scheduled trade tasks, assigned lead engineers, allocated task budgets, and completion percentages
                </span>
            </div>
        </div>
        <button class="btn-primary" style="font-size: 0.825rem;" onclick="openModal('addTaskModal')">
            + Schedule New Task
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Task & Trade Discipline</th>
                    <th>Assigned Lead</th>
                    <th>Schedule Window</th>
                    <th>Task Budget</th>
                    <th>Actual Incurred</th>
                    <th>Completion %</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->tasks as $task)
                    <tr>
                        <td>
                            <strong style="color: #f8fafc;">{{ $task->task_name }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $task->category }}</div>
                        </td>
                        <td>
                            <span style="color: #38bdf8; font-weight: 600; font-size: 0.85rem;">
                                {{ $task->assignedPersonnel->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td style="font-family: var(--font-mono); font-size: 0.8rem;">
                            {{ $task->start_date->format('M d') }} &rarr; {{ $task->due_date->format('M d, Y') }}
                        </td>
                        <td style="font-family: var(--font-mono);">₱{{ number_format($task->allocated_budget, 2) }}</td>
                        <td style="font-family: var(--font-mono); color: #f59e0b;">₱{{ number_format($task->actual_cost, 2) }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; height: 6px; background: rgba(255,255,255,0.08); border-radius: 3px; overflow: hidden; width: 60px;">
                                    <div style="width: {{ $task->progress }}%; height: 100%; background: {{ $task->progress >= 100 ? '#10b981' : '#38bdf8' }};"></div>
                                </div>
                                <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700;">{{ $task->progress }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $task->status }}">{{ str_replace('_', ' ', $task->status) }}</span>
                        </td>
                        <td style="text-align: right;">
                            <button class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px;" onclick="openUpdateTaskModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', {{ $task->progress }}, {{ $task->actual_cost }}, '{{ $task->status }}')">
                                Update
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                            No tasks scheduled yet. Click "+ Schedule New Task" to build the project execution plan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ====================================================
     SECTION 8: FINANCIAL PAYMENTS LEDGER & OFFICIAL RECEIPTS (OR)
     ==================================================== -->
<div class="glass-panel" style="margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: var(--radius-sm); background: rgba(16, 185, 129, 0.2); display: grid; place-items: center; font-size: 1.1rem; font-weight: 800; color: #10b981;">
                💳
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h3 class="panel-title" style="font-size: 1.15rem;">Project Billing, Financial Payments & Official Receipts (OR)</h3>
                    <span class="badge badge-paid">₱{{ number_format($totalPaid, 2) }} Cleared & Collected</span>
                </div>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Milestone progress billings, client settlements, invoice tracking, and official receipt generation
                </span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('payments.index', ['project_id' => $project->id]) }}" class="btn-secondary" style="font-size: 0.825rem;">
                Full Payments Hub &rarr;
            </a>
            <button class="btn-primary" style="font-size: 0.825rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-color: #10b981;" onclick="openModal('addProjectPaymentModal')">
                + Record Payment & Issue OR
            </button>
        </div>
    </div>

    <!-- Financial Billing Summary Matrix -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div class="summary-block" style="border-left: 3px solid #38bdf8; background: rgba(0,0,0,0.3); padding: 12px 16px; border-radius: var(--radius-sm);">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Contract Value</div>
            <div style="font-family: var(--font-mono); font-size: 1.25rem; font-weight: 800; color: #f8fafc; margin-top: 4px;">
                ₱{{ number_format($project->contract_budget, 2) }}
            </div>
            <div style="font-size: 0.725rem; color: var(--text-secondary); margin-top: 2px;">Gross Agreed Budget</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #10b981; background: rgba(0,0,0,0.3); padding: 12px 16px; border-radius: var(--radius-sm);">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Settled / Paid</div>
            <div style="font-family: var(--font-mono); font-size: 1.25rem; font-weight: 800; color: #10b981; margin-top: 4px;">
                ₱{{ number_format($totalPaid, 2) }}
            </div>
            <div style="font-size: 0.725rem; color: #10b981; margin-top: 2px; font-weight: 600;">
                {{ $salesCollectionRate }}% Collection Rate
            </div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #f59e0b; background: rgba(0,0,0,0.3); padding: 12px 16px; border-radius: var(--radius-sm);">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Remaining Balance</div>
            <div style="font-family: var(--font-mono); font-size: 1.25rem; font-weight: 800; color: #f59e0b; margin-top: 4px;">
                ₱{{ number_format($uncollectedBalance, 2) }}
            </div>
            <div style="font-size: 0.725rem; color: var(--text-secondary); margin-top: 2px;">Uncollected Receivable</div>
        </div>

        <div class="summary-block" style="border-left: 3px solid #ec4899; background: rgba(0,0,0,0.3); padding: 12px 16px; border-radius: var(--radius-sm);">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Pending Invoices</div>
            <div style="font-family: var(--font-mono); font-size: 1.25rem; font-weight: 800; color: #ec4899; margin-top: 4px;">
                ₱{{ number_format($totalPending, 2) }}
            </div>
            <div style="font-size: 0.725rem; color: var(--text-secondary); margin-top: 2px;">Awaiting Client Settlement</div>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Official Receipt / Invoice</th>
                    <th>Client / Payer Entity</th>
                    <th>Billing Milestone / Stage</th>
                    <th>Amount (₱)</th>
                    <th>Payment Date & Method</th>
                    <th>Reference / Check No.</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->payments as $payment)
                    <tr>
                        <td>
                            <strong style="color: #ef4444; font-family: var(--font-mono);">{{ $payment->effective_or_number }}</strong>
                            <div style="font-size: 0.725rem; color: var(--text-muted);">Invoice: {{ $payment->invoice_no }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #f8fafc; font-size: 0.85rem;">
                                {{ $payment->payer_name ?? $project->client_name }}
                            </div>
                            <div style="font-size: 0.725rem; color: var(--text-muted);">
                                {{ $project->client_name }}
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: #f8fafc;">{{ $payment->payment_stage }}</span>
                        </td>
                        <td style="font-family: var(--font-mono); font-size: 0.95rem; font-weight: 800; color: {{ $payment->status === 'paid' ? '#10b981' : '#f59e0b' }};">
                            ₱{{ number_format($payment->amount, 2) }}
                        </td>
                        <td>
                            <div style="font-family: var(--font-mono); font-size: 0.8rem;">
                                {{ $payment->payment_date->format('M d, Y') }}
                            </div>
                            <span class="spec-chip" style="font-size: 0.675rem; margin-top: 2px;">
                                @if(str_contains(strtolower($payment->payment_method), 'check') || str_contains(strtolower($payment->payment_method), 'cheque'))
                                    📄 {{ $payment->payment_method }}
                                @elseif(str_contains(strtolower($payment->payment_method), 'cash'))
                                    💵 {{ $payment->payment_method }}
                                @elseif(str_contains(strtolower($payment->payment_method), 'online') || str_contains(strtolower($payment->payment_method), 'gcash') || str_contains(strtolower($payment->payment_method), 'maya'))
                                    📱 {{ $payment->payment_method }}
                                @else
                                    💳 {{ $payment->payment_method }}
                                @endif
                            </span>
                        </td>
                        <td>
                            @if($payment->bank_reference)
                                <span style="font-family: var(--font-mono); font-size: 0.8rem; color: #38bdf8;">{{ $payment->bank_reference }}</span>
                            @else
                                <span style="font-size: 0.75rem; color: var(--text-muted);">&mdash;</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $payment->status }}">{{ strtoupper($payment->status) }}</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <a href="{{ route('payments.printReceipt', $payment->id) }}" target="_blank" class="btn-secondary" style="font-size: 0.725rem; padding: 4px 8px; color: #10b981; border-color: rgba(16,185,129,0.3);" title="Print Official Receipt Voucher">
                                    🖨️ Print OR
                                </a>
                                @if($payment->status !== 'paid')
                                    <form action="{{ route('payments.updateStatus', $payment->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="paid">
                                        <button type="submit" class="btn-primary" style="font-size: 0.725rem; padding: 4px 8px; background: #10b981; border-color: #10b981;">
                                            Mark Paid
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px;">
                            No client payments or billing records created yet. Click "+ Record Payment & Issue OR" to log milestone settlements.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ====================================================
     SECTION 9: AUDIT MILESTONE TIMELINE
     ==================================================== -->
<div class="glass-panel" style="margin-bottom: 28px;">
    <div class="panel-header" style="margin-bottom: 16px;">
        <div>
            <h3 class="panel-title" style="font-size: 1.15rem;">Project Execution Lifecycle & Milestone Audit</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Chronological event history from mobilization to final client handover</span>
        </div>
    </div>

    <div style="position: relative; padding-left: 20px; border-left: 2px solid var(--border-color); margin-left: 10px;">
        @foreach($milestones as $ms)
            <div style="position: relative; margin-bottom: 20px;">
                <div style="position: absolute; left: -26px; top: 2px; width: 12px; height: 12px; border-radius: 50%; background: {{ $ms['status'] === 'completed' ? '#10b981' : '#f59e0b' }}; border: 2px solid var(--bg-main);"></div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 2px;">
                    <div style="font-weight: 700; font-size: 0.9rem; color: #f8fafc;">{{ $ms['title'] }}</div>
                    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-secondary);">{{ $ms['date'] }}</div>
                </div>
                <div style="font-size: 0.75rem; color: #38bdf8; font-weight: 600; margin-bottom: 2px;">{{ $ms['type'] }}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">{{ $ms['description'] }}</div>
            </div>
        @endforeach
    </div>
</div>

<!-- ====================================================
     MODALS SECTION
     ==================================================== -->

<!-- Modal 1: Upload Blueprint / Design Photo -->
<div class="modal-overlay" id="uploadPhotoModal">
    <div class="modal-box modal-box-large" style="max-width: 680px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem;">📸</span>
                <div>
                    <h3 style="font-weight: 700; margin: 0; font-size: 1.15rem; color: #f8fafc;">Upload Blueprint, 3D Render or Progress Photo</h3>
                    <span style="font-size: 0.775rem; color: var(--text-muted);">Add technical CAD drawings, architectural renders, or on-site photographs</span>
                </div>
            </div>
            <button onclick="closeModal('uploadPhotoModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.photos.upload', $project->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Live Upload Preview Container -->
            <div id="uploadPreviewWrap" style="display: none; gap: 16px; margin-bottom: 18px; padding: 14px; background: rgba(0,0,0,0.35); border: 1px solid rgba(56, 189, 248, 0.4); border-radius: var(--radius-sm); align-items: center;">
                <div style="width: 130px; height: 95px; border-radius: 6px; overflow: hidden; background: #000; border: 1px solid rgba(255,255,255,0.2); flex-shrink: 0;">
                    <img id="uploadPhotoPreviewImg" src="" alt="Selected Preview" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: #38bdf8; text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Image Preview Ready</div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary);">This image will be stored in your project blueprint & photo repository.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Media Title / Subject <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" class="form-input" placeholder="e.g. Master Floor Plan Blueprint (Level 1-5)" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Media Classification Category <span style="color:#ef4444;">*</span></label>
                    <select name="photo_type" class="form-select" required>
                        <option value="blueprint">📐 Technical Blueprint / CAD Plan</option>
                        <option value="3d_render">🎨 3D Architectural Render (Target Design)</option>
                        <option value="client_want">💡 Client Design Inspiration / Request</option>
                        <option value="actual_site" selected>📸 Actual On-Site Progress Photo</option>
                        <option value="structural">🏗️ Structural & Foundation Works</option>
                        <option value="finishing">✨ Turnkey Architectural Finishing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Date Taken / Created</label>
                    <input type="date" name="taken_at" class="form-input" value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div style="background: rgba(15, 23, 42, 0.6); padding: 14px; border: 1px dashed rgba(255, 255, 255, 0.15); border-radius: var(--radius-sm); margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label" style="font-size: 0.8rem;">Upload Image File (JPG, PNG, WebP up to 10MB)</label>
                    <input type="file" name="photo_file" class="form-input" accept="image/*" onchange="previewUploadPhotoFile(this)">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem;">Or Enter Image Web URL</label>
                    <input type="text" name="photo_url" class="form-input" placeholder="https://example.com/blueprint.jpg" oninput="previewUploadPhotoUrl(this.value)">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Architectural Notes</label>
                <textarea name="description" class="form-textarea" rows="2" placeholder="Engineering specifications, floor elevations, trade notes..."></textarea>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px; padding: 10px; background: rgba(239, 68, 68, 0.08); border-radius: var(--radius-sm); border: 1px solid rgba(239, 68, 68, 0.2);">
                <input type="checkbox" name="is_primary" id="chkPrimaryPhoto" value="1" style="width: 18px; height: 18px; accent-color: #ef4444; cursor: pointer;">
                <label for="chkPrimaryPhoto" style="font-size: 0.85rem; color: #f8fafc; cursor: pointer; font-weight: 600;">
                    ⭐ Set this as the Primary Profile Hero Banner Image for this project
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <button type="button" class="btn-secondary" onclick="closeModal('uploadPhotoModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
                    📸 Upload Media to Gallery
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 1.5: Edit Project Photo / Blueprint / Render -->
<div class="modal-overlay" id="editPhotoModal">
    <div class="modal-box modal-box-large" style="max-width: 680px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 1.5rem;">🖼️</span>
                <div>
                    <h3 style="font-weight: 700; margin: 0; font-size: 1.15rem; color: #f8fafc;">Edit Project Image & Blueprint Media</h3>
                    <span style="font-size: 0.775rem; color: var(--text-muted);">Update specifications, change classification category, or replace the image/URL</span>
                </div>
            </div>
            <button onclick="closeModal('editPhotoModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="editPhotoForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_primary_submitted" value="1">

            <!-- Live Image Preview Card -->
            <div style="display: flex; gap: 16px; margin-bottom: 18px; padding: 14px; background: rgba(0,0,0,0.35); border: 1px solid var(--border-color); border-radius: var(--radius-sm); align-items: center;">
                <div style="width: 130px; height: 95px; border-radius: 6px; overflow: hidden; background: #000; border: 1px solid rgba(255,255,255,0.2); flex-shrink: 0; position: relative;">
                    <img id="editPhotoPreviewImg" src="" alt="Photo Preview" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Active Image Preview</div>
                    <div id="editPhotoPreviewTitle" style="font-size: 0.95rem; font-weight: 800; color: #f8fafc; margin-bottom: 4px;"></div>
                    <div style="font-size: 0.75rem; color: #38bdf8;">
                        💡 Uploading a new image file or typing a new media URL below will instantly update this image preview.
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Media Title / Subject <span style="color:#ef4444;">*</span></label>
                <input type="text" id="editPhotoTitle" name="title" class="form-input" placeholder="e.g. Master Floor Plan Blueprint (Level 1-5)" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Media Classification Category <span style="color:#ef4444;">*</span></label>
                    <select id="editPhotoType" name="photo_type" class="form-select" required>
                        <option value="blueprint">📐 Technical Blueprint / CAD Plan</option>
                        <option value="3d_render">🎨 3D Architectural Render (Target Design)</option>
                        <option value="client_want">💡 Client Design Inspiration / Request</option>
                        <option value="actual_site">📸 Actual On-Site Progress Photo</option>
                        <option value="structural">🏗️ Structural & Foundation Works</option>
                        <option value="finishing">✨ Turnkey Architectural Finishing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Date Taken / Created</label>
                    <input type="date" id="editPhotoTakenAt" name="taken_at" class="form-input">
                </div>
            </div>

            <!-- Replace Image Controls -->
            <div style="background: rgba(15, 23, 42, 0.6); padding: 14px; border: 1px dashed rgba(56, 189, 248, 0.4); border-radius: var(--radius-sm); margin-bottom: 16px;">
                <div style="font-size: 0.825rem; font-weight: 700; color: #38bdf8; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <span>🔄</span> Replace Existing Image File or Web URL
                </div>

                <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label" style="font-size: 0.8rem;">Upload New File (JPG, PNG, WebP up to 10MB)</label>
                    <input type="file" id="editPhotoFile" name="photo_file" class="form-input" accept="image/*" onchange="previewEditPhotoFile(this)">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 0.8rem;">Or Enter New Image URL</label>
                    <input type="text" id="editPhotoUrl" name="photo_url" class="form-input" placeholder="https://example.com/new-image.jpg" oninput="previewEditPhotoUrl(this.value)">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Architectural Notes</label>
                <textarea id="editPhotoDescription" name="description" class="form-textarea" rows="2" placeholder="Engineering specifications, floor elevations, trade notes..."></textarea>
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 10px; padding: 10px; background: rgba(239, 68, 68, 0.08); border-radius: var(--radius-sm); border: 1px solid rgba(239, 68, 68, 0.2);">
                <input type="checkbox" name="is_primary" id="editPhotoIsPrimary" value="1" style="width: 18px; height: 18px; accent-color: #ef4444; cursor: pointer;">
                <label for="editPhotoIsPrimary" style="font-size: 0.85rem; color: #f8fafc; cursor: pointer; font-weight: 600;">
                    ⭐ Set this as the Primary Profile Hero Banner Image for this project
                </label>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <button type="button" class="btn-secondary" id="editPhotoDeleteBtn" onclick="confirmDeletePhotoFromModal()" style="color: #ef4444; border-color: rgba(239, 68, 68, 0.3);">
                    🗑️ Delete Photo
                </button>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('editPhotoModal')">Cancel</button>
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
                        💾 Save & Update Media
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Form -->
        <form id="editPhotoDeleteForm" action="" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<!-- Modal 2: Set / Adjust Project Master Schedule -->
<div class="modal-overlay" id="updateScheduleModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">📅 Set Project Master Schedule & Phases</h3>
            <button onclick="closeModal('updateScheduleModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.updateSchedule', $project->id) }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Mobilization Start Date</label>
                    <input type="date" name="start_date" class="form-input" value="{{ $project->start_date->format('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Completion & Turnover</label>
                    <input type="date" name="end_date" class="form-input" value="{{ $project->end_date->format('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Current Construction Execution Phase</label>
                <select name="current_phase" class="form-select" required>
                    <option value="Phase 1: Mobilization & Earthworks" {{ ($project->current_phase ?? '') === 'Phase 1: Mobilization & Earthworks' ? 'selected' : '' }}>Phase 1: Mobilization & Earthworks</option>
                    <option value="Phase 2: Substructure & Concrete Frame" {{ ($project->current_phase ?? '') === 'Phase 2: Substructure & Concrete Frame' ? 'selected' : '' }}>Phase 2: Substructure & Concrete Frame</option>
                    <option value="Phase 3: MEP Rough-in & Conduits" {{ ($project->current_phase ?? '') === 'Phase 3: MEP Rough-in & Conduits' ? 'selected' : '' }}>Phase 3: MEP Rough-in & Conduits</option>
                    <option value="Phase 4: Enclosure & Turnkey Finishes" {{ ($project->current_phase ?? '') === 'Phase 4: Enclosure & Turnkey Finishes' ? 'selected' : '' }}>Phase 4: Enclosure & Turnkey Finishes</option>
                    <option value="Phase 5: Testing, Commissioning & Handover" {{ ($project->current_phase ?? '') === 'Phase 5: Testing, Commissioning & Handover' ? 'selected' : '' }}>Phase 5: Testing, Commissioning & Handover</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Schedule Clarifications & Phase Notes</label>
                <textarea name="schedule_notes" class="form-textarea" rows="3" placeholder="Explain schedule targets, weather contingencies, buffer weeks, critical path milestones...">{{ $project->schedule_notes }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('updateScheduleModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Master Schedule</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal 4: Return Excess Material to Central Inventory -->
<div class="modal-overlay" id="returnExcessModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">↩️ Return Excess Material to Central Warehouse</h3>
            <button onclick="closeModal('returnExcessModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="returnExcessForm" action="" method="POST">
            @csrf

            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 16px;">
                <div style="font-size: 0.8rem; color: #10b981; font-weight: 700; text-transform: uppercase;">Inventory Reconciliation:</div>
                <div style="font-weight: 700; font-size: 1rem; color: #f8fafc; margin: 4px 0;" id="retMaterialName">Material</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    Unused Site Stock Available for Return: <strong id="retMaxQty" style="color: #38bdf8; font-family: var(--font-mono);">0</strong>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Quantity to Return to Central Inventory</label>
                <input type="number" name="return_qty" id="retInputQty" class="form-input" min="1" required>
            </div>

            <div class="form-group">
                <label class="form-label">Reconciliation Remarks / Reason</label>
                <textarea name="return_notes" class="form-textarea" rows="2" placeholder="e.g. Unused excess bags returned upon completion of slab concrete work..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('returnExcessModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #10b981; border-color: #10b981;">Confirm Inventory Return</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal 6: Record Financial Payment & Generate Official Receipt (OR) -->
<div class="modal-overlay" id="addProjectPaymentModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700; margin: 0;">+ Record Client Payment & Issue Official Receipt (OR)</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Generate official payment voucher and log milestone progress billing &bull; {{ $project->project_code }}</span>
            </div>
            <button onclick="closeModal('addProjectPaymentModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <input type="hidden" name="redirect_to" value="{{ route('projects.show', $project->id) }}">

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
                    <input type="text" name="payer_name" class="form-input" value="{{ $project->client_name }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Settled / Paid Amount (₱) <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" name="amount" class="form-input" placeholder="₱ 0.00" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Payment Date <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="payment_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Milestone Phase <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="payment_stage" class="form-input" value="{{ $project->current_phase ?? 'Phase 1: Mobilization & Earthworks' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method <span style="color:#ef4444;">*</span></label>
                    <select name="payment_method" class="form-select" required>
                        <option value="Bank Transfer">💳 Bank Direct Wire / Transfer</option>
                        <option value="Cheque">📄 Cheque / Manager's Check</option>
                        <option value="Cash">💵 Cash Settlement</option>
                        <option value="Online Banking">📱 Online Banking (GCash / Maya / Instapay)</option>
                        <option value="Credit / Debit Card">💳 Credit / Debit Card</option>
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

            <div class="form-group" style="margin-top: 14px;">
                <label class="form-label">Financial Notes & Settlement Remarks</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="Official settlement terms, check clearing notes, remarks..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addProjectPaymentModal')">Cancel</button>
                <button type="submit" class="btn-primary">Record Payment & Generate OR</button>
            </div>
        </form>
    </div>
</div>

<!-- Lightbox Modal for Photo Gallery Preview -->
<div class="modal-overlay" id="photoLightboxModal" onclick="closeModal('photoLightboxModal')">
    <div style="max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative;" onclick="event.stopPropagation();">
        <button onclick="closeModal('photoLightboxModal')" style="position: absolute; top: -40px; right: 0; background: none; border: none; color: #fff; font-size: 2rem; cursor: pointer;">&times;</button>
        <img id="lightboxImg" src="" style="max-width: 100%; max-height: 70vh; border-radius: 8px; border: 2px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.8); object-fit: contain;">
        <div style="margin-top: 14px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px;">
            <div id="lightboxBadge" style="display: inline-block; font-size: 0.75rem; font-weight: 700; color: #38bdf8; background: rgba(56,189,248,0.15); padding: 4px 10px; border-radius: 4px;"></div>
            <div id="lightboxTitle" style="font-size: 1.1rem; font-weight: 800; color: #fff;"></div>
            <div id="lightboxDesc" style="font-size: 0.825rem; color: #94a3b8; max-width: 600px;"></div>
            <div style="display: flex; gap: 10px; margin-top: 4px;">
                <button type="button" id="lightboxEditBtn" class="btn-primary" style="font-size: 0.8rem; padding: 5px 14px; background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);">
                    ✏️ Edit This Image & Blueprint
                </button>
                <button type="button" class="btn-secondary" onclick="closeModal('photoLightboxModal')" style="font-size: 0.8rem; padding: 5px 14px;">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 7: Update Manpower Deployment Headcounts -->
<div class="modal-overlay" id="updateManpowerModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Update On-Site Workforce Deployment Headcount</h3>
            <button onclick="closeModal('updateManpowerModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.updateManpower', $project->id) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">General Construction Workers / Laborers</label>
                    <input type="number" name="deployed_workers" class="form-input" value="{{ $project->deployed_workers }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Skilled Tradesmen (Masons, Carpenters, Steelmen)</label>
                    <input type="number" name="deployed_skilled_workers" class="form-input" value="{{ $project->deployed_skilled_workers }}" min="0" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Field & Trade Engineers (Civil, Structural, MEP)</label>
                    <input type="number" name="deployed_engineers" class="form-input" value="{{ $project->deployed_engineers }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Architects & Spatial Design Leads</label>
                    <input type="number" name="deployed_architects" class="form-input" value="{{ $project->deployed_architects }}" min="0" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Site Foremen & Supervisors</label>
                    <input type="number" name="deployed_foremen" class="form-input" value="{{ $project->deployed_foremen }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Heavy Equipment Operators</label>
                    <input type="number" name="deployed_operators" class="form-input" value="{{ $project->deployed_operators }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Safety & QA/QC Officers</label>
                    <input type="number" name="deployed_safety_officers" class="form-input" value="{{ $project->deployed_safety_officers }}" min="0" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('updateManpowerModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update Deployment</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 8: Add Checklist / Scheduled Task -->
<div class="modal-overlay" id="addTaskModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #38bdf8;">+ Add Checklist Task &bull; {{ $project->project_code }}</h3>
            <button onclick="closeModal('addTaskModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.addTask', $project->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Task Name / Specification</label>
                <input type="text" name="task_name" id="addTaskNameInput" class="form-input" placeholder="e.g. Footing reinforcement, Main panel installation" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Trade Discipline Category</label>
                    <select name="category" id="addTaskCategorySelect" class="form-select" required>
                        <option value="Structural">🏗️ Structural Works</option>
                        <option value="Electrical">⚡ Electrical Works</option>
                        <option value="Piping & Plumbing">🚰 Piping & Plumbing</option>
                        <option value="Design-Build / Turnkey Finishing">✨ Design-Build / Turnkey Finishing</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Progress (%)</label>
                    <input type="number" name="progress" class="form-input" value="0" min="0" max="100" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Timeline Schedule Phase</label>
                    <select name="timeline_phase" id="addTaskTimelinePhase" class="form-select" required>
                        <option value="Phase 1: Mobilization & Substructure">Phase 1: Mobilization & Substructure (M1-2)</option>
                        <option value="Phase 2: Superstructure & Framing">Phase 2: Superstructure & Framing (M3-5)</option>
                        <option value="Phase 3: MEP Rough-Ins & Enclosures">Phase 3: MEP Rough-Ins & Enclosures (M6-8)</option>
                        <option value="Phase 4: Architectural Fit-Out & Finishes">Phase 4: Architectural Fit-Out & Finishes (M9-11)</option>
                        <option value="Phase 5: Commissioning & Handover">Phase 5: Commissioning & Handover (M11-12)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Timeline Month / Period</label>
                    <input type="text" name="timeline_month" id="addTaskTimelineMonth" class="form-input" placeholder="e.g. Month 1 - 2" value="Month 1 - 2">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Task Status</label>
                    <select name="status" class="form-select">
                        <option value="not_started">Not Started (0%)</option>
                        <option value="in_progress">In Progress (1-99%)</option>
                        <option value="completed">Completed (100%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned Lead (Optional)</label>
                    <select name="assigned_personnel_id" id="addTaskPersonnelSelect" class="form-select">
                        <option value="">-- Unassigned --</option>
                        @foreach($allPersonnel as $person)
                            <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->title }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addTaskModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #38bdf8; border-color: #38bdf8;">Add Task to Checklist</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 9: Edit Checklist Task Entry -->
<div class="modal-overlay" id="editTaskModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #38bdf8;">✏️ Edit Checklist Task</h3>
            <button onclick="closeModal('editTaskModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="editTaskForm" action="" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Task Description</label>
                <input type="text" name="task_name" id="editTaskNameInput" class="form-input" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Trade Discipline Category</label>
                    <select name="category" id="editTaskCategorySelect" class="form-select" required>
                        <option value="Structural">🏗️ Structural Works</option>
                        <option value="Electrical">⚡ Electrical Works</option>
                        <option value="Piping & Plumbing">🚰 Piping & Plumbing</option>
                        <option value="Design-Build / Turnkey Finishing">✨ Design-Build / Turnkey Finishing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Task Progress (%)</label>
                    <input type="number" name="progress" id="editTaskProgressInput" class="form-input" min="0" max="100" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Timeline Schedule Phase</label>
                    <select name="timeline_phase" id="editTaskTimelinePhase" class="form-select" required>
                        <option value="Phase 1: Mobilization & Substructure">Phase 1: Mobilization & Substructure (M1-2)</option>
                        <option value="Phase 2: Superstructure & Framing">Phase 2: Superstructure & Framing (M3-5)</option>
                        <option value="Phase 3: MEP Rough-Ins & Enclosures">Phase 3: MEP Rough-Ins & Enclosures (M6-8)</option>
                        <option value="Phase 4: Architectural Fit-Out & Finishes">Phase 4: Architectural Fit-Out & Finishes (M9-11)</option>
                        <option value="Phase 5: Commissioning & Handover">Phase 5: Commissioning & Handover (M11-12)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Timeline Month / Period</label>
                    <input type="text" name="timeline_month" id="editTaskTimelineMonth" class="form-input" placeholder="e.g. Month 1 - 2">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Task Status</label>
                    <select name="status" id="editTaskStatusSelect" class="form-select" required>
                        <option value="not_started">Not Started (0%)</option>
                        <option value="in_progress">In Progress (1-99%)</option>
                        <option value="completed">Completed (100%)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Assigned Lead (Optional)</label>
                    <select name="assigned_personnel_id" id="editTaskPersonnelSelect" class="form-select">
                        <option value="">-- Unassigned --</option>
                        @foreach($allPersonnel as $person)
                            <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->title }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editTaskModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #38bdf8; border-color: #38bdf8;">Save Task Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 10: Assign Professional Personnel -->
<div class="modal-overlay" id="assignPersonnelModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Assign Professional to Project</h3>
            <button onclick="closeModal('assignPersonnelModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.assignPersonnel', $project->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Select Engineer / Architect</label>
                <select name="personnel_id" class="form-select" required>
                    @foreach($allPersonnel as $person)
                        <option value="{{ $person->id }}">{{ $person->name }} &bull; {{ $person->title }} (PRC: {{ $person->license_no ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Project Assignment Role</label>
                <input type="text" name="assignment_role" class="form-input" placeholder="e.g. Project Lead Engineer, Lead Structural Consultant" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('assignPersonnelModal')">Cancel</button>
                <button type="submit" class="btn-primary">Assign Professional</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Structured Checklist State JSON -->
<div class="modal-overlay" id="checklistJsonModal">
    <div class="modal-box modal-box-large" style="max-width: 820px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-weight: 700; color: #10b981;">📋 Structured Task Checklist State (JSON)</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Real-time computed data state with project metrics, completion %, and array of individual tasks</span>
            </div>
            <button onclick="closeModal('checklistJsonModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <div style="position: relative; margin-bottom: 16px;">
            <pre id="checklistJsonContent" style="background: #020617; border: 1px solid var(--border-accent); border-radius: var(--radius-sm); padding: 16px; font-family: var(--font-mono); font-size: 0.775rem; color: #38bdf8; max-height: 460px; overflow-y: auto; white-space: pre-wrap; word-break: break-word;">Loading checklist state JSON...</pre>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span style="font-size: 0.75rem; color: var(--text-muted);">Exact Formula: <code>(Completed Tasks / Total Tasks) * 100</code> rounded to nearest whole number</span>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-secondary" onclick="copyChecklistJson()">📋 Copy JSON to Clipboard</button>
                <button type="button" class="btn-primary" onclick="closeModal('checklistJsonModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 11: Allocate Material to BOM -->
<div class="modal-overlay" id="addProjectBomModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #14b8a6; display: flex; align-items: center; gap: 8px;">
                <span>📦</span> Allocate Material to Project Site BOM
            </h3>
            <button onclick="closeModal('addProjectBomModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('bom.store') }}" method="POST">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">

            <div class="form-group">
                <label class="form-label">Select Warehouse Material Item</label>
                <select name="material_id" id="projBomMatSelect" class="form-select" onchange="updateBomPrice(this)" required>
                    <option value="">-- Choose Material Catalog Item --</option>
                    @foreach($allMaterials as $m)
                        <option value="{{ $m->id }}" data-price="{{ $m->unit_cost }}" data-stock="{{ $m->stock_quantity }}" data-unit="{{ $m->unit }}">
                            {{ $m->name }} ({{ $m->category }}) — Central Stock: {{ number_format($m->stock_quantity) }} {{ $m->unit }} — ₱{{ number_format($m->unit_cost, 2) }}/{{ $m->unit }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Quantity to Allocate</label>
                    <input type="number" name="allocated_qty" id="projBomQty" class="form-input" min="1" step="1" placeholder="e.g. 500" oninput="calculateAllocatedTotal()" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Price (₱)</label>
                    <input type="number" step="0.01" name="unit_price" id="projBomUnitPrice" class="form-input" placeholder="0.00" oninput="calculateAllocatedTotal()" required>
                </div>
            </div>

            <div style="margin-top: 6px; padding: 12px 16px; background: rgba(20, 184, 166, 0.1); border: 1px solid rgba(20, 184, 166, 0.3); border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.825rem; color: #f8fafc; font-weight: 600;">Total Allocation Value:</span>
                <strong id="projBomTotalValueDisp" style="color: #14b8a6; font-size: 1.15rem; font-family: var(--font-mono);">₱ 0.00</strong>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addProjectBomModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); border-color: #14b8a6;">
                    🚀 Allocate Material
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 12: Record Material Usage -->
<div class="modal-overlay" id="updateUsageModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Record Material Usage</h3>
            <button onclick="closeModal('updateUsageModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="updateUsageForm" action="" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Material Name</label>
                <input type="text" id="usageMatName" class="form-input" readonly style="opacity:0.8;">
            </div>

            <div class="form-group">
                <label class="form-label">Cumulative Used Quantity (<span id="usageUnitLbl">units</span>)</label>
                <input type="number" name="used_qty" id="usageInputQty" class="form-input" min="0" required>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                    Allocated Total: <strong id="usageAllocLbl" style="color: #38bdf8; font-family: var(--font-mono);">0</strong>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('updateUsageModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update Usage</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 13: Record Project Cost Item -->
<div class="modal-overlay" id="addCostItemModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Record Project Cost Item &bull; {{ $project->project_code }}</h3>
            <button onclick="closeModal('addCostItemModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.costs.store', $project->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Cost Item Name / Description</label>
                <input type="text" name="item_name" class="form-input" placeholder="e.g. Concrete Pouring Labor & Pump Truck Hire" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Cost Category</label>
                    <select name="cost_category" class="form-select" required>
                        <option value="Materials & Consumables">Materials & Consumables</option>
                        <option value="Labor & Engineering">Labor & Engineering</option>
                        <option value="Equipment & Heavy Machinery">Equipment & Heavy Machinery</option>
                        <option value="Subcontractor & Trade">Subcontractor & Trade</option>
                        <option value="Permits & Regulatory">Permits & Regulatory</option>
                        <option value="Site Overhead & Utilities">Site Overhead & Utilities</option>
                        <option value="Contingency & Testing">Contingency & Testing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Classification Type</label>
                    <select name="cost_type" class="form-select" required>
                        <option value="Direct">Direct Construction Cost</option>
                        <option value="Indirect">Indirect / Supervisory</option>
                        <option value="Subcontract">Subcontracted Trade</option>
                        <option value="Overhead">Site Overhead</option>
                        <option value="Contingency">Contingency / Safety</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" name="quantity" id="projShowQty" class="form-input" value="1.00" oninput="calcShowCostTotal()" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" name="unit" class="form-input" placeholder="e.g. lot, days, units" value="lot" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Rate (₱)</label>
                    <input type="number" step="0.01" name="unit_rate" id="projShowRate" class="form-input" placeholder="0.00" oninput="calcShowCostTotal()" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Estimated Cost (₱)</label>
                    <input type="number" step="0.01" name="estimated_cost" id="projShowEst" class="form-input" placeholder="Auto-calculated">
                </div>

                <div class="form-group">
                    <label class="form-label">Actual Incurred Cost (₱)</label>
                    <input type="number" step="0.01" name="actual_cost" id="projShowAct" class="form-input" placeholder="Auto-calculated">
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="incurred">Incurred / Active</option>
                        <option value="budgeted">Budgeted / Estimated</option>
                        <option value="committed">Committed / PO Issued</option>
                        <option value="settled">Settled & Cleared</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Date Incurred</label>
                    <input type="date" name="cost_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Vendor / Payee</label>
                    <input type="text" name="vendor_payee" class="form-input" placeholder="e.g. ReadyMix Concrete Supplier">
                </div>

                <div class="form-group">
                    <label class="form-label">Ref / PO Number</label>
                    <input type="text" name="reference_no" class="form-input" placeholder="e.g. PO-2026-092">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes & Ledger Remarks</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="Engineering notes, scope of work..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addCostItemModal')">Cancel</button>
                <button type="submit" class="btn-primary">Record Cost Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit & Adjust Project Specifications -->
<div class="modal-overlay" id="editProjectModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700;">✏️ Edit Project Specifications & Adjust Settings</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Update project details, financial contract, schedule, weights, and workforce</span>
            </div>
            <button onclick="closeModal('editProjectModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.update', $project->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Project Title</label>
                    <input type="text" name="title" class="form-input" value="{{ $project->title }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Project Code</label>
                    <input type="text" name="project_code" class="form-input" value="{{ $project->project_code }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Client / Developer Name</label>
                    <input type="text" name="client_name" class="form-input" value="{{ $project->client_name }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Site Location / Address</label>
                    <input type="text" name="location" class="form-input" value="{{ $project->location }}">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Project Classification</label>
                    <select name="project_type" class="form-select" required>
                        @foreach(['Commercial Construction', 'Residential Build', 'Industrial Complex', 'High-Rise Development', 'Institutional Facility', 'Infrastructure & Roadwork', 'Renovation & Overhaul', 'Interior Fit-Out & Turnkey'] as $type)
                            <option value="{{ $type }}" {{ $project->project_type == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" id="showEditStatus" onchange="document.getElementById('showEditActualCompletion').style.display = this.value === 'completed' ? 'block' : 'none';" required>
                        <option value="in_progress" {{ $project->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="approved" {{ $project->status == 'approved' ? 'selected' : '' }}>Approved / Planned</option>
                        <option value="on_hold" {{ $project->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed / Turned Over</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Current Execution Phase</label>
                    <select name="current_phase" class="form-select" required>
                        @foreach(['Phase 1: Mobilization & Earthworks', 'Phase 2: Substructure & Concrete Frame', 'Phase 3: MEP Rough-in & Conduits', 'Phase 4: Enclosure & Turnkey Finishes', 'Phase 5: Commissioning & Handover'] as $ph)
                            <option value="{{ $ph }}" {{ $project->current_phase == $ph ? 'selected' : '' }}>{{ $ph }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Land Area (m²)</label>
                    <input type="number" step="0.01" name="land_area_sqm" class="form-input" value="{{ $project->land_area_sqm }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Constructible Floor Area (m²)</label>
                    <input type="number" step="0.01" name="floor_area_sqm" class="form-input" value="{{ $project->floor_area_sqm }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Contract Budget (₱)</label>
                    <input type="number" step="0.01" name="contract_budget" class="form-input" value="{{ $project->contract_budget }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-input" value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Target Completion Date</label>
                    <input type="date" name="end_date" class="form-input" value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}" required>
                </div>
            </div>

            <div class="form-group" id="showEditActualCompletion" style="display: {{ $project->status === 'completed' ? 'block' : 'none' }};">
                <label class="form-label" style="color: #10b981;">Actual Handover / Turnover Date</label>
                <input type="date" name="actual_completion_date" class="form-input" value="{{ $project->actual_completion_date ? $project->actual_completion_date->format('Y-m-d') : '' }}">
            </div>

            <!-- Tripartite Loan Financing & Payment First Setup -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #38bdf8; margin-bottom: 10px;">🏦 Bank & Pag-IBIG Tripartite Loan Financing & Escrow Setup</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Financing Channel</label>
                        <select name="financing_type" class="form-select">
                            <option value="bank_loan" {{ $project->financing_type === 'bank_loan' ? 'selected' : '' }}>🏦 Bank Construction Loan</option>
                            <option value="pagibig_loan" {{ $project->financing_type === 'pagibig_loan' ? 'selected' : '' }}>🏠 Pag-IBIG (HDMF) Loan</option>
                            <option value="client_equity" {{ $project->financing_type === 'client_equity' ? 'selected' : '' }}>💵 Client Direct Equity</option>
                            <option value="cash_progress" {{ $project->financing_type === 'cash_progress' ? 'selected' : '' }}>💼 Direct Progress Cash</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Financing Bank / Institution</label>
                        <input type="text" name="financing_institution" class="form-input" value="{{ $project->financing_institution ?? 'BDO Unibank' }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Bank LOG / NOA / Loan Ref No.</label>
                        <input type="text" name="loan_account_no" class="form-input" value="{{ $project->loan_account_no }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #38bdf8;">Approved Loan Portion (₱)</label>
                        <input type="number" step="0.01" name="approved_loan_amount" class="form-input" value="{{ $project->approved_loan_amount > 0 ? $project->approved_loan_amount : ($project->contract_budget * 0.80) }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #ec4899;">Client Direct Equity (₱)</label>
                        <input type="number" step="0.01" name="client_equity_amount" class="form-input" value="{{ $project->client_equity_amount > 0 ? $project->client_equity_amount : ($project->contract_budget * 0.20) }}">
                    </div>
                </div>

                <div style="margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #f8fafc; cursor: pointer;">
                        <input type="checkbox" name="payment_first_policy" value="1" {{ $project->payment_first_policy ? 'checked' : '' }}>
                        <span><strong>Enforce "Payment First Before Construct" Policy:</strong> Require cleared loan drawdowns prior to phase mobilization.</span>
                    </label>
                </div>
            </div>

            <!-- Trade Weighting & Progress Bases -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #f59e0b; margin-bottom: 10px;">Engineering Progression Formula Weights & Completion Progress</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #38bdf8;">Structural Weight %</label>
                        <input type="number" name="structural_weight" class="form-input" value="{{ $project->structural_weight }}" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #38bdf8; margin-top: 4px;">Progress %</label>
                        <input type="number" name="structural_progress" class="form-input" value="{{ $project->structural_progress }}" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #f59e0b;">Electrical Weight %</label>
                        <input type="number" name="electrical_weight" class="form-input" value="{{ $project->electrical_weight }}" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #f59e0b; margin-top: 4px;">Progress %</label>
                        <input type="number" name="electrical_progress" class="form-input" value="{{ $project->electrical_progress }}" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #10b981;">Piping Weight %</label>
                        <input type="number" name="piping_weight" class="form-input" value="{{ $project->piping_weight }}" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #10b981; margin-top: 4px;">Progress %</label>
                        <input type="number" name="piping_progress" class="form-input" value="{{ $project->piping_progress }}" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem; color: #ec4899;">Finishing Weight %</label>
                        <input type="number" name="finishing_weight" class="form-input" value="{{ $project->finishing_weight }}" min="0" max="100">
                        <label class="form-label" style="font-size: 0.7rem; color: #ec4899; margin-top: 4px;">Progress %</label>
                        <input type="number" name="finishing_progress" class="form-input" value="{{ $project->finishing_progress }}" min="0" max="100">
                    </div>
                </div>
            </div>

            <!-- Workforce Headcounts -->
            <div style="margin-top: 14px; padding: 14px; background: rgba(0,0,0,0.25); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.85rem; color: #38bdf8; margin-bottom: 10px;">On-Site Workforce Deployment (Headcount)</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">General Laborers</label>
                        <input type="number" name="deployed_workers" class="form-input" value="{{ $project->deployed_workers }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Skilled Trades</label>
                        <input type="number" name="deployed_skilled_workers" class="form-input" value="{{ $project->deployed_skilled_workers }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Field Engineers</label>
                        <input type="number" name="deployed_engineers" class="form-input" value="{{ $project->deployed_engineers }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Architects</label>
                        <input type="number" name="deployed_architects" class="form-input" value="{{ $project->deployed_architects }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Site Foremen</label>
                        <input type="number" name="deployed_foremen" class="form-input" value="{{ $project->deployed_foremen }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Equipment Ops</label>
                        <input type="number" name="deployed_operators" class="form-input" value="{{ $project->deployed_operators }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 0.75rem;">Safety Officers</label>
                        <input type="number" name="deployed_safety_officers" class="form-input" value="{{ $project->deployed_safety_officers }}" min="0">
                    </div>
                </div>
            </div>

            <!-- Assigned Lead Personnel -->
            @if(isset($allPersonnel) && $allPersonnel->count() > 0)
            @php $assignedPersonnelIds = $project->personnel->pluck('id')->toArray(); @endphp
            <div style="margin-top: 14px;">
                <label class="form-label">Assign Lead Engineers & Architects</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px; max-height: 120px; overflow-y: auto; padding: 10px; background: rgba(0,0,0,0.3); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    @foreach($allPersonnel as $pers)
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="personnel_ids[]" value="{{ $pers->id }}" {{ in_array($pers->id, $assignedPersonnelIds) ? 'checked' : '' }}>
                        <span><strong>{{ $pers->name }}</strong> ({{ $pers->title }})</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="form-group" style="margin-top: 14px;">
                <label class="form-label">Project Scope & Technical Description</label>
                <textarea name="description" class="form-textarea" rows="2">{{ $project->description }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editProjectModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Project Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add New Scope of Work Item -->
<div class="modal-overlay" id="addScopeItemModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700;">Add New Scope of Work Item (DUPA Header)</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Defines an engineering item block (e.g. Item 1. Foundation and Footing)</span>
            </div>
            <button onclick="closeModal('addScopeItemModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.scopeItems.store', $project->id) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 100px 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Item #</label>
                    <input type="number" name="item_number" class="form-input" value="{{ $project->scopeItems->count() + 1 }}" required min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Scope Item Name</label>
                    <input type="text" name="item_name" class="form-input" placeholder="e.g. FOUNDATION AND FOOTING, ROOFING, COLUMNS" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Volume / Area Specs</label>
                    <input type="text" name="volume_or_area" class="form-input" placeholder="e.g. Volume of Concrete : 2.61 cu.m, Area : 79.24 Sq.m">
                </div>
                <div class="form-group">
                    <label class="form-label">Scope Details / Notes</label>
                    <input type="text" name="notes" class="form-input" placeholder="e.g. 4 units C1, 5 units C2, 3 units C3">
                </div>
            </div>

            <!-- Standard Philippine Markups -->
            <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px; margin-top: 10px; margin-bottom: 16px;">
                <div style="font-size: 0.75rem; font-weight: 700; color: #ef4444; text-transform: uppercase; margin-bottom: 8px;">
                    Standard Engineering Markups & Indirect Cost Factors (% of Direct Cost):
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.75rem;">Contingency (%)</label>
                        <input type="number" step="0.01" name="contingency_percent" class="form-input" value="15.00" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.75rem;">Taxes (%)</label>
                        <input type="number" step="0.01" name="taxes_percent" class="form-input" value="6.00" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.75rem;">Contractor Profit (%)</label>
                        <input type="number" step="0.01" name="profit_percent" class="form-input" value="10.00" required>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addScopeItemModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #ef4444; border-color: #ef4444;">Create Scope Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Line Item to Scope (Material, Labor, or Equipment) -->
<div class="modal-overlay" id="addScopeLineModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700;">Add Line Item to Scope</h3>
                <span id="scopeLineHeaderLabel" style="font-size: 0.825rem; color: #38bdf8; font-weight: 600;">ITEM 1</span>
            </div>
            <button onclick="closeModal('addScopeLineModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="addScopeLineForm" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Classification Category</label>
                    <select name="category" class="form-select" id="lineCatSelect" onchange="adjustLineCatFields(this.value)" required>
                        <option value="material" selected>A. Materials</option>
                        <option value="labor">B. Labor Sub-activity</option>
                        <option value="equipment">C. Equipment Expense</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Description / Specification</label>
                    <input type="text" name="description" id="lineDescInput" class="form-input" placeholder="e.g. 16mmx6m Corr. Steel bar, Excavation, Premix Concrete" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" name="quantity" id="lineQtyInput" class="form-input" value="1.00" oninput="calcLineTotalLive()" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" id="lineUnitInput" class="form-input" placeholder="e.g. lghts, kls, pcs, cu.m, bags, sq.m, Lump Sum" value="pcs" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Rate / Price (@ ₱)</label>
                    <input type="number" step="0.01" name="unit_price" id="linePriceInput" class="form-input" placeholder="0.00" oninput="calcLineTotalLive()" required>
                </div>
            </div>

            <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: var(--radius-sm); padding: 12px 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.8rem; color: var(--text-muted);">Calculated Line Total Amount:</span>
                <span id="lineCalculatedTotal" style="font-family: var(--font-mono); font-weight: 800; font-size: 1.15rem; color: #10b981;">₱ 0.00</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addScopeLineModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #38bdf8; border-color: #38bdf8;">Add Line Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Line Item (Material, Labor, or Equipment) -->
<div class="modal-overlay" id="editScopeLineModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; color: #38bdf8;">✏️ Edit Line Item Entry</h3>
            <button onclick="closeModal('editScopeLineModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="editScopeLineForm" method="POST">
            @csrf
            @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Classification Category</label>
                    <select name="category" class="form-select" id="editLineCatSelect" required>
                        <option value="material">A. Materials</option>
                        <option value="labor">B. Labor Sub-activity</option>
                        <option value="equipment">C. Equipment Expense</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Description / Specification</label>
                    <input type="text" name="description" id="editLineDescInput" class="form-input" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" name="quantity" id="editLineQtyInput" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" id="editLineUnitInput" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Unit Rate / Price (@ ₱)</label>
                    <input type="number" step="0.01" name="unit_price" id="editLinePriceInput" class="form-input" required>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('editScopeLineModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #38bdf8; border-color: #38bdf8;">Save Line Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Log Today's Material Usage on Site -->
<div class="modal-overlay" id="logDailyUsageModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700; color: #f59e0b;">📅 Log Today's On-Site Material Consumption</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Record materials used today to update remaining inventory for tomorrow</span>
            </div>
            <button onclick="closeModal('logDailyUsageModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="logDailyUsageForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Material Name</label>
                <div id="dailyUsageMatNameDisplay" style="font-weight: 800; font-size: 1rem; color: #f8fafc; padding: 8px 12px; background: rgba(0,0,0,0.3); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    Material Name
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Consumption Date</label>
                    <input type="date" name="usage_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Quantity Used Today (<span id="dailyUsageUnitLabel">units</span>)
                    </label>
                    <input type="number" step="0.01" name="quantity_used" id="dailyUsageQtyInput" class="form-input" placeholder="0.00" required>
                    <span style="font-size: 0.725rem; color: var(--text-muted);">
                        Available On-Site: <strong id="dailyUsageMaxLabel" style="color: #38bdf8;">0</strong>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Specific Activity / Trade Work</label>
                <input type="text" name="activity_description" class="form-input" placeholder="e.g. Level 9 Column Pouring, 4in CHB Wall Laying, Conduit Wiring" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Logged By / Site Supervisor</label>
                    <input type="text" name="logged_by" class="form-input" value="Site Project Engineer">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes / Observations (Optional)</label>
                    <input type="text" name="notes" class="form-input" placeholder="e.g. Completed with zero breakage">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('logDailyUsageModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #f59e0b; border-color: #f59e0b;">Save Daily Consumption Log</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Transfer Surplus Material to Another Project or Central Stock -->
<div class="modal-overlay" id="transferMaterialModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700; color: #38bdf8;">🔁 Transfer Surplus Material to Another Project</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Reallocate remaining unused materials to another active project or central inventory</span>
            </div>
            <button onclick="closeModal('transferMaterialModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form id="transferMaterialForm" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Material to Transfer</label>
                <div id="transferMatNameDisplay" style="font-weight: 800; font-size: 1rem; color: #38bdf8; padding: 8px 12px; background: rgba(0,0,0,0.3); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    Material Name
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Transfer Destination Mode</label>
                    <select name="transfer_type" class="form-select" id="transferTypeSelect" onchange="toggleTransferDest(this.value)" required>
                        <option value="inter_project" selected>🏢 Direct Inter-Project Transfer (Reallocate to another project)</option>
                        <option value="warehouse_stock">📦 Return & Stock in Central Warehouse (Save for future projects)</option>
                    </select>
                </div>

                <div class="form-group" id="destProjectFormGroup">
                    <label class="form-label">Select Target Project</label>
                    <select name="destination_project_id" class="form-select" id="destProjectSelect">
                        <option value="">-- Choose Target Active Project --</option>
                        @foreach($otherProjects as $other)
                            <option value="{{ $other->id }}">
                                {{ $other->project_code }} - {{ $other->title }} ({{ $other->client_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Transfer Date</label>
                    <input type="date" name="transfer_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Quantity to Transfer (<span id="transferUnitLabel">units</span>)
                    </label>
                    <input type="number" step="0.01" name="transfer_qty" id="transferQtyInput" class="form-input" placeholder="0.00" required>
                    <span style="font-size: 0.725rem; color: var(--text-muted);">
                        Available Surplus: <strong id="transferMaxLabel" style="color: #10b981;">0</strong>
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label">Authorized Signatory</label>
                    <input type="text" name="authorized_by" class="form-input" value="Engr. Sophia Martinez, PMP" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Transfer Reason & Notes</label>
                <textarea name="reason" class="form-textarea" rows="2" placeholder="e.g. Unused rebar cutoff surplus transferred to Villa residential framing phase..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="closeModal('transferMaterialModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #38bdf8; border-color: #38bdf8;">Confirm & Transfer Materials</button>
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

    function openAddScopeLineModal(itemId, itemNum, itemName) {
        document.getElementById('addScopeLineForm').action = '/projects/{{ $project->id }}/scope-items/' + itemId + '/lines';
        document.getElementById('scopeLineHeaderLabel').innerText = 'ITEM ' + itemNum + ': ' + itemName;
        document.getElementById('lineDescInput').value = '';
        document.getElementById('lineQtyInput').value = '1.00';
        document.getElementById('linePriceInput').value = '';
        document.getElementById('lineCalculatedTotal').innerText = '₱ 0.00';
        openModal('addScopeLineModal');
    }

    function openEditScopeLineModal(lineId, category, desc, qty, unit, price) {
        document.getElementById('editScopeLineForm').action = '/projects/scope-lines/' + lineId;
        document.getElementById('editLineCatSelect').value = category;
        document.getElementById('editLineDescInput').value = desc;
        document.getElementById('editLineQtyInput').value = qty;
        document.getElementById('editLineUnitInput').value = unit;
        document.getElementById('editLinePriceInput').value = price;
        openModal('editScopeLineModal');
    }

    function adjustLineCatFields(cat) {
        const unitInput = document.getElementById('lineUnitInput');
        if (cat === 'labor') {
            unitInput.value = 'sq.m';
        } else if (cat === 'equipment') {
            unitInput.value = 'Lump Sum';
        } else {
            unitInput.value = 'pcs';
        }
    }

    function calcLineTotalLive() {
        const qty = parseFloat(document.getElementById('lineQtyInput').value) || 0;
        const rate = parseFloat(document.getElementById('linePriceInput').value) || 0;
        const total = qty * rate;
        document.getElementById('lineCalculatedTotal').innerText = '₱ ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function openLightbox(src, title, badge, photoId, photoType, photoDesc, takenAt, isPrimary) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightboxTitle').innerText = title;
        document.getElementById('lightboxBadge').innerText = badge;
        const descEl = document.getElementById('lightboxDesc');
        if (descEl) descEl.innerText = photoDesc || '';
        const editBtn = document.getElementById('lightboxEditBtn');
        if (editBtn) {
            if (photoId) {
                editBtn.style.display = 'inline-block';
                editBtn.onclick = function() {
                    closeModal('photoLightboxModal');
                    openEditPhotoModal(photoId, title, photoType, photoDesc, src, takenAt, isPrimary);
                };
            } else {
                editBtn.style.display = 'none';
            }
        }
        openModal('photoLightboxModal');
    }

    function openEditPhotoModal(id, title, photo_type, description, file_path, taken_at, is_primary) {
        const form = document.getElementById('editPhotoForm');
        if (form) form.action = '/projects/photos/' + id + '/update';
        const delForm = document.getElementById('editPhotoDeleteForm');
        if (delForm) delForm.action = '/projects/photos/' + id;

        const titleInput = document.getElementById('editPhotoTitle');
        if (titleInput) titleInput.value = title || '';

        const typeSelect = document.getElementById('editPhotoType');
        if (typeSelect) typeSelect.value = photo_type || 'actual_site';

        const descInput = document.getElementById('editPhotoDescription');
        if (descInput) descInput.value = description || '';

        const takenInput = document.getElementById('editPhotoTakenAt');
        if (takenInput) takenInput.value = taken_at || '';

        const primaryChk = document.getElementById('editPhotoIsPrimary');
        if (primaryChk) primaryChk.checked = Boolean(is_primary && is_primary !== '0');

        const previewImg = document.getElementById('editPhotoPreviewImg');
        if (previewImg) previewImg.src = file_path || '';

        const previewTitle = document.getElementById('editPhotoPreviewTitle');
        if (previewTitle) previewTitle.innerText = title || 'Selected Media';

        const fileInput = document.getElementById('editPhotoFile');
        if (fileInput) fileInput.value = '';

        const urlInput = document.getElementById('editPhotoUrl');
        if (urlInput) urlInput.value = '';

        openModal('editPhotoModal');
    }

    function previewEditPhotoFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('editPhotoPreviewImg');
                if (previewImg) previewImg.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewEditPhotoUrl(url) {
        if (url && url.trim()) {
            const previewImg = document.getElementById('editPhotoPreviewImg');
            if (previewImg) previewImg.src = url.trim();
        }
    }

    function previewUploadPhotoFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewImg = document.getElementById('uploadPhotoPreviewImg');
                const wrap = document.getElementById('uploadPreviewWrap');
                if (previewImg) previewImg.src = e.target.result;
                if (wrap) wrap.style.display = 'flex';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewUploadPhotoUrl(url) {
        if (url && url.trim()) {
            const previewImg = document.getElementById('uploadPhotoPreviewImg');
            const wrap = document.getElementById('uploadPreviewWrap');
            if (previewImg) previewImg.src = url.trim();
            if (wrap) wrap.style.display = 'flex';
        }
    }

    function confirmDeletePhotoFromModal() {
        if (confirm('Are you sure you want to permanently delete this photo / blueprint from the project gallery?')) {
            const delForm = document.getElementById('editPhotoDeleteForm');
            if (delForm) delForm.submit();
        }
    }

    function filterGalleryCategory(cat, btn) {
        // Toggle active button style
        document.querySelectorAll('.active-gallery-filter').forEach(b => {
            b.classList.remove('active-gallery-filter');
            b.style.background = '';
            b.style.color = '';
            b.style.borderColor = '';
        });
        if (btn) {
            btn.classList.add('active-gallery-filter');
            btn.style.background = 'rgba(236, 72, 153, 0.2)';
            btn.style.color = '#ec4899';
            btn.style.borderColor = '#ec4899';
        }

        const cards = document.querySelectorAll('.gallery-photo-card');
        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            if (cat === 'all' || cardCat === cat) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function toggleShowFinancing(select) {
        const type = select.value;
        const instInput = document.getElementById('showProjFinInst');
        const disbInput = document.getElementById('showProjDisbursingEntity');

        if (type === 'bank_loan') {
            instInput.placeholder = 'e.g. BDO Unibank, BPI, Metrobank';
            if (disbInput) disbInput.value = (instInput.value || 'BDO Unibank') + ' Loan Disbursement Unit';
        } else if (type === 'pagibig_loan') {
            instInput.value = 'Pag-IBIG Fund (HDMF)';
            if (disbInput) disbInput.value = 'Pag-IBIG Fund (HDMF) Loan Release Division';
        } else if (type === 'client_equity') {
            instInput.value = 'Client Direct Equity';
            if (disbInput) disbInput.value = 'Client Personal Equity';
        } else {
            instInput.value = 'Direct Client Settlement';
            if (disbInput) disbInput.value = 'Client Direct Payment';
        }
    }

    function openLogDailyUsageModal(bomId, matName, remainingQty, unit) {
        document.getElementById('logDailyUsageForm').action = '/bom/' + bomId + '/daily-usage';
        document.getElementById('dailyUsageMatNameDisplay').innerText = matName;
        document.getElementById('dailyUsageUnitLabel').innerText = unit;
        document.getElementById('dailyUsageMaxLabel').innerText = remainingQty + ' ' + unit;
        document.getElementById('dailyUsageQtyInput').max = remainingQty;
        document.getElementById('dailyUsageQtyInput').value = Math.min(10, remainingQty);
        openModal('logDailyUsageModal');
    }

    function openTransferMaterialModal(bomId, matName, remainingQty, unit) {
        document.getElementById('transferMaterialForm').action = '/bom/' + bomId + '/transfer-project';
        document.getElementById('transferMatNameDisplay').innerText = matName;
        document.getElementById('transferUnitLabel').innerText = unit;
        document.getElementById('transferMaxLabel').innerText = remainingQty + ' ' + unit;
        document.getElementById('transferQtyInput').max = remainingQty;
        document.getElementById('transferQtyInput').value = remainingQty;
        openModal('transferMaterialModal');
    }

    function toggleTransferDest(mode) {
        const destGroup = document.getElementById('destProjectFormGroup');
        const destSelect = document.getElementById('destProjectSelect');
        if (mode === 'inter_project') {
            destGroup.style.display = 'block';
            destSelect.required = true;
        } else {
            destGroup.style.display = 'none';
            destSelect.required = false;
        }
    }

    function openReturnExcessModal(bomId, matName, remainingQty, unit) {
        document.getElementById('returnExcessForm').action = '/bom/' + bomId + '/return-excess';
        document.getElementById('retMaterialName').innerText = matName;
        document.getElementById('retMaxQty').innerText = remainingQty + ' ' + unit;
        document.getElementById('retInputQty').max = remainingQty;
        document.getElementById('retInputQty').value = remainingQty;
        openModal('returnExcessModal');
    }

    function openUpdateUsageModal(bomId, matName, allocatedQty, usedQty, unit) {
        document.getElementById('updateUsageForm').action = '/bom/' + bomId + '/update-usage';
        document.getElementById('usageMatName').value = matName;
        document.getElementById('usageUnitLbl').innerText = unit;
        document.getElementById('usageAllocLbl').innerText = allocatedQty + ' ' + unit;
        document.getElementById('usageInputQty').max = allocatedQty;
        document.getElementById('usageInputQty').value = usedQty;
        openModal('updateUsageModal');
    }

    function calcShowCostTotal() {
        const qty = parseFloat(document.getElementById('projShowQty').value) || 0;
        const rate = parseFloat(document.getElementById('projShowRate').value) || 0;
        const total = (qty * rate).toFixed(2);
        
        document.getElementById('projShowEst').value = total;
        document.getElementById('projShowAct').value = total;
    }

    function calcTotalWeight() {
        const s = parseInt(document.getElementById('wStruct').value) || 0;
        const e = parseInt(document.getElementById('wElec').value) || 0;
        const p = parseInt(document.getElementById('wPipe').value) || 0;
        const f = parseInt(document.getElementById('wFinish').value) || 0;
        const total = s + e + p + f;
        const el = document.getElementById('lblTotalWeight');
        el.innerText = total + '%';
        el.style.color = (total === 100) ? '#10b981' : '#f59e0b';
    }

    function openUpdateTaskModal(taskId, name, progress, actualCost, status) {
        document.getElementById('updateTaskForm').action = '/projects/tasks/' + taskId + '/update';
        document.getElementById('modalTaskName').value = name;
        document.getElementById('modalTaskProgress').value = progress;
        document.getElementById('lblTaskProg').innerText = progress + '%';
        document.getElementById('modalTaskActualCost').value = actualCost;
        document.getElementById('modalTaskStatus').value = status;
        openModal('updateTaskModal');
    }

    let currentDisciplineTab = 'all';
    let currentTimelineFilter = 'all';

    function switchChecklistTab(tab, btn) {
        currentDisciplineTab = tab;
        const tabs = document.querySelectorAll('.btn-tab');
        tabs.forEach(t => {
            t.classList.remove('active');
            t.style.background = 'rgba(15, 23, 42, 0.5)';
            t.style.borderColor = 'transparent';
            t.style.color = 'var(--text-secondary)';
        });
        btn.classList.add('active');
        btn.style.background = 'rgba(56, 189, 248, 0.15)';
        btn.style.borderColor = 'var(--border-color)';
        btn.style.color = '#38bdf8';

        applyCombinedChecklistFilter();
    }

    function filterChecklistByTimeline(filter) {
        currentTimelineFilter = filter;
        applyCombinedChecklistFilter();
    }

    function applyCombinedChecklistFilter() {
        const rows = document.querySelectorAll('.checklist-task-row');
        
        rows.forEach(row => {
            const phase = row.getAttribute('data-phase');
            const status = row.getAttribute('data-status');
            const progress = parseInt(row.getAttribute('data-progress')) || 0;

            let matchesTimeline = false;
            if (currentTimelineFilter === 'all') {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'phase1' && phase === 'phase1') {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'phase2' && phase === 'phase2') {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'phase3' && phase === 'phase3') {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'phase4' && phase === 'phase4') {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'phase5' && phase === 'phase5') {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'active' && progress > 0 && progress < 100) {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'completed' && (progress >= 100 || status === 'completed')) {
                matchesTimeline = true;
            } else if (currentTimelineFilter === 'upcoming' && progress === 0) {
                matchesTimeline = true;
            }

            row.style.display = matchesTimeline ? '' : 'none';
        });

        const secStruct = document.getElementById('chkSectionStructural');
        const secElec = document.getElementById('chkSectionElectrical');
        const secPipe = document.getElementById('chkSectionPiping');
        const secFinish = document.getElementById('chkSectionFinishing');

        if (currentDisciplineTab === 'all') {
            if (secStruct) secStruct.style.display = 'block';
            if (secElec) secElec.style.display = 'block';
            if (secPipe) secPipe.style.display = 'block';
            if (secFinish) secFinish.style.display = 'block';
        } else if (currentDisciplineTab === 'structural') {
            if (secStruct) secStruct.style.display = 'block';
            if (secElec) secElec.style.display = 'none';
            if (secPipe) secPipe.style.display = 'none';
            if (secFinish) secFinish.style.display = 'none';
        } else if (currentDisciplineTab === 'electrical') {
            if (secStruct) secStruct.style.display = 'none';
            if (secElec) secElec.style.display = 'block';
            if (secPipe) secPipe.style.display = 'none';
            if (secFinish) secFinish.style.display = 'none';
        } else if (currentDisciplineTab === 'piping') {
            if (secStruct) secStruct.style.display = 'none';
            if (secElec) secElec.style.display = 'none';
            if (secPipe) secPipe.style.display = 'block';
            if (secFinish) secFinish.style.display = 'none';
        } else if (currentDisciplineTab === 'finishing') {
            if (secStruct) secStruct.style.display = 'none';
            if (secElec) secElec.style.display = 'none';
            if (secPipe) secPipe.style.display = 'none';
            if (secFinish) secFinish.style.display = 'block';
        }
    }

    function jumpToTaskByTimeline(taskId) {
        if (!taskId) return;
        
        // Reset filters so the row is visible
        currentTimelineFilter = 'all';
        const filterSelect = document.getElementById('timelinePhaseFilter');
        if (filterSelect) filterSelect.value = 'all';
        currentDisciplineTab = 'all';

        const tabs = document.querySelectorAll('.btn-tab');
        tabs.forEach(t => {
            t.classList.remove('active');
            t.style.background = 'rgba(15, 23, 42, 0.5)';
            t.style.borderColor = 'transparent';
            t.style.color = 'var(--text-secondary)';
        });
        if (tabs[0]) {
            tabs[0].classList.add('active');
            tabs[0].style.background = 'rgba(56, 189, 248, 0.15)';
            tabs[0].style.borderColor = 'var(--border-color)';
            tabs[0].style.color = '#38bdf8';
        }

        applyCombinedChecklistFilter();

        const row = document.getElementById('task-row-' + taskId);
        if (row) {
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            row.style.transition = 'all 0.4s ease';
            row.style.backgroundColor = 'rgba(56, 189, 248, 0.25)';
            row.style.boxShadow = '0 0 15px rgba(56, 189, 248, 0.5)';
            setTimeout(() => {
                row.style.backgroundColor = '';
                row.style.boxShadow = 'none';
            }, 2500);
        }
    }

    function openAddSpecificTaskModal(category) {
        const sel = document.getElementById('addTaskCategorySelect');
        if (sel) {
            sel.value = category;
        }
        const nameInput = document.getElementById('addTaskNameInput');
        if (nameInput) {
            nameInput.value = '';
        }
        openModal('addTaskModal');
    }

    function openEditTaskModal(taskId, name, category, progress, status, personnelId, startDate, dueDate, budget, timelinePhase, timelineMonth) {
        document.getElementById('editTaskForm').action = '/projects/tasks/' + taskId + '/update';
        document.getElementById('editTaskNameInput').value = name;
        document.getElementById('editTaskCategorySelect').value = category;
        
        const progInput = document.getElementById('editTaskProgressInput');
        progInput.value = progress;
        progInput.min = progress; // Enforce monotonic progress (cannot decrease)

        const statusSelect = document.getElementById('editTaskStatusSelect');
        
        // If completed (100%), lock status and progress to prevent regression
        if (progress >= 100 || status === 'completed') {
            progInput.readOnly = true;
            progInput.style.opacity = '0.6';
            statusSelect.innerHTML = '<option value="completed">🔒 Completed (100% - Locked)</option>';
            statusSelect.value = 'completed';
        } else {
            progInput.readOnly = false;
            progInput.style.opacity = '1';
            let optionsHtml = '';
            if (progress <= 0) {
                optionsHtml += '<option value="not_started">Not Started (0%)</option>';
            }
            optionsHtml += '<option value="in_progress">In Progress (' + Math.max(1, progress) + '-99%)</option>';
            optionsHtml += '<option value="completed">Completed (100%)</option>';
            statusSelect.innerHTML = optionsHtml;
            statusSelect.value = status;
        }

        const persSelect = document.getElementById('editTaskPersonnelSelect');
        if (persSelect) {
            persSelect.value = personnelId || '';
        }
        const phaseSelect = document.getElementById('editTaskTimelinePhase');
        if (phaseSelect && timelinePhase) {
            phaseSelect.value = timelinePhase;
        }
        const monthInput = document.getElementById('editTaskTimelineMonth');
        if (monthInput) {
            monthInput.value = timelineMonth || '';
        }
        openModal('editTaskModal');
    }

    function openChecklistJsonModal() {
        openModal('checklistJsonModal');
        const pre = document.getElementById('checklistJsonContent');
        pre.innerText = 'Fetching real-time structured checklist state...';
        fetch('/projects/{{ $project->id }}/checklist-state')
            .then(res => res.json())
            .then(data => {
                pre.innerText = JSON.stringify(data, null, 2);
            })
            .catch(err => {
                pre.innerText = 'Error loading checklist state JSON: ' + err.message;
            });
    }

    function copyChecklistJson() {
        const text = document.getElementById('checklistJsonContent').innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert('Checklist state JSON successfully copied to clipboard!');
        });
    }

    // Toast Notification System (Floating, Anti-Jump)
    function showChecklistToast(message, isError = false) {
        let toastContainer = document.getElementById('checklistToastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'checklistToastContainer';
            toastContainer.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 99999; display: flex; flex-direction: column; gap: 8px; pointer-events: none;';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${isError ? 'rgba(239, 68, 68, 0.95)' : 'rgba(15, 23, 42, 0.95)'};
            color: ${isError ? '#ffffff' : '#38bdf8'};
            border: 1px solid ${isError ? '#ef4444' : 'rgba(56, 189, 248, 0.4)'};
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(8px);
            opacity: 0;
            transform: translateY(12px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: auto;
        `;
        toast.innerHTML = `<span>${message}</span>`;
        toastContainer.appendChild(toast);

        // Slide in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });

        // Slide out
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(12px)';
            setTimeout(() => toast.remove(), 350);
        }, 2800);
    }

    // Anti-Jump AJAX Checkbox Toggle
    function toggleChecklistAjax(taskId, inputEl) {
        inputEl.disabled = true;
        fetch('/projects/tasks/' + taskId + '/toggle', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status !== 200 || !body.success) {
                showChecklistToast(body.error || 'Action Blocked: Irreversible completion enforced.', true);
                inputEl.disabled = false;
                inputEl.checked = true;
                return;
            }

            const row = document.getElementById('task-row-' + taskId);
            if (row) {
                row.setAttribute('data-status', 'completed');
                row.style.background = 'rgba(16, 185, 129, 0.05)';

                const doneCell = row.querySelector('.cell-done');
                if (doneCell) {
                    doneCell.innerHTML = `<span class="locked-done-badge" title="🔒 Irreversible Completion: This task is completed and permanently locked." style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 6px; background: rgba(16, 185, 129, 0.2); border: 1.5px solid #10b981; color: #10b981; font-weight: 900; font-size: 0.9rem; cursor: not-allowed;">✓</span>`;
                }

                const titleText = row.querySelector('.task-title-text');
                if (titleText) {
                    titleText.style.color = '#94a3b8';
                    titleText.style.textDecoration = 'line-through';
                }

                const statusCell = row.querySelector('.cell-status');
                if (statusCell) {
                    statusCell.innerHTML = `<span class="badge badge-completed" style="font-size: 0.8rem; padding: 5px 12px; display: inline-flex; align-items: center; gap: 4px;" title="🔒 Permanent milestone: Completed and materials mobilized">🔒 Completed</span>`;
                }

                const delForm = row.querySelector('.cell-actions form');
                if (delForm) {
                    delForm.outerHTML = `<button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 7px; opacity: 0.4; cursor: not-allowed;" title="🔒 Completed milestones cannot be deleted" disabled>🔒</button>`;
                }
            }

            updateGlobalMetrics(body);
            if (body.active_materials_data) {
                renderActiveMaterials(body.active_materials_data);
            }
            showChecklistToast(`✓ ${body.task_name || 'Task'} marked Completed & materials accumulated! [🔒 Locked]`, false);
        })
        .catch(err => {
            showChecklistToast('Error updating task: ' + err.message, true);
            inputEl.disabled = false;
        });
    }

    // Anti-Jump AJAX Task Status Selector (Forward-Only Monotonic)
    function updateTaskStatusAjax(taskId, newStatus, selectEl) {
        selectEl.disabled = true;
        fetch('/projects/tasks/' + taskId + '/status', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            selectEl.disabled = false;
            if (status !== 200 || !body.success) {
                showChecklistToast(body.error || 'Action Blocked: Forward-Only progression rule enforced.', true);
                if (body.status !== undefined) {
                    selectEl.value = body.status;
                }
                return;
            }

            const row = document.getElementById('task-row-' + taskId);
            if (row) {
                row.setAttribute('data-status', body.status);

                if (body.status === 'completed') {
                    row.style.background = 'rgba(16, 185, 129, 0.05)';
                    const doneCell = row.querySelector('.cell-done');
                    if (doneCell) {
                        doneCell.innerHTML = `<span class="locked-done-badge" title="🔒 Irreversible Completion: This task is completed and permanently locked." style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 6px; background: rgba(16, 185, 129, 0.2); border: 1.5px solid #10b981; color: #10b981; font-weight: 900; font-size: 0.9rem; cursor: not-allowed;">✓</span>`;
                    }
                    const titleText = row.querySelector('.task-title-text');
                    if (titleText) {
                        titleText.style.color = '#94a3b8';
                        titleText.style.textDecoration = 'line-through';
                    }
                    const statusCell = row.querySelector('.cell-status');
                    if (statusCell) {
                        statusCell.innerHTML = `<span class="badge badge-completed" style="font-size: 0.8rem; padding: 5px 12px; display: inline-flex; align-items: center; gap: 4px;" title="🔒 Permanent milestone: Completed and materials mobilized">🔒 Completed</span>`;
                    }
                    const delForm = row.querySelector('.cell-actions form');
                    if (delForm) {
                        delForm.outerHTML = `<button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 7px; opacity: 0.4; cursor: not-allowed;" title="🔒 Completed milestones cannot be deleted" disabled>🔒</button>`;
                    }
                } else if (body.status === 'in_progress') {
                    selectEl.style.color = '#38bdf8';
                    selectEl.style.borderColor = 'rgba(56, 189, 248, 0.5)';
                    // Disable 'not_started' option once in_progress (Forward-only lock)
                    const notStartedOpt = selectEl.querySelector('option[value="not_started"]');
                    if (notStartedOpt) {
                        notStartedOpt.disabled = true;
                    }
                }
            }

            updateGlobalMetrics(body);
            if (body.active_materials_data) {
                renderActiveMaterials(body.active_materials_data);
            }
            showChecklistToast(`⚡ ${body.task_name || 'Task'} updated to ${body.status_label} & materials synchronized!`, false);
        })
        .catch(err => {
            selectEl.disabled = false;
            showChecklistToast('Error updating status: ' + err.message, true);
        });
    }

    // Anti-Jump AJAX Quick Timeline Update
    function updateQuickTimelineAjax(taskId, phaseVal, selectEl) {
        selectEl.disabled = true;
        fetch('/projects/tasks/' + taskId + '/quick-timeline', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ timeline_phase: phaseVal })
        })
        .then(res => res.json())
        .then(data => {
            selectEl.disabled = false;
            const row = document.getElementById('task-row-' + taskId);
            if (row && data.timeline_phase_key) {
                row.setAttribute('data-phase', data.timeline_phase_key);
            }
            showChecklistToast(`📅 Timeline phase updated to: ${phaseVal}`, false);
        })
        .catch(err => {
            selectEl.disabled = false;
            showChecklistToast('Error updating timeline: ' + err.message, true);
        });
    }

    // Live Synchronized Active Project Materials Re-Renderer (Zero Page Reload / Zero Scroll Jump)
    function renderActiveMaterials(activeData) {
        if (!activeData) return;

        // 1. Update KPI summary cards
        const countEl = document.getElementById('activeMatCount');
        if (countEl && activeData.total_active_items !== undefined) {
            countEl.innerText = `${activeData.total_active_items} Items`;
        }

        const unitsEl = document.getElementById('activeMatUnits');
        if (unitsEl && activeData.total_active_units !== undefined) {
            unitsEl.innerText = `${Number(activeData.total_active_units).toLocaleString()} Units`;
        }

        const valueEl = document.getElementById('activeMatValue');
        if (valueEl && activeData.total_active_value !== undefined) {
            valueEl.innerText = '₱' + Number(activeData.total_active_value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        const tasksEl = document.getElementById('activeTasksCount');
        if (tasksEl && activeData.active_tasks_count !== undefined) {
            tasksEl.innerText = `${activeData.active_tasks_count} Tasks Active`;
        }

        const compCountEl = document.getElementById('activeTasksCompletedCount');
        if (compCountEl && activeData.completed_tasks_count !== undefined) {
            compCountEl.innerText = activeData.completed_tasks_count;
        }

        const inProgCountEl = document.getElementById('activeTasksInProgCount');
        if (inProgCountEl && activeData.in_progress_tasks_count !== undefined) {
            inProgCountEl.innerText = activeData.in_progress_tasks_count;
        }

        // 2. Re-render Table Body
        const tbody = document.getElementById('activeMaterialsTableBody');
        if (!tbody) return;

        if (!activeData.materials || activeData.materials.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyActiveMaterialsRow">
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        No active materials accumulated yet. Check off tasks or advance tasks to "In Progress" in the checklist above to dynamically activate construction materials.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        activeData.materials.forEach((mat, idx) => {
            let catColor = '#ec4899';
            if (mat.category === 'Structural') catColor = '#38bdf8';
            else if (mat.category === 'Electrical') catColor = '#f59e0b';
            else if (mat.category === 'Piping & Plumbing' || mat.category === 'Piping') catColor = '#10b981';

            let taskChips = '';
            if (mat.task_names && Array.isArray(mat.task_names)) {
                mat.task_names.forEach(tName => {
                    taskChips += `<span class="badge" style="font-size: 0.675rem; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25);">📍 ${tName}</span>`;
                });
            }

            const statusBadge = mat.is_all_completed
                ? `<span class="badge badge-completed" style="font-size: 0.725rem;">🔒 Installed & Finalized</span>`
                : `<span class="badge badge-in_progress" style="font-size: 0.725rem;">⚡ In Consumption</span>`;

            html += `
                <tr class="active-mat-row" data-name="${(mat.material_name || '').toLowerCase()}" data-category="${(mat.category || '').toLowerCase()}">
                    <td style="text-align: center; color: var(--text-muted); font-family: var(--font-mono);">${idx + 1}</td>
                    <td><strong style="color: #f8fafc; font-size: 0.875rem;">🧱 ${mat.material_name}</strong></td>
                    <td><span class="spec-chip" style="font-size: 0.7rem; color: ${catColor}; border-color: ${catColor}44;">${mat.category}</span></td>
                    <td style="text-align: right; font-family: var(--font-mono); font-weight: 700; color: #f8fafc;">${Number(mat.total_quantity).toLocaleString()} ${mat.unit}</td>
                    <td style="text-align: right; font-family: var(--font-mono); color: var(--text-muted);">₱${Number(mat.unit_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td style="text-align: right; font-family: var(--font-mono); font-weight: 800; color: #10b981;">₱${Number(mat.total_cost).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td><div style="display: flex; flex-wrap: wrap; gap: 4px;">${taskChips}</div></td>
                    <td style="text-align: center;">${statusBadge}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    // Manual / Button Refresh Active Materials Sync
    function refreshActiveMaterialsAjax() {
        fetch('/projects/{{ $project->id }}/active-materials')
            .then(res => res.json())
            .then(data => {
                renderActiveMaterials(data);
                showChecklistToast('📦 Active project materials synchronized with task list!', false);
            })
            .catch(err => {
                showChecklistToast('Error syncing materials: ' + err.message, true);
            });
    }

    // Client-side search filter for active materials
    function filterActiveMaterialsTable(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#activeMaterialsTableBody .active-mat-row');
        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const cat = row.getAttribute('data-category') || '';
            if (!q || name.includes(q) || cat.includes(q)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Live Synchronized Global Metrics Update (Zero Page Reload / Zero Scroll Shift)
    function updateGlobalMetrics(data) {
        if (!data) return;

        // Overall progress & Badge
        const overallEl = document.getElementById('kpiOverallVal');
        if (overallEl && data.overall_progress !== undefined) {
            overallEl.innerText = data.overall_progress + '%';
        }
        const badgeEl = document.getElementById('sec4CompletedBadge');
        if (badgeEl && data.total_completed !== undefined && data.total_tasks !== undefined) {
            badgeEl.innerText = `${data.total_completed} / ${data.total_tasks} Tasks Completed (${data.overall_progress}%)`;
        }

        // Structural
        if (data.structural_progress !== undefined) {
            const val = document.getElementById('kpiStructVal');
            if (val) val.innerText = data.structural_progress + '%';
            const bar = document.getElementById('kpiStructBar');
            if (bar) bar.style.width = data.structural_progress + '%';
            const done = document.getElementById('kpiStructDone');
            if (done && data.structural_done !== undefined) done.innerText = `${data.structural_done} / ${data.structural_total} Tasks Done`;
            const contrib = document.getElementById('kpiStructContrib');
            if (contrib && data.structural_contrib !== undefined) contrib.innerText = `+${data.structural_contrib}%`;
            const secBadge = document.getElementById('structSectionDoneBadge');
            if (secBadge && data.structural_done !== undefined) secBadge.innerText = `${data.structural_done} / ${data.structural_total} Tasks Done`;
            const secVal = document.getElementById('structSectionProgVal');
            if (secVal) secVal.innerText = data.structural_progress + '%';
        }

        // Electrical
        if (data.electrical_progress !== undefined) {
            const val = document.getElementById('kpiElecVal');
            if (val) val.innerText = data.electrical_progress + '%';
            const bar = document.getElementById('kpiElecBar');
            if (bar) bar.style.width = data.electrical_progress + '%';
            const done = document.getElementById('kpiElecDone');
            if (done && data.electrical_done !== undefined) done.innerText = `${data.electrical_done} / ${data.electrical_total} Tasks Done`;
            const contrib = document.getElementById('kpiElecContrib');
            if (contrib && data.electrical_contrib !== undefined) contrib.innerText = `+${data.electrical_contrib}%`;
            const secBadge = document.getElementById('elecSectionDoneBadge');
            if (secBadge && data.electrical_done !== undefined) secBadge.innerText = `${data.electrical_done} / ${data.electrical_total} Tasks Done`;
            const secVal = document.getElementById('elecSectionProgVal');
            if (secVal) secVal.innerText = data.electrical_progress + '%';
        }

        // Piping
        if (data.piping_progress !== undefined) {
            const val = document.getElementById('kpiPipeVal');
            if (val) val.innerText = data.piping_progress + '%';
            const bar = document.getElementById('kpiPipeBar');
            if (bar) bar.style.width = data.piping_progress + '%';
            const done = document.getElementById('kpiPipeDone');
            if (done && data.piping_done !== undefined) done.innerText = `${data.piping_done} / ${data.piping_total} Tasks Done`;
            const contrib = document.getElementById('kpiPipeContrib');
            if (contrib && data.piping_contrib !== undefined) contrib.innerText = `+${data.piping_contrib}%`;
            const secBadge = document.getElementById('pipeSectionDoneBadge');
            if (secBadge && data.piping_done !== undefined) secBadge.innerText = `${data.piping_done} / ${data.piping_total} Tasks Done`;
            const secVal = document.getElementById('pipeSectionProgVal');
            if (secVal) secVal.innerText = data.piping_progress + '%';
        }

        // Finishing
        if (data.finishing_progress !== undefined) {
            const val = document.getElementById('kpiFinishVal');
            if (val) val.innerText = data.finishing_progress + '%';
            const bar = document.getElementById('kpiFinishBar');
            if (bar) bar.style.width = data.finishing_progress + '%';
            const done = document.getElementById('kpiFinishDone');
            if (done && data.finishing_done !== undefined) done.innerText = `${data.finishing_done} / ${data.finishing_total} Tasks Done`;
            const contrib = document.getElementById('kpiFinishContrib');
            if (contrib && data.finishing_contrib !== undefined) contrib.innerText = `+${data.finishing_contrib}%`;
            const secBadge = document.getElementById('finishSectionDoneBadge');
            if (secBadge && data.finishing_done !== undefined) secBadge.innerText = `${data.finishing_done} / ${data.finishing_total} Tasks Done`;
            const secVal = document.getElementById('finishSectionProgVal');
            if (secVal) secVal.innerText = data.finishing_progress + '%';
        }
    }

    function updateBomPrice(select) {
        const option = select.options[select.selectedIndex];
        if (option && option.dataset.price) {
            document.getElementById('projBomUnitPrice').value = option.dataset.price;
        }
        calculateAllocatedTotal();
    }

    function calculateAllocatedTotal() {
        const qty = parseFloat(document.getElementById('projBomQty')?.value) || 0;
        const price = parseFloat(document.getElementById('projBomUnitPrice')?.value) || 0;
        const total = qty * price;
        const disp = document.getElementById('projBomTotalValueDisp');
        if (disp) {
            disp.innerText = '₱ ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }
</script>
@endsection
