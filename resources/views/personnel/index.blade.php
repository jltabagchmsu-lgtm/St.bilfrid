@extends('layouts.app')

@section('title', 'Engineers & Architects Roster - St. Bilfrid Development Corporation')
@section('page_title', 'Engineers & Architects Roster')

@section('top_actions')
    <button class="btn-primary" onclick="openModal('addPersonnelModal')">+ Register Professional</button>
@endsection

@section('content')

<div class="glass-panel">
    <div class="panel-header">
        <h3 class="panel-title">Assigned Professionals & Credentials Matrix</h3>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Architects, Structural, Electrical, & Plumbing Engineers</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px;">
        @foreach($personnel as $p)
        <div style="background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--card-shadow);">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary);">{{ $p->name }}</h4>
                        <div style="color: var(--primary-red); font-size: 0.875rem; font-weight: 700;">{{ $p->title }}</div>
                    </div>
                    <span class="spec-chip" style="font-size: 0.75rem;">PRC: {{ $p->license_no ?? 'PENDING' }}</span>
                </div>

                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 16px;">
                    <div>Email: {{ $p->email }}</div>
                    <div>Phone: {{ $p->phone ?? 'N/A' }}</div>
                    <div style="margin-top: 4px; color: var(--text-muted);">Specialization: {{ $p->specialization ?? 'General Engineering' }}</div>
                </div>
            </div>

            <div style="border-top: 1px solid var(--border-color); margin-top: 12px; padding-top: 12px;">
                <div style="font-size: 0.775rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">Active Project Involvement ({{ $p->projects->count() }})</div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @forelse($p->projects as $prj)
                        <a href="{{ route('projects.show', $prj->id) }}" style="text-decoration: none;" class="spec-chip">
                            {{ $prj->project_code }}
                        </a>
                    @empty
                        <span style="font-size: 0.8rem; color: var(--text-muted);">No current project assignments</span>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal: Register Professional -->
<div class="modal-overlay" id="addPersonnelModal">
    <div class="modal-box">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700;">Register Engineer or Architect</h3>
            <button onclick="closeModal('addPersonnelModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('personnel.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Full Name & Honorific</label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Engr. Alexandra Wright" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Title / Role</label>
                    <select name="title" class="form-select" required>
                        <option value="Lead Principal Architect">Lead Principal Architect</option>
                        <option value="Senior Structural Engineer">Senior Structural Engineer</option>
                        <option value="Lead Electrical Engineer">Lead Electrical Engineer</option>
                        <option value="Senior Plumbing & Piping Engineer">Senior Plumbing & Piping Engineer</option>
                        <option value="Project Director">Project Director</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Professional License No.</label>
                    <input type="text" name="license_no" class="form-input" placeholder="PE-904123">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-input" placeholder="+1 (555) 000-0000">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Engineering / Architectural Specialization</label>
                <input type="text" name="specialization" class="form-input" placeholder="e.g. HVAC, Heavy Concrete Framework">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('addPersonnelModal')">Cancel</button>
                <button type="submit" class="btn-primary">Register Professional</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>
@endsection
