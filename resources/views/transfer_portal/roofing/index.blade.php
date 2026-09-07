@extends('layouts.app')

@section('title', 'Roofing Materials Transfer Station - St. Bilfrid Development Corporation')
@section('page_title', 'Roofing Materials Transfer Station')

@section('top_actions')
<div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
    @if(Auth::user()->isAdmin())
        <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #cbd5e1; font-size: 0.8rem; font-weight: 700; padding: 8px 14px; border: 1px solid rgba(148, 163, 184, 0.3); display: inline-flex; align-items: center; gap: 6px;">
            <span>Administrator Audit Mode (View-Only)</span>
        </span>
    @else
        <button type="button" class="btn-primary" onclick="openModal('restockStockModal')" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
            <span>Restock Roofing Inventory</span>
        </button>
        <button type="button" class="btn-primary" onclick="openModal('dispatchStockModal')" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
            <span>Dispatch Roofing Stock to Project</span>
        </button>
        <button type="button" class="btn-secondary" onclick="openModal('interProjectModal')" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
            <span>Inter-Project Transfer</span>
        </button>
    @endif
</div>
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Department Header & Role Indicator -->
    <div class="card" style="background: #fafbfc; border: 1px solid var(--border-color); border-left: 4px solid var(--primary-red); padding: 22px 26px; border-radius: var(--radius-lg); position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                    <span style="background: #fef2f2; color: var(--primary-red); font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(220, 38, 38, 0.3); text-transform: uppercase; letter-spacing: 0.06em;">
                        Roofing Trade Specialization
                    </span>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                        Logged in as: <strong style="color: var(--text-primary);">{{ Auth::user()->name }}</strong> ({{ Auth::user()->email }})
                    </span>
                    @if(Auth::user()->isAdmin())
                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-size: 0.75rem; font-weight: 700; border: 1px solid rgba(245, 158, 11, 0.3);">
                            Audit Mode: Read Only
                        </span>
                    @endif
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;">
                    Roofing & Metal Sheets Material Dispatch & Transfer Hub
                </h2>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 4px; max-width: 800px;">
                    Dedicated terminal for dispatching, transferring, restocking, and reconciling pre-painted rib-type roofing sheets, C-purlins, flashing, gutters, fasteners, and waterproofing materials across all firm project sites.
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="#transferLedger" class="btn-secondary" style="font-size: 0.825rem;">
                    <span>View Vouchers Log</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Admin View-Only Notice Banner -->
    @if(Auth::user()->isAdmin())
    <div class="card" style="background: #fafbfc; border: 1px solid var(--border-color); border-left: 5px solid #f59e0b; padding: 16px 20px; border-radius: var(--radius-md); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div>
                <div style="font-weight: 700; color: #d97706; font-size: 0.95rem;">
                    Master Administrator Audit & Monitoring Mode Active
                </div>
                <div style="font-size: 0.825rem; color: var(--text-secondary); margin-top: 2px;">
                    You have complete read-only visibility into Central Warehouse roofing stock levels, site allocations, low stock alerts, and verified voucher history. Modifying stock (dispatching, restocking, inter-site transferring, or returning excess) is restricted to the authorized <strong>Roofing Materials Transfer Officer</strong>.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- KPI Metric Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px;">
        <div class="card" style="padding: 20px; border-left: 4px solid #ef4444;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Warehouse Roofing Stock</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #f8fafc; margin-top: 8px;">
                {{ number_format($totalWarehouseStockUnits) }}
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-secondary);">units</span>
            </div>
            <div style="font-size: 0.775rem; color: #10b981; margin-top: 6px; font-weight: 600;">
                Valuation: ₱{{ number_format($totalWarehouseValuation, 2) }}
            </div>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid #38bdf8;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Allocated to Projects</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #38bdf8; margin-top: 8px;">
                {{ number_format($totalAllocatedUnits) }}
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-secondary);">units</span>
            </div>
            <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 6px;">
                Active site allocations
            </div>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Installed / Consumed</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #f59e0b; margin-top: 8px;">
                {{ number_format($totalUsedUnits) }}
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-secondary);">units</span>
            </div>
            <div style="font-size: 0.775rem; color: var(--text-secondary); margin-top: 6px;">
                Verified on-site installation
            </div>
        </div>

        <div class="card" style="padding: 20px; border-left: 4px solid #10b981;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Remaining Unused Site Balance</div>
            <div style="font-size: 1.8rem; font-weight: 800; color: #10b981; margin-top: 8px;">
                {{ number_format($totalRemainingOnSite) }}
                <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-secondary);">units</span>
            </div>
            <div style="font-size: 0.775rem; color: #38bdf8; margin-top: 6px; font-weight: 600;">
                Eligible for transfer or return
            </div>
        </div>
    </div>

    <!-- Section 1: Central Warehouse Roofing Materials Catalog & Direct Dispatch / Restock -->
    <div class="card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <span>Central Warehouse Roofing Inventory Catalog</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">
                    Available roofing materials ready for immediate site dispatch or warehouse replenishment
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                @if(!Auth::user()->isAdmin())
                <button type="button" class="btn-secondary" onclick="openModal('restockStockModal')" style="padding: 8px 14px; font-size: 0.85rem; font-weight: 700; color: #a78bfa; border-color: rgba(139, 92, 246, 0.4);">
                    <span>Quick Restock</span>
                </button>
                @endif
                <form method="GET" action="{{ route('roofing.index') }}" style="display: flex; gap: 10px;">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search roofing items..." class="form-input" style="padding: 8px 14px; font-size: 0.85rem; width: 220px;">
                    @if($selectedProjectId)
                        <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
                    @endif
                    <button type="submit" class="btn-secondary" style="padding: 8px 14px; font-size: 0.85rem;">Search</button>
                    @if($search)
                        <a href="{{ route('roofing.index', array_filter(['project_id' => $selectedProjectId])) }}" class="btn-secondary" style="padding: 8px 12px; font-size: 0.85rem;">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material Code</th>
                        <th>Description & Specifications</th>
                        <th>Category</th>
                        <th style="text-align: right;">Unit Rate</th>
                        <th style="text-align: center;">Available Stock</th>
                        <th style="text-align: right;">Stock Valuation</th>
                        <th style="text-align: center;">Action Controls</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roofingMaterials as $mat)
                    @php
                        $isLowStock = $mat->stock_quantity <= 25;
                    @endphp
                    <tr style="{{ $isLowStock ? 'background: rgba(239, 68, 68, 0.05);' : '' }}">
                        <td style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: #fca5a5;">
                            {{ $mat->material_code }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="font-weight: 700; color: var(--text-primary);">{{ $mat->name }}</span>
                                @if($isLowStock)
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 0.675rem; font-weight: 800; padding: 2px 6px;">
                                        LOW STOCK
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Standard stock unit: <strong>{{ $mat->unit }}</strong></div>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3);">
                                {{ $mat->category }}
                            </span>
                        </td>
                        <td style="text-align: right; font-family: var(--font-mono); font-weight: 600;">
                            ₱{{ number_format($mat->unit_cost, 2) }} / {{ $mat->unit }}
                        </td>
                        <td style="text-align: center;">
                            <span style="font-size: 1.05rem; font-weight: 800; color: {{ $mat->stock_quantity > 100 ? '#10b981' : ($isLowStock ? '#ef4444' : '#f59e0b') }};">
                                {{ number_format($mat->stock_quantity) }}
                            </span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $mat->unit }}</span>
                        </td>
                        <td style="text-align: right; font-family: var(--font-mono); font-weight: 700; color: var(--text-primary);">
                            ₱{{ number_format($mat->stock_quantity * $mat->unit_cost, 2) }}
                        </td>
                        <td style="text-align: center;">
                            @if(Auth::user()->isAdmin())
                                <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; font-size: 0.75rem; font-style: italic;">
                                    View-Only
                                </span>
                            @else
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    <button type="button" class="btn-primary" onclick="quickDispatch({{ $mat->id }}, '{{ addslashes($mat->name) }}', '{{ $mat->unit }}', {{ $mat->stock_quantity }})" style="padding: 6px 10px; font-size: 0.775rem; font-weight: 700; background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                                        Dispatch
                                    </button>
                                    <button type="button" class="btn-secondary" onclick="quickRestock({{ $mat->id }}, '{{ addslashes($mat->name) }}', '{{ $mat->unit }}', {{ $mat->unit_cost }}, {{ $mat->stock_quantity }})" style="padding: 6px 10px; font-size: 0.775rem; font-weight: 700; color: #a78bfa; border-color: rgba(139, 92, 246, 0.4);" title="Restock this material">
                                        Restock
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No roofing materials found matching your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Active Construction Projects Roofing Allocations -->
    <div class="card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <span>Project Site Roofing Allocations & Balances</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">
                    Monitor roofing materials dispatched to active builds and initiate excess return or inter-site transfers
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600;">Filter Project:</label>
                <select onchange="window.location.href='{{ route('roofing.index') }}?project_id=' + this.value" class="form-input" style="padding: 7px 12px; font-size: 0.85rem; width: 260px;">
                    <option value="">All Projects Roofing Allocations</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                            {{ $p->project_code }} - {{ $p->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Roofing Material</th>
                        <th style="text-align: center;">Allocated</th>
                        <th style="text-align: center;">Installed</th>
                        <th style="text-align: center;">Excess Returned</th>
                        <th style="text-align: center;">Remaining Site Balance</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projectRoofingMaterials as $pm)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary);">{{ $pm->project->title }}</div>
                            <span class="badge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; font-size: 0.7rem;">
                                {{ $pm->project->project_code }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary);">{{ $pm->material->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Rate: ₱{{ number_format($pm->unit_price, 2) }} / {{ $pm->material->unit }}</div>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            {{ number_format($pm->allocated_qty) }} <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $pm->material->unit }}</span>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #f59e0b;">
                            {{ number_format($pm->used_qty) }} <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $pm->material->unit }}</span>
                        </td>
                        <td style="text-align: center; font-weight: 700; color: #10b981;">
                            {{ number_format($pm->excess_returned_qty) }} <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $pm->material->unit }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-size: 1.05rem; font-weight: 800; color: {{ $pm->remaining_qty > 0 ? '#38bdf8' : 'var(--text-muted)' }};">
                                {{ number_format($pm->remaining_qty) }}
                            </span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $pm->material->unit }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if(Auth::user()->isAdmin())
                                <span class="badge" style="background: rgba(148, 163, 184, 0.15); color: #94a3b8; font-size: 0.75rem; font-style: italic;">
                                    Monitored
                                </span>
                            @else
                                <div style="display: inline-flex; gap: 6px;">
                                    @if($pm->remaining_qty > 0)
                                    <button type="button" class="btn-secondary" onclick="quickReturn({{ $pm->id }}, '{{ addslashes($pm->material->name) }}', '{{ $pm->project->title }}', {{ $pm->remaining_qty }}, '{{ $pm->material->unit }}')" style="padding: 5px 10px; font-size: 0.75rem; font-weight: 700; color: #10b981; border-color: rgba(16, 185, 129, 0.3);">
                                        Return to Stock
                                    </button>
                                    <button type="button" class="btn-secondary" onclick="quickInterProjectTransfer({{ $pm->project_id }}, {{ $pm->material_id }}, '{{ addslashes($pm->material->name) }}', {{ $pm->remaining_qty }}, '{{ $pm->material->unit }}')" style="padding: 5px 10px; font-size: 0.75rem; font-weight: 700; color: #38bdf8; border-color: rgba(56, 189, 248, 0.3);">
                                        Site Transfer
                                    </button>
                                    @else
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">No remaining balance</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No project site roofing allocations recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 3: Official Roofing Material Transfer Vouchers & History Log -->
    <div class="card" id="transferLedger" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                    <span>Official Roofing Material Transfer Slips & Ledger</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">
                    Authenticated transfer delivery vouchers with audit reference numbers and printable vouchers (Audit viewable by all)
                </p>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Voucher Ref</th>
                        <th>Transfer Date</th>
                        <th>Source & Destination</th>
                        <th>Material Item</th>
                        <th style="text-align: right;">Quantity</th>
                        <th>Authorized Officer</th>
                        <th>Reason / Engineering Purpose</th>
                        <th style="text-align: center;">Official Slip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransfers as $xf)
                    <tr>
                        <td style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: #fca5a5;">
                            {{ $xf->transfer_reference_no }}
                        </td>
                        <td style="font-size: 0.825rem; color: var(--text-secondary);">
                            {{ \Carbon\Carbon::parse($xf->transfer_date)->format('M d, Y') }}
                        </td>
                        <td>
                            @if($xf->transfer_type === 'warehouse_dispatch')
                                <div style="font-size: 0.825rem;">
                                    <span style="color: var(--text-muted);">From:</span> <strong>Central Warehouse Stock</strong><br>
                                    <span style="color: var(--text-muted);">To:</span> <strong style="color: #38bdf8;">{{ $xf->destinationProject->project_code ?? 'Project Site' }}</strong> ({{ $xf->destinationProject->title ?? '' }})
                                </div>
                            @elseif($xf->transfer_type === 'inter_project')
                                <div style="font-size: 0.825rem;">
                                    <span style="color: var(--text-muted);">From:</span> <strong style="color: #f59e0b;">{{ $xf->sourceProject->project_code ?? 'Source' }}</strong><br>
                                    <span style="color: var(--text-muted);">To:</span> <strong style="color: #10b981;">{{ $xf->destinationProject->project_code ?? 'Destination' }}</strong>
                                </div>
                            @else
                                <div style="font-size: 0.825rem;">
                                    <span style="color: var(--text-muted);">From:</span> <strong style="color: #f59e0b;">{{ $xf->sourceProject->project_code ?? 'Site' }}</strong><br>
                                    <span style="color: var(--text-muted);">To:</span> <strong style="color: #8b5cf6;">Central Warehouse Stock</strong>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-primary);">{{ $xf->material->name ?? 'Roofing Item' }}</div>
                        </td>
                        <td style="text-align: right; font-family: var(--font-mono); font-weight: 800; font-size: 0.95rem; color: #f8fafc;">
                            {{ number_format($xf->quantity_transferred) }} <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $xf->material->unit ?? 'units' }}</span>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-secondary);">
                            {{ $xf->authorized_by }}
                        </td>
                        <td style="font-size: 0.8rem; color: var(--text-muted); max-width: 240px;">
                            {{ $xf->reason }}
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ route('roofing.printVoucher', $xf->id) }}" target="_blank" class="btn-secondary" style="padding: 5px 12px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                <span>Print Slip</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No roofing material transfer vouchers recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if(!Auth::user()->isAdmin())
<!-- Modal 0: Restock Warehouse Roofing Stock -->
<div id="restockStockModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal('restockStockModal')"></div>
    <div class="modal-content" style="max-width: 540px; background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; position: relative; z-index: 1000; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <span>Restock Roofing Inventory (Central Warehouse)</span>
            </h3>
            <button type="button" onclick="closeModal('restockStockModal')" style="background: none; border: none; color: var(--text-muted); font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('roofing.restockStock') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Select Roofing Material Item *
                </label>
                <select name="material_id" id="restockMaterialSelect" class="form-input" required style="width: 100%; padding: 10px;" onchange="updateRestockHint(this)">
                    <option value="">-- Choose Roofing Material to Restock --</option>
                    @foreach($roofingMaterials as $rm)
                        <option value="{{ $rm->id }}" data-stock="{{ $rm->stock_quantity }}" data-unit="{{ $rm->unit }}" data-cost="{{ $rm->unit_cost }}">
                            {{ $rm->name }} (Current Stock: {{ number_format($rm->stock_quantity) }} {{ $rm->unit }} @ ₱{{ number_format($rm->unit_cost, 2) }})
                        </option>
                    @endforeach
                </select>
                <div id="restockStockHint" style="font-size: 0.775rem; color: var(--primary-red); margin-top: 4px;"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Restock Quantity *
                    </label>
                    <input type="number" step="0.01" min="0.01" name="restock_qty" id="restockQtyInput" class="form-input" placeholder="e.g. 500" required style="width: 100%; padding: 10px;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Unit Cost / Purchase Rate (₱)
                    </label>
                    <input type="number" step="0.01" min="0" name="unit_cost" id="restockCostInput" class="form-input" placeholder="Leave blank to keep existing rate" style="width: 100%; padding: 10px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Supplier / Mill Source
                    </label>
                    <input type="text" name="supplier_name" class="form-input" placeholder="e.g. Union Galvasteel / Metal Depot" style="width: 100%; padding: 10px;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Delivery / Receipt Date *
                    </label>
                    <input type="date" name="delivery_date" value="{{ date('Y-m-d') }}" class="form-input" required style="width: 100%; padding: 10px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    PO Reference / Stock Notes
                </label>
                <textarea name="notes" rows="2" class="form-input" placeholder="e.g. PO-2026-089 Replenishment of low stock pre-painted rib roofing sheets." style="width: 100%; padding: 10px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('restockStockModal')" class="btn-secondary" style="padding: 10px 18px;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; font-weight: 700; background: var(--primary-red); border-color: var(--primary-red);">
                    Confirm Warehouse Restock
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 1: Dispatch Roofing Stock to Project Site -->
<div id="dispatchStockModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal('dispatchStockModal')"></div>
    <div class="modal-content" style="max-width: 540px; background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; position: relative; z-index: 1000; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <span>Dispatch Roofing Stock to Project Site</span>
            </h3>
            <button type="button" onclick="closeModal('dispatchStockModal')" style="background: none; border: none; color: var(--text-muted); font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('roofing.dispatchStock') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Select Roofing Material from Warehouse Stock *
                </label>
                <select name="material_id" id="dispatchMaterialSelect" class="form-input" required style="width: 100%; padding: 10px;" onchange="updateStockHint(this)">
                    <option value="">-- Choose Roofing Material Item --</option>
                    @foreach($roofingMaterials as $rm)
                        <option value="{{ $rm->id }}" data-stock="{{ $rm->stock_quantity }}" data-unit="{{ $rm->unit }}">
                            {{ $rm->name }} (Available: {{ number_format($rm->stock_quantity) }} {{ $rm->unit }})
                        </option>
                    @endforeach
                </select>
                <div id="dispatchStockHint" style="font-size: 0.775rem; color: var(--primary-red); margin-top: 4px;"></div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Destination Active Project *
                </label>
                <select name="destination_project_id" class="form-input" required style="width: 100%; padding: 10px;">
                    <option value="">-- Select Receiving Project --</option>
                    @foreach($projects as $prj)
                        <option value="{{ $prj->id }}" {{ $selectedProjectId == $prj->id ? 'selected' : '' }}>
                            {{ $prj->project_code }} - {{ $prj->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Quantity to Dispatch *
                    </label>
                    <input type="number" step="0.01" min="0.01" name="transfer_qty" id="dispatchQtyInput" class="form-input" placeholder="e.g. 150" required style="width: 100%; padding: 10px;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Transfer Date *
                    </label>
                    <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="form-input" required style="width: 100%; padding: 10px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Dispatch Notes / Engineering Purpose
                </label>
                <textarea name="reason" rows="2" class="form-input" placeholder="e.g. Dispatched long span roofing rib sheets for Main Roof Truss installation milestone." style="width: 100%; padding: 10px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('dispatchStockModal')" class="btn-secondary" style="padding: 10px 18px;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; font-weight: 700; background: var(--primary-red); border-color: var(--primary-red);">
                    Confirm Stock Dispatch
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Inter-Project Transfer -->
<div id="interProjectModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal('interProjectModal')"></div>
    <div class="modal-content" style="max-width: 540px; background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; position: relative; z-index: 1000; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <span>Inter-Project Roofing Transfer</span>
            </h3>
            <button type="button" onclick="closeModal('interProjectModal')" style="background: none; border: none; color: var(--text-muted); font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('roofing.transferInterProject') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Source Project (Where surplus roofing materials are currently located) *
                </label>
                <select name="source_project_id" id="interSourceSelect" class="form-input" required style="width: 100%; padding: 10px;">
                    <option value="">-- Select Source Project --</option>
                    @foreach($projects as $prj)
                        <option value="{{ $prj->id }}">{{ $prj->project_code }} - {{ $prj->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Destination Project (Receiving build site) *
                </label>
                <select name="destination_project_id" id="interDestSelect" class="form-input" required style="width: 100%; padding: 10px;">
                    <option value="">-- Select Destination Project --</option>
                    @foreach($projects as $prj)
                        <option value="{{ $prj->id }}">{{ $prj->project_code }} - {{ $prj->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Roofing Material Item *
                </label>
                <select name="material_id" id="interMaterialSelect" class="form-input" required style="width: 100%; padding: 10px;">
                    <option value="">-- Select Roofing Material --</option>
                    @foreach($roofingMaterials as $rm)
                        <option value="{{ $rm->id }}">{{ $rm->name }} ({{ $rm->unit }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Quantity to Transfer *
                    </label>
                    <input type="number" step="0.01" min="0.01" name="transfer_qty" class="form-input" placeholder="e.g. 50" required style="width: 100%; padding: 10px;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Transfer Date *
                    </label>
                    <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="form-input" required style="width: 100%; padding: 10px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Reason for Inter-Project Transfer
                </label>
                <textarea name="reason" rows="2" class="form-input" placeholder="e.g. Surplus C-Purlins and flashing relocated to active bungalow framing phase." style="width: 100%; padding: 10px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('interProjectModal')" class="btn-secondary" style="padding: 10px 18px;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; font-weight: 700; background: var(--primary-red); border-color: var(--primary-red);">
                    Execute Inter-Project Transfer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Return Excess to Warehouse Stock -->
<div id="returnExcessModal" class="modal" style="display: none;">
    <div class="modal-backdrop" onclick="closeModal('returnExcessModal')"></div>
    <div class="modal-content" style="max-width: 500px; background: #fafbfc; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; position: relative; z-index: 1000; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <span>Return Excess Roofing to Warehouse</span>
            </h3>
            <button type="button" onclick="closeModal('returnExcessModal')" style="background: none; border: none; color: var(--text-muted); font-size: 1.4rem; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('roofing.returnExcess') }}" method="POST">
            @csrf
            <input type="hidden" name="project_material_id" id="returnProjectMaterialId">

            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 14px; border-radius: var(--radius-md); margin-bottom: 16px;">
                <div style="font-size: 0.8rem; color: var(--text-muted);">Returning Material:</div>
                <div id="returnMaterialTitle" style="font-weight: 800; color: var(--text-primary); font-size: 0.95rem; margin-top: 2px;"></div>
                <div id="returnProjectTitle" style="font-size: 0.775rem; color: var(--primary-red); margin-top: 2px;"></div>
                <div id="returnMaxBalance" style="font-size: 0.775rem; color: #059669; font-weight: 700; margin-top: 4px;"></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Quantity to Return *
                    </label>
                    <input type="number" step="0.01" min="0.01" name="return_qty" id="returnQtyInput" class="form-input" required style="width: 100%; padding: 10px;">
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                        Return Date *
                    </label>
                    <input type="date" name="transfer_date" value="{{ date('Y-m-d') }}" class="form-input" required style="width: 100%; padding: 10px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label" style="display: block; font-size: 0.825rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 6px;">
                    Reason for Return
                </label>
                <textarea name="reason" rows="2" class="form-input" placeholder="e.g. Surplus roofing materials reclaimed back to warehouse catalog for future projects." style="width: 100%; padding: 10px;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeModal('returnExcessModal')" class="btn-secondary" style="padding: 10px 18px;">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 10px 22px; font-weight: 700; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    Confirm Return to Stock
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(8px);
}
</style>

@endsection

@section('scripts')
<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

function quickRestock(materialId, name, unit, cost, stock) {
    const select = document.getElementById('restockMaterialSelect');
    if (select) {
        select.value = materialId;
        updateRestockHint(select);
    }
    const costInput = document.getElementById('restockCostInput');
    if (costInput && cost) {
        costInput.value = cost;
    }
    openModal('restockStockModal');
}

function updateRestockHint(el) {
    const opt = el.options[el.selectedIndex];
    const hint = document.getElementById('restockStockHint');
    if (opt && opt.dataset.stock !== undefined) {
        hint.innerHTML = `Current Central Warehouse Stock: <strong>${Number(opt.dataset.stock).toLocaleString()} ${opt.dataset.unit}</strong> (₱${Number(opt.dataset.cost).toFixed(2)}/${opt.dataset.unit})`;
        const costInput = document.getElementById('restockCostInput');
        if (costInput && opt.dataset.cost) {
            costInput.value = opt.dataset.cost;
        }
    } else {
        hint.innerHTML = '';
    }
}

function quickDispatch(materialId, name, unit, stock) {
    const select = document.getElementById('dispatchMaterialSelect');
    if (select) {
        select.value = materialId;
        updateStockHint(select);
    }
    openModal('dispatchStockModal');
}

function updateStockHint(el) {
    const opt = el.options[el.selectedIndex];
    const hint = document.getElementById('dispatchStockHint');
    if (opt && opt.dataset.stock !== undefined) {
        hint.innerHTML = `Available Central Warehouse Stock: <strong>${Number(opt.dataset.stock).toLocaleString()} ${opt.dataset.unit}</strong>`;
        const qtyInput = document.getElementById('dispatchQtyInput');
        if (qtyInput) qtyInput.max = opt.dataset.stock;
    } else {
        hint.innerHTML = '';
    }
}

function quickReturn(pmId, matName, projTitle, remainingQty, unit) {
    document.getElementById('returnProjectMaterialId').value = pmId;
    document.getElementById('returnMaterialTitle').innerText = matName;
    document.getElementById('returnProjectTitle').innerText = 'From Site: ' + projTitle;
    document.getElementById('returnMaxBalance').innerText = 'Unconsumed Site Balance: ' + remainingQty + ' ' + unit;
    document.getElementById('returnQtyInput').max = remainingQty;
    document.getElementById('returnQtyInput').value = remainingQty;
    openModal('returnExcessModal');
}

function quickInterProjectTransfer(sourceProjId, materialId, matName, remainingQty, unit) {
    document.getElementById('interSourceSelect').value = sourceProjId;
    document.getElementById('interMaterialSelect').value = materialId;
    openModal('interProjectModal');
}
</script>
@endsection
