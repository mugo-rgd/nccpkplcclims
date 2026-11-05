@auth
@php
    $logoPath = public_path('img/logo.png');
    $logoExists = file_exists($logoPath);

    // Spatie is the single source of truth for role checks.
    $isCustomer = auth()->user()->hasRole('customer');

    // Pass this value from a controller, view composer, or service provider.
    $batchCount = $batchCount ?? 0;
@endphp


<div id="appSidebar" class="col-md-2 sidebar d-none d-md-block">
    <div class="sidebar-logo">
        @if($logoExists)
            <img
                src="{{ asset('img/logo.png') }}"
                alt="{{ config('app.name', 'Meter Central Laboratory') }}"
            >
        @else
            <i class="fas fa-microscope fa-2x"></i>
        @endif

        <span class="sidebar-brand">METER CENTRAL LAB</span>
    </div>

    @if($isCustomer)
        {{-- ============================================ --}}
        {{-- CUSTOMER SIDEBAR MENU                       --}}
        {{-- ============================================ --}}
        <div class="nav-section-title">Main Menu</div>

        <a
            class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
            href="{{ route('customer.dashboard') }}"
            title="Dashboard"
        >
            <span class="menu-icon menu-dashboard">
                <i class="fas fa-tachometer-alt"></i>
            </span>
            Dashboard
        </a>

        <a
            class="nav-link {{ request()->routeIs('customer.calibrations', 'customer.calibrations.*') ? 'active' : '' }}"
            href="{{ route('customer.calibrations') }}"
            title="My Calibrations"
        >
            <span class="menu-icon menu-lab">
                <i class="fas fa-microscope"></i>
            </span>
            My Calibrations
        </a>

        <a
            class="nav-link {{ request()->routeIs('customer.quotes', 'customer.quotes.*') ? 'active' : '' }}"
            href="{{ route('customer.quotes') }}"
            title="My Quotes"
        >
            <span class="menu-icon menu-quotes">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
            My Quotes
        </a>

        <a
            class="nav-link {{ request()->routeIs('customer.certificates', 'customer.certificates.*') ? 'active' : '' }}"
            href="{{ route('customer.certificates') }}"
            title="My Certificates"
        >
            <span class="menu-icon menu-certificates">
                <i class="fas fa-certificate"></i>
            </span>
            My Certificates
        </a>

        <a
            class="nav-link {{ request()->routeIs('customer.job-cards', 'customer.job-cards.*') ? 'active' : '' }}"
            href="{{ route('customer.job-cards') }}"
            title="My Job Cards"
        >
            <span class="menu-icon menu-jobcards">
                <i class="fas fa-clipboard-list"></i>
            </span>
            My Job Cards
        </a>

        <a
            class="nav-link {{ request()->routeIs('customer.delivery-notes', 'customer.delivery-notes.*') ? 'active' : '' }}"
            href="{{ route('customer.delivery-notes') }}"
            title="Delivery Notes"
        >
            <span class="menu-icon menu-delivery">
                <i class="fas fa-truck"></i>
            </span>
            Delivery Notes
        </a>

        <a
            class="nav-link {{ request()->routeIs('customer.collection-notes', 'customer.collection-notes.*') ? 'active' : '' }}"
            href="{{ route('customer.collection-notes') }}"
            title="Collection Notes"
        >
            <span class="menu-icon menu-collection">
                <i class="fas fa-hand-holding"></i>
            </span>
            Collection Notes
        </a>

        <a
            class="nav-link {{ request()->routeIs('customer.request-forms', 'customer.request-forms.*') ? 'active' : '' }}"
            href="{{ route('customer.request-forms') }}"
            title="Request Forms"
        >
            <span class="menu-icon menu-request">
                <i class="fas fa-file-signature"></i>
            </span>
            Request Forms
        </a>

        @if(Route::has('calibrations.create'))
            <a
                class="nav-link new-request-link {{ request()->routeIs('calibrations.create') ? 'active' : '' }}"
                href="{{ route('calibrations.create') }}"
                title="New Calibration Request"
            >
                <span class="menu-icon menu-new">
                    <i class="fas fa-plus-circle"></i>
                </span>
                <span>New Request</span>
            </a>
        @endif

        <div class="nav-section-title mt-3">Support</div>

        @if(Route::has('user-guide.index'))
            <a
                class="nav-link {{ request()->routeIs('user-guide.*') ? 'active' : '' }}"
                href="{{ route('user-guide.index') }}"
                title="User Guide"
            >
                <span class="menu-icon menu-guide">
                    <i class="fas fa-book"></i>
                </span>
                User Guide
            </a>
        @endif

    @else
        {{-- ============================================ --}}
        {{-- ADMIN SIDEBAR MENU                          --}}
        {{-- ============================================ --}}
        <div class="nav-section-title">Main Menu</div>

        @can('view_dashboard')
            @if(Route::has('dashboard'))
                <a
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}"
                    title="Dashboard"
                >
                    <span class="menu-icon menu-dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                    </span>
                    Dashboard
                </a>
            @endif
        @endcan

        @can('view_calibrations')
            @if(Route::has('calibrations.index'))
                <a
                    class="nav-link {{ request()->routeIs('calibrations.*') && !request()->routeIs('calibrations.batches*') ? 'active' : '' }}"
                    href="{{ route('calibrations.index') }}"
                    title="Calibrations"
                >
                    <span class="menu-icon menu-lab">
                        <i class="fas fa-microscope"></i>
                    </span>
                    Calibrations
                </a>
            @endif
        @endcan

        @can('view_calibrations')
            @if(Route::has('calibrations.batches'))
                <a
                    class="nav-link {{ request()->routeIs('calibrations.batches*') ? 'active' : '' }}"
                    href="{{ route('calibrations.batches') }}"
                    title="Batches"
                >
                    <span class="menu-icon menu-batches">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    Batches

                    @if($batchCount > 0)
                        <span class="badge bg-info">{{ $batchCount }}</span>
                    @endif
                </a>
            @endif
        @endcan

        @can('view_customers')
            @if(Route::has('admin.customers.index'))
                <a
                    class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}"
                    href="{{ route('admin.customers.index') }}"
                    title="Customers"
                >
                    <span class="menu-icon menu-customers">
                        <i class="fas fa-building"></i>
                    </span>
                    Customers
                </a>
            @endif
        @endcan

        @if(Route::has('admin.quote-requests.index'))
            @php
                $pendingQuoteRequests = \App\Models\PublicQuoteRequest::query()
                    ->where('status', 'pending')
                    ->count();
            @endphp
            <a href="{{ route('admin.quote-requests.index') }}"
               class="nav-link {{ request()->routeIs('admin.quote-requests.*') ? 'active' : '' }}"
               title="Quote Requests">
                <span class="menu-icon menu-quotes">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                <span>Quote Requests</span>
                @if($pendingQuoteRequests > 0)
                    <span class="badge bg-danger ms-auto">{{ $pendingQuoteRequests }}</span>
                @endif
            </a>
        @endif

        @can('view_certificates')
            @if(Route::has('certificates.index'))
                <a
                    class="nav-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}"
                    href="{{ route('certificates.index') }}"
                    title="Certificates"
                >
                    <span class="menu-icon menu-certificates">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    Certificates
                </a>
            @endif
        @endcan

        @can('quality_check')
            @if(Route::has('admin.quality.dashboard'))
                <a
                    class="nav-link {{ request()->routeIs('admin.quality.*') ? 'active' : '' }}"
                    href="{{ route('admin.quality.dashboard') }}"
                    title="Quality Control"
                >
                    <span class="menu-icon menu-quality">
                        <i class="fas fa-clipboard-check"></i>
                    </span>
                    Quality Control
                </a>
            @endif
        @endcan

        @if(auth()->user()->hasAnyPermission([
            'view_quality_dashboard',
            'view_quality_objectives',
            'view_achievements',
            'view_corrective_actions',
            'view_quality_reports'
        ]))
            <div class="nav-section-title mt-3">Quality Management</div>

            @can('view_quality_dashboard')
                @if(Route::has('quality.dashboard'))
                    <a
                        class="nav-link {{ request()->routeIs('quality.dashboard') ? 'active' : '' }}"
                        href="{{ route('quality.dashboard') }}"
                        title="Quality Dashboard"
                    >
                        <span class="menu-icon menu-dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                        </span>
                        Dashboard
                    </a>
                @endif
            @endcan

            @can('view_quality_objectives')
                @if(Route::has('quality.objectives.index'))
                    <a
                        class="nav-link {{ request()->routeIs('quality.objectives.*') ? 'active' : '' }}"
                        href="{{ route('quality.objectives.index') }}"
                        title="Objectives"
                    >
                        <span class="menu-icon menu-objectives">
                            <i class="fas fa-bullseye"></i>
                        </span>
                        Objectives
                    </a>
                @endif
            @endcan

            @can('view_achievements')
                @if(Route::has('quality.achievements.index'))
                    <a
                        class="nav-link {{ request()->routeIs('quality.achievements.*') ? 'active' : '' }}"
                        href="{{ route('quality.achievements.index') }}"
                        title="Achievements"
                    >
                        <span class="menu-icon menu-achievements">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        Achievements
                    </a>
                @endif
            @endcan

            @can('view_corrective_actions')
                @if(Route::has('quality.corrective-actions.index'))
                    <a
                        class="nav-link {{ request()->routeIs('quality.corrective-actions.*') ? 'active' : '' }}"
                        href="{{ route('quality.corrective-actions.index') }}"
                        title="Corrective Actions"
                    >
                        <span class="menu-icon menu-actions">
                            <i class="fas fa-tasks"></i>
                        </span>
                        Corrective Actions
                    </a>
                @endif
            @endcan

            @can('view_quality_reports')
                @if(Route::has('quality.reports.index'))
                    <a
                        class="nav-link {{ request()->routeIs('quality.reports.*') ? 'active' : '' }}"
                        href="{{ route('quality.reports.index') }}"
                        title="Reports"
                    >
                        <span class="menu-icon menu-reports">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        Reports
                    </a>
                @endif
            @endcan
        @endif

        <div class="nav-section-title mt-3">Communication</div>

        @if(Route::has('chat.index'))
            <a
                class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"
                href="{{ route('chat.index') }}"
                title="Chat"
            >
                <span class="menu-icon menu-chat">
                    <i class="fas fa-comment-dots"></i>
                </span>
                Chat

                <span
                    id="sidebarUnreadBadge"
                    class="badge bg-info"
                    style="display: none;"
                >0</span>
            </a>
        @endif

        @if(Route::has('user-guide.index'))
            <a
                class="nav-link {{ request()->routeIs('user-guide.*') ? 'active' : '' }}"
                href="{{ route('user-guide.index') }}"
                title="User Guide"
            >
                <span class="menu-icon menu-guide">
                    <i class="fas fa-book"></i>
                </span>
                User Guide
            </a>
        @endif

        @can('view_system_documents')
            @if(Route::has('system-documents.index'))
                <a href="{{ route('system-documents.index') }}"
                   class="nav-link {{ request()->routeIs('system-documents.index') ? 'active' : '' }}"
                   title="System Documentation">
                    <span class="menu-icon menu-guide">
                        <i class="fas fa-book-open"></i>
                    </span>
                    <span>System Documentation</span>
                </a>
            @endif
        @endcan

        @can('manage_system_documents')
            @if(Route::has('system-documents.dashboard'))
                <a href="{{ route('system-documents.dashboard') }}"
                   class="nav-link {{ request()->routeIs('system-documents.dashboard') ? 'active' : '' }}"
                   title="Documentation Compliance">
                    <span class="menu-icon menu-reports">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <span>Documentation Compliance</span>
                </a>
            @endif

            @if(Route::has('system-documents.reports'))
                <a href="{{ route('system-documents.reports') }}"
                   class="nav-link {{ request()->routeIs('system-documents.reports*') ? 'active' : '' }}"
                   title="Documentation Reports">
                    <span class="menu-icon menu-reports">
                        <i class="fas fa-file-lines"></i>
                    </span>
                    <span>Documentation Reports</span>
                </a>
            @endif
        @endcan

        @can('view_equipment')
            @if(Route::has('equipment.index'))
                <a
                    class="nav-link {{ request()->routeIs('equipment.*') ? 'active' : '' }}"
                    href="{{ route('equipment.index') }}"
                    title="Equipment"
                >
                    <span class="menu-icon menu-equipment">
                        <i class="fas fa-tools"></i>
                    </span>
                    Equipment
                </a>
            @endif
        @endcan

        @can('view_users')
            <div class="nav-section-title mt-3">Administration</div>

            @if(Route::has('users.index'))
                <a
                    class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                    href="{{ route('users.index') }}"
                    title="Users"
                >
                    <span class="menu-icon menu-users">
                        <i class="fas fa-users-cog"></i>
                    </span>
                    Users
                </a>
            @endif
        @endcan

        @can('view_audit_logs')
            @if(Route::has('audit-logs.index'))
                <a
                    class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}"
                    href="{{ route('audit-logs.index') }}"
                    title="Audit Logs"
                >
                    <span class="menu-icon menu-audit">
                        <i class="fas fa-history"></i>
                    </span>
                    Audit Logs
                </a>
            @endif
        @endcan
    @endif
</div>
@endauth
