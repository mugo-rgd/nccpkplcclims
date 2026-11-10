<?php $__env->startSection('title', 'Dashboard - KPLC Meter Lab'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $currentUser = auth()->user();

    $statuses = [
        'pending' => [
            'label' => 'Pending',
            'color' => 'secondary',
            'icon' => 'clock',
        ],
        'quoted' => [
            'label' => 'Quoted',
            'color' => 'info',
            'icon' => 'file-invoice',
        ],
        'paid' => [
            'label' => 'Paid',
            'color' => 'success',
            'icon' => 'check-circle',
        ],
        'assigned' => [
            'label' => 'Assigned',
            'color' => 'primary',
            'icon' => 'user-check',
        ],
        'in_progress' => [
            'label' => 'In Progress',
            'color' => 'warning',
            'icon' => 'spinner',
        ],
        'testing' => [
            'label' => 'Testing',
            'color' => 'info',
            'icon' => 'flask',
        ],
        'completed' => [
            'label' => 'Completed',
            'color' => 'success',
            'icon' => 'check-double',
        ],
        'certified' => [
            'label' => 'Certified',
            'color' => 'success',
            'icon' => 'certificate',
        ],
    ];

    $totalEquipment = (int) ($stats['total_equipment'] ?? 0);

    $operationalEquipment =
        (int) ($stats['equipment_active'] ?? 0)
        + (int) ($stats['equipment_in_use'] ?? 0);

    $equipmentHealthPercentage = $totalEquipment > 0
        ? min(
            100,
            round(
                ($operationalEquipment / $totalEquipment) * 100
            )
        )
        : 0;
?>

<div class="container-fluid dashboard-page">

    
    
    
    <div class="card dashboard-hero border-0 mb-4">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                            <i class="fas fa-bolt me-1"></i>
                            ONLINE
                        </span>

                        <span class="text-white-50">
                            <i class="far fa-clock me-1"></i>
                            Page loaded:
                            <?php echo e(now()->format('l, F d, Y h:i A')); ?>

                        </span>
                    </div>

                    <h2 class="fw-bold mb-2">
                        <i class="fas fa-microscope me-2"></i>
                        Welcome Back
                    </h2>

                    <p class="lead mb-1 dashboard-user-name">
                        <strong><?php echo e($currentUser?->name ?? 'User'); ?></strong>
                    </p>

                    <p class="mb-3 dashboard-user-meta">
                        <i class="fas fa-user-tag me-1"></i>
                        <?php echo e($currentUser?->role_display ?? 'User'); ?>


                        <span class="mx-2">|</span>

                        <i class="fas fa-building me-1"></i>
                        <?php echo e($currentUser?->department ?? 'Meter Central Laboratory'); ?>

                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            System Online
                        </span>

                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-database text-primary me-1"></i>
                            Systems Operational
                        </span>

                        <?php if($currentUser?->hasRole('lab_technician')): ?>
                            <span class="badge bg-light text-dark px-3 py-2">
                                <i class="fas fa-tasks text-warning me-1"></i>
                                <?php echo e(number_format($stats['pending_calibrations'] ?? 0)); ?>

                                Pending Tasks
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4 text-center d-none d-lg-block">
                    <div class="hero-icon-wrap">
                        <i class="fas fa-microscope fa-5x text-white"></i>

                        <span class="hero-status-dot">
                            <i class="fas fa-circle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card dashboard-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>

                        <div>
                            <h6 class="text-muted mb-1">Total Users</h6>

                            <h3
                                class="fw-bold mb-0"
                                data-animate-number="<?php echo e((int) ($stats['total_users'] ?? 0)); ?>"
                            >
                                <?php echo e(number_format($stats['total_users'] ?? 0)); ?>

                            </h3>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            +<?php echo e(number_format($stats['new_users_this_month'] ?? 0)); ?>

                            this month
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card dashboard-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-building fa-2x"></i>
                        </div>

                        <div>
                            <h6 class="text-muted mb-1">Total Customers</h6>

                            <h3
                                class="fw-bold mb-0"
                                data-animate-number="<?php echo e((int) ($stats['total_customers'] ?? 0)); ?>"
                            >
                                <?php echo e(number_format($stats['total_customers'] ?? 0)); ?>

                            </h3>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            +<?php echo e(number_format($stats['new_customers_this_month'] ?? 0)); ?>

                            this month
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card dashboard-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-microscope fa-2x"></i>
                        </div>

                        <div>
                            <h6 class="text-muted mb-1">Calibrations</h6>

                            <h3
                                class="fw-bold mb-0"
                                data-animate-number="<?php echo e((int) ($stats['total_calibrations'] ?? 0)); ?>"
                            >
                                <?php echo e(number_format($stats['total_calibrations'] ?? 0)); ?>

                            </h3>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            +<?php echo e(number_format($stats['new_calibrations_this_month'] ?? 0)); ?>

                            this month
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card dashboard-stat-card border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-file-pdf fa-2x"></i>
                        </div>

                        <div>
                            <h6 class="text-muted mb-1">Certificates</h6>

                            <h3
                                class="fw-bold mb-0"
                                data-animate-number="<?php echo e((int) ($stats['total_certificates'] ?? 0)); ?>"
                            >
                                <?php echo e(number_format($stats['total_certificates'] ?? 0)); ?>

                            </h3>
                        </div>
                    </div>

                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-arrow-up text-success me-1"></i>
                            +<?php echo e(number_format($stats['new_certificates_this_month'] ?? 0)); ?>

                            this month
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <div class="card dashboard-card border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Calibration Status Overview
                </h5>

                <span class="badge bg-secondary">
                    <i class="fas fa-clock me-1"></i>
                    Updated <?php echo e(now()->format('H:i')); ?>

                </span>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-xl-3 col-md-4 col-6">
                        <div class="status-card">
                            <span class="badge bg-<?php echo e($status['color']); ?> rounded-pill px-3 py-2">
                                <i class="fas fa-<?php echo e($status['icon']); ?> me-1"></i>
                                <?php echo e($status['label']); ?>

                            </span>

                            <h4 class="fw-bold mt-3 mb-0">
                                <?php echo e(number_format($stats['status_counts'][$key] ?? 0)); ?>

                            </h4>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    
    
    <div class="card dashboard-card border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-tools text-primary me-2"></i>
                    Laboratory Equipment Overview
                </h5>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_equipment')): ?>
                    <?php if(Route::has('equipment.index')): ?>
                        <a
                            href="<?php echo e(route('equipment.index')); ?>"
                            class="btn btn-sm btn-outline-primary"
                        >
                            <i class="fas fa-arrow-right me-1"></i>
                            View All Equipment
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <?php $__empty_1 = true; $__currentLoopData = $featuredEquipment ?? collect(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $equipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $equipmentStatus = $equipment->status ?? 'unknown';

                        $equipmentStatusColor = match ($equipmentStatus) {
                            'active' => 'success',
                            'in_use' => 'warning',
                            'under_repair' => 'danger',
                            'retired' => 'secondary',
                            default => 'secondary',
                        };

                        $nextCalibrationDate = null;

                        try {
                            if (!empty($equipment->next_calibration_date)) {
                                $nextCalibrationDate = \Carbon\Carbon::parse(
                                    $equipment->next_calibration_date
                                )->format('d/m/Y');
                            }
                        } catch (\Throwable $exception) {
                            $nextCalibrationDate = null;
                        }
                    ?>

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card equipment-card h-100 border-0">
                            <div class="card-body text-center p-4">
                                <div class="equipment-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                                    <i class="fas fa-tools fa-3x"></i>
                                </div>

                                <h6 class="fw-bold">
                                    <?php echo e($equipment->name ?? 'Unnamed Equipment'); ?>

                                </h6>

                                <p class="text-muted small mb-2">
                                    <?php echo e($equipment->model
                                        ?? $equipment->equipment_number
                                        ?? 'Model not specified'); ?>

                                </p>

                                <span class="badge bg-<?php echo e($equipmentStatusColor); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $equipmentStatus))); ?>

                                </span>

                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        Next calibration:
                                        <?php echo e($nextCalibrationDate ?? 'Not scheduled'); ?>

                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-tools fa-3x mb-3"></i>
                            <p class="mb-0">
                                No laboratory equipment is currently available.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    
    
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card dashboard-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chart-pie text-primary me-2"></i>
                        Equipment Status Distribution
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="status-line">
                                <span class="status-dot bg-success"></span>
                                <span>Active</span>
                                <strong><?php echo e(number_format($stats['equipment_active'] ?? 0)); ?></strong>
                            </div>

                            <div class="status-line">
                                <span class="status-dot bg-warning"></span>
                                <span>In Use</span>
                                <strong><?php echo e(number_format($stats['equipment_in_use'] ?? 0)); ?></strong>
                            </div>

                            <div class="status-line">
                                <span class="status-dot bg-danger"></span>
                                <span>Under Repair</span>
                                <strong><?php echo e(number_format($stats['equipment_under_repair'] ?? 0)); ?></strong>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="status-line">
                                <span class="status-dot bg-info"></span>
                                <span>Calibration Due</span>
                                <strong><?php echo e(number_format($stats['equipment_calibration_due'] ?? 0)); ?></strong>
                            </div>

                            <div class="status-line">
                                <span class="status-dot bg-secondary"></span>
                                <span>Retired</span>
                                <strong><?php echo e(number_format($stats['equipment_retired'] ?? 0)); ?></strong>
                            </div>

                            <div class="status-line">
                                <span class="status-dot bg-primary"></span>
                                <span>Total Equipment</span>
                                <strong><?php echo e(number_format($totalEquipment)); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                Equipment Health
                            </small>

                            <small class="text-success fw-semibold">
                                <?php echo e($equipmentHealthPercentage); ?>% Operational
                            </small>
                        </div>

                        <div class="progress dashboard-progress">
                            <div
                                class="progress-bar bg-success"
                                role="progressbar"
                                style="width: <?php echo e($equipmentHealthPercentage); ?>%;"
                                aria-valuenow="<?php echo e($equipmentHealthPercentage); ?>"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card dashboard-card border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-bell text-primary me-2"></i>
                        Recent Activities
                    </h5>
                </div>

                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php $__empty_1 = true; $__currentLoopData = $recentActivities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $activityTime = 'Just now';

                                try {
                                    if (!empty($activity['time'])) {
                                        $activityTime = \Carbon\Carbon::parse(
                                            $activity['time']
                                        )->diffForHumans();
                                    }
                                } catch (\Throwable $exception) {
                                    $activityTime = 'Just now';
                                }
                            ?>

                            <li class="list-group-item d-flex align-items-center py-3">
                                <div class="activity-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="fas fa-<?php echo e($activity['icon'] ?? 'bell'); ?>"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <p class="mb-0 small">
                                        <strong><?php echo e($activity['user'] ?? 'System'); ?></strong>
                                        <?php echo e($activity['action'] ?? 'performed an action'); ?>

                                    </p>

                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo e($activityTime); ?>

                                    </small>
                                </div>

                                <?php if(!empty($activity['status'])): ?>
                                    <span class="badge bg-<?php echo e($activity['status_color'] ?? 'primary'); ?>">
                                        <?php echo e($activity['status']); ?>

                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="list-group-item text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                No recent activities
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-card border-0">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-bolt text-primary me-2"></i>
                        Quick Actions
                    </h6>

                    <div class="d-flex flex-wrap gap-2">
                        <?php if($currentUser?->can('create_calibration') && Route::has('calibrations.create')): ?>
                            <a href="<?php echo e(route('calibrations.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i>
                                New Calibration
                            </a>
                        <?php endif; ?>

                        <?php if($currentUser?->can('create_customer') && Route::has('customers.create')): ?>
                            <a href="<?php echo e(route('register')); ?>" class="btn btn-success">
                                <i class="fas fa-user-plus me-1"></i>
                                Add Customer
                            </a>
                        <?php endif; ?>

                        <?php if(
                            (
                                $currentUser?->can('create_certificate')
                                || $currentUser?->can('create_certificates')
                            )
                            && Route::has('certificates.create')
                        ): ?>
                            <a href="<?php echo e(route('certificates.create')); ?>" class="btn btn-info">
                                <i class="fas fa-file-pdf me-1"></i>
                                Generate Certificate
                            </a>
                        <?php endif; ?>

                        <?php if(
                            $currentUser?->can('view_equipment')
                            && Route::has('equipment.create')
                        ): ?>
                            <a href="<?php echo e(route('equipment.create')); ?>" class="btn btn-warning">
                                <i class="fas fa-plus me-1"></i>
                                Add Equipment
                            </a>
                        <?php endif; ?>
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any([
    'view_service_types',
    'view_equipment_types',
    'view_equipment_categories',
    'view_equipment_manufacturers'
])): ?>
    <?php if(Route::has('laboratory-configuration.index')): ?>
        <a
            href="<?php echo e(route('laboratory-configuration.index')); ?>"
            class="btn btn-outline-primary"
        >
            <i class="fas fa-gears me-1"></i>
            Laboratory Configuration
        </a>
    <?php endif; ?>
<?php endif; ?>
                        <?php if(
                            $currentUser?->can('view_reports')
                            && Route::has('reports.index')
                        ): ?>
                            <a href="<?php echo e(route('reports.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-chart-bar me-1"></i>
                                Generate Report
                            </a>
                        <?php endif; ?>

                        <?php if(Route::has('user-guide.index')): ?>
                            <a href="<?php echo e(route('user-guide.index')); ?>" class="btn btn-dark">
                                <i class="fas fa-book me-1"></i>
                                User Guide
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .dashboard-page {
        padding-bottom: 2rem;
    }

    .dashboard-hero {
        overflow: hidden;
        background:
            linear-gradient(
                135deg,
                #0d47a1 0%,
                #1565c0 50%,
                #1a237e 100%
            );
        box-shadow: 0 12px 35px rgba(13, 71, 161, 0.2);
    }

    .dashboard-user-name {
        font-size: 1.1rem;
        opacity: 0.95;
    }

    .dashboard-user-meta {
        opacity: 0.85;
    }

    .hero-icon-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.12);
    }

    .hero-status-dot {
        position: absolute;
        top: 10px;
        right: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #dc3545;
        color: #fff;
        font-size: 0.45rem;
    }

    .dashboard-card,
    .dashboard-stat-card,
    .equipment-card {
        border-radius: 0.85rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07);
    }

    .dashboard-stat-card,
    .equipment-card {
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .dashboard-stat-card:hover,
    .equipment-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
    }

    .stat-icon,
    .equipment-icon,
    .activity-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 50%;
    }

    .stat-icon {
        width: 62px;
        height: 62px;
    }

    .equipment-icon {
        width: 76px;
        height: 76px;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
    }

    .status-card {
        height: 100%;
        padding: 1rem;
        border: 1px solid #edf0f4;
        border-radius: 0.75rem;
        background: #f8f9fa;
        text-align: center;
    }

    .status-line {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.85rem;
    }

    .status-line strong {
        margin-left: auto;
    }

    .status-dot {
        display: inline-block;
        width: 11px;
        height: 11px;
        flex: 0 0 11px;
        border-radius: 50%;
    }

    .dashboard-progress {
        height: 10px;
        border-radius: 10px;
        background: #e9ecef;
    }

    .empty-state {
        padding: 3rem 1rem;
        color: #6c757d;
        text-align: center;
    }

    .list-group-item {
        border-color: #f0f0f0;
        transition: background-color 0.2s ease;
    }

    .list-group-item:hover {
        background: #f8f9fa;
    }

    .badge {
        font-weight: 500;
    }

    @media (max-width: 767.98px) {
        .dashboard-hero .card-body {
            padding: 1.5rem !important;
        }

        .status-card {
            padding: 0.85rem 0.5rem;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        document
            .querySelectorAll('[data-animate-number]')
            .forEach(function (element) {
                const finalValue = Number(
                    element.dataset.animateNumber || 0
                );

                if (!Number.isFinite(finalValue) || finalValue <= 0) {
                    element.textContent = '0';
                    return;
                }

                let currentValue = 0;
                const steps = 30;
                const increment = Math.max(
                    Math.ceil(finalValue / steps),
                    1
                );

                const timer = window.setInterval(function () {
                    currentValue += increment;

                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        window.clearInterval(timer);
                    }

                    element.textContent =
                        currentValue.toLocaleString();
                }, 30);
            });
    },
    { once: true }
);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\project\ncckplclims\resources\views/dashboard/index.blade.php ENDPATH**/ ?>