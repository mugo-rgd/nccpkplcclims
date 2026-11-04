<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'KPLC Meter Lab'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .navbar-top {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1040;
        }

        .navbar-top .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
        }

        .navbar-top .navbar-brand img {
            height: 40px;
            width: auto;
            margin-right: 12px;
            filter: brightness(0) invert(1);
        }

        .navbar-top .navbar-brand .brand-text small {
            display: block;
            font-size: 0.6rem;
            font-weight: 300;
            opacity: 0.8;
        }

        .navbar-top .nav-link {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .navbar-top .nav-link:hover,
        .navbar-top .nav-link.active {
            color: white !important;
            background: rgba(255,255,255,0.15);
        }

        .navbar-top .dropdown-toggle {
            color: white !important;
        }

        .navbar-top .dropdown-menu {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 0.5rem;
        }

        .navbar-top .dropdown-item {
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .navbar-top .dropdown-item:hover {
            background: #e3f2fd;
        }

        .badge-staff {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
        }

        .env-badge {
            font-size: 0.6rem;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
        }

        .sidebar-toggle-btn {
            border: none;
            background: rgba(255,255,255,0.15);
            color: white;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .sidebar-toggle-btn:hover {
            background: rgba(255,255,255,0.25);
            color: white;
        }

        body.top-menu-icons-only .navbar-top .navbar-nav.me-auto .nav-link {
            width: 42px;
            height: 42px;
            padding: 0;
            justify-content: center;
        }

        body.top-menu-icons-only .navbar-top .navbar-nav.me-auto .nav-link i {
            margin: 0;
            font-size: 1rem;
        }

        body.top-menu-icons-only .top-menu-label {
            display: none !important;
        }

        body.top-menu-icons-only .navbar-top .navbar-nav.me-auto .dropdown-toggle::after {
            display: none;
        }

        #appSidebar {
            background: white;
            min-height: calc(100vh - 60px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            padding: 20px 0;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar .nav-link {
            color: #555 !important;
            padding: 11px 20px;
            border-radius: 0;
            margin-bottom: 2px;
            border-left: 4px solid transparent;
            transition: all 0.25s ease;
            font-weight: 500;
            white-space: nowrap;
        }

        .sidebar .nav-link:hover {
            background: #f0f4ff;
            color: #1a237e !important;
            border-left-color: #1a237e;
        }

        .sidebar .nav-link.active {
            background: #e8eaf6;
            color: #1a237e !important;
            border-left-color: #1a237e;
            font-weight: 600;
        }

        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 8px;
        }

        .sidebar .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
            padding: 14px 20px 6px;
            font-weight: 700;
            white-space: nowrap;
        }

        .sidebar-logo {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid #e8eaf6;
            margin-bottom: 15px;
        }

        .sidebar-logo img {
            max-width: 80%;
            height: auto;
        }

        .sidebar-brand {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1a237e;
            display: block;
            margin-top: 5px;
        }

        #appMainContent {
            padding: 25px;
            min-height: calc(100vh - 60px);
            transition: all 0.3s ease;
        }

        body.sidebar-collapsed #appSidebar {
            width: 80px !important;
            max-width: 80px !important;
            flex: 0 0 80px !important;
        }

        body.sidebar-collapsed #appMainContent {
            width: calc(100% - 80px) !important;
            max-width: calc(100% - 80px) !important;
            flex: 0 0 calc(100% - 80px) !important;
        }

        body.sidebar-collapsed .sidebar-logo {
            padding: 10px;
        }

        body.sidebar-collapsed .sidebar-logo img {
            max-width: 45px;
        }

        body.sidebar-collapsed .sidebar-brand,
        body.sidebar-collapsed .nav-section-title {
            display: none;
        }

        body.sidebar-collapsed .sidebar .nav-link {
            text-align: center;
            padding: 13px 8px;
            font-size: 0;
        }

        body.sidebar-collapsed .sidebar .nav-link i {
            margin-right: 0;
            width: auto;
            font-size: 1.15rem;
        }

        body.sidebar-collapsed .sidebar .badge {
            font-size: 0.65rem;
            position: absolute;
            right: 8px;
            top: 6px;
        }

        .mobile-sidebar-backdrop {
            display: none;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 20px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
        }

        .alert-success { background: #e8f5e9; color: #2e7d32; }
        .alert-danger { background: #fce4ec; color: #c62828; }
        .alert-warning { background: #fff3e0; color: #e65100; }
        .alert-info { background: #e3f2fd; color: #0d47a1; }

        .workflow-step {
            position: relative;
            text-align: center;
        }

        .workflow-step .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .workflow-step .step-circle.completed { background: #198754; color: white; }
        .workflow-step .step-circle.active {
            background: #0d6efd;
            color: white;
            border: 3px solid #0d6efd;
            animation: pulse 2s infinite;
        }
        .workflow-step .step-circle.pending { background: #e9ecef; color: #6c757d; }

        .workflow-step .step-connector {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }

        .workflow-step .step-connector.completed { background: #198754; }

        .badge-status {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .shadow-hover { transition: box-shadow 0.3s ease; }
        .shadow-hover:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .cursor-pointer { cursor: pointer; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
        }

        @media (max-width: 768px) {
            .navbar-top {
                padding: 0.45rem 0.75rem;
            }

            .navbar-top .navbar-brand img {
                height: 30px;
            }

            .navbar-top .navbar-brand .brand-text {
                font-size: 0.9rem;
            }

            .navbar-top .navbar-brand .brand-text small,
            .env-badge {
                display: none !important;
            }

            body.top-menu-icons-only .navbar-top .navbar-nav.me-auto .nav-link {
                width: auto;
                height: auto;
                padding: 0.5rem 1rem;
                justify-content: flex-start;
            }

            body.top-menu-icons-only .top-menu-label {
                display: inline !important;
            }

            #appSidebar {
                display: block !important;
                position: fixed;
                top: 58px;
                left: -280px;
                width: 280px !important;
                max-width: 280px !important;
                height: calc(100vh - 58px);
                min-height: calc(100vh - 58px);
                z-index: 1050;
                transition: left 0.3s ease;
                padding: 15px 0;
            }

            body.mobile-sidebar-open #appSidebar {
                left: 0;
            }

            .mobile-sidebar-backdrop {
                display: none;
                position: fixed;
                top: 58px;
                left: 0;
                width: 100%;
                height: calc(100vh - 58px);
                background: rgba(0,0,0,0.45);
                z-index: 1045;
            }

            body.mobile-sidebar-open .mobile-sidebar-backdrop {
                display: block;
            }

            #appMainContent,
            body.sidebar-collapsed #appMainContent {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
                padding: 15px;
            }

            body.sidebar-collapsed #appSidebar {
                width: 280px !important;
                max-width: 280px !important;
                flex: unset !important;
            }

            body.sidebar-collapsed .sidebar .nav-link {
                text-align: left;
                padding: 11px 20px;
                font-size: inherit;
            }

            body.sidebar-collapsed .sidebar .nav-link i {
                width: 24px;
                margin-right: 8px;
                font-size: inherit;
            }

            body.sidebar-collapsed .sidebar-brand,
            body.sidebar-collapsed .nav-section-title {
                display: block;
            }

            .workflow-step .step-circle {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }

            .workflow-step .step-connector {
                display: none;
            }
        }

        /* Colored Top Menu Icons */
.navbar-top .nav-link i,
.navbar-top .dropdown-item i,
.sidebar-toggle-btn i {
    color: var(--menu-icon-color, #ffc107) !important;
}

/* Colored Sidebar Icons */
.sidebar .nav-link i {
    color: var(--menu-icon-color, #1a237e) !important;
}

/* Main menu icon colors */
.nav-link[title="Dashboard"] {
    --menu-icon-color: #00c853;
}

.nav-link[title="Calibrations"] {
    --menu-icon-color: #29b6f6;
}

.nav-link[title="Customers"] {
    --menu-icon-color: #ffb300;
}

.nav-link[title="Certificates"] {
    --menu-icon-color: #ef5350;
}

.nav-link[title="Quality Control"],
.nav-link[title="Quality"],
.nav-link[title="Quality Dashboard"] {
    --menu-icon-color: #ab47bc;
}

.nav-link[title="Objectives"] {
    --menu-icon-color: #26a69a;
}

.nav-link[title="Achievements"] {
    --menu-icon-color: #66bb6a;
}

.nav-link[title="Corrective Actions"] {
    --menu-icon-color: #ff7043;
}

.nav-link[title="Reports"] {
    --menu-icon-color: #5c6bc0;
}

.nav-link[title="Chat"] {
    --menu-icon-color: #00bcd4;
}

.nav-link[title="User Guide"] {
    --menu-icon-color: #8d6e63;
}

.nav-link[title="Equipment"] {
    --menu-icon-color: #ffa726;
}

.nav-link[title="Admin"] {
    --menu-icon-color: #ec407a;
}

.nav-link[title="Users"] {
    --menu-icon-color: #42a5f5;
}

.nav-link[title="Audit Logs"] {
    --menu-icon-color: #7e57c2;
}

/* Toggle button icon colors */
#sidebarToggle i {
    color: #ffeb3b !important;
}

#topMenuToggle i {
    color: #69f0ae !important;
}

/* Better active/hover icon background */
.sidebar .nav-link i,
.navbar-top .nav-link i {
    transition: transform 0.2s ease, color 0.2s ease;
}

.sidebar .nav-link:hover i,
.navbar-top .nav-link:hover i,
.sidebar .nav-link.active i,
.navbar-top .nav-link.active i {
    transform: scale(1.12);
}
    </style>

    @stack('styles')
</head>

<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-top">
        <div class="container-fluid px-3 px-md-4">
            @auth
                <button id="sidebarToggle" class="sidebar-toggle-btn me-2" type="button" title="Toggle Sidebar" data-bs-toggle="tooltip" data-bs-placement="bottom">
                    <i class="fas fa-bars"></i>
                </button>

                <button id="topMenuToggle" class="sidebar-toggle-btn me-2 d-none d-md-inline-flex" type="button" title="Toggle Top Menu Icons" data-bs-toggle="tooltip" data-bs-placement="bottom">
                    <i class="fas fa-grip"></i>
                </button>
            @endauth

            <a class="navbar-brand" href="{{ url('/') }}">
                @php
                    $logoPath = public_path('img/logo.png');
                    $logoExists = file_exists($logoPath);
                @endphp

                @if($logoExists)
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}">
                @else
                    <i class="fas fa-microscope me-2"></i>
                @endif

                <span class="brand-text">
                    {{ config('app.name', 'Meter Laboratory') }}
                    <small>Meter Central Laboratory</small>
                </span>

                @if(config('app.env') !== 'production')
                    <span class="badge env-badge bg-{{ config('app.env') === 'local' ? 'warning' : 'info' }} ms-2">
                        {{ strtoupper(config('app.env')) }}
                    </span>
                @endif
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    @auth
                        @can('view_dashboard')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Dashboard" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span class="top-menu-label">Dashboard</span>
                                </a>
                            </li>
                        @endcan

                        @can('view_calibrations')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('calibrations.*') ? 'active' : '' }}" href="{{ route('calibrations.index') }}" title="Calibrations" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                    <i class="fas fa-microscope"></i>
                                    <span class="top-menu-label">Calibrations</span>
                                </a>
                            </li>
                        @endcan

                        @can('view_customers')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}" title="Customers" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                    <i class="fas fa-building"></i>
                                    <span class="top-menu-label">Customers</span>
                                </a>
                            </li>
                        @endcan

                        @can('view_certificates')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}" href="{{ route('certificates.index') }}" title="Certificates" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                    <i class="fas fa-file-pdf"></i>
                                    <span class="top-menu-label">Certificates</span>
                                </a>
                            </li>
                        @endcan

                        @can('quality_check')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.quality.*') ? 'active' : '' }}" href="{{ route('admin.quality.dashboard') }}" title="Quality Control" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                    <i class="fas fa-clipboard-check"></i>
                                    <span class="top-menu-label">Quality Control</span>
                                </a>
                            </li>
                        @endcan

                        @if(auth()->user()->hasAnyPermission(['view_quality_dashboard', 'view_quality_objectives', 'view_achievements', 'view_corrective_actions', 'view_quality_reports']))
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('quality.*') ? 'active' : '' }}"
                                   href="#" id="qualityDropdown" role="button" data-bs-toggle="dropdown" title="Quality Management">
                                    <i class="fas fa-clipboard-check"></i>
                                    <span class="top-menu-label">Quality Management</span>
                                </a>

                                <ul class="dropdown-menu" aria-labelledby="qualityDropdown">
                                    @can('view_quality_dashboard')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('quality.dashboard') ? 'active' : '' }}" href="{{ route('quality.dashboard') }}">
                                                <i class="fas fa-tachometer-alt fa-fw"></i> Dashboard
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view_quality_objectives')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('quality.objectives.*') ? 'active' : '' }}" href="{{ route('quality.objectives.index') }}">
                                                <i class="fas fa-bullseye fa-fw"></i> Objectives
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view_achievements')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('quality.achievements.*') ? 'active' : '' }}" href="{{ route('quality.achievements.index') }}">
                                                <i class="fas fa-chart-line fa-fw"></i> Achievements
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view_corrective_actions')
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('quality.corrective-actions.*') ? 'active' : '' }}" href="{{ route('quality.corrective-actions.index') }}">
                                                <i class="fas fa-tasks fa-fw"></i> Corrective Actions
                                            </a>
                                        </li>
                                    @endcan

                                    @can('view_quality_reports')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('quality.reports.*') ? 'active' : '' }}" href="{{ route('quality.reports.index') }}">
                                                <i class="fas fa-file-alt fa-fw"></i> Reports
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}" title="Chat" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                <i class="fas fa-comment-dots"></i>
                                <span class="top-menu-label position-relative">
                                    Chat
                                    <span id="unreadBadge" class="badge bg-danger position-absolute top-0 start-100 translate-middle"
                                          style="font-size: 0.6rem; display: none;">0</span>
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('user-guide.*') ? 'active' : '' }}" href="{{ route('user-guide.index') }}" title="User Guide" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                <i class="fas fa-book"></i>
                                <span class="top-menu-label">User Guide</span>
                            </a>
                        </li>

                        @can('view_equipment')
                            @if(Route::has('equipment.index'))
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}" href="{{ route('equipment.index') }}" title="Equipment" data-bs-toggle="tooltip" data-bs-placement="bottom">
                                        <i class="fas fa-tools"></i>
                                        <span class="top-menu-label">Equipment</span>
                                    </a>
                                </li>
                            @endif
                        @endcan

                        @role('super_admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" title="Admin">
                                    <i class="fas fa-cog"></i>
                                    <span class="top-menu-label">Admin</span>
                                </a>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.index') }}">
                                            <i class="fas fa-users-cog"></i> Users
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('audit-logs.index') }}">
                                            <i class="fas fa-history"></i> Audit Logs
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endrole
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @guest
                        @if(Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            </li>
                        @endif

                        @if(Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">
                                    <i class="fas fa-user-plus"></i> Register
                                </a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                {{ Auth::user()->name }}
                                @if(Auth::user()->staff_number)
                                    <span class="badge-staff ms-1">{{ Auth::user()->staff_number }}</span>
                                @endif
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>

                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user-tag me-2"></i>
                                    <span class="badge bg-{{ Auth::user()->hasRole('super_admin') ? 'danger' : (Auth::user()->hasRole('lab_manager') ? 'primary' : 'secondary') }}">
                                        {{ Auth::user()->role_display ?? 'User' }}
                                    </span>
                                </a>

                                @if(Auth::user()->hasRole('customer'))
                                    <a class="dropdown-item" href="{{ route('customer.profile') }}">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                @endif

                                <hr class="dropdown-divider">

                                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <div class="mobile-sidebar-backdrop" id="mobileSidebarBackdrop"></div>

    <div class="container-fluid px-0">
        <div class="row g-0">
            @auth
                <div id="appSidebar" class="col-md-2 sidebar d-none d-md-block">
                    <div class="sidebar-logo">
                        @if($logoExists)
                            <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}">
                        @else
                            <i class="fas fa-microscope fa-2x" style="color: #1a237e;"></i>
                        @endif
                        <span class="sidebar-brand">METER CENTRAL LAB</span>
                    </div>

                    <div class="nav-section-title">Main Menu</div>

                    @can('view_dashboard')
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    @endcan

                    @can('view_calibrations')
                        <a class="nav-link {{ request()->routeIs('calibrations.*') ? 'active' : '' }}" href="{{ route('calibrations.index') }}" title="Calibrations">
                            <i class="fas fa-microscope"></i> Calibrations
                        </a>
                    @endcan

                    @can('view_calibrations')
                        @php
                            try {
                                $batchCount = \App\Models\Calibration::whereNotNull('batch_id')->distinct('batch_id')->count();
                            } catch (\Exception $e) {
                                $batchCount = 0;
                            }
                        @endphp

                        <a class="nav-link {{ request()->routeIs('calibrations.batches*') ? 'active' : '' }}" href="{{ route('calibrations.batches') }}" title="Batches">
                            <i class="fas fa-layer-group"></i> Batches
                            @if($batchCount > 0)
                                <span class="badge bg-info float-end">{{ $batchCount }}</span>
                            @endif
                        </a>
                    @endcan

                    @can('view_customers')
                        <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}" title="Customers">
                            <i class="fas fa-building"></i> Customers
                        </a>
                    @endcan

                    @can('view_certificates')
                        <a class="nav-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}" href="{{ route('certificates.index') }}" title="Certificates">
                            <i class="fas fa-file-pdf"></i> Certificates
                        </a>
                    @endcan

                    @can('quality_check')
                        <a class="nav-link {{ request()->routeIs('admin.quality.*') ? 'active' : '' }}" href="{{ route('admin.quality.dashboard') }}" title="Quality Control">
                            <i class="fas fa-clipboard-check"></i> Quality Control
                        </a>
                    @endcan

                    @if(auth()->user()->hasAnyPermission(['view_quality_dashboard', 'view_quality_objectives', 'view_achievements', 'view_corrective_actions', 'view_quality_reports']))
                        <div class="nav-section-title mt-3">Quality Management</div>

                        @can('view_quality_dashboard')
                            <a class="nav-link {{ request()->routeIs('quality.dashboard') ? 'active' : '' }}" href="{{ route('quality.dashboard') }}" title="Quality Dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        @endcan

                        @can('view_quality_objectives')
                            <a class="nav-link {{ request()->routeIs('quality.objectives.*') ? 'active' : '' }}" href="{{ route('quality.objectives.index') }}" title="Objectives">
                                <i class="fas fa-bullseye"></i> Objectives
                            </a>
                        @endcan

                        @can('view_achievements')
                            <a class="nav-link {{ request()->routeIs('quality.achievements.*') ? 'active' : '' }}" href="{{ route('quality.achievements.index') }}" title="Achievements">
                                <i class="fas fa-chart-line"></i> Achievements
                            </a>
                        @endcan

                        @can('view_corrective_actions')
                            <a class="nav-link {{ request()->routeIs('quality.corrective-actions.*') ? 'active' : '' }}" href="{{ route('quality.corrective-actions.index') }}" title="Corrective Actions">
                                <i class="fas fa-tasks"></i> Corrective Actions
                            </a>
                        @endcan

                        @can('view_quality_reports')
                            <a class="nav-link {{ request()->routeIs('quality.reports.*') ? 'active' : '' }}" href="{{ route('quality.reports.index') }}" title="Reports">
                                <i class="fas fa-file-alt"></i> Reports
                            </a>
                        @endcan
                    @endif

                    <div class="nav-section-title mt-3">Communication</div>

                    <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}" title="Chat">
                        <i class="fas fa-comment-dots"></i> Chat
                        <span id="sidebarUnreadBadge" class="badge bg-info float-end" style="display: none;">0</span>
                    </a>

                    <a class="nav-link {{ request()->routeIs('user-guide.*') ? 'active' : '' }}" href="{{ route('user-guide.index') }}" title="User Guide">
                        <i class="fas fa-book"></i> User Guide
                    </a>

                    @can('view_equipment')
                        <a class="nav-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}" href="{{ route('equipment.index') }}" title="Equipment">
                            <i class="fas fa-tools"></i> Equipment
                        </a>
                    @endcan

                    @can('view_users')
                        <div class="nav-section-title mt-3">Administration</div>

                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}" title="Users">
                            <i class="fas fa-users-cog"></i> Users
                        </a>
                    @endcan

                    @can('view_audit_logs')
                        <a class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}" href="{{ route('audit-logs.index') }}" title="Audit Logs">
                            <i class="fas fa-history"></i> Audit Logs
                        </a>
                    @endcan
                </div>
            @endauth

            <div id="appMainContent" class="col-md-10 main-content" style="{{ auth()->check() ? '' : 'margin-left: auto; margin-right: auto; max-width: 1200px;' }}">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    window.axios = axios;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    if (csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const topMenuToggle = document.getElementById('topMenuToggle');
        const mobileSidebarBackdrop = document.getElementById('mobileSidebarBackdrop');

        if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth > 768) {
            document.body.classList.add('sidebar-collapsed');
        }

        if (localStorage.getItem('topMenuIconsOnly') === 'true' && window.innerWidth > 768) {
            document.body.classList.add('top-menu-icons-only');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    document.body.classList.toggle('mobile-sidebar-open');
                    return;
                }

                document.body.classList.toggle('sidebar-collapsed');

                localStorage.setItem(
                    'sidebarCollapsed',
                    document.body.classList.contains('sidebar-collapsed') ? 'true' : 'false'
                );
            });
        }

        if (topMenuToggle) {
            topMenuToggle.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    return;
                }

                document.body.classList.toggle('top-menu-icons-only');

                localStorage.setItem(
                    'topMenuIconsOnly',
                    document.body.classList.contains('top-menu-icons-only') ? 'true' : 'false'
                );
            });
        }

        if (mobileSidebarBackdrop) {
            mobileSidebarBackdrop.addEventListener('click', function () {
                document.body.classList.remove('mobile-sidebar-open');
            });
        }

        document.querySelectorAll('#appSidebar .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 768) {
                    document.body.classList.remove('mobile-sidebar-open');
                }
            });
        });

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            new bootstrap.Tooltip(element);
        });

        if (window.axios && {{ auth()->check() ? 'true' : 'false' }} && {{ Route::has('chat.unread-count') ? 'true' : 'false' }}) {
            axios.get('{{ Route::has('chat.unread-count') ? route('chat.unread-count') : '#' }}')
                .then(function (response) {
                    const count = response.data.unread_count || 0;
                    const badges = [
                        document.getElementById('unreadBadge'),
                        document.getElementById('sidebarUnreadBadge')
                    ];

                    badges.forEach(function (badge) {
                        if (!badge) return;

                        badge.textContent = count > 0 ? count : '';
                        badge.style.display = count > 0 ? 'inline-block' : 'none';
                    });
                })
                .catch(function () {});
        }
    });
</script>

@stack('scripts')
</body>
</html>
