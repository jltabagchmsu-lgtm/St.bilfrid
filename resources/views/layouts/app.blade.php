<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'St. Bilfrid Development Corporation - Construction Management & Monitoring')</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="app-sidebar">
        <a href="/" class="brand-wrapper">
            <div class="brand-logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18"></path>
                    <path d="M5 21V7l8-4v18"></path>
                    <path d="M19 21V11l-6-3"></path>
                    <path d="M9 9h1"></path>
                    <path d="M9 13h1"></path>
                    <path d="M9 17h1"></path>
                </svg>
            </div>
            <div class="brand-text">
                <h1 style="font-size: 1.05rem; line-height: 1.2;">St. Bilfrid<span style="color: var(--primary-red); display: block; font-size: 0.775rem; font-weight: 700;">Dev. Corporation</span></h1>
                <div class="brand-sub" style="font-size: 0.65rem;">Project Monitor & Control</div>
            </div>
        </a>

        <nav class="nav-menu">
            @if(Auth::check() && Auth::user()->isRoofingOfficer())
                <!-- Roofing Transfer Officer Navigation -->
                <div class="nav-section-title">Roofing Operations</div>

                <a href="{{ route('roofing.index') }}" class="nav-item {{ request()->is('roofing*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </span>
                    Roofing Transfer Station
                </a>

                <a href="{{ route('roofing.index') }}#transferLedger" class="nav-item">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    </span>
                    Roofing Transfer Slips Log
                </a>

                <div class="nav-section-title" style="margin-top: 16px;">Quick Actions</div>
                <div style="padding: 0 12px;">
                    <button type="button" onclick="openModal('dispatchStockModal')" class="btn-primary" style="width: 100%; font-size: 0.8rem; padding: 10px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); margin-bottom: 8px;">
                        Dispatch Roofing Stock
                    </button>
                    <button type="button" onclick="openModal('interProjectModal')" class="btn-secondary" style="width: 100%; font-size: 0.8rem; padding: 10px;">
                        Inter-Project Transfer
                    </button>
                </div>

            @elseif(Auth::check() && Auth::user()->isWindowsDoorsOfficer())
                <!-- Windows & Doors Transfer Officer Navigation -->
                <div class="nav-section-title">Windows & Doors Operations</div>

                <a href="{{ route('windowsDoors.index') }}" class="nav-item {{ request()->is('windows-doors*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="3" x2="12" y2="21"></line><line x1="3" y1="12" x2="21" y2="12"></line></svg>
                    </span>
                    Doors & Windows Station
                </a>

                <a href="{{ route('windowsDoors.index') }}#transferLedger" class="nav-item">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    </span>
                    Transfer Slips & Vouchers
                </a>

                <div class="nav-section-title" style="margin-top: 16px;">Quick Actions</div>
                <div style="padding: 0 12px;">
                    <button type="button" onclick="openModal('dispatchStockModal')" class="btn-primary" style="width: 100%; font-size: 0.8rem; padding: 10px; background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); color: #0b0f17; margin-bottom: 8px;">
                        Dispatch Doors/Windows
                    </button>
                    <button type="button" onclick="openModal('interProjectModal')" class="btn-secondary" style="width: 100%; font-size: 0.8rem; padding: 10px;">
                        Inter-Project Transfer
                    </button>
                </div>

            @else
                <!-- Master Administrator Navigation -->
                <div class="nav-section-title">Core Management</div>

                <a href="/" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                    </span>
                    Executive Dashboard
                </a>

                <a href="/projects" class="nav-item {{ request()->is('projects*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20h20"></path><path d="M5 20V8.5L12 3l7 5.5V20"></path><path d="M9 20v-6h6v6"></path></svg>
                    </span>
                    Active Project Tracker
                </a>

                <!-- Separated Materials Inventory (INV) -->
                <a href="/inventory" class="nav-item {{ request()->is('inventory*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </span>
                    Materials Inventory (INV)
                </a>

                <!-- Separated Bill of Materials (BOM) with Clickable UI Dropdown -->
                <div class="nav-dropdown {{ request()->is('bom*') ? 'open active' : '' }}" id="bomDropdown">
                    <div class="nav-item nav-dropdown-trigger" onclick="toggleNavDropdown('bomDropdown')">
                        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </span>
                            <span>Bill of Materials (BOM)</span>
                        </div>
                        <span class="dropdown-chevron">▼</span>
                    </div>
                    <div class="nav-dropdown-menu">
                        <a href="/bom" class="nav-sub-item {{ request()->is('bom') && !request()->has('project_id') ? 'active' : '' }}">
                            <span class="sub-dot dot-active"></span> All Projects BOM
                        </a>
                        <div class="nav-sub-divider">Individual Project BOMs</div>
                        @if(isset($navProjects) && $navProjects->count() > 0)
                            @foreach($navProjects as $np)
                                <a href="/bom?project_id={{ $np->id }}" class="nav-sub-item {{ request()->query('project_id') == $np->id && request()->is('bom*') ? 'active' : '' }}" title="{{ $np->title }}">
                                    <span class="sub-dot {{ $np->status === 'completed' ? 'dot-completed' : 'dot-active' }}"></span>
                                    <span class="sub-text">{{ $np->title }}</span>
                                </a>
                            @endforeach
                        @else
                            <span class="nav-sub-empty">No projects created yet</span>
                        @endif
                    </div>
                </div>

                <!-- Supplier Network & Procurement Management -->
                <div class="nav-dropdown {{ request()->is('suppliers*') ? 'open active' : '' }}" id="supplierDropdown">
                    <div class="nav-item nav-dropdown-trigger" onclick="toggleNavDropdown('supplierDropdown')">
                        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            </span>
                            <span>Supplier Network</span>
                        </div>
                        <span class="dropdown-chevron">▼</span>
                    </div>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('admin.suppliers.index') }}" class="nav-sub-item {{ request()->is('suppliers') ? 'active' : '' }}">
                            <span class="sub-dot dot-active"></span> Supplier Hub & Partners
                        </a>
                        <a href="{{ route('admin.suppliers.materials') }}" class="nav-sub-item {{ request()->is('suppliers/materials*') ? 'active' : '' }}">
                            <span class="sub-dot dot-active"></span> Materials Catalog & Matrix
                        </a>
                        <a href="{{ route('admin.suppliers.orders') }}" class="nav-sub-item {{ request()->is('suppliers/orders*') ? 'active' : '' }}">
                            <span class="sub-dot dot-active"></span> Purchase Orders Tracker
                        </a>
                    </div>
                </div>

                <!-- Trade Transfer Hubs (Dedicated Roofing and Windows & Doors Terminals) -->
                <div class="nav-section-title" style="margin-top: 16px;">Trade Transfer Hubs</div>

                <a href="{{ route('roofing.index') }}" class="nav-item {{ request()->is('roofing*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </span>
                    <span>Roofing Transfer Hub</span>
                </a>

                <a href="{{ route('windowsDoors.index') }}" class="nav-item {{ request()->is('windows-doors*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="3" x2="12" y2="21"></line><line x1="3" y1="12" x2="21" y2="12"></line></svg>
                    </span>
                    <span>Windows & Doors Hub</span>
                </a>

                <!-- Project Costing & Expenditure Control with Dropdown -->
                <div class="nav-dropdown {{ request()->is('costing*') ? 'open active' : '' }}" id="costingDropdown" style="margin-top: 16px;">
                    <div class="nav-item nav-dropdown-trigger" onclick="toggleNavDropdown('costingDropdown')">
                        <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                            <span class="nav-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            </span>
                            <span>Project Costing</span>
                        </div>
                        <span class="dropdown-chevron">▼</span>
                    </div>
                    <div class="nav-dropdown-menu">
                        <a href="/costing" class="nav-sub-item {{ request()->is('costing') && !request()->has('project_id') ? 'active' : '' }}">
                            <span class="sub-dot dot-active"></span> All Projects Costing Matrix
                        </a>
                        <div class="nav-sub-divider">Individual Project Costing</div>
                        @if(isset($navProjects) && $navProjects->count() > 0)
                            @foreach($navProjects as $np)
                                <a href="/costing?project_id={{ $np->id }}" class="nav-sub-item {{ request()->query('project_id') == $np->id && request()->is('costing*') ? 'active' : '' }}" title="{{ $np->title }}">
                                    <span class="sub-dot {{ $np->status === 'completed' ? 'dot-completed' : 'dot-active' }}"></span>
                                    <span class="sub-text">{{ $np->title }}</span>
                                </a>
                            @endforeach
                        @else
                            <span class="nav-sub-empty">No projects created yet</span>
                        @endif
                    </div>
                </div>

                <a href="/personnel" class="nav-item {{ request()->is('personnel*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </span>
                    Engineers & Architects
                </a>

                <div class="nav-section-title" style="margin-top: 16px;">Ledgers & Archives</div>

                <a href="/history" class="nav-item {{ request()->is('history*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </span>
                    Project History
                </a>

                <a href="/payments" class="nav-item {{ request()->is('payments*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    </span>
                    Internal Payment Ledger
                </a>
            @endif
        </nav>

        <!-- User Profile Card & Logout -->
        <div class="sidebar-user-card" style="margin-top: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="user-avatar" style="background: {{ Auth::user()->isRoofingOfficer() ? 'rgba(239, 68, 68, 0.2)' : (Auth::user()->isWindowsDoorsOfficer() ? 'rgba(56, 189, 248, 0.2)' : 'rgba(239, 68, 68, 0.15)') }};">
                    <span style="font-weight: 800; font-size: 0.85rem; color: {{ Auth::user()->isRoofingOfficer() ? '#ef4444' : (Auth::user()->isWindowsDoorsOfficer() ? '#38bdf8' : '#ef4444') }};">
                        {{ Auth::user()->isRoofingOfficer() ? 'ROOF' : (Auth::user()->isWindowsDoorsOfficer() ? 'WNDR' : 'ADM') }}
                    </span>
                </div>
                <div style="flex: 1; overflow: hidden;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                        {{ Auth::user()->name ?? 'Administrator' }}
                    </div>
                    <div style="font-size: 0.7rem; color: {{ Auth::user()->isRoofingOfficer() ? '#ef4444' : (Auth::user()->isWindowsDoorsOfficer() ? '#38bdf8' : '#10b981') }}; font-weight: 600;">
                        ● {{ Auth::user()->role_title ?? 'Master Access' }}
                    </div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin-top: 10px;">
                @csrf
                <button type="submit" class="btn-logout" title="Sign out of Session">
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Shell -->
    <div class="app-content">
        <header class="top-navbar">
            <div style="display: flex; align-items: center; gap: 14px;">
                <h2 class="page-title">@yield('page_title', 'Dashboard')</h2>
                @if(Auth::check() && Auth::user()->isRoofingOfficer())
                    <span class="admin-badge-top" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5;">Roofing Specialist</span>
                @elseif(Auth::check() && Auth::user()->isWindowsDoorsOfficer())
                    <span class="admin-badge-top" style="background: rgba(56, 189, 248, 0.2); border-color: rgba(56, 189, 248, 0.4); color: #38bdf8;">Doors & Windows Specialist</span>
                @else
                    <span class="admin-badge-top">Master Admin</span>
                @endif
            </div>
            <div class="top-actions">
                @yield('top_actions')
            </div>
        </header>

        <main class="main-body">
            @if(session('error'))
                <div class="alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function toggleNavDropdown(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('open');
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
