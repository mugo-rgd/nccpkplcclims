{{-- ============================================================= --}}
{{-- KPLC METER CALIBRATION LABORATORY SYSTEM - NAVIGATION         --}}
{{-- ROW 1: BRAND / SYSTEM IDENTITY                                  --}}
{{-- ROW 2: APPLICATION MENUS + COMPACT/EXPAND                      --}}
{{-- ROW 3: NOTIFICATIONS + USER ACCOUNT / LOGOUT                   --}}
{{-- ============================================================= --}}

@php
    $currentUser = auth()->user();

    $isCustomer = $currentUser
        ? (
            $currentUser->hasRole('customer')
            || $currentUser->role === 'customer'
        )
        : false;

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    $navbarNotifications = collect();
    $navbarNotificationCount = 0;

    if ($currentUser) {
        $navbarNotifications = $currentUser
            ->unreadNotifications()
            ->latest()
            ->limit(10)
            ->get();

        $navbarNotificationCount = $currentUser
            ->unreadNotifications()
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */

    $logoPath = public_path('img/logo.png');

    $logoExists = file_exists($logoPath);

    /*
    |--------------------------------------------------------------------------
    | Pending Quote Requests
    |--------------------------------------------------------------------------
    */

    $pendingQuoteRequests = 0;

    if (
        $currentUser
        && !$isCustomer
        && Route::has('admin.quote-requests.index')
    ) {
        try {
            $pendingQuoteRequests =
                \App\Models\PublicQuoteRequest::query()
                    ->where('status', 'pending')
                    ->count();
        } catch (\Throwable $exception) {
            $pendingQuoteRequests = 0;
        }
    }
@endphp


<nav class="navbar navbar-expand-md kplc-top-navigation">

    {{-- ========================================================= --}}
    {{-- ROW 1: BRAND / HEADER                                    --}}
    {{-- ========================================================= --}}

    <div class="kplc-brand-row">

        <div class="container-fluid kplc-brand-container">

            {{-- ================================================= --}}
            {{-- LEFT SIDE                                        --}}
            {{-- ================================================= --}}

            <div class="kplc-brand-left">

                @auth
                    <button
                        id="sidebarToggle"
                        class="kplc-header-button"
                        type="button"
                        title="Collapse Sidebar"
                        aria-label="Collapse or expand sidebar"
                    >
                        <i class="fas fa-bars"></i>
                    </button>
                @endauth


                <a
                    class="kplc-brand"
                    href="{{ url('/') }}"
                >

                    @if($logoExists)

                        <img
                            src="{{ asset('img/logo.png') }}"
                            alt="{{ config('app.name') }}"
                            class="kplc-brand-logo"
                        >

                    @else

                        <span class="kplc-brand-logo-fallback">
                            <i class="fas fa-microscope"></i>
                        </span>

                    @endif


                    <span class="kplc-brand-text">

                        <span class="kplc-brand-title">
                            {{
                                config(
                                    'app.name',
                                    'KPLC Meter Calibration Laboratory System'
                                )
                            }}
                        </span>

                        <small class="kplc-brand-subtitle">
                            Meter Central Laboratory
                        </small>

                    </span>


                    @if(config('app.env') !== 'production')

                        <span
                            class="badge bg-{{
                                config('app.env') === 'local'
                                    ? 'warning'
                                    : 'info'
                            }}"
                        >
                            {{ strtoupper(config('app.env')) }}
                        </span>

                    @endif

                </a>

            </div>


            {{-- ================================================= --}}
            {{-- RIGHT SIDE: MOBILE MENU TOGGLE ONLY               --}}
            {{-- ================================================= --}}

            <div class="kplc-brand-actions">
                {{-- ============================================= --}}
                {{-- MOBILE MENU BUTTON                            --}}
                {{-- ============================================= --}}

                <button
                class="kplc-mobile-menu-button"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMain"
                aria-controls="navbarMain"
                aria-expanded="false"
                aria-label="Toggle main navigation"
                >
                <i class="fas fa-bars"></i>
                </button>
            </div>

        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- ROW 2: APPLICATION MENU                                  --}}
    {{-- ========================================================= --}}

    <div class="kplc-menu-row">

        <div
            class="
                collapse
                navbar-collapse
                kplc-navbar-main
            "
            id="navbarMain"
        >

            <div
                class="
                    container-fluid
                    kplc-menu-container
                "
            >

                {{-- ============================================= --}}
                {{-- MAIN MENU                                    --}}
                {{-- ============================================= --}}

                <div class="kplc-menu-scroll">

                    <ul
                        class="
                            navbar-nav
                            kplc-primary-menu
                        "
                    >

                        @auth

                            {{-- ================================= --}}
                            {{-- CUSTOMER MENU                     --}}
                            {{-- ================================= --}}

                            @if($isCustomer)

                                {{-- Customer: Dashboard --}}
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
                                       href="{{ route('customer.dashboard') }}">
                                        <span class="menu-icon menu-dashboard"><i class="fas fa-tachometer-alt"></i></span>
                                        <span class="top-menu-label">Dashboard</span>
                                    </a>
                                </li>

                                {{-- Customer: Calibrations --}}
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('customer.calibrations.*') ? 'active' : '' }}"
                                       href="{{ route('customer.calibrations') }}">
                                        <span class="menu-icon menu-lab"><i class="fas fa-microscope"></i></span>
                                        <span class="top-menu-label">Calibrations</span>
                                    </a>
                                </li>

                                {{-- Customer: Quotes --}}
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('customer.quotes.*') ? 'active' : '' }}"
                                       href="{{ route('customer.quotes') }}">
                                        <span class="menu-icon menu-quotes"><i class="fas fa-file-invoice-dollar"></i></span>
                                        <span class="top-menu-label">Quotes</span>
                                    </a>
                                </li>

                                {{-- Customer: Certificates --}}
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('customer.certificates.*') ? 'active' : '' }}"
                                       href="{{ route('customer.certificates') }}">
                                        <span class="menu-icon menu-certificates"><i class="fas fa-certificate"></i></span>
                                        <span class="top-menu-label">Certificates</span>
                                    </a>
                                </li>

                                {{-- Customer: More --}}
                                <li class="nav-item dropdown customer-more-menu">
                                    <a class="nav-link dropdown-toggle {{
                                        request()->routeIs('customer.job-cards')
                                        || request()->routeIs('customer.delivery-notes')
                                        || request()->routeIs('customer.collection-notes')
                                        || request()->routeIs('customer.request-forms')
                                        || request()->routeIs('calibrations.create')
                                            ? 'active' : ''
                                    }}"
                                       href="#"
                                       id="customerMoreDropdown"
                                       role="button"
                                       data-bs-toggle="dropdown"
                                       data-bs-display="static"
                                       aria-expanded="false">
                                        <span class="menu-icon menu-more"><i class="fas fa-ellipsis-h"></i></span>
                                        <span class="top-menu-label">More</span>
                                    </a>

                                    <ul class="dropdown-menu customer-more-dropdown" aria-labelledby="customerMoreDropdown">
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('customer.job-cards') ? 'active' : '' }}"
                                               href="{{ route('customer.job-cards') }}">
                                                <span class="menu-icon menu-jobcards"><i class="fas fa-clipboard-list"></i></span>
                                                Job Cards
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('customer.delivery-notes') ? 'active' : '' }}"
                                               href="{{ route('customer.delivery-notes') }}">
                                                <span class="menu-icon menu-delivery"><i class="fas fa-truck"></i></span>
                                                Delivery Notes
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('customer.collection-notes') ? 'active' : '' }}"
                                               href="{{ route('customer.collection-notes') }}">
                                                <span class="menu-icon menu-collection"><i class="fas fa-hand-holding"></i></span>
                                                Collection Notes
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('customer.request-forms') ? 'active' : '' }}"
                                               href="{{ route('customer.request-forms') }}">
                                                <span class="menu-icon menu-request"><i class="fas fa-file-signature"></i></span>
                                                Request Forms
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item {{ request()->routeIs('calibrations.create') ? 'active' : '' }}"
                                               href="{{ route('calibrations.create') }}">
                                                <span class="menu-icon menu-new"><i class="fas fa-plus-circle"></i></span>
                                                New Request
                                            </a>
                                        </li>
                                    </ul>
                                </li>


                            @else

                                {{-- ================================= --}}
                                {{-- INTERNAL STAFF MENU               --}}
                                {{-- ================================= --}}

                                @can('view_dashboard')

                                    <li class="nav-item">

                                        <a
                                            class="nav-link {{
                                                request()->routeIs(
                                                    'dashboard'
                                                )
                                                    ? 'active'
                                                    : ''
                                            }}"
                                            href="{{ route('dashboard') }}"
                                        >

                                            <span
                                                class="
                                                    menu-icon
                                                    menu-dashboard
                                                "
                                            >
                                                <i
                                                    class="
                                                        fas
                                                        fa-tachometer-alt
                                                    "
                                                ></i>
                                            </span>

                                            <span class="top-menu-label">
                                                Dashboard
                                            </span>

                                        </a>

                                    </li>

                                @endcan


                                @can('view_calibrations')

                                    <li class="nav-item">

                                        <a
                                            class="nav-link {{
                                                request()->routeIs(
                                                    'calibrations.*'
                                                )
                                                    ? 'active'
                                                    : ''
                                            }}"
                                            href="{{
                                                route(
                                                    'calibrations.index'
                                                )
                                            }}"
                                        >

                                            <span
                                                class="
                                                    menu-icon
                                                    menu-lab
                                                "
                                            >
                                                <i
                                                    class="
                                                        fas
                                                        fa-microscope
                                                    "
                                                ></i>
                                            </span>

                                            <span class="top-menu-label">
                                                Calibrations
                                            </span>

                                        </a>

                                    </li>

                                @endcan


                                @can('view_customers')

                                    @if(
                                        Route::has(
                                            'admin.customers.index'
                                        )
                                    )

                                        <li class="nav-item">

                                            <a
                                                class="nav-link {{
                                                    request()->routeIs(
                                                        'admin.customers.*'
                                                    )
                                                        ? 'active'
                                                        : ''
                                                }}"
                                                href="{{
                                                    route(
                                                        'admin.customers.index'
                                                    )
                                                }}"
                                            >

                                                <span
                                                    class="
                                                        menu-icon
                                                        menu-customers
                                                    "
                                                >
                                                    <i
                                                        class="
                                                            fas
                                                            fa-building
                                                        "
                                                    ></i>
                                                </span>

                                                <span class="top-menu-label">
                                                    Customers
                                                </span>

                                            </a>

                                        </li>

                                    @endif

                                @endcan


                                @can('quality_check')

                                    @if(
                                        Route::has(
                                            'admin.quality.dashboard'
                                        )
                                    )

                                        <li class="nav-item">

                                            <a
                                                class="nav-link {{
                                                    request()->routeIs(
                                                        'admin.quality.*'
                                                    )
                                                        ? 'active'
                                                        : ''
                                                }}"
                                                href="{{
                                                    route(
                                                        'admin.quality.dashboard'
                                                    )
                                                }}"
                                            >

                                                <span
                                                    class="
                                                        menu-icon
                                                        menu-quality-control
                                                    "
                                                >
                                                    <i
                                                        class="
                                                            fas
                                                            fa-clipboard-check
                                                        "
                                                    ></i>
                                                </span>

                                                <span class="top-menu-label">
                                                    Quality Control
                                                </span>

                                            </a>

                                        </li>

                                    @endif

                                @endcan


                                @if(Route::has('chat.index'))

                                    <li class="nav-item">

                                        <a
                                            class="nav-link {{
                                                request()->routeIs(
                                                    'chat.*'
                                                )
                                                    ? 'active'
                                                    : ''
                                            }}"
                                            href="{{
                                                route(
                                                    'chat.index'
                                                )
                                            }}"
                                        >

                                            <span
                                                class="
                                                    menu-icon
                                                    menu-chat
                                                "
                                            >
                                                <i
                                                    class="
                                                        fas
                                                        fa-comment-dots
                                                    "
                                                ></i>
                                            </span>

                                            <span
                                                class="
                                                    top-menu-label
                                                    position-relative
                                                "
                                            >
                                                Chat

                                                <span
                                                    id="unreadBadge"
                                                    class="
                                                        badge
                                                        bg-danger
                                                        position-absolute
                                                        top-0
                                                        start-100
                                                        translate-middle
                                                    "
                                                    style="
                                                        font-size: 0.6rem;
                                                        display: none;
                                                    "
                                                >
                                                    0
                                                </span>

                                            </span>

                                        </a>

                                    </li>

                                @endif

                            @endif

                        @endauth

                    </ul>

                </div>


                {{-- ============================================= --}}
                {{-- MENU ROW RIGHT SIDE                           --}}
                {{-- ============================================= --}}

                <div class="kplc-menu-actions">

                    {{-- ========================================= --}}
                    {{-- COMPACT / EXPAND MENU - ROW 2             --}}
                    {{-- ========================================= --}}

                    @auth
                        <button
                            id="topMenuToggle"
                            class="kplc-menu-compact-button d-none d-lg-inline-flex"
                            type="button"
                            title="Compact / Expand Menu"
                            aria-label="Compact or expand menu"
                        >
                            <i class="fas fa-table-columns me-2"></i>
                            <span class="compact-menu-text">Compact Menu</span>
                        </button>
                    @endauth


                    {{-- ========================================= --}}
                    {{-- QUALITY MANAGEMENT                       --}}
                    {{-- ========================================= --}}

                    @auth

                        @if(
                            !$isCustomer
                            && $currentUser
                            && $currentUser->hasAnyPermission([
                                'view_quality_dashboard',
                                'view_quality_objectives',
                                'view_achievements',
                                'view_corrective_actions',
                                'view_quality_reports',
                            ])
                        )

                            <ul
                                class="
                                    navbar-nav
                                    quality-menu-wrapper
                                "
                            >

                                <li
                                    class="
                                        nav-item
                                        dropdown
                                    "
                                >

                                    <a
                                        class="
                                            nav-link
                                            dropdown-toggle
                                            {{
                                                request()->routeIs(
                                                    'quality.*'
                                                )
                                                    ? 'active'
                                                    : ''
                                            }}
                                        "
                                        href="#"
                                        id="qualityDropdown"
                                        role="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >

                                        <span
                                            class="
                                                menu-icon
                                                menu-quality
                                            "
                                        >
                                            <i
                                                class="
                                                    fas
                                                    fa-award
                                                "
                                            ></i>
                                        </span>

                                        <span class="top-menu-label">
                                            Quality Management
                                        </span>

                                    </a>


                                    <ul
                                        class="
                                            dropdown-menu
                                            dropdown-menu-end
                                            quality-dropdown-menu
                                        "
                                        aria-labelledby="qualityDropdown"
                                    >

                                        <li>

                                            <h6 class="dropdown-header">

                                                <i
                                                    class="
                                                        fas
                                                        fa-award
                                                        text-warning
                                                        me-2
                                                    "
                                                ></i>

                                                Quality Management

                                            </h6>

                                        </li>


                                        @can('view_quality_dashboard')

                                            @if(
                                                Route::has(
                                                    'quality.dashboard'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'quality.dashboard'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'quality.dashboard'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-dashboard
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-tachometer-alt
                                                                    fa-fw
                                                                "
                                                            ></i>
                                                        </span>

                                                        Dashboard

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan


                                        @can('view_quality_objectives')

                                            @if(
                                                Route::has(
                                                    'quality.objectives.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'quality.objectives.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'quality.objectives.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-objectives
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-bullseye
                                                                    fa-fw
                                                                "
                                                            ></i>
                                                        </span>

                                                        Objectives

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan


                                        @can('view_achievements')

                                            @if(
                                                Route::has(
                                                    'quality.achievements.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'quality.achievements.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'quality.achievements.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-achievements
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-chart-line
                                                                    fa-fw
                                                                "
                                                            ></i>
                                                        </span>

                                                        Achievements

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan


                                        @can('view_corrective_actions')

                                            @if(
                                                Route::has(
                                                    'quality.corrective-actions.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'quality.corrective-actions.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'quality.corrective-actions.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-actions
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-tasks
                                                                    fa-fw
                                                                "
                                                            ></i>
                                                        </span>

                                                        Corrective Actions

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan


                                        @can('view_quality_reports')

                                            @if(
                                                Route::has(
                                                    'quality.reports.index'
                                                )
                                            )

                                                <li>
                                                    <hr
                                                        class="
                                                            dropdown-divider
                                                        "
                                                    >
                                                </li>

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'quality.reports.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'quality.reports.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-reports
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-file-alt
                                                                    fa-fw
                                                                "
                                                            ></i>
                                                        </span>

                                                        Reports

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan

                                    </ul>

                                </li>

                            </ul>

                        @endif

                    @endauth


                    {{-- ========================================= --}}
                    {{-- MORE MENU                                --}}
                    {{-- ========================================= --}}

                    @auth

                        @if(!$isCustomer)

                            <ul
                                class="
                                    navbar-nav
                                    more-menu-wrapper
                                "
                            >

                                <li
                                    class="
                                        nav-item
                                        dropdown
                                    "
                                >

                                    <a
                                        class="
                                            nav-link
                                            dropdown-toggle
                                            {{
                                                request()->routeIs(
                                                    'certificates.*'
                                                )
                                                || request()->routeIs(
                                                    'laboratory-configuration.*'
                                                )
                                                || request()->routeIs(
                                                    'admin.quote-requests.*'
                                                )
                                                || request()->routeIs(
                                                    'ai-assistant.*'
                                                )
                                                || request()->routeIs(
                                                    'user-guide.*'
                                                )
                                                || request()->routeIs(
                                                    'equipment.*'
                                                )
                                                || request()->routeIs(
                                                    'users.*'
                                                )
                                                || request()->routeIs(
                                                    'audit-logs.*'
                                                )
                                                    ? 'active'
                                                    : ''
                                            }}
                                        "
                                        href="#"
                                        id="moreMenuDropdown"
                                        role="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >

                                        <span
                                            class="
                                                menu-icon
                                                menu-more
                                            "
                                        >
                                            <i
                                                class="
                                                    fas
                                                    fa-ellipsis-h
                                                "
                                            ></i>
                                        </span>

                                        <span class="top-menu-label">
                                            More
                                        </span>

                                    </a>


                                    <ul
                                        class="
                                            dropdown-menu
                                            dropdown-menu-end
                                            more-dropdown-menu
                                        "
                                        aria-labelledby="moreMenuDropdown"
                                    >

                                        @can('view_certificates')

                                            @if(
                                                Route::has(
                                                    'certificates.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'certificates.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'certificates.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-certificates
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-file-pdf
                                                                "
                                                            ></i>
                                                        </span>

                                                        Certificates

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan


                                        @canany([
                                            'view_service_types',
                                            'view_equipment_types',
                                            'view_equipment_categories',
                                            'view_equipment_manufacturers'
                                        ])

                                            @if(
                                                Route::has(
                                                    'laboratory-configuration.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'laboratory-configuration.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'laboratory-configuration.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-configuration
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-gears
                                                                "
                                                            ></i>
                                                        </span>

                                                        Configuration

                                                    </a>

                                                </li>

                                            @endif

                                        @endcanany


                                        @if(
                                            Route::has(
                                                'admin.quote-requests.index'
                                            )
                                        )

                                            <li>

                                                <a
                                                    class="
                                                        dropdown-item
                                                        {{
                                                            request()->routeIs(
                                                                'admin.quote-requests.*'
                                                            )
                                                                ? 'active'
                                                                : ''
                                                        }}
                                                    "
                                                    href="{{
                                                        route(
                                                            'admin.quote-requests.index'
                                                        )
                                                    }}"
                                                >

                                                    <span
                                                        class="
                                                            menu-icon
                                                            menu-quotes
                                                        "
                                                    >
                                                        <i
                                                            class="
                                                                fas
                                                                fa-file-invoice-dollar
                                                            "
                                                        ></i>
                                                    </span>


                                                    <span
                                                        class="
                                                            flex-grow-1
                                                        "
                                                    >
                                                        Quote Requests
                                                    </span>


                                                    @if(
                                                        $pendingQuoteRequests
                                                        > 0
                                                    )

                                                        <span
                                                            class="
                                                                badge
                                                                bg-danger
                                                                rounded-pill
                                                                ms-auto
                                                            "
                                                        >
                                                            {{
                                                                $pendingQuoteRequests
                                                            }}
                                                        </span>

                                                    @endif

                                                </a>

                                            </li>

                                        @endif


                                        @can('view_ai_assistant')

                                            @if(
                                                Route::has(
                                                    'ai-assistant.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'ai-assistant.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'ai-assistant.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-ai-assistant
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-robot
                                                                "
                                                            ></i>
                                                        </span>

                                                        AI Assistant

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan


                                        @if(
                                            Route::has(
                                                'user-guide.index'
                                            )
                                        )

                                            <li>

                                                <a
                                                    class="
                                                        dropdown-item
                                                        {{
                                                            request()->routeIs(
                                                                'user-guide.*'
                                                            )
                                                                ? 'active'
                                                                : ''
                                                        }}
                                                    "
                                                    href="{{
                                                        route(
                                                            'user-guide.index'
                                                        )
                                                    }}"
                                                >

                                                    <span
                                                        class="
                                                            menu-icon
                                                            menu-guide
                                                        "
                                                    >
                                                        <i
                                                            class="
                                                                fas
                                                                fa-book
                                                            "
                                                        ></i>
                                                    </span>

                                                    User Guide

                                                </a>

                                            </li>

                                        @endif


                                        @can('view_equipment')

                                            @if(
                                                Route::has(
                                                    'equipment.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'equipment.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'equipment.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-equipment
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-tools
                                                                "
                                                            ></i>
                                                        </span>

                                                        Equipment

                                                    </a>

                                                </li>

                                            @endif

                                        @endcan


                                        @role('super_admin')

                                            <li>
                                                <hr
                                                    class="
                                                        dropdown-divider
                                                    "
                                                >
                                            </li>


                                            @if(
                                                Route::has(
                                                    'users.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'users.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'users.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-users
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-users-cog
                                                                "
                                                            ></i>
                                                        </span>

                                                        Users

                                                    </a>

                                                </li>

                                            @endif


                                            @if(
                                                Route::has(
                                                    'audit-logs.index'
                                                )
                                            )

                                                <li>

                                                    <a
                                                        class="
                                                            dropdown-item
                                                            {{
                                                                request()->routeIs(
                                                                    'audit-logs.*'
                                                                )
                                                                    ? 'active'
                                                                    : ''
                                                            }}
                                                        "
                                                        href="{{
                                                            route(
                                                                'audit-logs.index'
                                                            )
                                                        }}"
                                                    >

                                                        <span
                                                            class="
                                                                menu-icon
                                                                menu-audit
                                                            "
                                                        >
                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-history
                                                                "
                                                            ></i>
                                                        </span>

                                                        Audit Logs

                                                    </a>

                                                </li>

                                            @endif

                                        @endrole

                                    </ul>

                                </li>

                            </ul>

                        @endif

                    @endauth

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- ROW 3: NOTIFICATIONS / USER ACCOUNT                      --}}
    {{-- ========================================================= --}}

    <div class="kplc-utility-row">
        <div class="container-fluid kplc-utility-container">

            <div class="kplc-utility-spacer">
                <span class="kplc-utility-label">
                    <i class="fas fa-shield-alt me-2"></i>
                    Secure Laboratory Workspace
                </span>
            </div>

            <div class="kplc-utility-actions">
                {{-- ============================================= --}}
                {{-- NOTIFICATIONS                                 --}}
                {{-- ============================================= --}}

                @auth

                <div class="dropdown">

                <a
                class="
                kplc-header-button
                position-relative
                text-decoration-none
                "
                href="#"
                id="notificationDropdown"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="Notifications"
                >

                <i class="fas fa-bell"></i>


                @if($navbarNotificationCount > 0)

                <span class="notification-count-badge">

                {{
                $navbarNotificationCount > 99
                ? '99+'
                : $navbarNotificationCount
                }}

                </span>

                @endif

                </a>


                <div
                class="
                dropdown-menu
                dropdown-menu-end
                notification-dropdown
                "
                aria-labelledby="notificationDropdown"
                >

                <div class="notification-header">

                <div>

                <h6 class="mb-0">
                Notifications
                </h6>

                <small class="text-muted">
                {{ $navbarNotificationCount }}
                unread
                </small>

                </div>


                @if(
                $navbarNotificationCount > 0
                && Route::has(
                'notifications.mark-all-read'
                )
                )

                <form
                action="{{
                route(
                'notifications.mark-all-read'
                )
                }}"
                method="POST"
                class="m-0"
                >

                @csrf
                @method('PATCH')


                <button
                type="submit"
                class="
                btn
                btn-sm
                btn-link
                p-0
                text-decoration-none
                "
                >
                Mark all read
                </button>

                </form>

                @endif

                </div>


                <div class="notification-list">

                @forelse(
                $navbarNotifications
                as $notification
                )

                @php
                $notificationData =
                $notification->data
                ?? [];

                $notificationTitle =
                $notificationData['title']
                ?? 'System Notification';

                $notificationMessage =
                $notificationData['message']
                ?? 'A new notification is available.';

                $notificationColor =
                $notificationData['color']
                ?? 'secondary';

                $notificationIcon =
                $notificationData['icon']
                ?? 'fa-bell';

                $notificationTarget =
                $notificationData['url']
                ?? (
                Route::has(
                'notifications.index'
                )
                ? route(
                'notifications.index'
                )
                : '#'
                );

                $notificationHref =
                Route::has(
                'notifications.read'
                )
                ? route(
                'notifications.read',
                $notification
                )
                : $notificationTarget;
                @endphp


                <a
                href="{{ $notificationHref }}"
                class="
                dropdown-item
                notification-item
                "
                >

                <span
                class="
                notification-item-icon
                bg-{{ $notificationColor }}
                bg-opacity-10
                text-{{ $notificationColor }}
                "
                >

                <i
                class="
                fas
                {{ $notificationIcon }}
                "
                ></i>

                </span>


                <span
                class="
                notification-item-content
                "
                >

                <strong>
                {{ $notificationTitle }}
                </strong>


                <small
                class="
                d-block
                text-muted
                "
                >
                {{ $notificationMessage }}
                </small>


                <small
                class="
                d-block
                text-muted
                mt-1
                "
                >

                <i
                class="
                far
                fa-clock
                me-1
                "
                ></i>

                {{
                $notification
                ->created_at
                ?->diffForHumans()
                ?? 'Recently'
                }}

                </small>

                </span>

                </a>

                @empty

                <div class="notification-empty">

                <i
                class="
                fas
                fa-check-circle
                text-success
                fa-2x
                mb-2
                "
                ></i>

                <p class="mb-0">
                No unread notifications.
                </p>

                </div>

                @endforelse

                </div>


                @if(Route::has('notifications.index'))

                <div class="notification-footer">

                <a
                href="{{
                route(
                'notifications.index'
                )
                }}"
                >
                View all notifications
                </a>

                </div>

                @endif

                </div>

                </div>

                @endauth


                {{-- ============================================= --}}
                {{-- USER / PROFILE                                --}}
                {{-- ============================================= --}}

                @guest

                @if(Route::has('login'))

                <a
                class="kplc-header-link"
                href="{{ route('login') }}"
                >
                <i
                class="
                fas
                fa-sign-in-alt
                me-1
                "
                ></i>

                Login
                </a>

                @endif


                @if(Route::has('register'))

                <a
                class="kplc-header-link"
                href="{{ route('register') }}"
                >
                <i
                class="
                fas
                fa-user-plus
                me-1
                "
                ></i>

                Register
                </a>

                @endif

                @else

                <div
                class="
                dropdown
                kplc-profile-wrapper
                "
                >

                <a
                class="
                kplc-profile-toggle
                dropdown-toggle
                "
                href="#"
                id="profileDropdown"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                >

                <span class="kplc-profile-icon">

                <i
                class="
                fas
                fa-user-circle
                "
                ></i>

                </span>


                <span class="kplc-profile-name">
                {{ $currentUser?->name }}
                </span>


                @if($currentUser?->staff_number)

                <span class="kplc-staff-badge">

                {{
                $currentUser
                ?->staff_number
                }}

                </span>

                @endif

                </a>


                <div
                class="
                dropdown-menu
                dropdown-menu-end
                kplc-profile-dropdown
                "
                aria-labelledby="profileDropdown"
                >

                @if($isCustomer)

                <a
                class="dropdown-item"
                href="{{
                route(
                'customer.dashboard'
                )
                }}"
                >

                <span
                class="
                menu-icon
                menu-dashboard
                "
                >
                <i
                class="
                fas
                fa-tachometer-alt
                "
                ></i>
                </span>

                Dashboard

                </a>


                <a
                class="dropdown-item"
                href="{{
                route(
                'customer.calibrations'
                )
                }}"
                >

                <span
                class="
                menu-icon
                menu-lab
                "
                >
                <i
                class="
                fas
                fa-microscope
                "
                ></i>
                </span>

                My Calibrations

                </a>

                @else

                @if(Route::has('dashboard'))

                <a
                class="dropdown-item"
                href="{{ route('dashboard') }}"
                >

                <span
                class="
                menu-icon
                menu-dashboard
                "
                >
                <i
                class="
                fas
                fa-tachometer-alt
                "
                ></i>
                </span>

                Dashboard

                </a>

                @endif


                @if(
                Route::has(
                'calibrations.index'
                )
                )

                <a
                class="dropdown-item"
                href="{{
                route(
                'calibrations.index'
                )
                }}"
                >

                <span
                class="
                menu-icon
                menu-lab
                "
                >
                <i
                class="
                fas
                fa-microscope
                "
                ></i>
                </span>

                Calibrations

                </a>

                @endif

                @endif


                <div
                class="
                dropdown-item-text
                role-display-row
                "
                >

                <span
                class="
                menu-icon
                menu-profile
                "
                >
                <i
                class="
                fas
                fa-user-tag
                "
                ></i>
                </span>


                <span
                class="badge bg-{{
                $currentUser
                ?->hasRole(
                'super_admin'
                )
                ? 'danger'
                : (
                $currentUser
                ?->hasRole(
                'lab_manager'
                )
                ? 'primary'
                : 'secondary'
                )
                }}"
                >

                {{
                $currentUser
                ?->role_display
                ?? 'User'
                }}

                </span>

                </div>


                <hr class="dropdown-divider">


                @if(
                !$isCustomer
                && Route::has(
                'profile.edit'
                )
                )

                <a
                class="dropdown-item {{
                request()->routeIs(
                'profile.*'
                )
                ? 'active'
                : ''
                }}"
                href="{{
                route(
                'profile.edit'
                )
                }}"
                >

                <span
                class="
                menu-icon
                menu-profile
                "
                >

                <i
                class="
                fas
                fa-user-cog
                "
                ></i>

                </span>

                My Profile

                </a>


                <div
                class="
                dropdown-item-text
                signature-status
                "
                >

                @if(
                $currentUser
                ?->signature_path
                )

                <i
                class="
                fas
                fa-check-circle
                text-success
                me-1
                "
                ></i>

                Signature configured


                @if(
                $currentUser
                ?->use_auto_signature
                )

                <span
                class="
                badge
                bg-success
                ms-1
                "
                >
                Auto
                </span>

                @endif

                @else

                <i
                class="
                fas
                fa-exclamation-circle
                text-warning
                me-1
                "
                ></i>

                Signature not configured

                @endif

                </div>


                <hr class="dropdown-divider">

                @endif


                <a
                class="
                dropdown-item
                text-danger
                "
                href="{{ route('logout') }}"
                onclick="
                event.preventDefault();
                document
                .getElementById(
                'logout-form'
                )
                .submit();
                "
                >

                <span
                class="
                menu-icon
                menu-logout
                "
                >
                <i
                class="
                fas
                fa-sign-out-alt
                "
                ></i>
                </span>

                Logout

                </a>


                <form
                id="logout-form"
                action="{{ route('logout') }}"
                method="POST"
                class="d-none"
                >
                @csrf
                </form>

                </div>

                </div>

                @endguest
            </div>

        </div>
    </div>


</nav>

{{-- ============================================================= --}}
{{-- STYLES                                                        --}}
{{-- ============================================================= --}}

<style>

/* ================================================================
   ROOT
================================================================ */

.kplc-top-navigation {
    position: relative;
    z-index: 1040;

    display: block;

    width: 100%;

    overflow: visible !important;

    background: #ffffff;

    box-shadow:
        0 3px 12px
        rgba(0, 0, 0, 0.14);
}


/* ================================================================
   ROW 1
================================================================ */

.kplc-brand-row {
    position: relative;

    z-index: 1050;

    width: 100%;

    background: #ffffff;

    border-bottom:
        1px solid
        #e3e8ef;
}


.kplc-brand-container {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 1rem;

    min-height: 78px;

    padding:
        0.55rem
        1.25rem;
}


.kplc-brand-left {
    display: flex;

    align-items: center;

    flex:
        1
        1
        auto;

    min-width: 0;

    gap: 0.75rem;
}


.kplc-brand-actions {
    display: flex;

    align-items: center;

    justify-content: flex-end;

    flex:
        0
        0
        auto;

    gap: 0.55rem;

    overflow: visible !important;
}


/* ================================================================
   BRAND
================================================================ */

.kplc-brand {
    display: flex;

    align-items: center;

    min-width: 0;

    color: #202d84;

    text-decoration: none;
}


.kplc-brand:hover {
    color: #202d84;
}


.kplc-brand-logo {
    width: 54px;

    height: 54px;

    flex:
        0
        0
        54px;

    margin-right: 0.75rem;

    object-fit: contain;
}


.kplc-brand-logo-fallback {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 50px;

    height: 50px;

    flex:
        0
        0
        50px;

    margin-right: 0.75rem;

    border-radius: 12px;

    background: #eef3ff;

    color: #202d84;

    font-size: 1.4rem;
}


.kplc-brand-text {
    display: flex;

    flex-direction: column;

    min-width: 0;

    line-height: 1.12;
}


.kplc-brand-title {
    display: block;

    color: #202d84;

    font-size: 1.3rem;

    font-weight: 800;

    white-space: nowrap;
}


.kplc-brand-subtitle {
    display: block;

    margin-top: 4px;

    color: #6c757d;

    font-size: 0.78rem;

    font-weight: 500;
}


/* ================================================================
   HEADER BUTTONS
================================================================ */

.kplc-header-button,
.kplc-mobile-menu-button {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 44px;

    height: 44px;

    flex:
        0
        0
        44px;

    padding: 0;

    border:
        1px solid
        #dde4ed;

    border-radius: 11px;

    background: #f8fafc;

    color: #202d84;

    text-decoration: none;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}


.kplc-header-button:hover,
.kplc-header-button:focus,
.kplc-mobile-menu-button:hover,
.kplc-mobile-menu-button:focus {
    background: #eef3ff;

    color: #153e97;

    transform:
        translateY(-1px);
}


.kplc-mobile-menu-button {
    display: none;
}


.kplc-header-link {
    display: inline-flex;

    align-items: center;

    min-height: 42px;

    padding:
        0.45rem
        0.75rem;

    border-radius: 9px;

    color: #202d84;

    font-weight: 600;

    text-decoration: none;
}


/* ================================================================
   PROFILE
================================================================ */

.kplc-profile-wrapper {
    position: relative;

    overflow: visible !important;
}


.kplc-profile-toggle {
    display: inline-flex;

    align-items: center;

    gap: 0.5rem;

    min-height: 44px;

    padding:
        0.35rem
        0.75rem;

    border:
        1px solid
        #dde4ed;

    border-radius: 11px;

    background: #f8fafc;

    color: #26354d;

    font-weight: 600;

    text-decoration: none;
}


.kplc-profile-toggle:hover {
    background: #eef3ff;

    color: #153e97;
}


.kplc-profile-icon {
    color: #202d84;

    font-size: 1.35rem;
}


.kplc-profile-name {
    display: inline-block;

    max-width: 190px;

    overflow: hidden;

    white-space: nowrap;

    text-overflow: ellipsis;
}


.kplc-staff-badge {
    display: inline-flex;

    padding:
        0.28rem
        0.5rem;

    border-radius: 999px;

    background: #e8eefc;

    color: #202d84;

    font-size: 0.7rem;

    font-weight: 700;
}


.kplc-profile-dropdown {
    min-width: 275px;
}


/* ================================================================
   ROW 2
================================================================ */

.kplc-menu-row {
    position: relative;

    z-index: 1040;

    width: 100%;

    overflow: visible !important;

    background:
        linear-gradient(
            90deg,
            #202d84 0%,
            #154ca9 100%
        );
}


.kplc-navbar-main {
    width: 100%;

    overflow: visible !important;
}


.kplc-menu-container {
    display: flex;

    align-items: stretch;

    width: 100%;

    min-width: 0;

    min-height: 58px;

    padding:
        0
        1rem;

    overflow: visible !important;
}


/* ================================================================
   PRIMARY MENU
================================================================ */

.kplc-menu-scroll {
    flex:
        1
        1
        auto;

    min-width: 0;

    overflow-x: auto;

    overflow-y: hidden;

    scrollbar-width: none;
}


.kplc-menu-scroll::-webkit-scrollbar {
    display: none;
}


.kplc-primary-menu {
    display: flex;

    flex-direction: row !important;

    flex-wrap: nowrap;

    align-items: stretch;

    width: max-content;

    min-width: max-content;

    margin: 0;
}


.kplc-primary-menu > .nav-item {
    display: flex;

    align-items: stretch;
}


/* ================================================================
   MENU ACTIONS
================================================================ */

.kplc-menu-actions {
    display: flex;

    align-items: stretch;

    flex:
        0
        0
        auto;

    margin-left: auto;

    overflow: visible !important;
}


.quality-menu-wrapper,
.more-menu-wrapper {
    display: flex;

    flex-direction: row;

    flex-wrap: nowrap;

    align-items: stretch;

    margin: 0;

    padding: 0;

    overflow: visible !important;
}


/* ================================================================
   MENU LINKS
================================================================ */

.kplc-menu-row .nav-link {
    display: inline-flex;

    align-items: center;

    gap: 0.45rem;

    min-height: 58px;

    padding:
        0.65rem
        0.85rem;

    border-bottom:
        3px solid
        transparent;

    color:
        rgba(
            255,
            255,
            255,
            0.93
        );

    font-size: 0.9rem;

    font-weight: 600;

    white-space: nowrap;

    text-decoration: none;

    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease;
}


.kplc-menu-row .nav-link:hover,
.kplc-menu-row .nav-link:focus {
    background:
        rgba(
            255,
            255,
            255,
            0.10
        );

    color: #ffffff;
}


.kplc-menu-row .nav-link.active {
    background:
        rgba(
            255,
            255,
            255,
            0.12
        );

    border-bottom-color:
        #ffd34d;

    color: #ffffff;
}


/* ================================================================
   ICONS
================================================================ */

.menu-icon {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 24px;

    min-width: 24px;

    flex:
        0
        0
        24px;
}


.menu-dashboard {
    color: #39e69c;
}

.menu-lab {
    color: #3bd7e7;
}

.menu-customers {
    color: #ffc14d;
}

.menu-quality-control {
    color: #f7a8ff;
}

.menu-quality {
    color: #f2a0ef;
}

.menu-chat {
    color: #82dcff;
}

.menu-quotes {
    color: #75e4a2;
}

.menu-certificates {
    color: #ff8b8b;
}

.menu-jobcards {
    color: #b9afff;
}

.menu-delivery {
    color: #72dce8;
}

.menu-collection {
    color: #a8de70;
}

.menu-request {
    color: #ffc65a;
}

.menu-new {
    color: #8be0a5;
}

.menu-more {
    color: #ffd95c;
}

.menu-objectives {
    color: #a88aff;
}

.menu-achievements {
    color: #44c98b;
}

.menu-actions {
    color: #f0a632;
}

.menu-reports {
    color: #5999e8;
}

.menu-configuration {
    color: #805ad5;
}

.menu-ai-assistant {
    color: #269be8;
}

.menu-guide {
    color: #3c84c6;
}

.menu-equipment {
    color: #cf8f00;
}

.menu-users {
    color: #3c84c6;
}

.menu-audit {
    color: #69788d;
}

.menu-profile {
    color: #1b8fa0;
}

.menu-logout {
    color: #dc3545;
}


/* Customer More stays with the primary menu and preserves icon colours. */
.customer-more-menu {
    position: relative;
}

.customer-more-dropdown {
    min-width: 235px !important;
}

.dropdown-menu .dropdown-item .menu-icon,
.dropdown-menu .dropdown-item .menu-icon i {
    color: inherit;
}

.dropdown-menu .dropdown-item:not(.active) .menu-jobcards { color: #7667d8; }
.dropdown-menu .dropdown-item:not(.active) .menu-delivery { color: #1597a8; }
.dropdown-menu .dropdown-item:not(.active) .menu-collection { color: #5d9827; }
.dropdown-menu .dropdown-item:not(.active) .menu-request { color: #c88a00; }
.dropdown-menu .dropdown-item:not(.active) .menu-new { color: #258a4b; }

/* ================================================================
   DROPDOWNS
================================================================ */

.kplc-top-navigation .dropdown-menu {
    z-index: 1095 !important;

    min-width: 225px;

    padding:
        0.45rem
        0;

    border: 0;

    border-radius: 12px;

    background: #ffffff;

    box-shadow:
        0 14px 40px
        rgba(0, 0, 0, 0.20);
}


.dropdown-menu .dropdown-item {
    display: flex;

    align-items: center;

    gap: 0.65rem;

    min-height: 42px;

    padding:
        0.65rem
        1rem;

    color: #212529;

    font-weight: 500;

    white-space: nowrap;
}


.dropdown-menu .dropdown-item:hover,
.dropdown-menu .dropdown-item:focus {
    background: #eef5ff;

    color: #0d3f83;
}


.dropdown-menu .dropdown-item.active {
    background: #0d6efd;

    color: #ffffff;
}


.quality-dropdown-menu {
    min-width: 250px !important;
}


.more-dropdown-menu {
    min-width: 245px !important;
}


.role-display-row {
    display: flex;

    align-items: center;

    gap: 0.65rem;

    padding:
        0.65rem
        1rem;
}


.signature-status {
    padding:
        0.55rem
        1rem;

    color: #6c757d;

    font-size: 0.78rem;
}


/* ================================================================
   NOTIFICATIONS
================================================================ */

.notification-count-badge {
    position: absolute;

    top: -5px;

    right: -5px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 20px;

    height: 20px;

    padding:
        0
        5px;

    border:
        2px solid
        #ffffff;

    border-radius: 999px;

    background: #ff2d20;

    color: #ffffff;

    font-size: 0.67rem;

    font-weight: 700;
}


.notification-dropdown {
    width: 400px;

    max-width:
        calc(
            100vw - 24px
        );

    padding: 0 !important;

    overflow: hidden;
}


.notification-header {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 1rem;

    padding: 15px;

    border-bottom:
        1px solid
        #e9ecef;
}


.notification-list {
    max-height: 420px;

    overflow-y: auto;
}


.notification-item {
    display: flex !important;

    align-items: flex-start !important;

    gap: 12px !important;

    padding: 15px !important;

    white-space: normal !important;

    border-bottom:
        1px solid
        #f1f3f5;
}


.notification-item-icon {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 42px;

    height: 42px;

    flex:
        0
        0
        42px;

    border-radius: 50%;
}


.notification-item-content {
    flex: 1;

    min-width: 0;
}


.notification-item-content strong {
    display: block;

    margin-bottom: 4px;
}


.notification-footer {
    padding: 12px;

    border-top:
        1px solid
        #e9ecef;

    text-align: center;
}


.notification-footer a {
    color: #0d6efd;

    font-size: 0.875rem;

    font-weight: 600;

    text-decoration: none;
}


.notification-empty {
    padding:
        35px
        20px;

    color: #6c757d;

    text-align: center;
}


/* ================================================================
   NEW REQUEST
================================================================ */

.new-request-link {
    color: #c8f8d4 !important;
}


/* ================================================================
   COMPACT MODE
================================================================ */

body.top-menu-compact
.kplc-menu-scroll
.top-menu-label {
    display: none;
}


body.top-menu-compact
.kplc-menu-scroll
.nav-link {
    justify-content: center;

    width: 52px;
}


/* ================================================================
   DESKTOP MENU VISIBILITY GUARD
================================================================ */

@media (min-width: 768px) {
    .kplc-navbar-main {
        display: flex !important;
        flex-basis: auto !important;
        visibility: visible !important;
    }

    .kplc-mobile-menu-button {
        display: none !important;
    }
}


/* ================================================================
   TABLET / MOBILE
================================================================ */

@media (max-width: 767.98px) {

    .kplc-brand-container {
        min-height: 68px;

        gap: 0.45rem;

        padding:
            0.45rem
            0.65rem;
    }


    .kplc-brand-logo {
        width: 40px;

        height: 40px;

        flex-basis: 40px;

        margin-right: 0.4rem;
    }


    .kplc-brand-title {
        font-size: 0.88rem;

        white-space: normal;

        line-height: 1.12;
    }


    .kplc-brand-subtitle {
        display: none;
    }


    .kplc-profile-name,
    .kplc-staff-badge {
        display: none;
    }


    .kplc-profile-toggle {
        width: 42px;

        height: 42px;

        justify-content: center;

        padding: 0;
    }


    .kplc-profile-toggle::after {
        display: none;
    }


    .kplc-mobile-menu-button {
        display: inline-flex;
    }


    .kplc-navbar-main {
        display: none !important;
    }


    .kplc-navbar-main.show {
        display: block !important;
    }


    .kplc-menu-container {
        display: block;

        min-height: auto;

        padding:
            0.5rem
            0.75rem
            0.75rem;
    }


    .kplc-menu-scroll {
        width: 100%;

        max-height: 55vh;

        overflow-x: hidden;

        overflow-y: auto;
    }


    .kplc-primary-menu {
        display: block !important;

        width: 100%;

        min-width: 0;
    }


    .kplc-primary-menu > .nav-item {
        display: block;

        width: 100%;
    }


    .kplc-menu-row .nav-link {
        display: flex;

        width: 100%;

        min-height: 46px;

        padding:
            0.6rem
            0.75rem;

        border: 0;

        border-radius: 8px;
    }


    .kplc-menu-actions {
        display: block;

        width: 100%;

        margin: 0;

        padding-top: 0.5rem;

        border-top:
            1px solid
            rgba(
                255,
                255,
                255,
                0.15
            );
    }


    .quality-menu-wrapper,
    .more-menu-wrapper {
        display: block;

        width: 100%;
    }


    .quality-menu-wrapper
    .nav-item,
    .more-menu-wrapper
    .nav-item {
        display: block;

        width: 100%;
    }


    .quality-menu-wrapper
    .nav-link,
    .more-menu-wrapper
    .nav-link {
        width: 100%;

        justify-content: flex-start;
    }


    .kplc-menu-actions
    .dropdown-menu {
        position: static !important;

        width: 100%;

        max-width: none;

        transform: none !important;
    }
}


/* ================================================================
   SMALL MOBILE
================================================================ */

@media (max-width: 575.98px) {

    .kplc-brand-title {
        font-size: 0.78rem;
    }


    .kplc-brand-logo {
        width: 34px;

        height: 34px;

        flex-basis: 34px;
    }


    .kplc-header-button,
    .kplc-mobile-menu-button,
    .kplc-profile-toggle {
        width: 38px;

        height: 38px;

        flex-basis: 38px;
    }


    .notification-dropdown {
        position: fixed !important;

        top: 64px !important;

        right: 10px !important;

        left: 10px !important;

        width: auto;

        max-width: none;

        transform: none !important;
    }


    .notification-list {
        max-height:
            calc(
                100vh - 180px
            );
    }
}


/* ================================================================
   DO NOT CLIP DROPDOWNS
================================================================ */

.kplc-top-navigation,
.kplc-brand-row,
.kplc-brand-container,
.kplc-brand-actions,
.kplc-menu-row,
.kplc-navbar-main,
.kplc-menu-container,
.kplc-menu-actions,
.quality-menu-wrapper,
.more-menu-wrapper,
.kplc-profile-wrapper,
.kplc-top-navigation .dropdown {
    overflow: visible !important;
}



/* ================================================================
   THREE-ROW NAVIGATION OVERRIDES
   Row 1: Brand
   Row 2: Menus + Compact / Expand
   Row 3: Notifications + User account
================================================================ */

/* Row 1 is intentionally compact and clean. */
.kplc-brand-container {
    min-height: 68px;
    padding-top: 0.4rem;
    padding-bottom: 0.4rem;
}

.kplc-brand-logo {
    width: 46px;
    height: 46px;
    flex-basis: 46px;
}

.kplc-brand-title {
    font-size: 1.15rem;
}

.kplc-brand-subtitle {
    font-size: 0.74rem;
}

/* Compact / Expand belongs visually and functionally to row 2. */
.kplc-menu-actions {
    display: flex;
    align-items: stretch;
    gap: 0;
}

.kplc-menu-compact-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    align-self: center;
    min-height: 40px;
    margin: 7px 0.45rem 7px 0.25rem;
    padding: 0.45rem 0.8rem;
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    font-size: 0.82rem;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease;
}

.kplc-menu-compact-button:hover,
.kplc-menu-compact-button:focus {
    background: rgba(255, 255, 255, 0.17);
    border-color: rgba(255, 255, 255, 0.75);
    color: #ffffff;
    transform: translateY(-1px);
}

/* Row 3: clean utility/account strip. */
.kplc-utility-row {
    position: relative;
    z-index: 1045;
    width: 100%;
    background: #ffffff;
    border-bottom: 1px solid #e3e8ef;
    box-shadow: 0 3px 9px rgba(31, 45, 89, 0.06);
    overflow: visible !important;
}

.kplc-utility-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-height: 54px;
    padding: 0.35rem 1.25rem;
    overflow: visible !important;
}

.kplc-utility-spacer {
    display: flex;
    align-items: center;
    min-width: 0;
    color: #738095;
    font-size: 0.78rem;
    font-weight: 600;
}

.kplc-utility-label {
    display: inline-flex;
    align-items: center;
}

.kplc-utility-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.55rem;
    margin-left: auto;
    overflow: visible !important;
}

/* Existing notification button, now scoped to row 3. */
.kplc-utility-actions .kplc-header-button {
    width: 40px;
    height: 40px;
    flex-basis: 40px;
    background: #f8fafc;
    color: #202d84;
}

/* Existing user dropdown, now scoped to row 3. */
.kplc-utility-actions .kplc-profile-toggle {
    min-height: 40px;
    background: #f8fafc;
}

/* Utility dropdowns must float over dashboard content. */
.kplc-utility-row .dropdown,
.kplc-utility-row .kplc-profile-wrapper,
.kplc-utility-actions {
    overflow: visible !important;
}

.kplc-utility-row .dropdown-menu {
    z-index: 1105 !important;
}

/* Keep menu row balanced when compact button is present. */
@media (max-width: 1300px) {
    .kplc-menu-compact-button .compact-menu-text {
        display: none;
    }

    .kplc-menu-compact-button {
        width: 44px;
        padding-left: 0;
        padding-right: 0;
    }

    .kplc-menu-compact-button i {
        margin-right: 0 !important;
    }
}

@media (max-width: 767.98px) {
    .kplc-brand-container {
        min-height: 60px;
    }

    .kplc-utility-container {
        min-height: 50px;
        padding: 0.3rem 0.65rem;
    }

    .kplc-utility-spacer {
        display: none;
    }

    .kplc-utility-actions {
        width: 100%;
        justify-content: flex-end;
    }

    .kplc-menu-compact-button {
        display: none !important;
    }

    .kplc-utility-actions .kplc-profile-name,
    .kplc-utility-actions .kplc-staff-badge {
        display: inline-block;
    }
}

@media (max-width: 575.98px) {
    .kplc-utility-actions .kplc-profile-name,
    .kplc-utility-actions .kplc-staff-badge {
        display: none;
    }
}


/* ================================================================
   KPLC CORPORATE ROW 1
================================================================ */

.kplc-brand-row {
    background:
        linear-gradient(
            90deg,
            #0b2f6b 0%,
            #114da8 65%,
            #0b2f6b 100%
        ) !important;
    border-bottom: 4px solid #f4c400 !important;
}

.kplc-brand-container {
    min-height: 64px !important;
    padding: 0.35rem 1rem !important;
}

.kplc-brand {
    color: #ffffff !important;
}

.kplc-brand:hover {
    color: #ffffff !important;
}

.kplc-brand-logo {
    width: 44px !important;
    height: 44px !important;
    flex-basis: 44px !important;
    margin-right: 0.65rem !important;
    padding: 3px;
    border-radius: 8px;
    background: #ffffff;
}

.kplc-brand-title {
    color: #ffffff !important;
    font-size: 1.08rem !important;
    font-weight: 800 !important;
}

.kplc-brand-subtitle {
    color: rgba(255,255,255,0.82) !important;
    font-size: 0.72rem !important;
}

.kplc-brand-row .kplc-header-button,
.kplc-brand-row .kplc-mobile-menu-button {
    border-color: rgba(255,255,255,0.28) !important;
    background: rgba(255,255,255,0.12) !important;
    color: #ffffff !important;
}

.kplc-brand-row .kplc-header-button:hover,
.kplc-brand-row .kplc-mobile-menu-button:hover {
    background: rgba(255,255,255,0.22) !important;
    color: #ffffff !important;
}


/* ================================================================
   COMPACT / EXPAND VISUAL STATE
================================================================ */

body.top-menu-compact .kplc-menu-scroll .top-menu-label {
    display: none !important;
}

body.top-menu-compact .kplc-menu-scroll .nav-link {
    justify-content: center !important;
    width: 52px !important;
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
}

body.top-menu-compact .quality-menu-wrapper .top-menu-label,
body.top-menu-compact .more-menu-wrapper .top-menu-label {
    display: none !important;
}

body.top-menu-compact .quality-menu-wrapper .nav-link,
body.top-menu-compact .more-menu-wrapper .nav-link {
    justify-content: center !important;
    width: 52px !important;
}

.kplc-menu-compact-button.is-compact {
    background: #f4c400 !important;
    border-color: #f4c400 !important;
    color: #0b2f6b !important;
}


/* ================================================================
   ROW 3 POLISH
================================================================ */

.kplc-utility-row {
    background: #f7f9fc !important;
    border-bottom: 1px solid #dbe3ee !important;
}

.kplc-utility-container {
    min-height: 48px !important;
    padding: 0.3rem 1rem !important;
}

.kplc-utility-label {
    color: #53647a;
}

.kplc-utility-actions .kplc-header-button,
.kplc-utility-actions .kplc-profile-toggle {
    background: #ffffff !important;
}



/* ================================================================
   CUSTOMER MORE DROPDOWN - VISIBILITY FIX
   The customer dropdown lives inside .kplc-menu-scroll. The original
   overflow-x:auto / overflow-y:hidden clipped the absolute dropdown.
================================================================ */

@media (min-width: 768px) {
    .kplc-menu-scroll {
        overflow: visible !important;
    }

    .kplc-primary-menu,
    .kplc-primary-menu > .nav-item,
    .customer-more-menu {
        overflow: visible !important;
    }

    .customer-more-menu {
        position: relative !important;
    }

    .customer-more-dropdown {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: auto !important;
        margin-top: 0 !important;
        min-width: 245px !important;
        transform: none !important;
        z-index: 1200 !important;
    }

    .customer-more-dropdown.show {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

/*
 * Mobile: keep the dropdown in the normal collapsed navigation flow
 * so all customer links remain visible and scrollable.
 */
@media (max-width: 767.98px) {
    .customer-more-menu {
        width: 100%;
    }

    .customer-more-dropdown {
        position: static !important;
        width: 100% !important;
        min-width: 0 !important;
        margin: 0.25rem 0 0.35rem !important;
        transform: none !important;
        float: none !important;
    }

    .customer-more-dropdown.show {
        display: block !important;
    }
}

</style>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('topMenuToggle');

    if (!toggle) {
        return;
    }

    const storageKey = 'meterlabTopMenuCompact';

    function applyCompactState(isCompact) {
        document.body.classList.toggle('top-menu-compact', isCompact);
        toggle.classList.toggle('is-compact', isCompact);
        toggle.setAttribute('aria-pressed', isCompact ? 'true' : 'false');

        const text = toggle.querySelector('.compact-menu-text');
        const icon = toggle.querySelector('i');

        if (text) {
            text.textContent = isCompact ? 'Expand Menu' : 'Compact Menu';
        }

        if (icon) {
            icon.classList.remove('fa-table-columns', 'fa-expand');
            icon.classList.add(isCompact ? 'fa-expand' : 'fa-table-columns');
        }

        toggle.title = isCompact ? 'Expand Menu' : 'Compact Menu';
        toggle.setAttribute(
            'aria-label',
            isCompact ? 'Expand menu' : 'Compact menu'
        );
    }

    let initialState = false;

    try {
        initialState = localStorage.getItem(storageKey) === '1';
    } catch (error) {
        initialState = false;
    }

    applyCompactState(initialState);

    toggle.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const nextState =
            !document.body.classList.contains('top-menu-compact');

        applyCompactState(nextState);

        try {
            localStorage.setItem(storageKey, nextState ? '1' : '0');
        } catch (error) {
            // Continue without persistence when storage is unavailable.
        }
    });
});
</script>
