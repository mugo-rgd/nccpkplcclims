<?php $__env->startSection('title', 'Calibrations'); ?>

<?php $__env->startSection('content'); ?>
<?php
    /*
     * Complete calibration workflow status configuration.
     *
     * Use this single array for:
     * - Status filter
     * - Badge colours
     * - Status labels
     * - Status icons
     */
    $calibrationStatusConfig = [
        'pending' => [
            'label' => 'Pending',
            'color' => 'warning',
            'icon' => 'fa-clock',
            'group' => 'pending',
        ],

        'quoted' => [
            'label' => 'Quoted',
            'color' => 'info',
            'icon' => 'fa-file-invoice-dollar',
            'group' => 'pending',
        ],

        'paid' => [
            'label' => 'Paid',
            'color' => 'success',
            'icon' => 'fa-money-check-alt',
            'group' => 'pending',
        ],

        'assigned' => [
            'label' => 'Assigned',
            'color' => 'primary',
            'icon' => 'fa-user-check',
            'group' => 'in_progress',
        ],

        'in_progress' => [
            'label' => 'In Progress',
            'color' => 'primary',
            'icon' => 'fa-spinner',
            'group' => 'in_progress',
        ],

        'testing' => [
            'label' => 'Testing',
            'color' => 'info',
            'icon' => 'fa-flask',
            'group' => 'in_progress',
        ],

        'completed' => [
            'label' => 'Completed',
            'color' => 'success',
            'icon' => 'fa-check-double',
            'group' => 'completed',
        ],

        'certified' => [
            'label' => 'Certified',
            'color' => 'success',
            'icon' => 'fa-certificate',
            'group' => 'completed',
        ],

        'rejected' => [
            'label' => 'Rejected',
            'color' => 'danger',
            'icon' => 'fa-times-circle',
            'group' => 'closed',
        ],

        'dispatched' => [
            'label' => 'Dispatched',
            'color' => 'secondary',
            'icon' => 'fa-truck',
            'group' => 'closed',
        ],
    ];

    /*
     * Build simple arrays from the main configuration.
     */
    $statusColors = collect($calibrationStatusConfig)
        ->mapWithKeys(
            fn (array $config, string $status) => [
                $status => $config['color'],
            ]
        )
        ->all();

    $statusIcons = collect($calibrationStatusConfig)
        ->mapWithKeys(
            fn (array $config, string $status) => [
                $status => $config['icon'],
            ]
        )
        ->all();

    $statusLabels = collect($calibrationStatusConfig)
        ->mapWithKeys(
            fn (array $config, string $status) => [
                $status => $config['label'],
            ]
        )
        ->all();

    /*
     * Payment status configuration.
     */
    $paymentColors = [
        'unpaid' => 'secondary',
        'pending' => 'warning',
        'partially_paid' => 'info',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'secondary',
        'cancelled' => 'dark',
    ];

    /*
     * Certificate status configuration.
     */
    $certificateStatusColors = [
        'draft' => 'secondary',
        'generated' => 'info',
        'checked' => 'primary',
        'approved' => 'success',
        'rejected' => 'danger',
        'issued' => 'primary',
        'sent' => 'success',
    ];

    $certificateStatusIcons = [
        'draft' => 'fa-file',
        'generated' => 'fa-file-pdf',
        'checked' => 'fa-check-circle',
        'approved' => 'fa-check-double',
        'rejected' => 'fa-times-circle',
        'issued' => 'fa-certificate',
        'sent' => 'fa-paper-plane',
    ];

    $serviceTypeMap = collect($serviceTypes ?? [])
        ->pluck('serv_code', 'id');

    $currentUser = auth()->user();

    $canViewCertificates =
        $currentUser?->can('view_certificates') ?? false;

    $canCheckCertificates =
        $currentUser?->can('check_certificates') ?? false;

    $canApproveCertificates =
        $currentUser?->can('approve_certificates') ?? false;

    $canAccessCertificate = $currentUser?->canAny([
        'view_certificates',
        'check_certificates',
        'approve_certificates',
    ]) ?? false;

    $technicianId = $technicianId ?? null;

    /*
     * Important:
     * These counts apply only to the records currently loaded in the
     * paginator page. For database-wide counts, calculate them in
     * the controller.
     */
    $currentPageCalibrations = collect($calibrations->items());

    $pendingCount = $currentPageCalibrations
        ->whereIn('status', [
            'pending',
            'quoted',
            'paid',
        ])
        ->count();

    $inProgressCount = $currentPageCalibrations
        ->whereIn('status', [
            'assigned',
            'in_progress',
            'testing',
        ])
        ->count();

    $completedCount = $currentPageCalibrations
        ->whereIn('status', [
            'completed',
            'certified',
            'dispatched',
        ])
        ->count();

    $rejectedCount = $currentPageCalibrations
        ->where('status', 'rejected')
        ->count();

$canReviewQuotation = $currentUser?->hasAnyRole([
    'supervisor',
    'lab_manager',
    'super_admin',
]) ?? false;
        
?>

<div class="container-fluid px-0 calibrations-index-page">

    
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-microscope text-primary me-2"></i>
                Calibrations
                <span class="badge bg-secondary"><?php echo e($calibrations->total()); ?></span>
            </h1>
            <p class="text-muted mb-0">
                Manage calibration requests, technician assignments, testing, approvals, and certificates.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_calibrations')): ?>
                <?php if(Route::has('calibrations.bulk-upload')): ?>
                    <a href="<?php echo e(route('calibrations.bulk-upload')); ?>" class="btn btn-success">
                        <i class="fas fa-file-upload me-1"></i>
                        Bulk Upload
                    </a>
                <?php endif; ?>

                <a href="<?php echo e(route('calibrations.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    New Calibration
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="row mb-3 g-2">
    <div class="col-6 col-lg">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-chart-bar me-1"></i>
                    Total
                </h6>

                <h3 class="mb-0">
                    <?php echo e(number_format($calibrations->total())); ?>

                </h3>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg">
        <div class="card text-dark bg-warning h-100">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-clock me-1"></i>
                    Awaiting Action
                </h6>

                <h3 class="mb-0">
                    <?php echo e(number_format($pendingCount)); ?>

                </h3>

                <small>
                    Pending, quoted, or paid
                </small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-spinner me-1"></i>
                    In Progress
                </h6>

                <h3 class="mb-0">
                    <?php echo e(number_format($inProgressCount)); ?>

                </h3>

                <small>
                    Assigned, active, or testing
                </small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-check-circle me-1"></i>
                    Completed
                </h6>

                <h3 class="mb-0">
                    <?php echo e(number_format($completedCount)); ?>

                </h3>

                <small>
                    Completed, certified, or dispatched
                </small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg">
        <div class="card text-white bg-danger h-100">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-times-circle me-1"></i>
                    Rejected
                </h6>

                <h3 class="mb-0">
                    <?php echo e(number_format($rejectedCount)); ?>

                </h3>
            </div>
        </div>
    </div>
</div>

    
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('calibrations.index')); ?>" class="row g-2 g-md-3" id="filterForm">

                <div class="col-md-2 col-sm-6">
                    <input
                        type="text"
                        name="search"
                        class="form-control form-control-sm"
                        placeholder="Search..."
                        value="<?php echo e(request('search')); ?>"
                    >
                </div>

                <div class="col-md-2 col-sm-6">
    <select
        name="status"
        class="form-select form-select-sm"
    >
        <option value="">All Statuses</option>

        <?php $__currentLoopData = $calibrationStatusConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option
                value="<?php echo e($status); ?>"
                <?php echo e(request('status') === $status ? 'selected' : ''); ?>

            >
                <?php echo e($config['label']); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>

                <div class="col-md-2 col-sm-6">
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">All Customers</option>

                        <?php $__currentLoopData = $customers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($id); ?>" <?php echo e(request('customer_id') == $id ? 'selected' : ''); ?>>
                                <?php echo e($name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

              <div class="col-md-2 col-sm-6">
    <select
        name="technician_id"
        class="form-select form-select-sm"
    >
        <option
            value="all"
            <?php echo e(request()->has('technician_id')
                && in_array(
                    request('technician_id'),
                    ['all', ''],
                    true
                )
                    ? 'selected'
                    : ''); ?>

        >
            All Technicians
        </option>

        <?php $__currentLoopData = $technicians ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option
                value="<?php echo e($id); ?>"
                <?php echo e((
                        request()->has('technician_id')
                            ? (string) request('technician_id')
                                === (string) $id
                            : (int) $technicianId === (int) $id
                    )
                        ? 'selected'
                        : ''); ?>

            >
                <?php if(
                    $currentUser?->hasRole('lab_technician')
                    && (int) $currentUser?->id === (int) $id
                ): ?>
                    My Calibrations — <?php echo e($name); ?>

                <?php else: ?>
                    <?php echo e($name); ?>

                <?php endif; ?>
            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>

                <div class="col-md-2 col-sm-6">
                    <select name="service_type_id" class="form-select form-select-sm">
                        <option value="">All Services</option>

                        <?php $__currentLoopData = $serviceTypes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($service->id); ?>" <?php echo e(request('service_type_id') == $service->id ? 'selected' : ''); ?>>
                                <?php echo e($service->serv_code ?? $service->name ?? 'Service #' . $service->id); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="fas fa-filter me-1"></i>
                            Filter
                        </button>

                        <a href="<?php echo e(route('calibrations.index')); ?>" class="btn btn-secondary btn-sm" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    
    <div class="card">
        <div class="card-body p-0">

            
            <div class="table-scroll-top d-none d-md-block" id="scrollTop">
                <div class="scroll-track">
                    <div class="scroll-thumb" id="scrollThumbTop"></div>
                </div>
            </div>

            
            <div class="table-responsive" id="tableContainer" style="overflow-x: auto; -webkit-overflow-scrolling: touch; max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-striped table-hover align-middle mb-0" id="calibrationsTable" style="min-width: 1800px; width: 100%;">
                    <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="min-width: 50px; width: 50px; position: sticky; left: 0; z-index: 12; background: #212529; box-shadow: 2px 0 4px rgba(0,0,0,0.1);">#</th>
                            <th style="min-width: 150px; width: 150px; position: sticky; left: 50px; z-index: 12; background: #212529; box-shadow: 2px 0 4px rgba(0,0,0,0.1);">Customer</th>
                            <th style="min-width: 130px; width: 130px;">Equipment</th>
                            <th style="min-width: 110px; width: 110px;">Serial #</th>
                            <th style="min-width: 120px; width: 120px;">Service Types</th>
                            <th style="min-width: 100px; width: 100px;">Status</th>
                            <th style="min-width: 100px; width: 100px;">Location</th>
                            <th style="min-width: 120px; width: 120px;">Assigned To</th>
                            <th style="min-width: 140px; width: 140px;">Assigned Date</th>
                            <th style="min-width: 100px; width: 100px;">Priority</th>
                            <th style="min-width: 100px; width: 100px;">Payment</th>
                            <th style="min-width: 120px; width: 120px;">Progress</th>
                            <th style="min-width: 130px; width: 130px;">Certificate</th>
                            <th style="min-width: 120px; width: 120px;">Equipment Type</th>
                            <th style="min-width: 200px; width: 200px; position: sticky; right: 0; z-index: 12; background: #212529; box-shadow: -2px 0 4px rgba(0,0,0,0.1);">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $calibrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $calibration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php

                            $latestQuotation = $calibration->quotes
    ->sortByDesc('id')
    ->first();
                                $calibrationStatus =
        $calibration->status
        ?? 'unknown';

    $statusConfig =
        $calibrationStatusConfig[$calibrationStatus]
        ?? [
            'label' => ucfirst(
                str_replace('_', ' ', $calibrationStatus)
            ),
            'color' => 'secondary',
            'icon' => 'fa-circle-question',
        ];
                                $paymentColor = $paymentColors[$calibration->payment_status] ?? 'secondary';

                                $rawServiceTypes = $calibration->service_types ?? [];

                                if (is_string($rawServiceTypes)) {
                                    $decodedServiceTypes = json_decode($rawServiceTypes, true);
                                    $serviceTypeIds = is_array($decodedServiceTypes) ? $decodedServiceTypes : [];
                                } elseif (is_array($rawServiceTypes)) {
                                    $serviceTypeIds = $rawServiceTypes;
                                } else {
                                    $serviceTypeIds = [];
                                }

                                $serviceCodes = collect($serviceTypeIds)
                                    ->map(fn ($id) => $serviceTypeMap[$id] ?? null)
                                    ->filter()
                                    ->values();

                                $serviceCount = $serviceCodes->count();

                                $equipmentName = $calibration->equipment_name
                                    ?? optional($calibration->equipment)->name
                                    ?? 'N/A';

                                $serialNumber = $calibration->serial_number
                                    ?? optional($calibration->equipment)->serial_number
                                    ?? 'N/A';

                                $equipmentTypeName = optional($calibration->equipmentType)->name
                                    ?? $calibration->equipment_type
                                    ?? null;

                                // Technician permissions and actions
                                $isAssignedTechnician =
                                    !empty($calibration->performed_by)
                                    && (int) $currentUser?->id === (int) $calibration->performed_by;

                                $technicianActionStatuses = [
                                    'assigned',
                                    'in_progress',
                                    'testing',
                                ];

                                $canPerformCalibration =
                                    $isAssignedTechnician
                                    && in_array(
                                        $calibration->status,
                                        $technicianActionStatuses,
                                        true
                                    );

                                /*
                                 * Environmental conditions are mandatory before
                                 * laboratory test-result entry.
                                 */
                                $hasEnvironmentalConditions =
                                    $calibration->hasEnvironmentalConditions();

                                $canRecordTestParameters =
                                    $isAssignedTechnician
                                    && $calibration->status === 'testing';

                                $canAddTestResults =
                                    $canRecordTestParameters
                                    && $hasEnvironmentalConditions;

                                // Certificate information
                                $latestCert = $calibration->relationLoaded('certificates')
                                    ? $calibration->certificates
                                        ->sortByDesc('id')
                                        ->first()
                                    : $calibration->certificates()
                                        ->latest('id')
                                        ->first();

                                $hasCertificate = !is_null($latestCert);
                                $certificateStatus = $latestCert?->status;
                                $certificateNumber = $latestCert?->certificate_number;

                                $certColor = $certificateStatusColors[$certificateStatus]
                                    ?? 'secondary';

                                $certIcon = $certificateStatusIcons[$certificateStatus]
                                    ?? 'fa-file';

                                /*
                                 * Build a destination that matches the user's
                                 * actual permission. A view-only user should not
                                 * be sent to the certificate checking screen.
                                 */
                                $certificateActionUrl = null;
                                $certificateActionTitle = null;
                                $certificateButtonClass = 'btn-outline-secondary';

                                if ($hasCertificate) {
                                    if (
                                        ($canCheckCertificates || $canApproveCertificates)
                                        && Route::has('certificates.check.show')
                                    ) {
                                        $certificateActionUrl = route(
                                            'certificates.check.show',
                                            $latestCert
                                        );

                                        $certificateActionTitle =
                                            'Review certificate: '
                                            . ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $certificateStatus ?? 'draft'
                                                )
                                            );
                                    } elseif (
                                        $canViewCertificates
                                        && Route::has('certificates.preview')
                                    ) {
                                        $certificateActionUrl = route(
                                            'certificates.preview',
                                            $latestCert
                                        );

                                        $certificateActionTitle =
                                            'Preview certificate';
                                    } elseif (
                                        $canViewCertificates
                                        && Route::has('certificates.show')
                                    ) {
                                        $certificateActionUrl = route(
                                            'certificates.show',
                                            $latestCert
                                        );

                                        $certificateActionTitle =
                                            'View certificate';
                                    }

                                    $certificateButtonClass = in_array(
                                        $certificateStatus,
                                        ['approved', 'sent', 'issued'],
                                        true
                                    )
                                        ? 'btn-success'
                                        : (
                                            $certificateStatus === 'rejected'
                                                ? 'btn-danger'
                                                : 'btn-secondary'
                                        );
                                } elseif (
                                    ($canCheckCertificates || $canApproveCertificates)
                                    && in_array(
                                        $calibration->status,
                                        ['completed', 'certified'],
                                        true
                                    )
                                    && Route::has('certificates.check.index')
                                ) {
                                    $certificateActionUrl = route(
                                        'certificates.check.index'
                                    );

                                    $certificateActionTitle =
                                        'Open certificate module';
                                }

                                $showCertificateButton =
                                    $canAccessCertificate
                                    && !empty($certificateActionUrl);

                            ?>

                            <tr
                                onclick="window.location.href='<?php echo e(route('calibrations.show', $calibration)); ?>'"
                                style="cursor:pointer;"
                                class="clickable-row"
                            >
                                <td style="position: sticky; left: 0; z-index: 6; background: white; box-shadow: 2px 0 4px rgba(0,0,0,0.05);"><?php echo e($calibration->id); ?></td>

                                <td style="position: sticky; left: 50px; z-index: 6; background: white; box-shadow: 2px 0 4px rgba(0,0,0,0.05);">
                                    <strong><?php echo e(optional($calibration->customer)->company_name ?? 'N/A'); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo e(optional($calibration->customer)->contact_person ?? ''); ?>

                                    </small>
                                </td>

                                <td><?php echo e($equipmentName); ?></td>

                                <td>
                                    <code><?php echo e($serialNumber); ?></code>
                                </td>

                                <td>
                                    <?php if($serviceCount > 0): ?>
                                        <span
                                            class="badge bg-info"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e($serviceCodes->implode(', ')); ?>"
                                        >
                                            <?php echo e($serviceCount); ?> service(s)
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <td>
    <?php
        $calibrationStatus = $calibration->status ?? 'unknown';

        $statusConfig = $calibrationStatusConfig[$calibrationStatus]
            ?? [
                'label' => ucfirst(
                    str_replace('_', ' ', $calibrationStatus)
                ),
                'color' => 'secondary',
                'icon' => 'fa-circle-question',
            ];
    ?>

    <span class="badge bg-<?php echo e($statusConfig['color']); ?>">
        <i class="fas <?php echo e($statusConfig['icon']); ?> me-1"></i>
        <?php echo e($statusConfig['label']); ?>

    </span>
</td>

                                <td>
                                    <?php if($calibration->calibration_location): ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo e(ucfirst(str_replace('_', ' ', $calibration->calibration_location))); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($calibration->performedBy): ?>
                                        <span class="badge bg-primary"><?php echo e($calibration->performedBy->name); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Unassigned</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($calibration->assigned_at): ?>
                                        <?php echo e(\Carbon\Carbon::parse($calibration->assigned_at)->format('Y-m-d H:i')); ?>

                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($calibration->priority && $calibration->priority !== 'normal'): ?>
                                        <span class="badge bg-<?php echo e($calibration->priority === 'urgent' ? 'danger' : 'warning'); ?>">
                                            <i class="fas fa-flag me-1"></i>
                                            <?php echo e(ucfirst($calibration->priority)); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Normal</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($calibration->payment_status): ?>
                                        <span class="badge bg-<?php echo e($paymentColor); ?>">
                                            <?php echo e(ucfirst($calibration->payment_status)); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="progress" style="height: 8px; width: 100px;">
                                        <div class="progress-bar bg-<?php echo e($calibration->getStatusColorAttribute() ?? 'primary'); ?>"
                                             role="progressbar"
                                             style="width: <?php echo e($calibration->getProgressPercentage() ?? 0); ?>%;"
                                             aria-valuenow="<?php echo e($calibration->getProgressPercentage() ?? 0); ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo e($calibration->getProgressPercentage() ?? 0); ?>%</small>
                                </td>

                                <td>
                                    <?php if($hasCertificate): ?>
                                        <span class="badge bg-<?php echo e($certColor); ?>">
                                            <i class="fas <?php echo e($certIcon); ?> me-1"></i>
                                            <?php echo e(ucfirst($certificateStatus)); ?>

                                        </span>
                                        <?php if($certificateNumber): ?>
                                            <br>
                                            <small class="text-muted"><?php echo e($certificateNumber); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No certificate</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if($equipmentTypeName): ?>
                                        <span class="badge bg-secondary"><?php echo e($equipmentTypeName); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>

                                <td style="position: sticky; right: 0; z-index: 6; background: white; box-shadow: -2px 0 4px rgba(0,0,0,0.05);">
                                    <div class="btn-group" role="group" onclick="event.stopPropagation();">

                                        <!-- View Details -->
                                        <a
                                            href="<?php echo e(route('calibrations.show', $calibration)); ?>"
                                            class="btn btn-sm btn-info"
                                            title="View Details"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Edit -->
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_calibrations')): ?>
                                            <a
                                                href="<?php echo e(route('calibrations.edit', $calibration)); ?>"
                                                class="btn btn-sm btn-warning"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>


<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.view')): ?>
    <?php if($calibration->is_external && $latestQuotation && $latestQuotation->is_external && Route::has('admin.payments.index')): ?>
        <a href="<?php echo e(route('admin.payments.index', $latestQuotation)); ?>"
           class="btn btn-sm btn-success"
           title="Manage payments for <?php echo e($latestQuotation->quote_number); ?>"
           aria-label="Manage payments for <?php echo e($latestQuotation->quote_number); ?>"
           onclick="event.stopPropagation();">
            <i class="fas fa-money-bill-wave"></i>
        </a>
    <?php endif; ?>
<?php endif; ?>

                                        <!-- Assign Technician -->
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign_calibrations')): ?>
                                            <?php if(in_array($calibration->status, ['pending', 'paid'])): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#assignModal<?php echo e($calibration->id); ?>"
                                                    title="Assign Technician"
                                                >
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            <?php elseif($calibration->status === 'assigned' && $calibration->performed_by): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success"
                                                    disabled
                                                    title="Already assigned to <?php echo e(optional($calibration->performedBy)->name ?? 'technician'); ?>"
                                                >
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        
                                        
                                        

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('perform_calibrations')): ?>

                                            <?php if(
                                                $isAssignedTechnician
                                                && $calibration->status === 'testing'
                                            ): ?>

                                                
                                                <button
                                                    type="button"
                                                    class="btn btn-sm <?php echo e($hasEnvironmentalConditions ? 'btn-info' : 'btn-outline-info'); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#testParametersModal<?php echo e($calibration->id); ?>"
                                                    title="<?php echo e($hasEnvironmentalConditions
                                                        ? 'Edit Test Parameters / Environmental Conditions'
                                                        : 'Enter Test Parameters / Environmental Conditions'); ?>"
                                                >
                                                    <i class="fas fa-temperature-half"></i>

                                                    <?php if($hasEnvironmentalConditions): ?>
                                                        <i class="fas fa-check ms-1"></i>
                                                    <?php endif; ?>
                                                </button>

                                                
                                                <?php if($canAddTestResults): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#testModal<?php echo e($calibration->id); ?>"
                                                        title="Add Test Result"
                                                    >
                                                        <i class="fas fa-flask"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        disabled
                                                        title="Record Test Parameters / Environmental Conditions first"
                                                    >
                                                        <i class="fas fa-flask"></i>
                                                        <i class="fas fa-lock ms-1"></i>
                                                    </button>
                                                <?php endif; ?>

                                            <?php elseif(
                                                in_array(
                                                    $calibration->status,
                                                    ['assigned', 'in_progress', 'testing'],
                                                    true
                                                )
                                            ): ?>

                                                <?php if(!$calibration->performed_by): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-secondary"
                                                        disabled
                                                        title="No technician is assigned"
                                                    >
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>

                                                <?php elseif(
                                                    (int) $currentUser?->id
                                                    !== (int) $calibration->performed_by
                                                ): ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-secondary"
                                                        disabled
                                                        title="Only the assigned technician can perform testing actions"
                                                    >
                                                        <i class="fas fa-lock"></i>
                                                    </button>

                                                <?php else: ?>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        disabled
                                                        title="Environmental conditions can be entered when status is Testing"
                                                    >
                                                        <i class="fas fa-temperature-half"></i>
                                                    </button>
                                                <?php endif; ?>

                                            <?php endif; ?>

                                        <?php endif; ?>

                                        <!-- ============================================ -->
                                        <!-- CERTIFICATE APPROVAL / CHECK BUTTON         -->
                                        <!-- ============================================ -->
                                        <?php if($showCertificateButton): ?>
                                            <a
                                                href="<?php echo e($certificateActionUrl); ?>"
                                                class="btn btn-sm <?php echo e($certificateButtonClass); ?>"
                                                title="<?php echo e($certificateActionTitle); ?>"
                                                aria-label="<?php echo e($certificateActionTitle); ?>"
                                            >
                                                <i class="fas <?php echo e($hasCertificate ? $certIcon : 'fa-certificate'); ?>"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Approve Calibration -->
                                        

                                        <!-- Reject Calibration -->
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reject_calibrations')): ?>
                                            <?php if(in_array($calibration->status, ['pending', 'assigned', 'testing'])): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal<?php echo e($calibration->id); ?>"
                                                    title="Reject Calibration"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

 
<?php if(
    $canReviewQuotation
    && $latestQuotation
    && Route::has('admin.quotes.show')
): ?>
    <a
        href="<?php echo e(route('admin.quotes.show', $latestQuotation->id)); ?>"
        class="btn btn-sm btn-outline-primary"
        title="Review Quotation: <?php echo e($latestQuotation->quote_number); ?>"
        aria-label="Review Quotation <?php echo e($latestQuotation->quote_number); ?>"
        onclick="event.stopPropagation();"
    >
        <i class="fas fa-file-invoice-dollar"></i>
    </a>
<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="15" class="text-center py-5">
                                    <i class="fas fa-microscope fa-4x text-muted mb-3 d-block"></i>

                                    <h4 class="text-muted">No calibrations found</h4>

                                    <p class="text-muted">
                                        Try adjusting your search or filters.
                                    </p>

                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_calibrations')): ?>
                                        <a href="<?php echo e(route('calibrations.create')); ?>" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            Create First Calibration
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

            
            <div class="table-scroll-bottom d-none d-md-block" id="scrollBottom">
                <div class="scroll-track">
                    <div class="scroll-thumb" id="scrollThumbBottom"></div>
                </div>
            </div>

        </div>
    </div>

    
    <div class="text-center text-muted small mt-2 d-block d-md-none">
        <i class="fas fa-arrows-alt-h me-1"></i>
        Swipe left or right to view all columns
    </div>

    
<?php echo e($calibrations->appends(request()->query())->links()); ?>


</div>





<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('perform_calibrations')): ?>

    <?php $__currentLoopData = $calibrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $calibration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <?php

            $isAssignedTech =
                !empty($calibration->performed_by)
                && (int) $currentUser?->id
                    === (int) $calibration->performed_by;

            $canRecordParameters =
                $isAssignedTech
                && $calibration->status === 'testing';

        ?>


        <?php if($canRecordParameters): ?>

            <div
                class="modal fade"
                id="testParametersModal<?php echo e($calibration->id); ?>"
                tabindex="-1"
                aria-labelledby="testParametersModalLabel<?php echo e($calibration->id); ?>"
                aria-hidden="true"
            >

                <div class="modal-dialog modal-lg modal-dialog-centered">

                    <div class="modal-content">

                        <form
                            action="<?php echo e(route(
                                    'calibrations.test-parameters.store',
                                    $calibration
                                )); ?>"
                            method="POST"
                        >

                            <?php echo csrf_field(); ?>


                            
                            
                            

                            <div class="modal-header bg-primary text-white">

                                <div>

                                    <h5
                                        class="modal-title"
                                        id="testParametersModalLabel<?php echo e($calibration->id); ?>"
                                    >
                                        <i
                                            class="
                                                fas
                                                fa-temperature-half
                                                me-2
                                            "
                                        ></i>

                                        Test Parameters /
                                        Environmental Conditions
                                    </h5>

                                    <small>
                                        Calibration #<?php echo e($calibration->id); ?>

                                    </small>

                                </div>


                                <button
                                    type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>

                            </div>


                            
                            
                            

                            <div class="modal-body">


                                

                                <div
                                    class="
                                        alert
                                        alert-light
                                        border
                                        mb-4
                                    "
                                >

                                    <div class="row">

                                        <div class="col-md-4">

                                            <small
                                                class="
                                                    text-muted
                                                    d-block
                                                "
                                            >
                                                Customer
                                            </small>

                                            <strong>
                                                <?php echo e($calibration
                                                        ->customer
                                                        ?->company_name
                                                    ?? 'N/A'); ?>

                                            </strong>

                                        </div>


                                        <div class="col-md-4">

                                            <small
                                                class="
                                                    text-muted
                                                    d-block
                                                "
                                            >
                                                Equipment
                                            </small>

                                            <strong>
                                                <?php echo e($calibration
                                                        ->equipment_name
                                                    ?? $calibration
                                                        ->equipment
                                                        ?->name
                                                    ?? 'N/A'); ?>

                                            </strong>

                                        </div>


                                        <div class="col-md-4">

                                            <small
                                                class="
                                                    text-muted
                                                    d-block
                                                "
                                            >
                                                Serial Number
                                            </small>

                                            <strong>
                                                <?php echo e($calibration
                                                        ->serial_number
                                                    ?? $calibration
                                                        ->equipment
                                                        ?->serial_number
                                                    ?? 'N/A'); ?>

                                            </strong>

                                        </div>

                                    </div>

                                </div>


                                
                                
                                

                                <div class="card mb-4">

                                    <div class="card-header bg-light">

                                        <strong>
                                            <i
                                                class="
                                                    fas
                                                    fa-cloud-sun
                                                    me-2
                                                    text-primary
                                                "
                                            ></i>

                                            Environmental Conditions
                                        </strong>

                                    </div>


                                    <div class="card-body">

                                        <div class="row g-3">


                                            

                                            <div class="col-md-6">

                                                <label
                                                    class="
                                                        form-label
                                                        fw-semibold
                                                    "
                                                >
                                                    Temperature Start

                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>


                                                <div class="input-group">

                                                    <input
                                                        type="number"
                                                        name="temperature_start"
                                                        class="form-control"
                                                        step="0.01"
                                                        min="-50"
                                                        max="100"
                                                        value="<?php echo e(old(
                                                                'temperature_start',
                                                                $calibration
                                                                    ->temperature_start
                                                            )); ?>"
                                                        required
                                                    >

                                                    <span class="input-group-text">
                                                        °C
                                                    </span>

                                                </div>

                                            </div>


                                            

                                            <div class="col-md-6">

                                                <label
                                                    class="
                                                        form-label
                                                        fw-semibold
                                                    "
                                                >
                                                    Temperature End

                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>


                                                <div class="input-group">

                                                    <input
                                                        type="number"
                                                        name="temperature_end"
                                                        class="form-control"
                                                        step="0.01"
                                                        min="-50"
                                                        max="100"
                                                        value="<?php echo e(old(
                                                                'temperature_end',
                                                                $calibration
                                                                    ->temperature_end
                                                            )); ?>"
                                                        required
                                                    >

                                                    <span class="input-group-text">
                                                        °C
                                                    </span>

                                                </div>

                                            </div>


                                            

                                            <div class="col-md-6">

                                                <label
                                                    class="
                                                        form-label
                                                        fw-semibold
                                                    "
                                                >
                                                    Relative Humidity Start

                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>


                                                <div class="input-group">

                                                    <input
                                                        type="number"
                                                        name="humidity_start"
                                                        class="form-control"
                                                        step="0.01"
                                                        min="0"
                                                        max="100"
                                                        value="<?php echo e(old(
                                                                'humidity_start',
                                                                $calibration
                                                                    ->humidity_start
                                                            )); ?>"
                                                        required
                                                    >

                                                    <span class="input-group-text">
                                                        %
                                                    </span>

                                                </div>

                                            </div>


                                            

                                            <div class="col-md-6">

                                                <label
                                                    class="
                                                        form-label
                                                        fw-semibold
                                                    "
                                                >
                                                    Relative Humidity End

                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>


                                                <div class="input-group">

                                                    <input
                                                        type="number"
                                                        name="humidity_end"
                                                        class="form-control"
                                                        step="0.01"
                                                        min="0"
                                                        max="100"
                                                        value="<?php echo e(old(
                                                                'humidity_end',
                                                                $calibration
                                                                    ->humidity_end
                                                            )); ?>"
                                                        required
                                                    >

                                                    <span class="input-group-text">
                                                        %
                                                    </span>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                
                                
                                

                                <div class="card">

                                    <div class="card-header bg-light">

                                        <strong>

                                            <i
                                                class="
                                                    fas
                                                    fa-bolt
                                                    me-2
                                                    text-warning
                                                "
                                            ></i>

                                            Supply Conditions

                                        </strong>

                                    </div>


                                    <div class="card-body">

                                        <div class="row g-3">


                                            

                                            <div class="col-md-6">

                                                <label
                                                    class="
                                                        form-label
                                                        fw-semibold
                                                    "
                                                >
                                                    Supply Voltage

                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>


                                                <div class="input-group">

                                                    <input
                                                        type="number"
                                                        name="supply_voltage"
                                                        class="form-control"
                                                        step="0.01"
                                                        min="0"
                                                        max="1000"
                                                        value="<?php echo e(old(
                                                                'supply_voltage',
                                                                $calibration
                                                                    ->supply_voltage
                                                                ?? 230
                                                            )); ?>"
                                                        required
                                                    >

                                                    <span class="input-group-text">
                                                        V
                                                    </span>

                                                </div>

                                            </div>


                                            

                                            <div class="col-md-6">

                                                <label
                                                    class="
                                                        form-label
                                                        fw-semibold
                                                    "
                                                >
                                                    Supply Frequency

                                                    <span class="text-danger">
                                                        *
                                                    </span>
                                                </label>


                                                <div class="input-group">

                                                    <input
                                                        type="number"
                                                        name="supply_frequency"
                                                        class="form-control"
                                                        step="0.01"
                                                        min="0"
                                                        max="1000"
                                                        value="<?php echo e(old(
                                                                'supply_frequency',
                                                                $calibration
                                                                    ->supply_frequency
                                                                ?? 50
                                                            )); ?>"
                                                        required
                                                    >

                                                    <span class="input-group-text">
                                                        Hz
                                                    </span>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                

                                <?php if(
                                    $calibration->test_parameters_recorded_at
                                ): ?>

                                    <div
                                        class="
                                            alert
                                            alert-success
                                            mt-4
                                            mb-0
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-check-circle
                                                me-2
                                            "
                                        ></i>

                                        Last recorded:

                                        <strong>
                                            <?php echo e($calibration
                                                    ->test_parameters_recorded_at
                                                    ->format(
                                                        'd/m/Y H:i'
                                                    )); ?>

                                        </strong>


                                        <?php if(
                                            $calibration
                                                ->testParametersRecordedBy
                                        ): ?>

                                            by

                                            <strong>
                                                <?php echo e($calibration
                                                        ->testParametersRecordedBy
                                                        ->name); ?>

                                            </strong>

                                        <?php endif; ?>

                                    </div>

                                <?php endif; ?>

                            </div>


                            
                            
                            

                            <div class="modal-footer">

                                <button
                                    type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal"
                                >
                                    <i class="fas fa-times me-1"></i>
                                    Cancel
                                </button>


                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >
                                    <i class="fas fa-save me-1"></i>

                                    Save Test Parameters
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php endif; ?>
<!-- ============================================ -->
<!-- TEST RESULT MODALS                           -->
<!-- ============================================ -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('perform_calibrations')): ?>
    <?php $__currentLoopData = $calibrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $calibration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $isAssignedTech = !empty($calibration->performed_by)
                && (int) $currentUser?->id === (int) $calibration->performed_by;
            $canRecordTests =
                $isAssignedTech
                && $calibration->status === 'testing'
                && $calibration->hasEnvironmentalConditions();
            $equipmentTypeName = $calibration->equipmentType?->name
                ?? $calibration->equipment_type
                ?? 'Energy Meter';
            $normalizedEquipmentType = \Illuminate\Support\Str::slug($equipmentTypeName, '_');
            $equipmentTypeCode = match (true) {
                str_contains($normalizedEquipmentType, 'current_transformer') || $normalizedEquipmentType === 'ct' => 'current_transformer',
                str_contains($normalizedEquipmentType, 'voltage_transformer') || str_contains($normalizedEquipmentType, 'potential_transformer') || in_array($normalizedEquipmentType, ['vt', 'pt'], true) => 'voltage_transformer',
                str_contains($normalizedEquipmentType, 'reference_meter') => 'reference_meter',
                str_contains($normalizedEquipmentType, 'test_bench') => 'meter_test_bench',
                str_contains($normalizedEquipmentType, 'portable') => 'portable_test_unit',
                str_contains($normalizedEquipmentType, 'clamp') => 'clamp_meter',
                str_contains($normalizedEquipmentType, 'multimeter') => 'multimeter',
                str_contains($normalizedEquipmentType, 'power_analyzer') => 'power_analyzer',
                str_contains($normalizedEquipmentType, 'insulation') => 'insulation_tester',
                default => 'energy_meter',
            };
        ?>
        <?php if($canRecordTests): ?>
            <div class="modal fade calibration-test-modal" id="testModal<?php echo e($calibration->id); ?>" tabindex="-1" aria-labelledby="testModalLabel<?php echo e($calibration->id); ?>" aria-hidden="true">
                <div class="modal-dialog modal-xl"><div class="modal-content">
                    <form action="<?php echo e(route('calibrations.tests.store', $calibration)); ?>" method="POST" class="calibration-test-form" data-calibration-id="<?php echo e($calibration->id); ?>" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="modal-header test-modal-header"><div><h5 class="modal-title" id="testModalLabel<?php echo e($calibration->id); ?>"><i class="fas fa-flask me-2"></i>Record Laboratory Test Result</h5><small>Calibration #<?php echo e($calibration->id); ?> · <?php echo e($equipmentTypeName); ?></small></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">

        <div class="calibration-context-card mb-4">
            <div class="context-item"><span class="context-icon"><i class="fas fa-building"></i></span><div><small>Customer</small><strong><?php echo e($calibration->customer?->company_name ?? 'N/A'); ?></strong></div></div>
            <div class="context-item"><span class="context-icon"><i class="fas fa-microscope"></i></span><div><small>Equipment</small><strong><?php echo e($calibration->equipment_name ?? $calibration->equipment?->name ?? 'N/A'); ?></strong></div></div>
            <div class="context-item"><span class="context-icon"><i class="fas fa-barcode"></i></span><div><small>Serial Number</small><strong><?php echo e($calibration->serial_number ?? $calibration->equipment?->serial_number ?? 'N/A'); ?></strong></div></div>
            <div class="context-item"><span class="context-icon"><i class="fas fa-layer-group"></i></span><div><small>Equipment Type</small><strong><?php echo e($equipmentTypeName); ?></strong></div></div>
        </div>

        <div class="test-workflow mb-4" aria-label="Test result entry steps">
            <button type="button" class="workflow-step active" data-step-target="1"><span>1</span><small>Test details</small></button>
            <div class="workflow-line"></div>
            <button type="button" class="workflow-step" data-step-target="2"><span>2</span><small>Measurements</small></button>
            <div class="workflow-line"></div>
            <button type="button" class="workflow-step" data-step-target="3"><span>3</span><small>Calculation</small></button>
            <div class="workflow-line"></div>
            <button type="button" class="workflow-step" data-step-target="4"><span>4</span><small>Review & save</small></button>
        </div>

        <section class="wizard-panel active" data-step="1">
            <div class="test-entry-section">
                <div class="section-heading"><span class="section-heading-icon"><i class="fas fa-clipboard-list"></i></span><div><h6>Equipment-based test selection</h6><p>Select a category and standard test applicable to this equipment type.</p></div></div>
                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Equipment type</label>
                        <input type="text" class="form-control" value="<?php echo e($equipmentTypeName); ?>" readonly>
                        <input type="hidden" name="equipment_type" class="equipment-type-field" value="<?php echo e($equipmentTypeCode); ?>">
                    </div>
                    <div class="col-lg-4">
                        <label for="testCategory<?php echo e($calibration->id); ?>" class="form-label">Test category <span class="text-danger">*</span></label>
                        <select id="testCategory<?php echo e($calibration->id); ?>" name="test_category" class="form-select test-category-select" required><option value="">— Select category —</option></select>
                    </div>
                    <div class="col-lg-4">
                        <label for="standardTest<?php echo e($calibration->id); ?>" class="form-label">Standard test <span class="text-danger">*</span></label>
                        <select id="standardTest<?php echo e($calibration->id); ?>" class="form-select standard-test-select" required disabled><option value="">— Select category first —</option></select>
                    </div>
                    <div class="col-lg-4 custom-test-wrapper d-none">
                        <label for="customTestName<?php echo e($calibration->id); ?>" class="form-label">Custom test name <span class="text-danger">*</span></label>
                        <input type="text" id="customTestName<?php echo e($calibration->id); ?>" class="form-control custom-test-name" placeholder="Enter test name">
                    </div>
                    <div class="col-lg-4 custom-register-current-wrapper d-none">
                        <label for="customRegisterCurrent<?php echo e($calibration->id); ?>" class="form-label">Register current <span class="text-danger">*</span></label>
                        <div class="input-group"><input type="number" id="customRegisterCurrent<?php echo e($calibration->id); ?>" class="form-control custom-register-current" step="0.01" min="0"><span class="input-group-text">A</span></div>
                    </div>
                    <input type="hidden" name="test_name" class="test-name-field" required>
                    <input type="hidden" name="test_type" class="test-type-field" required>
                    <input type="hidden" name="standard_test_code" class="standard-test-code-field">
                    <input type="hidden" name="workflow_type" class="workflow-type-field">

                    <div class="col-md-4 condition-field condition-applied"><label class="form-label">Applied value</label><input type="text" name="applied_value" class="form-control applied-value-field"></div>
                    <div class="col-md-4 condition-field condition-power-factor"><label class="form-label">Power factor</label><select name="power_factor" class="form-select power-factor-field"><option value="">Not applicable</option><option value="1.0PF">1.0 Unity</option><option value="0.5LAG">0.5 Lagging</option><option value="0.5LEAD">0.5 Leading</option><option value="0.8LEAD">0.8 Leading</option></select></div>
                    <div class="col-md-4 condition-field condition-load-type"><label class="form-label">Load / range</label><input type="text" name="load_type" class="form-control load-type-field"></div>
                    <div class="col-md-4 condition-field condition-voltage"><label class="form-label">Voltage</label><input type="text" name="voltage" class="form-control voltage-field"></div>
                    <div class="col-md-4 condition-field condition-frequency"><label class="form-label">Frequency</label><input type="text" name="frequency" class="form-control frequency-field"></div>
                    <div class="col-md-4"><label class="form-label">Tolerance (%) <span class="text-danger">*</span></label><input type="number" name="tolerance" class="form-control tolerance-field" step="0.0001" min="0" value="<?php echo e(old('tolerance', 0.5)); ?>" required></div>
                </div>

                <div class="standard-reference-card mt-3"><div><small>Selected test</small><strong class="selected-test-preview">Select a standard test</strong><small class="mt-1">Reference</small><strong class="standard-reference">Reference will appear here</strong></div><i class="fas fa-scale-balanced"></i></div>

                <div class="register-fields d-none mt-3">
                    <hr><div class="section-heading compact"><span class="section-heading-icon"><i class="fas fa-calculator"></i></span><div><h6>Register test details</h6><p>Enter start and stop register readings.</p></div></div>
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Start reading (kWh) <span class="text-danger">*</span></label><input type="number" name="start_reading" class="form-control start-reading-field" step="0.0001"></div>
                        <div class="col-md-3"><label class="form-label">Stop reading (kWh) <span class="text-danger">*</span></label><input type="number" name="stop_reading" class="form-control stop-reading-field" step="0.0001"></div>
                        <div class="col-md-3"><label class="form-label">Energy difference</label><input type="number" name="energy_difference" class="form-control calculated-field energy-difference-field" step="0.0001" readonly></div>
                        <div class="col-md-3"><label class="form-label">Dial error (%)</label><input type="number" name="dial_error" class="form-control dial-error-field" step="0.0001"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="wizard-panel" data-step="2">
            <div class="test-entry-section">
                <div class="section-heading"><span class="section-heading-icon"><i class="fas fa-table-list"></i></span><div><h6 class="stage-two-title">Test measurements</h6><p class="stage-two-description">Enter measurements for the selected test.</p></div></div>

                <div class="dynamic-test-panel d-none" data-workflow-panel="trial_readings">
                    <div class="trial-progress mb-3"><div class="d-flex justify-content-between mb-1"><small>Readings entered</small><strong class="trial-count">0 / 10</strong></div><div class="progress" style="height:8px"><div class="progress-bar trial-progress-bar" style="width:0%"></div></div></div>
                    <div class="table-responsive trial-table-wrapper"><table class="table trial-readings-table mb-0"><thead><tr><th>Trial</th><th>Error (%)</th><th>Trial</th><th>Error (%)</th></tr></thead><tbody>
                        <?php for($i=1;$i<=5;$i++): ?><tr><th><?php echo e($i); ?></th><td><input type="number" name="trial_data[]" class="form-control trial-input" step="0.001"></td><th><?php echo e($i+5); ?></th><td><input type="number" name="trial_data[]" class="form-control trial-input" step="0.001"></td></tr><?php endfor; ?>
                    </tbody></table></div><div class="trial-validation-message mt-3 text-warning">Enter all 10 readings to continue.</div>
                </div>

                <div class="dynamic-test-panel d-none" data-workflow-panel="starting"><div class="row g-3"><div class="col-md-3"><label class="form-label">Applied voltage (V)</label><input type="number" name="starting_voltage" class="form-control dynamic-required starting-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Starting current (mA)</label><input type="number" name="starting_current" class="form-control dynamic-required starting-current" step="0.001"></div><div class="col-md-3"><label class="form-label">Pulse detected</label><select name="pulse_detected" class="form-select dynamic-required starting-pulse"><option value="">Select</option><option value="yes">Yes</option><option value="no">No</option></select></div><div class="col-md-3"><label class="form-label">Time taken (s)</label><input type="number" name="starting_time" class="form-control starting-time" step="0.01"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="creep"><div class="row g-3"><div class="col-md-3"><label class="form-label">Applied voltage (V)</label><input type="number" name="creep_voltage" class="form-control dynamic-required creep-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Observation time (min)</label><input type="number" name="creep_time" class="form-control dynamic-required creep-time" step="0.01"></div><div class="col-md-3"><label class="form-label">Pulse count</label><input type="number" name="creep_pulses" class="form-control dynamic-required creep-pulses" step="1"></div><div class="col-md-3"><label class="form-label">Maximum allowed pulses</label><input type="number" name="creep_max_pulses" class="form-control dynamic-required creep-max-pulses" step="1" value="0"></div></div></div>

                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_ratio"><div class="row g-3"><div class="col-md-3"><label class="form-label">Rated primary current (A)</label><input type="number" name="ct_rated_primary" class="form-control dynamic-required ct-rated-primary" step="0.001"></div><div class="col-md-3"><label class="form-label">Rated secondary current (A)</label><input type="number" name="ct_rated_secondary" class="form-control dynamic-required ct-rated-secondary" step="0.001"></div><div class="col-md-3"><label class="form-label">Applied primary current (A)</label><input type="number" name="ct_applied_primary" class="form-control dynamic-required ct-applied-primary" step="0.001"></div><div class="col-md-3"><label class="form-label">Measured secondary current (A)</label><input type="number" name="ct_measured_secondary" class="form-control dynamic-required ct-measured-secondary" step="0.0001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_accuracy"><div class="row g-3"><div class="col-md-3"><label class="form-label">Rated primary current (A)</label><input type="number" name="ct_accuracy_rated_primary" class="form-control dynamic-required ct-accuracy-rated-primary" step="0.001"></div><div class="col-md-3"><label class="form-label">Rated secondary current (A)</label><input type="number" name="ct_accuracy_rated_secondary" class="form-control dynamic-required ct-accuracy-rated-secondary" step="0.001"></div><div class="col-md-3"><label class="form-label">Measured primary current (A)</label><input type="number" name="ct_accuracy_primary" class="form-control dynamic-required ct-accuracy-primary" step="0.0001"></div><div class="col-md-3"><label class="form-label">Measured secondary current (A)</label><input type="number" name="ct_accuracy_secondary" class="form-control dynamic-required ct-accuracy-secondary" step="0.0001"></div><div class="col-md-3"><label class="form-label">Phase displacement (min)</label><input type="number" name="phase_displacement" class="form-control ct-phase" step="0.001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_excitation"><div class="row g-3"><div class="col-md-3"><label class="form-label">Applied voltage (V)</label><input type="number" name="excitation_voltage" class="form-control dynamic-required excitation-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Excitation current (mA)</label><input type="number" name="excitation_current" class="form-control dynamic-required excitation-current" step="0.001"></div><div class="col-md-3"><label class="form-label">Knee-point voltage (V)</label><input type="number" name="knee_point_voltage" class="form-control dynamic-required knee-point-voltage" step="0.01"></div><div class="col-md-3"><label class="form-label">Minimum knee point (V)</label><input type="number" name="minimum_knee_point" class="form-control dynamic-required minimum-knee-point" step="0.01"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="ct_burden"><div class="row g-3"><div class="col-md-4"><label class="form-label">Rated burden (VA)</label><input type="number" name="rated_burden" class="form-control dynamic-required rated-burden" step="0.01"></div><div class="col-md-4"><label class="form-label">Measured burden (VA)</label><input type="number" name="measured_burden" class="form-control dynamic-required measured-burden" step="0.01"></div><div class="col-md-4"><label class="form-label">Phase displacement (min)</label><input type="number" name="burden_phase" class="form-control burden-phase" step="0.001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="vt_ratio"><div class="row g-3"><div class="col-md-3"><label class="form-label">Rated primary voltage (V)</label><input type="number" name="vt_rated_primary" class="form-control dynamic-required vt-rated-primary" step="0.01"></div><div class="col-md-3"><label class="form-label">Rated secondary voltage (V)</label><input type="number" name="vt_rated_secondary" class="form-control dynamic-required vt-rated-secondary" step="0.01"></div><div class="col-md-3"><label class="form-label">Applied primary voltage (V)</label><input type="number" name="vt_applied_primary" class="form-control dynamic-required vt-applied-primary" step="0.01"></div><div class="col-md-3"><label class="form-label">Measured secondary voltage (V)</label><input type="number" name="vt_measured_secondary" class="form-control dynamic-required vt-measured-secondary" step="0.0001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="comparison"><div class="row g-3"><div class="col-md-3"><label class="form-label">Reference value</label><input type="number" name="reference_value" class="form-control dynamic-required comparison-reference" step="0.000001"></div><div class="col-md-3"><label class="form-label">Indicated value</label><input type="number" name="indicated_value" class="form-control dynamic-required comparison-indicated" step="0.000001"></div><div class="col-md-3"><label class="form-label">Range / unit</label><input type="text" name="measurement_range" class="form-control dynamic-required"></div><div class="col-md-3"><label class="form-label">Resolution</label><input type="number" name="instrument_resolution" class="form-control comparison-resolution" step="0.000001"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="resistance"><div class="row g-3"><div class="col-md-4"><label class="form-label">Reference resistance (Ω)</label><input type="number" name="reference_resistance" class="form-control dynamic-required resistance-reference" step="0.0001"></div><div class="col-md-4"><label class="form-label">Measured resistance (Ω)</label><input type="number" name="measured_resistance" class="form-control dynamic-required resistance-measured" step="0.0001"></div><div class="col-md-4"><label class="form-label">Temperature (°C)</label><input type="number" name="resistance_temperature" class="form-control" step="0.1"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="insulation"><div class="row g-3"><div class="col-md-4"><label class="form-label">Test voltage (V DC)</label><input type="number" name="insulation_test_voltage" class="form-control dynamic-required insulation-voltage" step="1"></div><div class="col-md-4"><label class="form-label">Measured resistance (MΩ)</label><input type="number" name="insulation_measured_resistance" class="form-control dynamic-required insulation-resistance" step="0.01"></div><div class="col-md-4"><label class="form-label">Minimum acceptable (MΩ)</label><input type="number" name="insulation_minimum" class="form-control dynamic-required insulation-minimum" step="0.01"></div></div></div>
                <div class="dynamic-test-panel d-none" data-workflow-panel="custom"><div class="row g-3"><div class="col-md-4"><label class="form-label">Reference value</label><input type="number" name="custom_reference_value" class="form-control dynamic-required custom-reference" step="0.000001"></div><div class="col-md-4"><label class="form-label">Measured value</label><input type="number" name="custom_measured_value" class="form-control dynamic-required custom-measured" step="0.000001"></div><div class="col-md-4"><label class="form-label">Unit</label><input type="text" name="custom_unit" class="form-control dynamic-required"></div></div></div>
            </div>
        </section>

        <section class="wizard-panel" data-step="3">
            <div class="test-entry-section">
                <div class="section-heading"><span class="section-heading-icon"><i class="fas fa-square-root-variable"></i></span><div><h6 class="stage-three-title">Calculation and decision</h6><p class="stage-three-description">Review the calculated result.</p></div></div>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label dynamic-result-label">Calculated error (%)</label><input type="number" name="error_value" class="form-control calculated-field error-value-field" step="0.000001" readonly></div>
                    <div class="col-md-3 standard-deviation-wrapper d-none"><label class="form-label">Standard deviation</label><input type="number" name="standard_deviation" class="form-control calculated-field standard-deviation-field" step="0.000001" readonly></div>
                    <div class="col-md-3 uncertainty-wrapper d-none"><label class="form-label">Expanded uncertainty (%)</label><input type="number" name="uncertainty" class="form-control calculated-field uncertainty-field" step="0.000001" readonly></div>
                    <div class="col-md-3"><label class="form-label">Result <span class="text-danger">*</span></label><select name="result" class="form-select result-field" required><option value="">Select Result</option><option value="PASS">PASS</option><option value="FAIL">FAIL</option></select></div>
                </div>
                <div class="calculation-breakdown mt-3"><div><small class="metric-one-label">Measured</small><strong class="u1-value">—</strong></div><div><small class="metric-two-label">Reference</small><strong class="u2-value">—</strong></div><div><small class="metric-three-label">Deviation</small><strong class="u3-value">—</strong></div><div><small>Tolerance</small><strong class="dynamic-tolerance-display">—</strong></div></div>
                <div class="mt-3"><button type="button" class="btn btn-info calculate-button"><i class="fas fa-calculator me-1"></i>Recalculate</button><span class="calculation-message ms-2"></span></div>
                <div class="result-summary-card result-pending mt-4"><div class="result-status"><span class="result-status-icon"><i class="fas fa-hourglass-half"></i></span><div><small>Test decision</small><strong class="result-status-text">Awaiting calculation</strong></div></div><div class="result-metrics"><div><small>Error / margin</small><strong class="summary-error">—</strong></div><div><small>Additional metric</small><strong class="summary-uncertainty">—</strong></div><div><small>Tolerance</small><strong class="summary-tolerance">—</strong></div></div></div>
            </div>
        </section>

        <section class="wizard-panel" data-step="4">
            <div class="row g-4"><div class="col-lg-8"><div class="test-entry-section h-100"><div class="section-heading"><span class="section-heading-icon"><i class="fas fa-clipboard-check"></i></span><div><h6>Review result</h6><p>Confirm all details before saving.</p></div></div><div class="review-grid"><div><small>Equipment type</small><strong class="review-equipment-type">—</strong></div><div><small>Test category</small><strong class="review-category">—</strong></div><div><small>Test name</small><strong class="review-test-name">—</strong></div><div><small>Test type</small><strong class="review-test-type">—</strong></div><div><small>Applied value</small><strong class="review-applied-value">—</strong></div><div><small>Conditions</small><strong class="review-voltage-frequency">—</strong></div><div><small class="review-readings-label">Measurements</small><strong class="review-trials">—</strong></div><div><small>Error / margin</small><strong class="review-error">—</strong></div><div><small>Uncertainty</small><strong class="review-uncertainty">—</strong></div><div><small>Result</small><strong class="review-result">—</strong></div></div></div></div><div class="col-lg-4"><div class="test-entry-section h-100"><div class="section-heading"><span class="section-heading-icon"><i class="fas fa-note-sticky"></i></span><div><h6>Technician notes</h6><p>Add observations or abnormalities.</p></div></div><textarea name="notes" class="form-control" rows="10"><?php echo e(old('notes')); ?></textarea></div></div></div>
        </section>


                        </div>
<div class="modal-footer test-modal-footer"><div class="footer-calculation-status"><i class="fas fa-circle-info me-1"></i><span>Complete step 1 to continue.</span></div><div class="d-flex gap-2"><button type="button" class="btn btn-outline-secondary previous-step-button d-none"><i class="fas fa-arrow-left me-1"></i>Previous</button><button type="button" class="btn btn-primary next-step-button">Next<i class="fas fa-arrow-right ms-1"></i></button><button type="submit" class="btn btn-success save-test-button d-none"><span class="save-label"><i class="fas fa-check me-1"></i>Save Test Result</span><span class="saving-label d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving...</span></button></div></div>

                    </form>
                </div></div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<!-- COMPLETE WITHOUT TEST RESULTS MODALS         -->
<!-- ============================================ -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('perform_calibrations')): ?>
    <?php $__currentLoopData = $calibrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $calibration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $isAssignedTech =
                $calibration->performed_by
                && (int) auth()->id()
                    === (int) $calibration->performed_by;

            $canCompleteWithoutTests =
                $isAssignedTech
                && in_array(
                    $calibration->status,
                    ['assigned', 'in_progress', 'testing'],
                    true
                );
        ?>

        
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<!-- ============================================ -->
<!-- ASSIGN TECHNICIAN MODALS                    -->
<!-- ============================================ -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign_calibrations')): ?>
    <?php $__currentLoopData = $calibrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $calibration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(in_array($calibration->status, ['pending', 'paid'])): ?>
            <div class="modal fade" id="assignModal<?php echo e($calibration->id); ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="<?php echo e(route('calibrations.assign', $calibration)); ?>" method="POST">
                            <?php echo csrf_field(); ?>

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-user-check me-1"></i>
                                    Assign Calibration #<?php echo e($calibration->id); ?>

                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Select Technician <span class="text-danger">*</span>
                                    </label>
                                    <select name="technician_id" class="form-select" required>
                                        <option value="">— Select Technician —</option>
                                        <?php $__currentLoopData = $technicians ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($id); ?>" <?php echo e($calibration->performed_by == $id ? 'selected' : ''); ?>>
                                                <?php echo e($name); ?>

                                                <?php if($calibration->performed_by == $id): ?>
                                                    (Current)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Assignment Notes</label>
                                    <textarea name="assignment_notes" class="form-control" rows="2"
                                              placeholder="Any instructions for the technician..."></textarea>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Equipment:</strong> <?php echo e($calibration->equipment_name ?? $calibration->equipment->name ?? 'N/A'); ?><br>
                                    <strong>Customer:</strong> <?php echo e(optional($calibration->customer)->company_name ?? 'N/A'); ?>

                                    <?php if($calibration->performedBy): ?>
                                        <br>
                                        <strong>Currently Assigned:</strong> <?php echo e($calibration->performedBy->name); ?>

                                    <?php endif; ?>
                                </div>

                                <div class="alert alert-warning mt-2">
                                    <strong>Current Status:</strong>
                                    <span class="badge bg-<?php echo e($statusConfig['color']); ?>">
    <i class="fas <?php echo e($statusConfig['icon']); ?> me-1"></i>
    <?php echo e($statusConfig['label']); ?>

</span>
                                    <br>
                                    <strong>After Assignment:</strong>
                                    <span class="badge bg-primary">Assigned</span>
                                    <br>
                                    <small>Assigned Date will be set to: <?php echo e(now()->format('Y-m-d H:i')); ?></small>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-check me-1"></i> Assign Technician
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<!-- ============================================ -->
<!-- REJECT MODALS                              -->
<!-- ============================================ -->
<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reject_calibrations')): ?>
    <?php $__currentLoopData = $calibrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $calibration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(in_array($calibration->status, ['pending', 'assigned', 'testing'])): ?>
            <div class="modal fade" id="rejectModal<?php echo e($calibration->id); ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?php echo e($calibration->id); ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="<?php echo e(route('calibrations.reject', $calibration)); ?>" method="POST">
                            <?php echo csrf_field(); ?>

                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectModalLabel<?php echo e($calibration->id); ?>">
                                    <i class="fas fa-times-circle text-danger me-1"></i>
                                    Reject Calibration #<?php echo e($calibration->id); ?>

                                </h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Please provide a reason for rejecting this calibration.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Reason for rejection <span class="text-danger">*</span>
                                    </label>

                                    <textarea
                                        name="reason"
                                        class="form-control"
                                        rows="3"
                                        required
                                        placeholder="Explain why this calibration is being rejected..."
                                    ></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancel
                                </button>

                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times me-1"></i>
                                    Reject
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* ======================================================
       CALIBRATIONS INDEX PAGE
       ====================================================== */

    .calibrations-index-page .clickable-row {
        transition: background-color 0.2s ease;
    }

    .calibrations-index-page .clickable-row:hover {
        background-color: rgba(13, 110, 253, 0.05) !important;
    }

    .calibrations-index-page .card.text-white .card-body {
        padding: 0.75rem 1rem;
    }

    .calibrations-index-page .card.text-white .card-title {
        margin-bottom: 0.25rem;
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .calibrations-index-page .card.text-white h3 {
        margin-bottom: 0;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .calibrations-index-page .btn-group .btn {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
    }

    .calibrations-index-page .table td {
        vertical-align: middle;
    }

    .calibrations-index-page .badge {
        padding: 0.35rem 0.65rem;
        font-size: 0.8rem;
    }

    .calibrations-index-page code {
        padding: 2px 6px;
        border-radius: 4px;
        background: #f4f4f4;
        font-size: 0.85rem;
    }

    /* ======================================================
       CALIBRATIONS TABLE SCROLLING
       ====================================================== */

    #tableContainer {
        max-height: 600px;
        overflow-x: auto;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }

    #calibrationsTable {
        width: 100%;
        min-width: 1800px;
    }

    .table-scroll-top,
    .table-scroll-bottom {
        display: none;
        padding: 4px 8px;
        background: #f8f9fa;
    }

    .table-scroll-top {
        border-bottom: 1px solid #dee2e6;
    }

    .table-scroll-bottom {
        border-top: 1px solid #dee2e6;
    }

    .table-scroll-top .scroll-track,
    .table-scroll-bottom .scroll-track {
        position: relative;
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
        cursor: pointer;
    }

    .table-scroll-top .scroll-thumb,
    .table-scroll-bottom .scroll-thumb {
        position: absolute;
        top: 0;
        left: 0;
        min-width: 40px;
        height: 100%;
        border-radius: 4px;
        background: #6c757d;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .table-scroll-top .scroll-thumb:hover,
    .table-scroll-bottom .scroll-thumb:hover {
        background: #495057;
    }

    .card-body.has-horizontal-scroll .table-scroll-top,
    .card-body.has-horizontal-scroll .table-scroll-bottom {
        display: block;
    }

    /* ======================================================
       STICKY TABLE COLUMNS
       ====================================================== */

    #calibrationsTable th:first-child,
    #calibrationsTable td:first-child {
        position: sticky;
        left: 0;
        z-index: 6;
    }

    #calibrationsTable th:nth-child(2),
    #calibrationsTable td:nth-child(2) {
        position: sticky;
        left: 50px;
        z-index: 6;
    }

    #calibrationsTable th:last-child,
    #calibrationsTable td:last-child {
        position: sticky;
        right: 0;
        z-index: 6;
    }

    #calibrationsTable thead th:first-child,
    #calibrationsTable thead th:nth-child(2),
    #calibrationsTable thead th:last-child {
        z-index: 12;
        background: #212529;
    }

    #calibrationsTable thead th:first-child,
    #calibrationsTable thead th:nth-child(2) {
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
    }

    #calibrationsTable thead th:last-child {
        box-shadow: -2px 0 4px rgba(0, 0, 0, 0.1);
    }

    #calibrationsTable tbody td:first-child,
    #calibrationsTable tbody td:nth-child(2),
    #calibrationsTable tbody td:last-child {
        background: #ffffff;
    }

    #calibrationsTable tbody td:first-child,
    #calibrationsTable tbody td:nth-child(2) {
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
    }

    #calibrationsTable tbody td:last-child {
        box-shadow: -2px 0 4px rgba(0, 0, 0, 0.05);
    }

    #calibrationsTable tbody tr:hover td:first-child,
    #calibrationsTable tbody tr:hover td:nth-child(2),
    #calibrationsTable tbody tr:hover td:last-child {
        background: #f3f7fd;
    }

    /* ======================================================
       MODAL POSITIONING BELOW FIXED NAVBAR
       ====================================================== */

    .calibration-test-modal {
        padding-top: 78px;
        padding-right: 0 !important;
    }

    .calibration-test-modal .modal-dialog {
        display: flex;
        align-items: stretch;
        width: calc(100vw - 48px);
        max-width: 1280px;
        height: calc(100vh - 100px);
        max-height: calc(100vh - 100px);
        margin: 0 auto 20px;
        transform: none !important;
    }

    .calibration-test-modal .modal-content {
        display: flex;
        flex-direction: column;
        width: 100%;
        height: 100%;
        min-height: 0;
        overflow: hidden;
        border: 0;
        border-radius: 16px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
    }

    .calibration-test-modal .calibration-test-form {
        display: flex;
        flex: 1 1 auto;
        min-height: 0;
        flex-direction: column;
    }

    /* ======================================================
       COMPACT TEST MODAL HEADER
       ====================================================== */

    .calibration-test-modal .test-modal-header {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        min-height: 48px;
        padding: 5px 14px;
        border-bottom: 3px solid #f2b705;
        background: linear-gradient(
            135deg,
            #071e3e,
            #0b2d5c 60%,
            #14508f
        );
        color: #ffffff;
    }

    .calibration-test-modal .test-modal-header > div {
        min-width: 0;
    }

    .calibration-test-modal .test-modal-header .modal-title {
        display: flex;
        align-items: center;
        margin: 0;
        color: #ffffff;
        font-size: 0.92rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .calibration-test-modal .test-modal-header .modal-title i {
        flex: 0 0 auto;
        margin-right: 7px !important;
        font-size: 0.88rem;
    }

    .calibration-test-modal .test-modal-header small {
        display: block;
        margin-top: 1px;
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.65rem;
        font-weight: 500;
        line-height: 1;
    }

    .calibration-test-modal .test-modal-header .btn-close {
        flex: 0 0 auto;
        width: 0.72rem;
        height: 0.72rem;
        margin: 0;
        padding: 0.38rem;
        opacity: 0.8;
        transform: scale(0.8);
    }

    .calibration-test-modal .test-modal-header .btn-close:hover,
    .calibration-test-modal .test-modal-header .btn-close:focus {
        opacity: 1;
    }

    /* ======================================================
       MODAL BODY & FOOTER
       ====================================================== */

    .calibration-test-modal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        padding: 10px 16px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .calibration-test-modal .test-modal-footer {
        position: relative;
        z-index: 20;
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        min-height: 58px;
        margin-top: auto;
        padding: 8px 16px;
        border-top: 1px solid #dce5f0;
        background: #ffffff;
        box-shadow: 0 -5px 15px rgba(15, 23, 42, 0.08);
    }

    .calibration-test-modal .footer-calculation-status {
        color: #68768a;
        font-size: 0.76rem;
    }

    .calibration-test-modal .save-test-button {
        min-width: 165px;
        font-weight: 800;
    }

    /* ======================================================
       CALIBRATION CONTEXT
       ====================================================== */

    .calibration-test-modal .calibration-context-card {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 7px;
        margin-bottom: 8px !important;
        padding: 7px 9px;
        border: 1px solid #dce5f0;
        border-radius: 11px;
        background: #f7f9fc;
    }

    .calibration-test-modal .context-item {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    .calibration-test-modal .context-icon,
    .calibration-test-modal .section-heading-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        border-radius: 8px;
        background: #e8f0fa;
        color: #0b2d5c;
    }

    .calibration-test-modal .context-item div {
        min-width: 0;
    }

    .calibration-test-modal .context-item small,
    .calibration-test-modal .standard-reference-card small,
    .calibration-test-modal .result-status small,
    .calibration-test-modal .result-metrics small,
    .calibration-test-modal .calculation-breakdown small,
    .calibration-test-modal .review-grid small {
        display: block;
        color: #68768a;
        font-size: 0.62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .calibration-test-modal .context-item strong {
        display: block;
        overflow: hidden;
        color: #172033;
        font-size: 0.76rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ======================================================
       WORKFLOW STEPPER
       ====================================================== */

    .calibration-test-modal .test-workflow {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        margin-bottom: 8px !important;
    }

    .calibration-test-modal .workflow-step {
        width: 88px;
        padding: 0;
        border: 0;
        background: transparent;
        text-align: center;
        cursor: pointer;
    }

    .calibration-test-modal .workflow-step span {
        width: 27px;
        height: 27px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #cad5e3;
        border-radius: 50%;
        background: #ffffff;
        color: #66758a;
        font-size: 0.7rem;
        font-weight: 800;
    }

    .calibration-test-modal .workflow-step.active span {
        border-color: #0b2d5c;
        background: #0b2d5c;
        color: #ffffff;
    }

    .calibration-test-modal .workflow-step.completed span {
        border-color: #198754;
        background: #198754;
        color: #ffffff;
    }

    .calibration-test-modal .workflow-step small {
        display: block;
        margin-top: 2px;
        color: #68768a;
        font-size: 0.64rem;
        font-weight: 700;
    }

    .calibration-test-modal .workflow-line {
        width: 50px;
        height: 2px;
        margin-top: 12px;
        background: #d4deea;
    }

    /* ======================================================
       WIZARD PANELS
       ====================================================== */

    .calibration-test-modal .wizard-panel {
        display: none;
    }

    .calibration-test-modal .wizard-panel.active {
        display: block;
        animation: wizardFadeIn 0.22s ease;
    }

    @keyframes wizardFadeIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .calibration-test-modal .test-entry-section {
        padding: 12px;
        border: 1px solid #dce5f0;
        border-radius: 12px;
        background: #ffffff;
    }

    .calibration-test-modal .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        margin-bottom: 8px;
    }

    .calibration-test-modal .section-heading.compact {
        margin-bottom: 7px;
    }

    .calibration-test-modal .section-heading h6 {
        margin: 0 0 1px;
        color: #0b2d5c;
        font-size: 0.9rem;
        font-weight: 800;
    }

    .calibration-test-modal .section-heading p {
        margin: 0;
        color: #68768a;
        font-size: 0.72rem;
    }

    /* ======================================================
       FORM CONTROLS
       ====================================================== */

    .calibration-test-modal .form-label {
        margin-bottom: 0.22rem;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .calibration-test-modal .form-control,
    .calibration-test-modal .form-select {
        min-height: 34px;
        padding: 0.3rem 0.65rem;
        font-size: 0.84rem;
    }

    .calibration-test-modal .form-text {
        margin-top: 0.18rem;
        font-size: 0.68rem;
    }

    .calibration-test-modal .calculated-field {
        background: #eef3f8 !important;
        color: #0b2d5c;
        font-weight: 800;
    }

    /* ======================================================
       STANDARD REFERENCE
       ====================================================== */

    .calibration-test-modal .standard-reference-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 10px;
        border-left: 4px solid #f2b705;
        border-radius: 8px;
        background: #fff9df;
    }

    .calibration-test-modal .standard-reference-card strong {
        display: block;
        margin-top: 2px;
        color: #614c00;
        font-size: 0.76rem;
    }

    /* ======================================================
       TRIAL READINGS
       ====================================================== */

    .calibration-test-modal .trial-progress {
        margin-bottom: 7px !important;
    }

    .calibration-test-modal .trial-table-wrapper {
        overflow-x: auto;
        border: 1px solid #dce5f0;
        border-radius: 9px;
    }

    .calibration-test-modal .trial-readings-table {
        width: 100%;
        table-layout: fixed;
    }

    .calibration-test-modal .trial-readings-table thead th {
        padding: 6px 8px;
        border-bottom: 0;
        background: #0b2d5c;
        color: #ffffff;
        font-size: 0.7rem;
    }

    .calibration-test-modal .trial-readings-table tbody th {
        width: 46px;
        padding: 4px;
        background: #f3f6fa;
        color: #0b2d5c;
        font-size: 0.76rem;
        text-align: center;
        vertical-align: middle;
    }

    .calibration-test-modal .trial-readings-table td {
        padding: 3px 6px;
    }

    .calibration-test-modal .trial-readings-table .form-control {
        min-width: 96px;
        min-height: 32px;
        height: 32px;
        padding: 3px 7px;
        font-size: 0.78rem;
        text-align: right;
    }

    .calibration-test-modal .trial-input.is-filled {
        border-color: #198754;
        background: #f0faf4;
    }

    .calibration-test-modal .trial-validation-message {
        margin-top: 6px !important;
        font-size: 0.72rem;
    }

    /* ======================================================
       CALCULATION & REVIEW
       ====================================================== */

    .calibration-test-modal .calculation-breakdown,
    .calibration-test-modal .review-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 9px;
    }

    .calibration-test-modal .calculation-breakdown > div,
    .calibration-test-modal .review-grid > div {
        padding: 9px;
        border: 1px solid #dce5f0;
        border-radius: 8px;
        background: #f7f9fc;
    }

    .calibration-test-modal .calculation-breakdown strong,
    .calibration-test-modal .review-grid strong {
        display: block;
        margin-top: 3px;
        color: #172033;
        font-size: 0.8rem;
        overflow-wrap: anywhere;
    }

    /* ======================================================
       RESULT SUMMARY
       ====================================================== */

    .calibration-test-modal .result-summary-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px;
        border: 1px solid #dce5f0;
        border-left: 6px solid #94a3b8;
        border-radius: 11px;
        background: #f8fafc;
    }

    .calibration-test-modal .result-summary-card.result-pass {
        border-left-color: #198754;
        background: #eaf8f0;
    }

    .calibration-test-modal .result-summary-card.result-fail {
        border-left-color: #dc3545;
        background: #fdeced;
    }

    .calibration-test-modal .result-status {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .calibration-test-modal .result-status-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        font-size: 0.95rem;
    }

    .calibration-test-modal .result-pass .result-status-icon {
        background: #198754;
        color: #ffffff;
    }

    .calibration-test-modal .result-fail .result-status-icon {
        background: #dc3545;
        color: #ffffff;
    }

    .calibration-test-modal .result-status-text {
        display: block;
        color: #334155;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .calibration-test-modal .result-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(82px, 1fr));
        gap: 9px;
    }

    .calibration-test-modal .result-metrics > div {
        padding: 6px 9px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.72);
        text-align: center;
    }

    .calibration-test-modal .result-metrics strong {
        display: block;
        margin-top: 2px;
        color: #172033;
        font-size: 0.82rem;
    }

    /* ======================================================
       RESPONSIVE
       ====================================================== */

    @media (max-width: 1200px) {
        #calibrationsTable {
            min-width: 1400px;
        }
    }

    @media (max-width: 991.98px) {
        .calibration-test-modal {
            padding-top: 68px;
        }

        .calibration-test-modal .modal-dialog {
            width: calc(100vw - 24px);
            height: calc(100vh - 82px);
            max-height: calc(100vh - 82px);
        }

        .calibration-test-modal .calibration-context-card {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .calibration-test-modal .calculation-breakdown,
        .calibration-test-modal .review-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .calibration-test-modal .result-summary-card {
            align-items: stretch;
            flex-direction: column;
        }

        .calibration-test-modal .result-metrics {
            width: 100%;
        }
    }

    @media (max-height: 800px) and (min-width: 768px) {
        .calibration-test-modal {
            padding-top: 66px;
        }

        .calibration-test-modal .modal-dialog {
            height: calc(100vh - 78px);
            max-height: calc(100vh - 78px);
            margin: 0 auto 12px;
        }

        .calibration-test-modal .test-modal-header {
            min-height: 44px;
            padding: 4px 12px;
        }

        .calibration-test-modal .test-modal-header .modal-title {
            font-size: 0.86rem;
        }

        .calibration-test-modal .test-modal-header small {
            font-size: 0.61rem;
        }

        .calibration-test-modal .workflow-step small,
        .calibration-test-modal .section-heading p {
            display: none;
        }

        .calibration-test-modal .trial-readings-table .form-control {
            min-height: 29px;
            height: 29px;
        }
    }

    @media (max-width: 768px) {
        .calibrations-index-page h1 {
            font-size: 1.45rem;
        }

        #tableContainer {
            font-size: 0.85rem;
        }

        .calibrations-index-page .btn-group .btn {
            padding: 0.15rem 0.35rem;
            font-size: 0.7rem;
        }

        .calibrations-index-page .card.text-white h3 {
            font-size: 1.3rem;
        }

        .table-scroll-top,
        .table-scroll-bottom {
            display: none !important;
        }

        #calibrationsTable th:first-child,
        #calibrationsTable td:first-child {
            left: 0;
            width: 40px;
            min-width: 40px;
        }

        #calibrationsTable th:nth-child(2),
        #calibrationsTable td:nth-child(2) {
            left: 40px;
            min-width: 100px;
        }

        #calibrationsTable th:last-child,
        #calibrationsTable td:last-child {
            right: 0;
            min-width: 120px;
        }
    }

    @media (max-width: 575.98px) {
        .calibration-test-modal {
            padding-top: 58px;
        }

        .calibration-test-modal .modal-dialog {
            width: calc(100vw - 12px);
            height: calc(100vh - 66px);
            max-height: calc(100vh - 66px);
            margin: 0 auto 8px;
        }

        .calibration-test-modal .calibration-context-card,
        .calibration-test-modal .calculation-breakdown,
        .calibration-test-modal .review-grid,
        .calibration-test-modal .result-metrics {
            grid-template-columns: 1fr;
        }

        .calibration-test-modal .test-workflow {
            overflow-x: auto;
            justify-content: flex-start;
        }

        .calibration-test-modal .workflow-line {
            width: 35px;
            min-width: 35px;
        }

        .calibration-test-modal .test-modal-footer {
            align-items: stretch;
            flex-direction: column;
            gap: 8px;
        }

        .calibration-test-modal .test-modal-footer > div:last-child {
            width: 100%;
        }

        .calibration-test-modal .previous-step-button,
        .calibration-test-modal .next-step-button,
        .calibration-test-modal .save-test-button {
            flex: 1 1 auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .calibration-test-modal *,
        .calibration-test-modal *::before,
        .calibration-test-modal *::after {
            animation: none !important;
            transition: none !important;
        }
    }

    .dynamic-test-panel { padding: 4px 0; }
    .dynamic-test-panel.d-none { display: none !important; }
    .calculated-field { background: #eef3f8 !important; font-weight: 700; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const laboratoryTestConfig = {
        energy_meter:{label:'Energy Meter',categories:{accuracy:{label:'Accuracy and Uncertainty',tests:{uncertainty_low_load:{name:'UNCERTAINTY OF MEASUREMENT AT 0.05 Ib; 1.0 PF',type:'accuracy',appliedValue:'0.05 Ib',powerFactor:'1.0PF',loadType:'Low load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'},uncertainty_base_load:{name:'UNCERTAINTY OF MEASUREMENT AT Ib; 1.0 PF',type:'accuracy',appliedValue:'Ib',powerFactor:'1.0PF',loadType:'Base load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'},uncertainty_inductive:{name:'UNCERTAINTY OF MEASUREMENT AT Ib; 0.5 LAG PF',type:'accuracy',appliedValue:'Ib',powerFactor:'0.5LAG',loadType:'Inductive load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'},uncertainty_imax:{name:'UNCERTAINTY OF MEASUREMENT AT IMAX; 1.0 PF',type:'accuracy',appliedValue:'IMAX',powerFactor:'1.0PF',loadType:'Maximum load',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'IEC 62053 series',workflow:'trial_readings'}}},register:{label:'Register Verification',tests:{register_025a:{name:'REGISTER TEST @ 0.25 A',type:'register',appliedValue:'0.25 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_1a:{name:'REGISTER TEST @ 1 A',type:'register',appliedValue:'1 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_5a:{name:'REGISTER TEST @ 5 A',type:'register',appliedValue:'5 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_20a:{name:'REGISTER TEST @ 20 A',type:'register',appliedValue:'20 A',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_imax:{name:'REGISTER TEST @ IMAX',type:'register',appliedValue:'IMAX',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Register verification procedure',workflow:'register'},register_custom:{name:'',type:'register',appliedValue:'',powerFactor:'1.0PF',loadType:'Register',voltage:'230V',frequency:'50Hz',tolerance:.5,reference:'Custom register verification',workflow:'register',customCurrent:true}}},functional:{label:'Functional Tests',tests:{starting_current:{name:'STARTING CURRENT TEST',type:'starting',appliedValue:'',powerFactor:'1.0PF',loadType:'Starting current',voltage:'230V',frequency:'50Hz',tolerance:0,reference:'Starting-current procedure',workflow:'starting'},creep:{name:'NO-LOAD / CREEP TEST',type:'creep',appliedValue:'No load',powerFactor:'1.0PF',loadType:'No load',voltage:'230V',frequency:'50Hz',tolerance:0,reference:'No-load / creep procedure',workflow:'creep'}}},custom:{label:'Other Tests',tests:{other:{name:'',type:'custom',appliedValue:'',powerFactor:'',loadType:'',voltage:'',frequency:'',tolerance:1,reference:'Laboratory procedure',workflow:'custom',customName:true}}}}},
        current_transformer:{label:'Current Transformer',categories:{accuracy:{label:'Accuracy',tests:{ct_accuracy_5:{name:'CT ACCURACY TEST @ 5% RATED CURRENT',type:'ct_accuracy',appliedValue:'5% rated current',frequency:'50Hz',tolerance:1,reference:'IEC 61869-2',workflow:'ct_accuracy'},ct_accuracy_20:{name:'CT ACCURACY TEST @ 20% RATED CURRENT',type:'ct_accuracy',appliedValue:'20% rated current',frequency:'50Hz',tolerance:1,reference:'IEC 61869-2',workflow:'ct_accuracy'},ct_accuracy_100:{name:'CT ACCURACY TEST @ 100% RATED CURRENT',type:'ct_accuracy',appliedValue:'100% rated current',frequency:'50Hz',tolerance:1,reference:'IEC 61869-2',workflow:'ct_accuracy'}}},ratio:{label:'Ratio and Polarity',tests:{ct_ratio:{name:'CT TRANSFORMATION RATIO TEST',type:'ct_ratio',tolerance:1,reference:'IEC 61869-2',workflow:'ct_ratio'}}},excitation:{label:'Excitation',tests:{ct_excitation:{name:'CT EXCITATION / KNEE-POINT TEST',type:'ct_excitation',tolerance:0,reference:'IEC 61869-2',workflow:'ct_excitation'}}},burden:{label:'Burden',tests:{ct_burden:{name:'CT BURDEN TEST',type:'ct_burden',tolerance:5,reference:'IEC 61869-2',workflow:'ct_burden'}}},resistance:{label:'Resistance and Insulation',tests:{ct_resistance:{name:'CT SECONDARY WINDING RESISTANCE',type:'resistance',tolerance:1,reference:'IEC 61869-2',workflow:'resistance'},ct_insulation:{name:'CT INSULATION RESISTANCE TEST',type:'insulation',tolerance:0,reference:'IEC 61869-2',workflow:'insulation'}}}}},
        voltage_transformer:{label:'Voltage Transformer',categories:{ratio:{label:'Ratio and Accuracy',tests:{vt_ratio:{name:'VT TRANSFORMATION RATIO TEST',type:'vt_ratio',frequency:'50Hz',tolerance:1,reference:'IEC 61869-3',workflow:'vt_ratio'},vt_accuracy:{name:'VT ACCURACY TEST',type:'vt_accuracy',frequency:'50Hz',tolerance:1,reference:'IEC 61869-3',workflow:'vt_ratio'}}},insulation:{label:'Insulation',tests:{vt_insulation:{name:'VT INSULATION RESISTANCE TEST',type:'insulation',tolerance:0,reference:'IEC 61869-3',workflow:'insulation'}}}}},
        reference_meter:{label:'Reference Meter',categories:{performance:{label:'Performance',tests:{reference_accuracy:{name:'REFERENCE METER ACCURACY VERIFICATION',type:'comparison',tolerance:.1,reference:'Laboratory comparison procedure',workflow:'comparison'},reference_stability:{name:'REFERENCE METER STABILITY TEST',type:'comparison',tolerance:.1,reference:'Laboratory stability procedure',workflow:'comparison'}}}}},
        meter_test_bench:{label:'Meter Test Bench',categories:{verification:{label:'Bench Verification',tests:{bench_voltage:{name:'METER TEST BENCH VOLTAGE VERIFICATION',type:'comparison',tolerance:.2,reference:'Bench verification procedure',workflow:'comparison'},bench_current:{name:'METER TEST BENCH CURRENT VERIFICATION',type:'comparison',tolerance:.2,reference:'Bench verification procedure',workflow:'comparison'},bench_energy:{name:'METER TEST BENCH ENERGY VERIFICATION',type:'comparison',tolerance:.2,reference:'Bench verification procedure',workflow:'comparison'},bench_frequency:{name:'METER TEST BENCH FREQUENCY VERIFICATION',type:'comparison',tolerance:.1,reference:'Bench verification procedure',workflow:'comparison'}}}}},
        portable_test_unit:{label:'Portable Test Unit',categories:{verification:{label:'Verification',tests:{portable_voltage:{name:'PORTABLE TEST UNIT VOLTAGE VERIFICATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},portable_current:{name:'PORTABLE TEST UNIT CURRENT VERIFICATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'}}}}},
        clamp_meter:{label:'Clamp Meter',categories:{current:{label:'Current Calibration',tests:{clamp_ac:{name:'CLAMP METER AC CURRENT CALIBRATION',type:'comparison',frequency:'50Hz',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},clamp_dc:{name:'CLAMP METER DC CURRENT CALIBRATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'}}}}},
        multimeter:{label:'Multimeter',categories:{electrical:{label:'Electrical Measurements',tests:{dmm_dc_voltage:{name:'MULTIMETER DC VOLTAGE CALIBRATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},dmm_ac_voltage:{name:'MULTIMETER AC VOLTAGE CALIBRATION',type:'comparison',frequency:'50Hz',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},dmm_current:{name:'MULTIMETER CURRENT CALIBRATION',type:'comparison',tolerance:1,reference:'Laboratory comparison procedure',workflow:'comparison'},dmm_resistance:{name:'MULTIMETER RESISTANCE CALIBRATION',type:'resistance',tolerance:1,reference:'Laboratory comparison procedure',workflow:'resistance'}}}}},
        power_analyzer:{label:'Power Analyzer',categories:{electrical:{label:'Electrical Accuracy',tests:{pa_voltage:{name:'POWER ANALYZER VOLTAGE ACCURACY',type:'comparison',tolerance:.5,reference:'Laboratory comparison procedure',workflow:'comparison'},pa_current:{name:'POWER ANALYZER CURRENT ACCURACY',type:'comparison',tolerance:.5,reference:'Laboratory comparison procedure',workflow:'comparison'},pa_power:{name:'POWER ANALYZER POWER ACCURACY',type:'comparison',tolerance:.5,reference:'Laboratory comparison procedure',workflow:'comparison'},pa_frequency:{name:'POWER ANALYZER FREQUENCY ACCURACY',type:'comparison',tolerance:.1,reference:'Laboratory comparison procedure',workflow:'comparison'}}}}},
        insulation_tester:{label:'Insulation Tester',categories:{insulation:{label:'Insulation Tests',tests:{insulation_output:{name:'INSULATION TESTER OUTPUT VOLTAGE VERIFICATION',type:'comparison',tolerance:5,reference:'Laboratory comparison procedure',workflow:'comparison'},insulation_resistance:{name:'INSULATION RESISTANCE VERIFICATION',type:'insulation',tolerance:0,reference:'Laboratory insulation procedure',workflow:'insulation'}}}}}
    };

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        if (window.bootstrap?.Tooltip) {
            new bootstrap.Tooltip(element);
        }
    });

    const tableContainer = document.getElementById('tableContainer');
    const table = document.getElementById('calibrationsTable');
    const topThumb = document.getElementById('scrollThumbTop');
    const bottomThumb = document.getElementById('scrollThumbBottom');

    if (tableContainer && table && topThumb && bottomThumb) {
        const cardBody = tableContainer.closest('.card-body');
        const updateScrollbars = function () {
            const maxScroll = tableContainer.scrollWidth - tableContainer.clientWidth;
            const hasScroll = maxScroll > 1;
            cardBody?.classList.toggle('has-horizontal-scroll', hasScroll);
            if (!hasScroll) return;
            const width = Math.max(40, (tableContainer.clientWidth / tableContainer.scrollWidth) * tableContainer.clientWidth);
            const left = maxScroll > 0 ? (tableContainer.scrollLeft / maxScroll) * (tableContainer.clientWidth - width) : 0;
            [topThumb, bottomThumb].forEach(function (thumb) {
                thumb.style.width = `${width}px`;
                thumb.style.left = `${left}px`;
            });
        };
        tableContainer.addEventListener('scroll', updateScrollbars, { passive: true });
        window.addEventListener('resize', updateScrollbars);
        updateScrollbars();
    }

    document.querySelectorAll('.calibration-test-form, #testForm').forEach(function(form){
        if(form.dataset.wizardReady==='1') return; form.dataset.wizardReady='1';
        let currentStep=1, highestCompletedStep=0, activeTest=null;
        const q=s=>form.querySelector(s), qa=s=>[...form.querySelectorAll(s)], num=e=>{if(!e||e.value.trim()==='')return null;const v=Number(e.value);return Number.isFinite(v)?v:null};
        const panels=qa('.wizard-panel'), steps=qa('.workflow-step'), lines=qa('.workflow-line'), dynamicPanels=qa('.dynamic-test-panel'), trials=qa('.trial-input');
        const previous=q('.previous-step-button'), next=q('.next-step-button'), save=q('.save-test-button'), footer=q('.footer-calculation-status span');
        const equipment=q('.equipment-type-field'), category=q('.test-category-select'), standard=q('.standard-test-select'), testName=q('.test-name-field'), testType=q('.test-type-field'), testCode=q('.standard-test-code-field'), workflowField=q('.workflow-type-field');
        const customNameWrap=q('.custom-test-wrapper'), customName=q('.custom-test-name'), customCurrentWrap=q('.custom-register-current-wrapper'), customCurrent=q('.custom-register-current');
        const applied=q('.applied-value-field'), pf=q('.power-factor-field'), load=q('.load-type-field'), voltage=q('.voltage-field'), frequency=q('.frequency-field'), tolerance=q('.tolerance-field'), reference=q('.standard-reference'), preview=q('.selected-test-preview');
        const registerFields=q('.register-fields'), start=q('.start-reading-field'), stop=q('.stop-reading-field'), difference=q('.energy-difference-field'), dial=q('.dial-error-field');
        const error=q('.error-value-field'), sd=q('.standard-deviation-field'), uncertainty=q('.uncertainty-field'), result=q('.result-field'), calcButton=q('.calculate-button');
        const workflow=()=>activeTest?.workflow||'custom';
        function config(){return laboratoryTestConfig[equipment.value]||laboratoryTestConfig.energy_meter}
        function fillCategories(){category.innerHTML='<option value="">— Select category —</option>';Object.entries(config().categories).forEach(([k,v])=>category.add(new Option(v.label,k)));standard.innerHTML='<option value="">— Select category first —</option>';standard.disabled=true}
        function fillTests(){const c=config().categories[category.value];standard.innerHTML='<option value="">— Select standard test —</option>';activeTest=null;resetSelection();if(!c){standard.disabled=true;return}Object.entries(c.tests).forEach(([k,v])=>standard.add(new Option(v.name||'Other / Custom Test',k)));standard.disabled=false}
        function resetSelection(){testName.value='';testType.value='';testCode.value='';workflowField.value='';[applied,load,voltage,frequency].forEach(e=>e.value='');pf.value='';preview.textContent='Select a standard test';reference.textContent='Reference will appear here';customNameWrap.classList.add('d-none');customCurrentWrap.classList.add('d-none');registerFields.classList.add('d-none');customName.required=false;customCurrent.required=false;setRegisterRequired(false);updateDynamicPanel()}
        function applyTest(){const c=config().categories[category.value];activeTest=c?.tests?.[standard.value]||null;if(!activeTest){resetSelection();return}testName.value=activeTest.name||'';testType.value=activeTest.type||'';testCode.value=standard.value;workflowField.value=activeTest.workflow;applied.value=activeTest.appliedValue||'';pf.value=activeTest.powerFactor||'';load.value=activeTest.loadType||'';voltage.value=activeTest.voltage||'';frequency.value=activeTest.frequency||'';tolerance.value=activeTest.tolerance??.5;preview.textContent=activeTest.name||'Custom laboratory test';reference.textContent=activeTest.reference||'Laboratory procedure';customNameWrap.classList.toggle('d-none',!activeTest.customName);customCurrentWrap.classList.toggle('d-none',!activeTest.customCurrent);customName.required=!!activeTest.customName;customCurrent.required=!!activeTest.customCurrent;registerFields.classList.toggle('d-none',workflow()!=='register');setRegisterRequired(workflow()==='register');updateDynamicPanel();updateWorkflowLabels()}
        function setRegisterRequired(on){[start,stop].forEach(e=>{if(e)e.required=on})}
        function updateRegister(){const a=num(start),b=num(stop);difference.value=a!==null&&b!==null?(b-a).toFixed(4):''}
        function updateDynamicPanel(){dynamicPanels.forEach(p=>{p.classList.add('d-none');p.querySelectorAll('.dynamic-required').forEach(e=>e.required=false)});trials.forEach(e=>e.required=false);if(workflow()==='register')return;const p=q(`[data-workflow-panel="${workflow()}"]`)||q('[data-workflow-panel="custom"]');p?.classList.remove('d-none');p?.querySelectorAll('.dynamic-required').forEach(e=>e.required=true);if(workflow()==='trial_readings')trials.forEach(e=>e.required=true);const map={trial_readings:['Ten mandatory trial readings','Enter all 10 error readings.'],starting:['Starting-current measurements','Enter voltage, starting current and pulse observation.'],creep:['No-load / creep measurements','Enter observation time and pulse count.'],ct_ratio:['CT ratio measurements','Enter rated, applied and measured currents.'],ct_accuracy:['CT accuracy measurements','Enter measured currents and phase displacement.'],ct_excitation:['CT excitation measurements','Enter voltage, current and knee-point requirements.'],ct_burden:['CT burden measurements','Enter rated and measured burden.'],vt_ratio:['VT ratio measurements','Enter rated, applied and measured voltages.'],comparison:['Reference comparison','Enter reference and indicated values.'],resistance:['Resistance measurements','Enter reference and measured resistance.'],insulation:['Insulation resistance','Enter test voltage and acceptance limits.'],custom:['Custom measurements','Enter reference and measured values.']};const m=map[workflow()]||map.custom;q('.stage-two-title').textContent=m[0];q('.stage-two-description').textContent=m[1]}
        function updateWorkflowLabels(){const labels={trial_readings:['10 readings','Uncertainty'],starting:['Measurements','Assessment'],creep:['Measurements','Assessment'],ct_ratio:['Ratio data','Ratio result'],ct_accuracy:['Accuracy data','Accuracy result'],ct_excitation:['Excitation data','Assessment'],ct_burden:['Burden data','Assessment'],vt_ratio:['Voltage data','Ratio result'],comparison:['Comparison','Error result'],resistance:['Resistance','Error result'],insulation:['Insulation','Assessment'],custom:['Measurements','Calculation'],register:['Register details','Review']};const l=labels[workflow()]||labels.custom;q('.workflow-step[data-step-target="2"] small').textContent=l[0];q('.workflow-step[data-step-target="3"] small').textContent=l[1]}
        function validateStep1(){form.classList.add('was-validated');if(!category.value||!standard.value||!testName.value||!testType.value){footer.textContent='Select the category and standard test.';return false}if(customName.required&&!customName.value.trim()){customName.focus();return false}if(customCurrent.required&&num(customCurrent)===null){customCurrent.focus();return false}if(workflow()==='register'&&(num(start)===null||num(stop)===null)){footer.textContent='Enter register start and stop readings.';return false}return true}
        function validateStep2(){if(workflow()==='register')return true;const p=q(`[data-workflow-panel="${workflow()}"]`)||q('[data-workflow-panel="custom"]');if(workflow()==='trial_readings'){const vals=trials.map(num);if(vals.some(v=>v===null)){footer.textContent='All 10 trial readings are required.';return false}}const invalid=p?.querySelector(':invalid');if(invalid){invalid.reportValidity();invalid.focus();return false}return true}
        function pct(ref,meas){return ref===null||meas===null||ref===0?null:((meas-ref)/ref)*100}
        function present(title,label,showStats=false){q('.stage-three-title').textContent=title;q('.dynamic-result-label').textContent=label;q('.standard-deviation-wrapper').classList.toggle('d-none',!showStats);q('.uncertainty-wrapper').classList.toggle('d-none',!showStats)}
        function finalize(value,m1,m2,m3,forced=null,extra='N/A'){const tol=num(tolerance)??0;const pass=forced?forced==='PASS':Math.abs(value)<=tol;error.value=value.toFixed(6);result.value=pass?'PASS':'FAIL';q('.u1-value').textContent=m1;q('.u2-value').textContent=m2;q('.u3-value').textContent=m3;q('.dynamic-tolerance-display').textContent=`±${tol.toFixed(4)}%`;q('.summary-error').textContent=value.toFixed(6);q('.summary-uncertainty').textContent=extra;q('.summary-tolerance').textContent=`±${tol.toFixed(4)}%`;const box=q('.result-summary-card');box.classList.remove('result-pending','result-pass','result-fail');box.classList.add(pass?'result-pass':'result-fail');q('.result-status-text').textContent=pass?'PASS':'FAIL';q('.result-status-icon i').className=pass?'fas fa-circle-check':'fas fa-circle-xmark';return true}
        function calculate(){const w=workflow();if(w==='register'){const d=num(dial)??0;present('Register test decision','Dial error (%)');return finalize(d,`${num(difference)?.toFixed(4)??'—'} kWh`,'Register difference',`${d.toFixed(4)}%`)}if(w==='trial_readings'){const vals=trials.map(num);if(vals.some(v=>v===null))return false;const mean=vals.reduce((a,b)=>a+b,0)/vals.length;const variance=vals.reduce((a,b)=>a+(b-mean)**2,0)/(vals.length-1);const s=Math.sqrt(variance),u1=s/Math.sqrt(vals.length),u2=.05/Math.sqrt(3),u3=.01/Math.sqrt(12),U=2*Math.sqrt(u1*u1+u2*u2+u3*u3);sd.value=s.toFixed(6);uncertainty.value=U.toFixed(6);present('Accuracy and uncertainty calculation','Average error (%)',true);return finalize(mean,u1.toFixed(6),u2.toFixed(6),u3.toFixed(6),null,`${U.toFixed(6)}%`)}
            let ref=null,meas=null,val=null,forced=null,m1='—',m2='—',m3='—',extra='N/A';
            if(w==='starting'){const detected=q('.starting-pulse').value==='yes';val=num(q('.starting-current'))??0;forced=detected?'PASS':'FAIL';present('Starting-current assessment','Starting current (mA)');return finalize(val,`${val} mA`,detected?'Pulse detected':'No pulse',`${num(q('.starting-time'))??'—'} s`,forced)}
            if(w==='creep'){const pulses=num(q('.creep-pulses')),max=num(q('.creep-max-pulses'));if(pulses===null||max===null)return false;val=pulses-max;forced=pulses<=max?'PASS':'FAIL';present('No-load / creep assessment','Pulse margin');return finalize(val,`${pulses} pulses`,`${max} allowed`,`${val} margin`,forced)}
            if(w==='ct_ratio'||w==='ct_accuracy'){const rp=num(q(w==='ct_ratio'?'.ct-rated-primary':'.ct-accuracy-rated-primary')),rs=num(q(w==='ct_ratio'?'.ct-rated-secondary':'.ct-accuracy-rated-secondary')),ap=num(q(w==='ct_ratio'?'.ct-applied-primary':'.ct-accuracy-primary')),ms=num(q(w==='ct_ratio'?'.ct-measured-secondary':'.ct-accuracy-secondary'));if([rp,rs,ap,ms].some(v=>v===null)||rs===0||ms===0)return false;ref=rp/rs;meas=ap/ms;val=pct(ref,meas);m1=meas.toFixed(6);m2=ref.toFixed(6);m3=w==='ct_accuracy'?`${num(q('.ct-phase'))??'—'} min`:`${val.toFixed(6)}%`;present('Current-transformer ratio calculation','Ratio error (%)');return finalize(val,m1,m2,m3,null,w==='ct_accuracy'?m3:'N/A')}
            if(w==='ct_excitation'){meas=num(q('.knee-point-voltage'));ref=num(q('.minimum-knee-point'));if(meas===null||ref===null)return false;val=meas-ref;forced=meas>=ref?'PASS':'FAIL';present('CT excitation assessment','Knee-point margin (V)');return finalize(val,`${meas} V`,`${ref} V`,`${num(q('.excitation-current'))??'—'} mA`,forced)}
            if(w==='ct_burden'){ref=num(q('.rated-burden'));meas=num(q('.measured-burden'));if(ref===null||meas===null)return false;val=pct(ref,meas);present('CT burden calculation','Burden error (%)');return finalize(val,`${meas} VA`,`${ref} VA`,`${num(q('.burden-phase'))??'—'} min`)}
            if(w==='vt_ratio'){const rp=num(q('.vt-rated-primary')),rs=num(q('.vt-rated-secondary')),ap=num(q('.vt-applied-primary')),ms=num(q('.vt-measured-secondary'));if([rp,rs,ap,ms].some(v=>v===null)||rs===0||ms===0)return false;ref=rp/rs;meas=ap/ms;val=pct(ref,meas);present('Voltage-transformer ratio calculation','Ratio error (%)');return finalize(val,meas.toFixed(6),ref.toFixed(6),`${val.toFixed(6)}%`)}
            if(w==='comparison'){ref=num(q('.comparison-reference'));meas=num(q('.comparison-indicated'))}else if(w==='resistance'){ref=num(q('.resistance-reference'));meas=num(q('.resistance-measured'))}else if(w==='insulation'){meas=num(q('.insulation-resistance'));ref=num(q('.insulation-minimum'));if(meas===null||ref===null)return false;val=meas-ref;forced=meas>=ref?'PASS':'FAIL';present('Insulation resistance assessment','Acceptance margin (MΩ)');return finalize(val,`${meas} MΩ`,`${ref} MΩ`,`${val} MΩ`,forced)}else{ref=num(q('.custom-reference'));meas=num(q('.custom-measured'))}if(ref===null||meas===null)return false;val=pct(ref,meas);present('Reference comparison calculation','Error (%)');return finalize(val,meas.toFixed(6),ref.toFixed(6),(meas-ref).toFixed(6))
        }
        function updateTrials(){const count=trials.filter(e=>num(e)!==null).length;const c=q('.trial-count'),b=q('.trial-progress-bar');if(c)c.textContent=`${count} / 10`;if(b)b.style.width=`${count*10}%`}
        function updateReview(){q('.review-equipment-type').textContent=config().label;q('.review-category').textContent=config().categories[category.value]?.label||'—';q('.review-test-name').textContent=testName.value||'—';q('.review-test-type').textContent=testType.value||'—';q('.review-applied-value').textContent=applied.value||'—';q('.review-voltage-frequency').textContent=[voltage.value,frequency.value,pf.value].filter(Boolean).join(' · ')||'—';q('.review-trials').textContent=workflow()==='trial_readings'?trials.map(e=>e.value||'—').join(', '):workflow()==='register'?`${start.value} → ${stop.value} kWh`:workflow().replaceAll('_',' ');q('.review-error').textContent=error.value||'—';q('.review-uncertainty').textContent=uncertainty.value||'N/A';q('.review-result').textContent=result.value||'—'}
        function showStep(n){currentStep=n;panels.forEach(p=>p.classList.toggle('active',Number(p.dataset.step)===n));steps.forEach(s=>{const x=Number(s.dataset.stepTarget);s.classList.toggle('active',x===n);s.classList.toggle('completed',x<n&&x<=highestCompletedStep)});lines.forEach((l,i)=>l.classList.toggle('completed',i+1<n&&i+1<=highestCompletedStep));previous.classList.toggle('d-none',n===1);next.classList.toggle('d-none',n===4);save.classList.toggle('d-none',n!==4);footer.textContent=n===1?'Complete test details to continue.':n===2?'Enter all required measurements.':n===3?'Review the calculated decision.':'Confirm and save the result.';q(`.wizard-panel[data-step="${n}"]`)?.scrollIntoView({block:'start',behavior:'smooth'})}
        function goNext(){if(currentStep===1){if(!validateStep1())return;highestCompletedStep=Math.max(highestCompletedStep,1);if(workflow()==='register'){calculate();updateReview();highestCompletedStep=3;showStep(4)}else showStep(2);return}if(currentStep===2){if(!validateStep2())return;if(!calculate()){footer.textContent='Check the entered measurements.';return}highestCompletedStep=Math.max(highestCompletedStep,2);showStep(3);return}if(currentStep===3){if(!calculate())return;highestCompletedStep=Math.max(highestCompletedStep,3);updateReview();showStep(4)}}
        category.addEventListener('change',fillTests);standard.addEventListener('change',applyTest);customName.addEventListener('input',()=>{if(activeTest?.customName){testName.value=customName.value.trim();preview.textContent=testName.value||'Custom laboratory test'}});customCurrent.addEventListener('input',()=>{if(activeTest?.customCurrent){const v=customCurrent.value;testName.value=v?`REGISTER TEST @ ${v} A`:'';applied.value=v?`${v} A`:'';preview.textContent=testName.value||'Custom register test'}});[start,stop].forEach(e=>e?.addEventListener('input',updateRegister));trials.forEach(e=>e.addEventListener('input',updateTrials));next.addEventListener('click',goNext);previous.addEventListener('click',()=>showStep(Math.max(1,currentStep-1)));calcButton?.addEventListener('click',calculate);steps.forEach(s=>s.addEventListener('click',()=>{const t=Number(s.dataset.stepTarget);if(t<=highestCompletedStep+1&&!(workflow()==='register'&&(t===2||t===3)))showStep(t)}));form.addEventListener('submit',e=>{if(!validateStep1()||!validateStep2()||!calculate()){e.preventDefault();return}save.disabled=true;q('.save-label')?.classList.add('d-none');q('.saving-label')?.classList.remove('d-none')});
        fillCategories();updateDynamicPanel();updateWorkflowLabels();updateTrials();showStep(1);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\project\ncckplclims\resources\views/admin/calibrations/index.blade.php ENDPATH**/ ?>