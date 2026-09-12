<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'St. Bilfrid Development Corporation - Construction Management & Monitoring')</title>
    <link rel="stylesheet" href="/css/app.css?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">
    @yield('styles')
    @stack('styles')
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
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                @if(!request()->is('/') && !request()->is('home'))
                    <button type="button" onclick="navigateAppBack()" class="btn-topbar-back" title="Go back">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 8 8 12 12 16"></polyline>
                            <line x1="16" y1="12" x2="8" y2="12"></line>
                        </svg>
                        <span>Back</span>
                    </button>
                @endif
                <h2 class="page-title">@yield('page_title', 'Dashboard')</h2>
                @if(Auth::check() && Auth::user()->isRoofingOfficer())
                    <span class="admin-badge-top" style="background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5;">Roofing Specialist</span>
                @elseif(Auth::check() && Auth::user()->isWindowsDoorsOfficer())
                    <span class="admin-badge-top" style="background: rgba(56, 189, 248, 0.2); border-color: rgba(56, 189, 248, 0.4); color: #38bdf8;">Doors & Windows Specialist</span>
                @else
                    <span class="admin-badge-top">Master Admin</span>
                @endif

                <!-- Fast Project Quick Switcher -->
                @if(isset($navProjects) && $navProjects->count() > 0)
                    <div class="nav-project-quick-select-wrapper" style="position: relative;">
                        <button type="button" class="btn-project-switcher" onclick="toggleProjectSwitcherMenu(event)">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 20h20"></path><path d="M5 20V8.5L12 3l7 5.5V20"></path><path d="M9 20v-6h6v6"></path>
                            </svg>
                            <span class="switcher-label">Switch Project</span>
                            <span style="font-size: 0.65rem; opacity: 0.7;">▼</span>
                        </button>
                        <div class="project-switcher-dropdown" id="projectSwitcherDropdown">
                            <div class="switcher-header">Active Build Sites ({{ $navProjects->count() }})</div>
                            <div class="switcher-list">
                                @foreach($navProjects as $np)
                                    <a href="/projects/{{ $np->id }}" class="switcher-item">
                                        <div class="switcher-item-main">
                                            <span class="switcher-dot {{ $np->status === 'completed' ? 'dot-completed' : 'dot-active' }}"></span>
                                            <div style="flex: 1; min-width: 0;">
                                                <div class="switcher-title">{{ $np->title }}</div>
                                                <div class="switcher-meta">{{ $np->project_code ?? 'PROJ' }} &bull; {{ $np->overall_progress ?? 0 }}% Progress</div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Controls: Command Search Trigger & Top Action Slot -->
            <div class="top-actions" style="display: flex; align-items: center; gap: 10px;">
                <!-- Global Command Palette Hotkey Trigger Button -->
                <button type="button" class="btn-command-search" onclick="openCommandPalette()" title="Press Ctrl + K to search anything">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <span class="cmd-search-text">Search projects, materials, BOM...</span>
                    <kbd class="cmd-shortcut-key">Ctrl K</kbd>
                </button>

                @yield('top_actions')
            </div>
        </header>

        <main class="main-body">
            @if(session('error'))
                <div class="alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                    {{ session('error') }}
                </div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="alert-danger" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem;">
                    <div style="font-weight: 700; margin-bottom: 4px;">Please review the following errors:</div>
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert-warning" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.5); color: #d97706; padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span>{{ session('warning') }}</span>
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

    <!-- ====================================================
         GLOBAL INTERACTIVE COMMAND PALETTE MODAL (Ctrl + K)
         ==================================================== -->
    <div id="commandPaletteModal" class="cmd-palette-backdrop" onclick="handleCmdBackdropClick(event)">
        <div class="cmd-palette-card" onclick="event.stopPropagation()">
            <div class="cmd-palette-header">
                <div class="cmd-input-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" id="cmdSearchInput" class="cmd-search-input" placeholder="Type a command, project name, BOM item, or module..." autocomplete="off" oninput="filterCommandPalette(this.value)" onkeydown="handleCmdKeyNav(event)">
                <span class="cmd-close-btn" onclick="closeCommandPalette()" title="Close (Esc)">&times;</span>
            </div>

            <div class="cmd-palette-body" id="cmdPaletteResults">
                <!-- Category 1: Quick Actions -->
                <div class="cmd-category-group" data-category="actions">
                    <div class="cmd-category-title">⚡ Quick Operational Actions</div>
                    <div class="cmd-item" onclick="window.location.href='/projects#createProjectModal'" data-text="create new project add build site start contract">
                        <div class="cmd-item-icon icon-red">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">+ Create New Project</div>
                            <div class="cmd-item-sub">Initialize contract budget, land/floor area, and trade weights</div>
                        </div>
                        <span class="cmd-badge">Project</span>
                    </div>

                    <div class="cmd-item" onclick="window.location.href='/estimation'" data-text="new service estimate estimation client quotation cost calculation">
                        <div class="cmd-item-icon icon-amber">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">+ New Service Estimate</div>
                            <div class="cmd-item-sub">Generate instant finish-tier pricing and project quotation</div>
                        </div>
                        <span class="cmd-badge">Estimate</span>
                    </div>

                    <div class="cmd-item" onclick="window.location.href='{{ route('admin.suppliers.orders') }}'" data-text="new purchase order po procurement supplier order requisition">
                        <div class="cmd-item-icon icon-blue">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">+ Purchase Orders Hub</div>
                            <div class="cmd-item-sub">Issue supplier POs, manage deliveries, and track stock receipts</div>
                        </div>
                        <span class="cmd-badge">PO</span>
                    </div>
                </div>

                <!-- Category 2: Active Projects -->
                @if(isset($navProjects) && $navProjects->count() > 0)
                    <div class="cmd-category-group" data-category="projects">
                        <div class="cmd-category-title">🏗️ Active Construction Sites</div>
                        @foreach($navProjects as $np)
                            <div class="cmd-item" onclick="window.location.href='/projects/{{ $np->id }}'" data-text="{{ strtolower($np->title . ' ' . $np->project_code . ' ' . $np->client_name . ' ' . $np->location) }}">
                                <div class="cmd-item-icon icon-emerald">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20h20"></path><path d="M5 20V8.5L12 3l7 5.5V20"></path></svg>
                                </div>
                                <div class="cmd-item-content">
                                    <div class="cmd-item-title">{{ $np->title }} <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">({{ $np->project_code ?? 'PROJ' }})</span></div>
                                    <div class="cmd-item-sub">
                                        Client: {{ $np->client_name ?? 'N/A' }} &bull; Progress: {{ $np->overall_progress ?? 0 }}% &bull; Budget: ₱{{ number_format($np->contract_budget ?? 0) }}
                                    </div>
                                </div>
                                <span class="cmd-badge {{ $np->status === 'completed' ? 'badge-completed' : 'badge-active' }}">
                                    {{ ucfirst(str_replace('_', ' ', $np->status)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Category 3: System Modules & Ledgers -->
                <div class="cmd-category-group" data-category="navigation">
                    <div class="cmd-category-title">🧭 System Navigation & Ledgers</div>
                    <div class="cmd-item" onclick="window.location.href='/'" data-text="executive dashboard sales revenue metrics analytics summary home">
                        <div class="cmd-item-icon icon-blue">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">Executive Dashboard & Sales Command</div>
                            <div class="cmd-item-sub">Real-time revenue metrics, profit margins, and workforce allocation</div>
                        </div>
                        <span class="cmd-badge">Dashboard</span>
                    </div>

                    <div class="cmd-item" onclick="window.location.href='/bom'" data-text="bill of materials bom cost quantities takeoffs requisition">
                        <div class="cmd-item-icon icon-emerald">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">Bill of Materials (BOM) Hub</div>
                            <div class="cmd-item-sub">Cross-project materials breakdown, variances, and excess control</div>
                        </div>
                        <span class="cmd-badge">BOM</span>
                    </div>

                    <div class="cmd-item" onclick="window.location.href='/inventory'" data-text="inventory warehouse stock materials levels reorder">
                        <div class="cmd-item-icon icon-amber">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">Materials Inventory (INV)</div>
                            <div class="cmd-item-sub">Warehouse stock valuation, minimum thresholds, and safety alerts</div>
                        </div>
                        <span class="cmd-badge">Inventory</span>
                    </div>

                    <div class="cmd-item" onclick="window.location.href='{{ route('admin.suppliers.index') }}'" data-text="suppliers vendor network catalog materials pricing quotation">
                        <div class="cmd-item-icon icon-red">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">Supplier Network & Material Matrix</div>
                            <div class="cmd-item-sub">Supplier portal access, live catalogs, and price comparisons</div>
                        </div>
                        <span class="cmd-badge">Suppliers</span>
                    </div>

                    <div class="cmd-item" onclick="window.location.href='/payments'" data-text="payments sales internal ledger inflow invoices cash settlement">
                        <div class="cmd-item-icon icon-emerald">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        </div>
                        <div class="cmd-item-content">
                            <div class="cmd-item-title">Internal Payment & Sales Ledger</div>
                            <div class="cmd-item-sub">Milestone invoice tracking, cleared cash inflow, and overdue collections</div>
                        </div>
                        <span class="cmd-badge">Ledger</span>
                    </div>
                </div>
            </div>

            <!-- Command Palette Footer -->
            <div class="cmd-palette-footer">
                <div class="cmd-footer-keys">
                    <span class="key-hint"><kbd>&uarr;</kbd><kbd>&darr;</kbd> Navigate</span>
                    <span class="key-hint"><kbd>&crarr;</kbd> Select</span>
                    <span class="key-hint"><kbd>esc</kbd> Dismiss</span>
                </div>
                <div style="font-size: 0.725rem; color: var(--text-muted); font-weight: 600;">
                    St. Bilfrid Command Suite
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="/js/app.js?v={{ file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : time() }}"></script>
    <script>
        function toggleNavDropdown(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('open');
            }
        }

        function toggleProjectSwitcherMenu(e) {
            e.stopPropagation();
            const dd = document.getElementById('projectSwitcherDropdown');
            if (dd) {
                dd.classList.toggle('active');
            }
        }

        window.addEventListener('click', function(e) {
            const dd = document.getElementById('projectSwitcherDropdown');
            if (dd && !e.target.closest('.nav-project-quick-select-wrapper')) {
                dd.classList.remove('active');
            }
        });

        function navigateAppBack() {
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }

        function openModal(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add('active');
                el.style.display = 'flex';
            }
        }

        function closeModal(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('active');
                el.style.display = 'none';
            }
        }

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-backdrop')) {
                event.target.classList.remove('active');
                event.target.style.display = 'none';
            }
        });
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
