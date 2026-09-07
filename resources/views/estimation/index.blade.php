@extends('layouts.app')

@section('title', 'Service Cost Estimator & Project Quotation Builder - St. Bilfrid Development Corporation')
@section('page_title', 'Service Cost Estimator & Project Quotation Engine')

@section('top_actions')
    <button class="btn-primary" onclick="openModal('estimateModal')">+ Perform New Cost Estimation</button>
@endsection

@section('content')

<!-- Estimator Formula Information Card -->
<div class="glass-panel" style="padding: 24px 28px; margin-bottom: 28px; background: #fafbfc; border: 1px solid var(--border-color); border-left: 4px solid var(--primary-red); box-shadow: var(--card-shadow);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div style="flex: 1; min-width: 320px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="spec-chip" style="color: var(--primary-red); background: var(--primary-red-light); border-color: rgba(220, 38, 38, 0.3);">
                    UNIT COST ESTIMATION
                </span>
                <span class="spec-chip" style="color: #059669; background: #ecfdf5; border-color: rgba(16, 185, 129, 0.3);">
                    ACCURATE CONTRACT BUDGETS
                </span>
            </div>
            <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-bottom: 6px;">
                Automated Land & Floor Area Cost Estimation Engine (₱)
            </h4>
            <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5;">
                Cost estimations are calculated based on contract service categories, constructible <strong>Floor Area (m²)</strong>, and site <strong>Land Area (m²)</strong> preparation in Philippine Pesos (₱). Generated estimates can be directly converted into tracked construction projects.
            </p>
        </div>

        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px 16px; font-size: 0.8rem; color: var(--text-secondary); max-width: 360px;">
            <strong style="color: var(--primary-red);">Standard Rates Benchmark:</strong>
            <p style="margin-top: 4px; font-size: 0.775rem;">
                Residential: ₱1,100/m² &bull; Commercial: ₱1,400/m² &bull; Industrial: ₱1,600/m² &bull; Renovation: ₱800/m² + Land Prep: ₱200/m².
            </p>
        </div>
    </div>
</div>

<!-- Requests Table -->
<div class="glass-panel">
    <div class="panel-header">
        <div>
            <h3 class="panel-title">Service Requests & Project Quotations</h3>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Calculated project estimates, client specifications, and one-click project initialization</span>
        </div>
        <button class="btn-primary" style="font-size: 0.825rem; padding: 6px 14px;" onclick="openModal('estimateModal')">
            + New Estimate
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Code & Client</th>
                    <th>Service Type</th>
                    <th>Land & Floor Area Specs</th>
                    <th>Total Contract Estimate (₱)</th>
                    <th>Target Start Date</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td>
                        <strong style="color: var(--text-primary); font-size: 0.95rem;">{{ $req->client_name }}</strong>
                        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: #ef4444;">{{ $req->request_code }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $req->client_email }}</div>
                    </td>
                    <td>
                        <strong style="color: #38bdf8;">{{ $req->service_type }}</strong>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 2px;">
                            <span class="spec-chip" style="font-size: 0.75rem;">Land: {{ number_format($req->land_area_sqm) }} m²</span>
                            <span class="spec-chip" style="font-size: 0.75rem;">Floor: {{ number_format($req->floor_area_sqm) }} m²</span>
                        </div>
                    </td>
                    <td>
                        <strong style="font-family: var(--font-mono); font-size: 1.05rem; color: #10b981;">
                            ₱{{ number_format($req->estimated_cost, 2) }}
                        </strong>
                    </td>
                    <td style="font-family: var(--font-mono); font-size: 0.825rem;">
                        {{ $req->requested_start_date->format('M d, Y') }}
                    </td>
                    <td>
                        @if($req->status === 'approved')
                            <span class="badge badge-completed">Initialized</span>
                        @else
                            <span class="badge badge-in_progress">Ready / Calculated</span>
                        @endif
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 6px; align-items: center;">
                            @if($req->status !== 'approved')
                                <form action="{{ route('estimation.initialize', $req->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-primary" style="font-size: 0.75rem; padding: 5px 10px; white-space: nowrap; background: #38bdf8; border-color: #38bdf8;">
                                        Initialize to Tracker
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 0.8rem; color: #10b981; font-weight: 700; white-space: nowrap;">In Active Tracker</span>
                            @endif

                            <form action="{{ route('estimation.destroy', $req->id) }}" method="POST" onsubmit="return confirm('Delete this estimation record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-secondary" style="font-size: 0.75rem; padding: 5px 8px; color: #ef4444;" title="Delete Estimate">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="color: var(--text-muted); text-align: center; padding: 36px;">
                        No service requests logged in system.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Cost Estimation -->
<div class="modal-overlay" id="estimateModal">
    <div class="modal-box modal-box-large">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h3 style="font-weight: 700;">Perform New Service Cost Estimation</h3>
                <span style="font-size: 0.8rem; color: var(--text-muted);">Calculates estimated total contract cost based on constructible areas</span>
            </div>
            <button onclick="closeModal('estimateModal')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('estimation.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="client_name" class="form-input" placeholder="e.g. Engr. Gabriel Santos" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Client Email</label>
                    <input type="email" name="client_email" class="form-input" placeholder="client@example.com" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Client Phone</label>
                    <input type="text" name="client_phone" class="form-input" placeholder="+63 (917) 000-0000">
                </div>

                <div class="form-group">
                    <label class="form-label">Target Start Date</label>
                    <input type="date" name="requested_start_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Service Classification</label>
                <select name="service_type" class="form-select" id="estServiceType" onchange="calcLiveEstimate()" required>
                    <option value="Commercial Construction">Commercial Construction (₱1,400/m²)</option>
                    <option value="Residential Build" selected>Residential Build (₱1,100/m²)</option>
                    <option value="Industrial Complex">Industrial Complex (₱1,600/m²)</option>
                    <option value="Renovation & Overhaul">Renovation & Overhaul (₱800/m²)</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Land Area (m²)</label>
                    <input type="number" step="0.01" name="land_area_sqm" id="estLandArea" class="form-input" placeholder="e.g. 500.00" oninput="calcLiveEstimate()" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Constructible Floor Area (m²)</label>
                    <input type="number" step="0.01" name="floor_area_sqm" id="estFloorArea" class="form-input" placeholder="e.g. 350.00" oninput="calcLiveEstimate()" required>
                </div>
            </div>

            <!-- Live Calculated Cost Preview -->
            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary-red); text-transform: uppercase; margin-bottom: 8px;">
                    Estimated Contract Value:
                </div>
                <div>
                    <div id="previewTotalEst" style="font-family: var(--font-mono); font-weight: 800; color: #10b981; font-size: 1.4rem;">₱ 0.00</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Scope Notes & Project Details</label>
                <textarea name="notes" class="form-textarea" rows="2" placeholder="Specify any unique structural or trade requirements..."></textarea>
            </div>

            <div class="form-group" style="background: #f8fafc; padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                    <input type="checkbox" name="initialize_project_now" value="1" style="width: 18px; height: 18px; accent-color: var(--primary-red);" checked>
                    <span><strong>Initialize directly into Active Project Tracker</strong> upon calculation</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn-secondary" onclick="closeModal('estimateModal')">Cancel</button>
                <button type="submit" class="btn-primary">Calculate & Save Estimate</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function calcLiveEstimate() {
        const floor = parseFloat(document.getElementById('estFloorArea').value) || 0;
        const land = parseFloat(document.getElementById('estLandArea').value) || 0;
        const type = document.getElementById('estServiceType').value;

        let rate = 1100;
        if (type === 'Commercial Construction') rate = 1400;
        else if (type === 'Industrial Complex') rate = 1600;
        else if (type === 'Renovation & Overhaul') rate = 800;

        const total = (floor * rate) + (land * 200);

        document.getElementById('previewTotalEst').innerText = '₱ ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
</script>
@endsection
