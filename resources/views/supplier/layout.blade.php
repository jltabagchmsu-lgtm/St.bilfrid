<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Supplier Portal - St. Bilfrid Dev. Corp')</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .supplier-badge-wndr { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); }
        .supplier-badge-roof { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.35); }
        .supplier-badge-strc { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.35); }
        
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.65);
        }
        .grid-table th {
            background: rgba(30, 41, 59, 0.85);
            padding: 14px 16px;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
        }
        .grid-table th:last-child { border-right: none; }
        .grid-table td {
            padding: 14px 16px;
            font-size: 0.85rem;
            color: var(--text-primary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            vertical-align: middle;
        }
        .grid-table td:last-child { border-right: none; }
        .grid-table tbody tr:last-child td { border-bottom: none; }
        .grid-table tbody tr:hover { background: rgba(255, 255, 255, 0.025); }

        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(3, 7, 18, 0.8);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.active { display: flex; }
        .modal-box {
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        }
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-body { padding: 24px; }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background: rgba(15, 23, 42, 0.4);
        }

        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .filter-pill {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .filter-pill:hover, .filter-pill.active {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.4);
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Supplier Sidebar Navigation -->
    <aside class="app-sidebar">
        <a href="{{ route('supplier.dashboard') }}" class="brand-wrapper">
            <div class="brand-logo" style="background: {{ Auth::user()->supplier && Auth::user()->supplier->category === 'Windows & Doors' ? 'linear-gradient(135deg, #38bdf8 0%, #0284c7 100%)' : (Auth::user()->supplier && Auth::user()->supplier->category === 'Structural & Masonry' ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)') }};">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <div class="brand-text">
                <h1 style="font-size: 0.95rem; line-height: 1.2;">{{ Auth::user()->supplier ? Str::limit(Auth::user()->supplier->name, 20) : 'Supplier Operations' }}</h1>
                <div class="brand-sub" style="font-size: 0.65rem; color: #38bdf8;">{{ Auth::user()->supplier ? Auth::user()->supplier->category : 'Trade Partner' }}</div>
            </div>
        </a>

        <nav class="nav-menu">
            <div class="nav-section-title">Operations Portal</div>

            <a href="{{ route('supplier.dashboard') }}" class="nav-item {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                </span>
                Dashboard Overview
            </a>

            <a href="{{ route('supplier.materials') }}" class="nav-item {{ request()->routeIs('supplier.materials*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </span>
                My Materials & Inventory
            </a>

            <a href="{{ route('supplier.orders') }}" class="nav-item {{ request()->routeIs('supplier.orders*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                </span>
                Firm Purchase Orders
            </a>

            <div class="nav-section-title" style="margin-top: 16px;">Account & Settings</div>

            <a href="{{ route('supplier.profile') }}" class="nav-item {{ request()->routeIs('supplier.profile*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </span>
                Supplier Organization Profile
            </a>

            <div class="nav-section-title" style="margin-top: 16px;">Quick Actions</div>
            <div style="padding: 0 12px;">
                <button type="button" onclick="openModal('addMaterialModal')" class="btn-primary" style="width: 100%; font-size: 0.8rem; padding: 10px; margin-bottom: 8px;">
                    + Add New Material
                </button>
            </div>
        </nav>

        <!-- Supplier User Profile Card & Logout -->
        <div class="sidebar-user-card" style="margin-top: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="user-avatar" style="background: rgba(56, 189, 248, 0.15);">
                    <span style="font-weight: 800; font-size: 0.85rem; color: #38bdf8;">SUP</span>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ Auth::user()->name }}
                    </div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ Auth::user()->supplier ? Auth::user()->supplier->category : 'Supplier Account' }}
                    </div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin-top: 12px;">
                @csrf
                <button type="submit" class="btn-secondary" style="width: 100%; padding: 6px; font-size: 0.75rem; justify-content: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="app-main" style="margin-left: 290px; flex: 1; min-height: 100vh; padding: 32px 40px;">

        <!-- Top Header Bar -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">@yield('page_title', 'Supplier Portal')</h2>
                    @if(Auth::user()->supplier)
                        <span class="pill-badge {{ Auth::user()->supplier->category === 'Windows & Doors' ? 'supplier-badge-wndr' : (Auth::user()->supplier->category === 'Structural & Masonry' ? 'supplier-badge-strc' : 'supplier-badge-roof') }}">
                            {{ Auth::user()->supplier->category }} Partner
                        </span>
                    @endif
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                    @yield('page_subtitle', 'Manage material availability, specifications, and client purchase orders.')
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="font-size: 0.8rem; color: var(--text-secondary); background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.08); padding: 8px 14px; border-radius: 8px;">
                    <span style="color: var(--text-muted);">Contractor Partner:</span> <strong style="color: var(--text-primary);">St. Bilfrid Dev. Corp</strong>
                </div>

                <a href="{{ route('supplier.profile') }}" class="btn-secondary" style="font-size: 0.8rem; padding: 8px 14px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Settings
                </a>
            </div>
        </div>

        <!-- Flash Notices -->
        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 12px; padding: 14px 20px; margin-bottom: 24px; color: #10b981; font-size: 0.875rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #10b981; cursor: pointer; font-size: 1.1rem;">&times;</button>
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); border-radius: 12px; padding: 14px 20px; margin-bottom: 24px; color: #ef4444; font-size: 0.875rem;">
                <div style="display: flex; align-items: center; gap: 10px; font-weight: 700;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>{{ session('error') ?? 'Please check the form for errors:' }}</span>
                </div>
                @if(isset($errors) && $errors->any())
                    <ul style="margin: 8px 0 0 28px; font-size: 0.825rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Global Add Material Modal -->
    <div class="modal-backdrop" id="addMaterialModal">
        <div class="modal-box">
            <div class="modal-header">
                <div>
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Add New Material to Catalog</h3>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">Register a material offering with specifications, inventory quantity, and unit price.</p>
                </div>
                <button type="button" onclick="closeModal('addMaterialModal')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem;">&times;</button>
            </div>
            <form action="{{ route('supplier.materials.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Material Name <span style="color: var(--primary-red);">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="e.g. 1.20m x 1.20m Sliding Window 1/4 Glass" class="input-field" style="width: 100%;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Subcategory
                            </label>
                            <input type="text" name="subcategory" placeholder="e.g. Windows, Doors, Gutters, Cement" class="input-field" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Unit of Measurement <span style="color: var(--primary-red);">*</span>
                            </label>
                            <input type="text" name="unit" required placeholder="pcs, sets, ln.m., bags, boxes, cu.m" class="input-field" style="width: 100%;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Available Quantity <span style="color: var(--primary-red);">*</span>
                            </label>
                            <input type="number" name="available_quantity" min="0" required placeholder="0" class="input-field" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Unit Price (PHP) <span style="color: var(--primary-red);">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" name="unit_price" required placeholder="0.00" class="input-field" style="width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Min Order Qty (MOQ)
                            </label>
                            <input type="number" min="1" name="min_order_qty" value="1" class="input-field" style="width: 100%;">
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Material Specifications
                        </label>
                        <textarea name="specifications" rows="2" placeholder="e.g. 6mm Tempered Glass, Powder Coated Aluminum Section 38mm, ASTM C150 Standard" class="input-field" style="width: 100%; resize: vertical;"></textarea>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Description / Application Notes
                        </label>
                        <textarea name="description" rows="2" placeholder="Brief explanation of the material usage, warranty, or packaging." class="input-field" style="width: 100%; resize: vertical;"></textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                            Availability Status Override
                        </label>
                        <select name="availability_status" class="input-field" style="width: 100%;">
                            <option value="available">Available (In Stock)</option>
                            <option value="low_stock">Low Stock</option>
                            <option value="out_of_stock">Out of Stock</option>
                            <option value="unavailable">Unavailable / Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('addMaterialModal')" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save to Catalog</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('active');
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('active');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal-backdrop')) {
                event.target.classList.remove('active');
            }
        };
    </script>
    @stack('scripts')
</body>
</html>
