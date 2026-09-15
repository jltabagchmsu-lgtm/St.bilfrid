<!-- =========================================================================
     MODAL: Add Excess / Surplus Materials to Central Warehouse Inventory (INV)
     Provides Itemized Breakdown of "Which Material Is It"
     ========================================================================= -->
<div class="modal-overlay" id="reconcileProjectExcessModal" style="display: none; z-index: 9999;">
    <div class="modal-box modal-box-large" style="max-width: 960px; max-height: 90vh; display: flex; flex-direction: column; padding: 0; overflow: hidden; background: #ffffff; border: 1px solid var(--border-color); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        
        <!-- Modal Header -->
        <div style="padding: 20px 24px; background: #fafbfc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: center; color: #10b981; flex-shrink: 0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: var(--text-primary);">Add Excess Materials to Inventory (INV)</h3>
                        <span id="modalProjectStatusBadge" class="badge badge-completed" style="font-size: 0.725rem;">Project Completed</span>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 3px;">
                        Reconcile leftover site stock from <strong id="modalProjectTitle" style="color: var(--text-primary);">Project Title</strong> 
                        (<span id="modalProjectCode" style="font-family: var(--font-mono); color: var(--primary-red); font-weight: 700;">PRJ-CODE</span>)
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeReconcileExcessModal()" style="background: none; border: none; color: var(--text-muted); font-size: 1.75rem; line-height: 1; cursor: pointer; padding: 4px;" title="Close">&times;</button>
        </div>

        <!-- Navigation Tabs inside Modal -->
        <div style="display: flex; border-bottom: 1px solid var(--border-color); background: #f8fafc; padding: 0 24px;">
            <button type="button" id="tabBtnAllocatedExcess" onclick="switchExcessModalTab('allocated')" class="excess-tab-btn active" style="padding: 12px 18px; font-size: 0.875rem; font-weight: 700; border: none; background: transparent; border-bottom: 2px solid #10b981; color: #10b981; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <span>📋 Tracked Site Excess Materials</span>
                <span id="modalTrackedCountBadge" class="badge" style="background: rgba(16, 185, 129, 0.2); color: #10b981; font-size: 0.7rem;">0</span>
            </button>
            <button type="button" id="tabBtnCustomExcess" onclick="switchExcessModalTab('custom')" class="excess-tab-btn" style="padding: 12px 18px; font-size: 0.875rem; font-weight: 600; border: none; background: transparent; border-bottom: 2px solid transparent; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <span>➕ Add Unlisted Surplus Material</span>
            </button>
        </div>

        <!-- Tab 1: Tracked Site Materials Batch Return Form -->
        <div id="tabContentAllocated" style="flex: 1; overflow-y: auto; padding: 20px 24px;">
            
            <form id="reconcileExcessBatchForm" action="" method="POST">
                @csrf

                <!-- Header Notice & Controls -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 8px; padding: 12px 16px;">
                    <div style="font-size: 0.825rem; color: var(--text-secondary);">
                        <strong style="color: #059669;">Material Inventory Reconciliation:</strong> Select which materials to return to Central Warehouse Inventory stock. Quantities are prefilled with remaining on-site units.
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="btn-secondary" onclick="toggleSelectAllExcess(true)" style="font-size: 0.75rem; padding: 4px 10px;">Select All</button>
                        <button type="button" class="btn-secondary" onclick="toggleSelectAllExcess(false)" style="font-size: 0.75rem; padding: 4px 10px;">Deselect All</button>
                    </div>
                </div>

                <!-- Materials Table with full "WHICH MATERIAL IS IT" details -->
                <div style="border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; margin-bottom: 18px; background: #ffffff;">
                    <div style="max-height: 340px; overflow-y: auto;">
                        <table class="custom-table" style="margin-bottom: 0; width: 100%; border-collapse: collapse; font-size: 0.825rem;">
                            <thead style="position: sticky; top: 0; background: #f8fafc; z-index: 2; border-bottom: 1px solid var(--border-color);">
                                <tr>
                                    <th style="width: 44px; text-align: center; padding: 10px 8px;">
                                        <input type="checkbox" id="selectAllExcessChk" onchange="toggleSelectAllExcess(this.checked)" checked style="cursor: pointer;">
                                    </th>
                                    <th style="min-width: 220px; padding: 10px 12px;">Material Specification & Code</th>
                                    <th style="width: 120px; padding: 10px 8px;">Trade Discipline</th>
                                    <th style="width: 130px; text-align: right; padding: 10px 10px;">Allocated / Used</th>
                                    <th style="width: 120px; text-align: right; padding: 10px 10px; color: #10b981;">Available Excess</th>
                                    <th style="width: 160px; padding: 10px 12px; text-align: right;">Qty to Return to INV</th>
                                    <th style="width: 110px; text-align: right; padding: 10px 12px;">Est. Value (₱)</th>
                                </tr>
                            </thead>
                            <tbody id="excessMaterialsModalTbody">
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                        Loading project materials...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Live Summary KPI Strip -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 18px;">
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px;">
                        <div style="font-size: 0.725rem; color: var(--text-muted); text-transform: uppercase;">Selected Materials</div>
                        <div style="font-family: var(--font-mono); font-size: 1.25rem; font-weight: 800; color: #38bdf8;" id="summarySelectedCount">0 Items</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px;">
                        <div style="font-size: 0.725rem; color: var(--text-muted); text-transform: uppercase;">Total Units Returning</div>
                        <div style="font-family: var(--font-mono); font-size: 1.25rem; font-weight: 800; color: #f59e0b;" id="summaryTotalUnits">0 Units</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 10px 14px; background: rgba(16, 185, 129, 0.05);">
                        <div style="font-size: 0.725rem; color: #059669; text-transform: uppercase; font-weight: 700;">Reclaimed Capital Valuation</div>
                        <div style="font-family: var(--font-mono); font-size: 1.25rem; font-weight: 800; color: #10b981;" id="summaryTotalValuation">₱0.00</div>
                    </div>
                </div>

                <!-- Optional Remarks & Date -->
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 14px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.78rem;">Reconciliation Date</label>
                        <input type="date" name="transfer_date" class="form-input" value="{{ date('Y-m-d') }}" style="font-size: 0.825rem;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.78rem;">General Audit Remarks / Warehouse Bay Notes</label>
                        <input type="text" name="general_notes" class="form-input" placeholder="e.g. Unused building materials reclaimed and stocked into Central Warehouse Rack B upon project turnover..." style="font-size: 0.825rem;">
                    </div>
                </div>

                <!-- Action Footer -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 16px;">
                    <button type="button" class="btn-secondary" onclick="closeReconcileExcessModal()" style="padding: 8px 18px;">Cancel</button>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="submit" name="return_all" value="1" class="btn-secondary" style="color: #059669; border-color: rgba(16, 185, 129, 0.35); font-weight: 700; padding: 8px 16px;" onclick="return confirm('Confirm returning 100% of remaining excess materials from this project directly to Central Inventory?')">
                            ⚡ Return All Excess (1-Click)
                        </button>
                        <button type="submit" class="btn-primary" style="background: #10b981; border-color: #10b981; padding: 8px 20px; font-weight: 700;">
                            ✓ Add Selected Excess to INV
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab 2: Custom / Unlisted Excess Material Entry Form -->
        <div id="tabContentCustom" style="display: none; flex: 1; overflow-y: auto; padding: 20px 24px;">
            <form id="reconcileExcessCustomForm" action="" method="POST">
                @csrf

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-left: 4px solid #38bdf8; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;">
                    <h5 style="margin: 0 0 4px 0; font-size: 0.9rem; font-weight: 700; color: var(--text-primary);">Add Additional / Unlisted Surplus Discovered on Site</h5>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);">If extra building materials, surplus fittings, or fixtures were reclaimed that were not originally tracked in the project site BOM, record them here to credit this project and increment Central Warehouse inventory.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label">Select from Central Catalog <span style="font-weight: normal; color: var(--text-muted);">(Optional)</span></label>
                        <select name="material_id" id="customExcessMatSelect" class="form-select" onchange="handleCustomMaterialSelect(this)">
                            <option value="">-- Choose Existing Warehouse Catalog Item --</option>
                            @if(isset($materialsCatalog))
                                @foreach($materialsCatalog as $cm)
                                    <option value="{{ $cm->id }}" data-name="{{ $cm->name }}" data-category="{{ $cm->category }}" data-unit="{{ $cm->unit }}" data-cost="{{ $cm->unit_cost }}">
                                        {{ $cm->name }} ({{ $cm->material_code }}) - ₱{{ number_format($cm->unit_cost, 2) }}/{{ $cm->unit }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Material Name / Specification</label>
                        <input type="text" name="custom_material_name" id="customExcessMatName" class="form-input" placeholder="e.g. Portland Cement Type 1 / 40kg" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label">Trade Discipline</label>
                        <select name="category" id="customExcessMatCat" class="form-select">
                            <option value="Structural & Masonry">Structural & Masonry</option>
                            <option value="Roofing">Roofing</option>
                            <option value="Windows & Doors">Windows & Doors</option>
                            <option value="Plumbing & Sanitary">Plumbing & Sanitary</option>
                            <option value="Electrical Works">Electrical Works</option>
                            <option value="Architectural & Finishes">Architectural & Finishes</option>
                            <option value="General Building Materials">General Building Materials</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit of Measure</label>
                        <input type="text" name="unit" id="customExcessMatUnit" class="form-input" placeholder="e.g. bags, pcs, sheets, sets" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Quantity Recovered</label>
                        <input type="number" step="0.01" min="0.01" name="quantity" class="form-input" placeholder="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit Cost (₱)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" id="customExcessMatCost" class="form-input" placeholder="0.00" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 14px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label">Date Recovered</label>
                        <input type="date" name="transfer_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Surplus Source & Condition Remarks</label>
                        <input type="text" name="notes" class="form-input" placeholder="e.g. Leftover boxed tiles in original packaging from finished build...">
                    </div>
                </div>

                <!-- Action Footer -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 16px;">
                    <button type="button" class="btn-secondary" onclick="closeReconcileExcessModal()" style="padding: 8px 18px;">Cancel</button>
                    <button type="submit" class="btn-primary" style="background: #10b981; border-color: #10b981; padding: 8px 20px; font-weight: 700;">
                        + Add Surplus to Central Inventory
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    let currentExcessProjectData = null;

    function openReconcileExcessModalForProject(projectId, projectCode, projectTitle, projectStatus) {
        document.getElementById('modalProjectTitle').innerText = projectTitle || 'Project';
        document.getElementById('modalProjectCode').innerText = projectCode || 'PRJ';
        if (projectStatus) {
            document.getElementById('modalProjectStatusBadge').innerText = projectStatus === 'completed' ? 'Project Completed' : 'Active Project';
        }
        
        document.getElementById('reconcileExcessBatchForm').action = '/projects/' + projectId + '/return-excess-materials';
        document.getElementById('reconcileExcessCustomForm').action = '/projects/' + projectId + '/add-custom-excess-material';

        const tbody = document.getElementById('excessMaterialsModalTbody');
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:32px; color:var(--text-muted);">Loading itemized material breakdown...</td></tr>';

        // Fetch excess materials JSON
        fetch('/projects/' + projectId + '/excess-materials-json')
            .then(res => res.json())
            .then(data => {
                currentExcessProjectData = data;
                renderExcessMaterialsTable(data.materials || []);
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:24px; color:#ef4444;">Failed to load project materials. Please reload and try again.</td></tr>';
            });

        document.getElementById('reconcileProjectExcessModal').style.display = 'flex';
    }

    function closeReconcileExcessModal() {
        document.getElementById('reconcileProjectExcessModal').style.display = 'none';
    }

    function switchExcessModalTab(tab) {
        const btnAlloc = document.getElementById('tabBtnAllocatedExcess');
        const btnCustom = document.getElementById('tabBtnCustomExcess');
        const contentAlloc = document.getElementById('tabContentAllocated');
        const contentCustom = document.getElementById('tabContentCustom');

        if (tab === 'allocated') {
            btnAlloc.style.borderBottom = '2px solid #10b981';
            btnAlloc.style.color = '#10b981';
            btnAlloc.style.fontWeight = '700';
            btnCustom.style.borderBottom = '2px solid transparent';
            btnCustom.style.color = 'var(--text-muted)';
            btnCustom.style.fontWeight = '600';
            contentAlloc.style.display = 'block';
            contentCustom.style.display = 'none';
        } else {
            btnCustom.style.borderBottom = '2px solid #10b981';
            btnCustom.style.color = '#10b981';
            btnCustom.style.fontWeight = '700';
            btnAlloc.style.borderBottom = '2px solid transparent';
            btnAlloc.style.color = 'var(--text-muted)';
            btnAlloc.style.fontWeight = '600';
            contentAlloc.style.display = 'none';
            contentCustom.style.display = 'block';
        }
    }

    function renderExcessMaterialsTable(materials) {
        const tbody = document.getElementById('excessMaterialsModalTbody');
        document.getElementById('modalTrackedCountBadge').innerText = materials.length;

        if (materials.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted);">
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">No Site Materials Currently Tracked in Project BOM</div>
                        <div style="font-size: 0.8rem;">You can use the <strong>"➕ Add Unlisted Surplus Material"</strong> tab above to record any surplus items recovered from this completed site.</div>
                    </td>
                </tr>`;
            updateExcessSummaryMetrics();
            return;
        }

        let html = '';
        materials.forEach((mat, idx) => {
            const hasExcess = mat.remaining_qty > 0;
            const catColor = getCategoryBadgeColor(mat.category);

            html += `
                <tr class="excess-mat-row" style="border-bottom: 1px solid #f1f5f9; ${!hasExcess ? 'opacity: 0.65; background: #fafafa;' : ''}">
                    <td style="text-align: center; padding: 8px;">
                        <input type="checkbox" name="materials[${idx}][selected]" value="1" class="excess-item-chk" ${hasExcess ? 'checked' : 'disabled'} onchange="updateExcessSummaryMetrics()" style="cursor: pointer;">
                        <input type="hidden" name="materials[${idx}][project_material_id]" value="${mat.project_material_id}">
                    </td>
                    <td style="padding: 8px 12px;">
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.875rem;">
                            ${escapeHtml(mat.name)}
                        </div>
                        <div style="font-family: var(--font-mono); font-size: 0.725rem; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                            <span>${mat.material_code || 'MAT-CAT'}</span>
                            <span style="color: var(--text-muted);">&bull; ₱${numberFormat(mat.unit_price, 2)} / ${escapeHtml(mat.unit)}</span>
                        </div>
                    </td>
                    <td style="padding: 8px;">
                        <span class="spec-chip" style="font-size: 0.68rem; color: ${catColor}; border-color: ${catColor}44;">
                            ${escapeHtml(mat.category || 'General')}
                        </span>
                    </td>
                    <td style="text-align: right; font-family: var(--font-mono); padding: 8px 10px; font-size: 0.8rem; color: var(--text-muted);">
                        <div>Alloc: ${numberFormat(mat.allocated_qty)}</div>
                        <div style="color: var(--text-secondary);">Used: ${numberFormat(mat.used_qty)}</div>
                    </td>
                    <td style="text-align: right; font-family: var(--font-mono); font-weight: 800; color: ${hasExcess ? '#10b981' : 'var(--text-muted)'}; padding: 8px 10px; font-size: 0.9rem;">
                        ${numberFormat(mat.remaining_qty)} <span style="font-size: 0.725rem; font-weight: normal; color: var(--text-muted);">${escapeHtml(mat.unit)}</span>
                    </td>
                    <td style="padding: 8px 12px; text-align: right;">
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                            <input type="number" name="materials[${idx}][return_qty]" value="${hasExcess ? mat.remaining_qty : 0}" min="0" max="${mat.remaining_qty}" step="1" class="form-input excess-qty-input" data-unit-price="${mat.unit_price}" data-unit="${escapeHtml(mat.unit)}" ${!hasExcess ? 'disabled' : ''} oninput="updateExcessSummaryMetrics()" style="width: 85px; padding: 4px 8px; font-size: 0.825rem; text-align: right; font-family: var(--font-mono); font-weight: 700;">
                            <button type="button" class="btn-secondary" onclick="setMaxExcessQty(this, ${mat.remaining_qty})" style="padding: 3px 6px; font-size: 0.7rem;" title="Set to Full Remaining Quantity" ${!hasExcess ? 'disabled' : ''}>Max</button>
                        </div>
                    </td>
                    <td style="text-align: right; font-family: var(--font-mono); font-weight: 800; color: #10b981; padding: 8px 12px;" class="excess-val-cell">
                        ₱${numberFormat(mat.remaining_qty * mat.unit_price, 2)}
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;
        updateExcessSummaryMetrics();
    }

    function setMaxExcessQty(btn, maxQty) {
        const input = btn.parentElement.querySelector('.excess-qty-input');
        if (input) {
            input.value = maxQty;
            const chk = btn.closest('tr').querySelector('.excess-item-chk');
            if (chk) chk.checked = true;
            updateExcessSummaryMetrics();
        }
    }

    function toggleSelectAllExcess(checked) {
        document.getElementById('selectAllExcessChk').checked = checked;
        const checkboxes = document.querySelectorAll('.excess-item-chk:not(:disabled)');
        checkboxes.forEach(chk => {
            chk.checked = checked;
        });
        updateExcessSummaryMetrics();
    }

    function updateExcessSummaryMetrics() {
        const rows = document.querySelectorAll('.excess-mat-row');
        let totalItems = 0;
        let totalUnits = 0;
        let totalValuation = 0;

        rows.forEach(row => {
            const chk = row.querySelector('.excess-item-chk');
            const qtyInput = row.querySelector('.excess-qty-input');
            const valCell = row.querySelector('.excess-val-cell');

            if (chk && qtyInput) {
                const qty = parseFloat(qtyInput.value) || 0;
                const unitPrice = parseFloat(qtyInput.dataset.unitPrice) || 0;
                const lineVal = qty * unitPrice;

                if (valCell) {
                    valCell.innerText = '₱' + numberFormat(lineVal, 2);
                }

                if (chk.checked && qty > 0) {
                    totalItems++;
                    totalUnits += qty;
                    totalValuation += lineVal;
                }
            }
        });

        document.getElementById('summarySelectedCount').innerText = totalItems + ' Items';
        document.getElementById('summaryTotalUnits').innerText = numberFormat(totalUnits) + ' Units';
        document.getElementById('summaryTotalValuation').innerText = '₱' + numberFormat(totalValuation, 2);
    }

    function handleCustomMaterialSelect(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('customExcessMatName').value = opt.dataset.name || '';
            document.getElementById('customExcessMatUnit').value = opt.dataset.unit || '';
            document.getElementById('customExcessMatCost').value = opt.dataset.cost || '';
            if (opt.dataset.category) {
                const catSelect = document.getElementById('customExcessMatCat');
                for (let i = 0; i < catSelect.options.length; i++) {
                    if (catSelect.options[i].value === opt.dataset.category) {
                        catSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }
    }

    function getCategoryBadgeColor(cat) {
        switch (cat) {
            case 'Structural & Masonry':
            case 'Structural': return '#dc2626';
            case 'Electrical Works':
            case 'Electrical': return '#d97706';
            case 'Plumbing & Sanitary':
            case 'Piping/Plumbing':
            case 'Piping': return '#059669';
            case 'Roofing':
            case 'Roofing & Metal Sheets': return '#ea580c';
            case 'Windows & Doors':
            case 'Doors & Windows': return '#4f46e5';
            case 'Architectural & Finishes':
            case 'Finishing': return '#7c3aed';
            default: return '#38bdf8';
        }
    }

    function numberFormat(val, decimals = 0) {
        const num = parseFloat(val) || 0;
        return num.toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
