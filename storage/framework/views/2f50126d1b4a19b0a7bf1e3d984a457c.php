<?php if(auth()->guard()->check()): ?>
<?php
    $logoPath = public_path('img/logo.png');
    $logoExists = file_exists($logoPath);

    // Spatie is the single source of truth for role checks.
    $isCustomer = auth()->user()->hasRole('customer');

    // Pass this value from a controller, view composer, or service provider.
    $batchCount = $batchCount ?? 0;
?>


<div id="appSidebar" class="sidebar d-none d-md-block">
    <div class="sidebar-logo">
        <?php if($logoExists): ?>
            <img
                src="<?php echo e(asset('img/logo.png')); ?>"
                alt="<?php echo e(config('app.name', 'Meter Central Laboratory')); ?>"
            >
        <?php else: ?>
            <i class="fas fa-microscope fa-2x"></i>
        <?php endif; ?>

        <span class="sidebar-brand">METER CENTRAL LAB</span>
    </div>

    <?php if($isCustomer): ?>
        
        
        
        <div class="nav-section-title">Main Menu</div>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.dashboard') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.dashboard')); ?>"
            title="Dashboard"
        >
            <span class="menu-icon menu-dashboard">
                <i class="fas fa-tachometer-alt"></i>
            </span>
            Dashboard
        </a>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.calibrations', 'customer.calibrations.*') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.calibrations')); ?>"
            title="My Calibrations"
        >
            <span class="menu-icon menu-lab">
                <i class="fas fa-microscope"></i>
            </span>
            My Calibrations
        </a>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.quotes', 'customer.quotes.*') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.quotes')); ?>"
            title="My Quotes"
        >
            <span class="menu-icon menu-quotes">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
            My Quotes
        </a>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.certificates', 'customer.certificates.*') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.certificates')); ?>"
            title="My Certificates"
        >
            <span class="menu-icon menu-certificates">
                <i class="fas fa-certificate"></i>
            </span>
            My Certificates
        </a>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.job-cards', 'customer.job-cards.*') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.job-cards')); ?>"
            title="My Job Cards"
        >
            <span class="menu-icon menu-jobcards">
                <i class="fas fa-clipboard-list"></i>
            </span>
            My Job Cards
        </a>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.delivery-notes', 'customer.delivery-notes.*') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.delivery-notes')); ?>"
            title="Delivery Notes"
        >
            <span class="menu-icon menu-delivery">
                <i class="fas fa-truck"></i>
            </span>
            Delivery Notes
        </a>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.collection-notes', 'customer.collection-notes.*') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.collection-notes')); ?>"
            title="Collection Notes"
        >
            <span class="menu-icon menu-collection">
                <i class="fas fa-hand-holding"></i>
            </span>
            Collection Notes
        </a>

        <a
            class="nav-link <?php echo e(request()->routeIs('customer.request-forms', 'customer.request-forms.*') ? 'active' : ''); ?>"
            href="<?php echo e(route('customer.request-forms')); ?>"
            title="Request Forms"
        >
            <span class="menu-icon menu-request">
                <i class="fas fa-file-signature"></i>
            </span>
            Request Forms
        </a>

        <?php if(Route::has('calibrations.create')): ?>
            <a
                class="nav-link new-request-link <?php echo e(request()->routeIs('calibrations.create') ? 'active' : ''); ?>"
                href="<?php echo e(route('calibrations.create')); ?>"
                title="New Calibration Request"
            >
                <span class="menu-icon menu-new">
                    <i class="fas fa-plus-circle"></i>
                </span>
                <span>New Request</span>
            </a>
        <?php endif; ?>

        <div class="nav-section-title mt-3">Support</div>

        <?php if(Route::has('user-guide.index')): ?>
            <a
                class="nav-link <?php echo e(request()->routeIs('user-guide.*') ? 'active' : ''); ?>"
                href="<?php echo e(route('user-guide.index')); ?>"
                title="User Guide"
            >
                <span class="menu-icon menu-guide">
                    <i class="fas fa-book"></i>
                </span>
                User Guide
            </a>
        <?php endif; ?>

    <?php else: ?>
        
        
        
        <div class="nav-section-title">Main Menu</div>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_dashboard')): ?>
            <?php if(Route::has('dashboard')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>"
                    href="<?php echo e(route('dashboard')); ?>"
                    title="Dashboard"
                >
                    <span class="menu-icon menu-dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                    </span>
                    Dashboard
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_calibrations')): ?>
            <?php if(Route::has('calibrations.index')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('calibrations.*') && !request()->routeIs('calibrations.batches*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('calibrations.index')); ?>"
                    title="Calibrations"
                >
                    <span class="menu-icon menu-lab">
                        <i class="fas fa-microscope"></i>
                    </span>
                    Calibrations
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_calibrations')): ?>
            <?php if(Route::has('calibrations.batches')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('calibrations.batches*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('calibrations.batches')); ?>"
                    title="Batches"
                >
                    <span class="menu-icon menu-batches">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    Batches

                    <?php if($batchCount > 0): ?>
                        <span class="badge bg-info"><?php echo e($batchCount); ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_customers')): ?>
            <?php if(Route::has('admin.customers.index')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('admin.customers.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.customers.index')); ?>"
                    title="Customers"
                >
                    <span class="menu-icon menu-customers">
                        <i class="fas fa-building"></i>
                    </span>
                    Customers
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(Route::has('admin.quote-requests.index')): ?>
            <?php
                $pendingQuoteRequests = \App\Models\PublicQuoteRequest::query()
                    ->where('status', 'pending')
                    ->count();
            ?>
            <a href="<?php echo e(route('admin.quote-requests.index')); ?>"
               class="nav-link <?php echo e(request()->routeIs('admin.quote-requests.*') ? 'active' : ''); ?>"
               title="Quote Requests">
                <span class="menu-icon menu-quotes">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                <span>Quote Requests</span>
                <?php if($pendingQuoteRequests > 0): ?>
                    <span class="badge bg-danger ms-auto"><?php echo e($pendingQuoteRequests); ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.view')): ?>
            <?php if(Route::has('admin.payments.dashboard')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('admin.payments.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.payments.dashboard')); ?>"
                    title="Payments"
                >
                    <span class="menu-icon menu-payments">
                        <i class="fas fa-money-check-alt"></i>
                    </span>
                    Payments
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_certificates')): ?>
            <?php if(Route::has('certificates.index')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('certificates.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('certificates.index')); ?>"
                    title="Certificates"
                >
                    <span class="menu-icon menu-certificates">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    Certificates
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('quality_check')): ?>
            <?php if(Route::has('admin.quality.dashboard')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('admin.quality.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('admin.quality.dashboard')); ?>"
                    title="Quality Control"
                >
                    <span class="menu-icon menu-quality">
                        <i class="fas fa-clipboard-check"></i>
                    </span>
                    Quality Control
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(auth()->user()->hasAnyPermission([
            'view_quality_dashboard',
            'view_quality_objectives',
            'view_achievements',
            'view_corrective_actions',
            'view_quality_reports'
        ])): ?>
            <div class="nav-section-title mt-3">Quality Management</div>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_quality_dashboard')): ?>
                <?php if(Route::has('quality.dashboard')): ?>
                    <a
                        class="nav-link <?php echo e(request()->routeIs('quality.dashboard') ? 'active' : ''); ?>"
                        href="<?php echo e(route('quality.dashboard')); ?>"
                        title="Quality Dashboard"
                    >
                        <span class="menu-icon menu-dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                        </span>
                        Dashboard
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_quality_objectives')): ?>
                <?php if(Route::has('quality.objectives.index')): ?>
                    <a
                        class="nav-link <?php echo e(request()->routeIs('quality.objectives.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('quality.objectives.index')); ?>"
                        title="Objectives"
                    >
                        <span class="menu-icon menu-objectives">
                            <i class="fas fa-bullseye"></i>
                        </span>
                        Objectives
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_achievements')): ?>
                <?php if(Route::has('quality.achievements.index')): ?>
                    <a
                        class="nav-link <?php echo e(request()->routeIs('quality.achievements.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('quality.achievements.index')); ?>"
                        title="Achievements"
                    >
                        <span class="menu-icon menu-achievements">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        Achievements
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_corrective_actions')): ?>
                <?php if(Route::has('quality.corrective-actions.index')): ?>
                    <a
                        class="nav-link <?php echo e(request()->routeIs('quality.corrective-actions.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('quality.corrective-actions.index')); ?>"
                        title="Corrective Actions"
                    >
                        <span class="menu-icon menu-actions">
                            <i class="fas fa-tasks"></i>
                        </span>
                        Corrective Actions
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_quality_reports')): ?>
                <?php if(Route::has('quality.reports.index')): ?>
                    <a
                        class="nav-link <?php echo e(request()->routeIs('quality.reports.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('quality.reports.index')); ?>"
                        title="Reports"
                    >
                        <span class="menu-icon menu-reports">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        Reports
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <div class="nav-section-title mt-3">Communication</div>

        <?php if(Route::has('chat.index')): ?>
            <a
                class="nav-link <?php echo e(request()->routeIs('chat.*') ? 'active' : ''); ?>"
                href="<?php echo e(route('chat.index')); ?>"
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
        <?php endif; ?>

        <?php if(Route::has('user-guide.index')): ?>
            <a
                class="nav-link <?php echo e(request()->routeIs('user-guide.*') ? 'active' : ''); ?>"
                href="<?php echo e(route('user-guide.index')); ?>"
                title="User Guide"
            >
                <span class="menu-icon menu-guide">
                    <i class="fas fa-book"></i>
                </span>
                User Guide
            </a>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_system_documents')): ?>
            <?php if(Route::has('system-documents.index')): ?>
                <a href="<?php echo e(route('system-documents.index')); ?>"
                   class="nav-link <?php echo e(request()->routeIs('system-documents.index') ? 'active' : ''); ?>"
                   title="System Documentation">
                    <span class="menu-icon menu-guide">
                        <i class="fas fa-book-open"></i>
                    </span>
                    <span>System Documentation</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage_system_documents')): ?>
            <?php if(Route::has('system-documents.dashboard')): ?>
                <a href="<?php echo e(route('system-documents.dashboard')); ?>"
                   class="nav-link <?php echo e(request()->routeIs('system-documents.dashboard') ? 'active' : ''); ?>"
                   title="Documentation Compliance">
                    <span class="menu-icon menu-reports">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    <span>Documentation Compliance</span>
                </a>
            <?php endif; ?>

            <?php if(Route::has('system-documents.reports')): ?>
                <a href="<?php echo e(route('system-documents.reports')); ?>"
                   class="nav-link <?php echo e(request()->routeIs('system-documents.reports*') ? 'active' : ''); ?>"
                   title="Documentation Reports">
                    <span class="menu-icon menu-reports">
                        <i class="fas fa-file-lines"></i>
                    </span>
                    <span>Documentation Reports</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_equipment')): ?>
            <?php if(Route::has('equipment.index')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('equipment.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('equipment.index')); ?>"
                    title="Equipment"
                >
                    <span class="menu-icon menu-equipment">
                        <i class="fas fa-tools"></i>
                    </span>
                    Equipment
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_users')): ?>
            <div class="nav-section-title mt-3">Administration</div>

            <?php if(Route::has('users.index')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('users.index')); ?>"
                    title="Users"
                >
                    <span class="menu-icon menu-users">
                        <i class="fas fa-users-cog"></i>
                    </span>
                    Users
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_audit_logs')): ?>
            <?php if(Route::has('audit-logs.index')): ?>
                <a
                    class="nav-link <?php echo e(request()->routeIs('audit-logs.*') ? 'active' : ''); ?>"
                    href="<?php echo e(route('audit-logs.index')); ?>"
                    title="Audit Logs"
                >
                    <span class="menu-icon menu-audit">
                        <i class="fas fa-history"></i>
                    </span>
                    Audit Logs
                </a>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php /**PATH G:\project\ncckplclims\resources\views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>