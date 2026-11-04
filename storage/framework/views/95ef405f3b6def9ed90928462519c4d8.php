<?php $__env->startSection('title', 'Certificate #' . $certificate->certificate_number); ?>

<?php $__env->startSection('content'); ?>
<?php
    /*
    |--------------------------------------------------------------------------
    | Certificate status and workflow
    |--------------------------------------------------------------------------
    */

    $statusOrder = [
        'draft' => 0,
        'pending' => 0,
        'generated' => 1,
        'checked' => 2,
        'approved' => 3,
        'sent' => 4,
        'issued' => 4,
        'rejected' => 0,
    ];

    $currentStatusLevel =
        $statusOrder[$certificate->status] ?? 0;

    /*
    |--------------------------------------------------------------------------
    | Test result display
    |--------------------------------------------------------------------------
    */

    $normalizedTestResult = strtolower(
        trim((string) $certificate->test_result)
    );

    $testResultClass = match ($normalizedTestResult) {
        'pass', 'passed' => 'success',
        'fail', 'failed' => 'danger',
        default => 'secondary',
    };

    /*
    |--------------------------------------------------------------------------
    | Current-user authorization
    |--------------------------------------------------------------------------
    */

    $currentUser = auth()->user();
    $currentUserId = (int) auth()->id();

    $isSuperAdmin =
        $currentUser?->hasRole('super_admin') ?? false;

    $isSupervisor =
        $currentUser?->hasRole('supervisor') ?? false;

    $isLabManager =
        $currentUser?->hasRole('lab_manager') ?? false;

    /*
     * A supervisor may check a generated certificate,
     * but the generator should not check their own certificate.
     *
     * Super administrators may override this restriction.
     */
    $canCheckCertificate =
        $certificate->status === 'generated'
        && $isSupervisor
        && (int) $certificate->generated_by !== $currentUserId;

    /*
     * A lab manager may approve a checked certificate,
     * but should not approve a certificate they generated or checked.
     *
     * Super administrators may override this restriction.
     */
    $canApproveCertificate =
        $certificate->status === 'checked'
        && $isLabManager
        && (int) $certificate->generated_by !== $currentUserId
        && (int) $certificate->checked_by !== $currentUserId;

    /*
     * Approved certificates may be sent to the customer.
     */
    $canSendCertificate =
        $certificate->status === 'approved'
        && (
            $isSuperAdmin
            || $isLabManager
            || $isSupervisor
        );

    /*
     * Final certificate download is restricted until approval.
     * Super administrators retain an override.
     */
    $canDownloadFinalCertificate =
        in_array(
            $certificate->status,
            ['approved', 'sent', 'issued'],
            true
        )
        || $isSuperAdmin;

    /*
     * Preview remains available internally before approval.
     */
    $canPreviewCertificate =
        $currentUser !== null;

    /*
    |--------------------------------------------------------------------------
    | Customer email
    |--------------------------------------------------------------------------
    */

    $customerEmail =
        $certificate->customer?->email
        ?? $certificate->calibration?->customer?->email;

    /*
    |--------------------------------------------------------------------------
    | Test-result collection
    |--------------------------------------------------------------------------
    */

    $testResults = collect($testResults ?? []);
?>

<div class="container-fluid px-0 certificate-show-page">
    <div class="card border-0 shadow-sm">
        
        <div class="card-header bg-white py-3">
            <div
                class="d-flex justify-content-between align-items-start flex-wrap gap-3"
            >
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-file-pdf text-danger me-1"></i>
                        Certificate #<?php echo e($certificate->certificate_number); ?>

                    </h2>

                    <p class="text-muted mb-0">
                        Reference:
                        <?php echo e($certificate->reference_number ?? 'N/A'); ?>

                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span
                        class="badge bg-<?php echo e($certificate->status_color ?? 'secondary'); ?> fs-6"
                    >
                        Status:
                        <?php echo e($certificate->status_label
                            ?? ucfirst(
                                str_replace(
                                    '_',
                                    ' ',
                                    $certificate->status ?? 'N/A'
                                )
                            )); ?>

                    </span>

                    <span
                        class="badge bg-<?php echo e($testResultClass); ?> fs-6"
                    >
                        Result:
                        <?php echo e($certificate->test_result ?: 'Pending'); ?>

                    </span>
                </div>
            </div>
        </div>

        <div class="card-body">
            
            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-1"></i>
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <strong>
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Please correct the following:
                    </strong>

                    <ul class="mb-0 mt-2">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            
            <section class="certificate-section mb-4">
                <h5 class="section-title">
                    <i class="fas fa-route text-primary me-1"></i>
                    Certificate Workflow
                </h5>

                <div class="certificate-workflow">
                    <div class="workflow-step">
                        <div
                            class="workflow-icon
                                <?php echo e($currentStatusLevel >= 1 ? 'completed' : ''); ?>"
                        >
                            <i class="fas fa-file"></i>
                        </div>

                        <p>Generated</p>
                    </div>

                    <div
                        class="workflow-line
                            <?php echo e($currentStatusLevel >= 2 ? 'completed' : ''); ?>"
                    ></div>

                    <div class="workflow-step">
                        <div
                            class="workflow-icon
                                <?php echo e($currentStatusLevel >= 2 ? 'completed' : ''); ?>"
                        >
                            <i class="fas fa-check-circle"></i>
                        </div>

                        <p>Checked</p>
                    </div>

                    <div
                        class="workflow-line
                            <?php echo e($currentStatusLevel >= 3 ? 'completed' : ''); ?>"
                    ></div>

                    <div class="workflow-step">
                        <div
                            class="workflow-icon
                                <?php echo e($currentStatusLevel >= 3 ? 'completed' : ''); ?>"
                        >
                            <i class="fas fa-user-check"></i>
                        </div>

                        <p>Approved</p>
                    </div>

                    <div
                        class="workflow-line
                            <?php echo e($currentStatusLevel >= 4 ? 'completed' : ''); ?>"
                    ></div>

                    <div class="workflow-step">
                        <div
                            class="workflow-icon
                                <?php echo e($currentStatusLevel >= 4 ? 'completed' : ''); ?>"
                        >
                            <i class="fas fa-envelope"></i>
                        </div>

                        <p>Sent</p>
                    </div>
                </div>

                <?php if($certificate->status === 'rejected'): ?>
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="fas fa-circle-xmark me-1"></i>
                        This certificate was rejected.

                        <?php if($certificate->comments): ?>
                            <div class="mt-2">
                                <strong>Comments:</strong>
                                <?php echo e($certificate->comments); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>

            
            <?php if($certificate->test_report_attachment_path): ?>
                <div class="alert alert-success">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-file-pdf mt-1"></i>

                        <div>
                            <strong>Test report attached:</strong>

                            <?php echo e($certificate->test_report_original_name
                                ?? $certificate->test_report_attachment_filename
                                ?? 'Test Report'); ?>


                            <small class="text-muted d-block mt-1">
                                Machine:
                                <?php echo e($certificate->machine_used ?? 'N/A'); ?>


                                |

                                Serial:
                                <?php echo e($certificate->machine_serial ?? 'N/A'); ?>


                                |

                                Engineer:
                                <?php echo e($certificate->test_engineer ?? 'N/A'); ?>


                                |

                                Test date:
                                <?php echo e($certificate->test_date
                                    ? \Carbon\Carbon::parse(
                                        $certificate->test_date
                                    )->format('d M Y')
                                    : 'N/A'); ?>

                            </small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="row g-4">
                <div class="col-lg-6">
                    <section class="certificate-section h-100">
                        <h5 class="section-title">
                            <i class="fas fa-tools text-primary me-1"></i>
                            Equipment Information
                        </h5>

                        <div class="detail-grid">
                            <div>
                                <small>Equipment Name</small>
                                <strong>
                                    <?php echo e($certificate->equipment_name
                                        ?? $certificate->calibration?->equipment_name
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Serial Number</small>
                                <strong>
                                    <?php echo e($certificate->serial_number
                                        ?? $certificate->calibration?->serial_number
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Manufacturer</small>
                                <strong>
                                    <?php echo e($certificate->manufacturer
                                        ?? $certificate->calibration?->manufacturer
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Model</small>
                                <strong>
                                    <?php echo e($certificate->model
                                        ?? $certificate->calibration?->model
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Equipment Type</small>
                                <strong>
                                    <?php echo e($certificate->equipment_type
                                        ?? $certificate->calibration?->equipment_type
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Accuracy Class</small>
                                <strong>
                                    <?php echo e($certificate->accuracy_class
                                        ?? $certificate->calibration?->accuracy_class
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Ratings</small>
                                <strong>
                                    <?php echo e($certificate->ratings
                                        ?? $certificate->calibration?->ratings
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Customer</small>
                                <strong>
                                    <?php echo e($certificate->customer?->company_name
                                        ?? $certificate->calibration?->customer?->company_name
                                        ?? 'N/A'); ?>

                                </strong>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-6">
                    <section class="certificate-section h-100">
                        <h5 class="section-title">
                            <i class="fas fa-clipboard-list text-success me-1"></i>
                            Service and Certificate Information
                        </h5>

                        <div class="detail-grid">
                            <div>
                                <small>Service</small>
                                <strong>
                                    <?php echo e($certificate->service_name ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Method</small>
                                <strong>
                                    <?php echo e($certificate->service_method ?? 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Test Result</small>

                                <span class="badge bg-<?php echo e($testResultClass); ?>">
                                    <?php echo e($certificate->test_result ?: 'Pending'); ?>

                                </span>
                            </div>

                            <div>
                                <small>Issue Date</small>
                                <strong>
                                    <?php echo e($certificate->issued_date
                                        ? \Carbon\Carbon::parse(
                                            $certificate->issued_date
                                        )->format('d M Y')
                                        : (
                                            $certificate->approved_at
                                                ? \Carbon\Carbon::parse(
                                                    $certificate->approved_at
                                                )->format('d M Y')
                                                : 'Pending'
                                        )); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Next Calibration / Expiry</small>
                                <strong>
                                    <?php echo e($certificate->expiry_date
                                        ? \Carbon\Carbon::parse(
                                            $certificate->expiry_date
                                        )->format('d M Y')
                                        : 'N/A'); ?>

                                </strong>
                            </div>

                            <div>
                                <small>Status</small>

                                <span
                                    class="badge bg-<?php echo e($certificate->status_color ?? 'secondary'); ?>"
                                >
                                    <?php echo e($certificate->status_label
                                        ?? ucfirst(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $certificate->status ?? 'N/A'
                                            )
                                        )); ?>

                                </span>
                            </div>
                        </div>

                        <?php if($certificate->service_notes): ?>
                            <div class="mt-3">
                                <small class="text-muted d-block fw-bold">
                                    Service Notes
                                </small>

                                <div class="border rounded bg-light p-3 mt-1">
                                    <?php echo e($certificate->service_notes); ?>

                                </div>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>

            
            <?php if($testResults->isNotEmpty()): ?>
                <section class="certificate-section mt-4">
                    <h5 class="section-title">
                        <i class="fas fa-flask text-warning me-1"></i>
                        Test Results
                    </h5>

                    <div class="table-responsive">
                        <table
                            class="table table-bordered table-striped table-hover align-middle"
                        >
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Parameter</th>
                                    <th>Nominal Value</th>
                                    <th>Measured Value</th>
                                    <th>Tolerance</th>
                                    <th>Result</th>
                                    <th>Trial Data</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__currentLoopData = $testResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $trialData =
                                            $result->trial_data_array
                                            ?? $result->trial_data
                                            ?? [];

                                        if (is_string($trialData)) {
                                            $trialData =
                                                json_decode(
                                                    $trialData,
                                                    true
                                                ) ?: [];
                                        }

                                        $trialData = is_array($trialData)
                                            ? array_values(
                                                array_filter(
                                                    $trialData,
                                                    fn ($value) =>
                                                        $value !== null
                                                        && $value !== ''
                                                )
                                            )
                                            : [];

                                        $trialStats =
                                            $result->trial_data_stats
                                            ?? [];

                                        if (
                                            empty($trialStats)
                                            && count($trialData) > 0
                                        ) {
                                            $numericTrials = collect(
                                                $trialData
                                            )->map(
                                                fn ($value) => (float) $value
                                            );

                                            $trialStats = [
                                                'mean' =>
                                                    $numericTrials->average(),

                                                'min' =>
                                                    $numericTrials->min(),

                                                'max' =>
                                                    $numericTrials->max(),

                                                'count' =>
                                                    $numericTrials->count(),
                                            ];
                                        }

                                        $withinTolerance =
                                            $result->is_within_tolerance;

                                        $rowResultClass =
                                            $withinTolerance === true
                                                ? 'success'
                                                : (
                                                    $withinTolerance === false
                                                        ? 'danger'
                                                        : 'secondary'
                                                );

                                        $rowResultLabel =
                                            $withinTolerance === true
                                                ? 'PASS'
                                                : (
                                                    $withinTolerance === false
                                                        ? 'FAIL'
                                                        : 'N/A'
                                                );
                                    ?>

                                    <tr>
                                        <td><?php echo e($index + 1); ?></td>

                                        <td>
                                            <strong>
                                                <?php echo e($result->parameter
                                                    ?? $result->test_name
                                                    ?? 'Test Parameter'); ?>

                                            </strong>

                                            <?php if(
                                                $result->test_name
                                                && $result->test_name
                                                    !== $result->parameter
                                            ): ?>
                                                <small
                                                    class="text-muted d-block"
                                                >
                                                    <?php echo e($result->test_name); ?>

                                                </small>
                                            <?php endif; ?>

                                            <?php if($result->test_type): ?>
                                                <small
                                                    class="text-muted d-block"
                                                >
                                                    Type:
                                                    <?php echo e(ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $result->test_type
                                                        )
                                                    )); ?>

                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php echo e($result->nominal_value !== null
                                                ? number_format(
                                                    (float) $result->nominal_value,
                                                    4
                                                )
                                                : 'N/A'); ?>


                                            <?php echo e($result->unit ?? ''); ?>

                                        </td>

                                        <td>
                                            <?php echo e($result->measured_value !== null
                                                ? number_format(
                                                    (float) $result->measured_value,
                                                    4
                                                )
                                                : 'N/A'); ?>


                                            <?php echo e($result->unit ?? ''); ?>

                                        </td>

                                        <td>
                                            <?php if($result->tolerance !== null): ?>
                                                ±<?php echo e(number_format(
                                                    (float) $result->tolerance,
                                                    2
                                                )); ?>%
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>

                                            <?php if(
                                                $result->tolerance_min !== null
                                                || $result->tolerance_max !== null
                                            ): ?>
                                                <small
                                                    class="text-muted d-block"
                                                >
                                                    <?php echo e($result->tolerance_min !== null
                                                        ? number_format(
                                                            (float) $result->tolerance_min,
                                                            4
                                                        )
                                                        : 'N/A'); ?>


                                                    -

                                                    <?php echo e($result->tolerance_max !== null
                                                        ? number_format(
                                                            (float) $result->tolerance_max,
                                                            4
                                                        )
                                                        : 'N/A'); ?>

                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <span
                                                class="badge bg-<?php echo e($rowResultClass); ?>"
                                            >
                                                <?php echo e($rowResultLabel); ?>

                                            </span>

                                            <?php if(
                                                $result->error_percentage !== null
                                                || $result->error_value !== null
                                            ): ?>
                                                <small
                                                    class="text-muted d-block mt-1"
                                                >
                                                    Error:
                                                    <?php echo e(number_format(
                                                        (float) (
                                                            $result->error_percentage
                                                            ?? $result->error_value
                                                        ),
                                                        4
                                                    )); ?>%
                                                </small>
                                            <?php endif; ?>

                                            <?php if($result->uncertainty !== null): ?>
                                                <small
                                                    class="text-info d-block"
                                                >
                                                    Uncertainty:
                                                    ±<?php echo e(number_format(
                                                        (float) $result->uncertainty,
                                                        4
                                                    )); ?>

                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if(count($trialData) > 0): ?>
                                                <div class="trial-badges">
                                                    <?php $__currentLoopData = $trialData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trialIndex => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span
                                                            class="badge bg-secondary"
                                                        >
                                                            T<?php echo e($trialIndex + 1); ?>:
                                                            <?php echo e(is_numeric($value)
                                                                ? number_format(
                                                                    (float) $value,
                                                                    4
                                                                )
                                                                : $value); ?>

                                                        </span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>

                                                <small
                                                    class="text-muted d-block mt-2"
                                                >
                                                    Mean:
                                                    <?php echo e(isset($trialStats['mean'])
                                                        ? number_format(
                                                            (float) $trialStats['mean'],
                                                            4
                                                        )
                                                        : 'N/A'); ?>


                                                    |

                                                    Min:
                                                    <?php echo e(isset($trialStats['min'])
                                                        ? number_format(
                                                            (float) $trialStats['min'],
                                                            4
                                                        )
                                                        : 'N/A'); ?>


                                                    |

                                                    Max:
                                                    <?php echo e(isset($trialStats['max'])
                                                        ? number_format(
                                                            (float) $trialStats['max'],
                                                            4
                                                        )
                                                        : 'N/A'); ?>


                                                    <?php if(
                                                        isset(
                                                            $trialStats['std_dev']
                                                        )
                                                        && $trialStats['std_dev']
                                                            !== null
                                                    ): ?>
                                                        |

                                                        Std Dev:
                                                        <?php echo e(number_format(
                                                            (float) $trialStats['std_dev'],
                                                            4
                                                        )); ?>

                                                    <?php endif; ?>

                                                    |

                                                    Count:
                                                    <?php echo e($trialStats['count']
                                                        ?? count($trialData)); ?>

                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    No detailed trial data
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>

            
            <?php if($certificate->test_report_attachment_path): ?>
                <section class="certificate-section mt-4">
                    <h5 class="section-title">
                        <i class="fas fa-microchip text-info me-1"></i>
                        Test Machine Details
                    </h5>

                    <div class="detail-grid detail-grid-four">
                        <div>
                            <small>Machine Used</small>
                            <strong>
                                <?php echo e($certificate->machine_used ?? 'N/A'); ?>

                            </strong>
                        </div>

                        <div>
                            <small>Machine Serial</small>
                            <strong>
                                <?php echo e($certificate->machine_serial ?? 'N/A'); ?>

                            </strong>
                        </div>

                        <div>
                            <small>Test Engineer</small>
                            <strong>
                                <?php echo e($certificate->test_engineer ?? 'N/A'); ?>

                            </strong>
                        </div>

                        <div>
                            <small>Test Date</small>
                            <strong>
                                <?php echo e($certificate->test_date
                                    ? \Carbon\Carbon::parse(
                                        $certificate->test_date
                                    )->format('d M Y H:i')
                                    : 'N/A'); ?>

                            </strong>
                        </div>

                        <div>
                            <small>Report File</small>
                            <strong>
                                <?php echo e($certificate->test_report_original_name
                                    ?? $certificate->test_report_attachment_filename
                                    ?? 'N/A'); ?>

                            </strong>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            
            <section class="certificate-section mt-4">
                <h5 class="section-title">
                    <i class="fas fa-signature text-info me-1"></i>
                    Digital Signatures
                </h5>

                <div class="row g-3">
                    
                    <div class="col-md-4">
                        <div class="signature-card">
                            <h6>Generated By</h6>

                            <?php if($certificate->generatedBy): ?>
                                <p class="mb-2">
                                    <strong>
                                        <?php echo e($certificate->generatedBy->name); ?>

                                    </strong>
                                </p>

                                <?php if(
                                    $certificate->generatedBy->signature_path
                                ): ?>
                                    <img
                                        src="<?php echo e(asset(
                                            'storage/'
                                            . $certificate->generatedBy
                                                ->signature_path
                                        )); ?>"
                                        class="signature-image"
                                        alt="Generator signature"
                                    >
                                <?php else: ?>
                                    <p class="text-muted small">
                                        No signature uploaded
                                    </p>
                                <?php endif; ?>

                                <small class="text-muted">
                                    <?php echo e($certificate->generated_at
                                        ? \Carbon\Carbon::parse(
                                            $certificate->generated_at
                                        )->format('d M Y H:i')
                                        : ''); ?>

                                </small>
                            <?php else: ?>
                                <p class="text-muted mb-0">
                                    Not generated yet
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="col-md-4">
                        <div class="signature-card">
                            <h6>Checked By</h6>

                            <?php if($certificate->checkedBy): ?>
                                <p class="mb-2">
                                    <strong>
                                        <?php echo e($certificate->checkedBy->name); ?>

                                    </strong>
                                </p>

                                <?php if($certificate->checkedBy->signature_path): ?>
                                    <img
                                        src="<?php echo e(asset(
                                            'storage/'
                                            . $certificate->checkedBy
                                                ->signature_path
                                        )); ?>"
                                        class="signature-image"
                                        alt="Checker signature"
                                    >
                                <?php else: ?>
                                    <p class="text-muted small">
                                        No signature uploaded
                                    </p>
                                <?php endif; ?>

                                <small class="text-muted">
                                    <?php echo e($certificate->checked_at
                                        ? \Carbon\Carbon::parse(
                                            $certificate->checked_at
                                        )->format('d M Y H:i')
                                        : ''); ?>

                                </small>
                            <?php else: ?>
                                <p class="text-muted mb-0">
                                    Not checked yet
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="col-md-4">
                        <div class="signature-card">
                            <h6>Approved By</h6>

                            <?php if($certificate->approvedBy): ?>
                                <p class="mb-2">
                                    <strong>
                                        <?php echo e($certificate->approvedBy->name); ?>

                                    </strong>
                                </p>

                                <?php if(
                                    $certificate->approvedBy->signature_path
                                ): ?>
                                    <img
                                        src="<?php echo e(asset(
                                            'storage/'
                                            . $certificate->approvedBy
                                                ->signature_path
                                        )); ?>"
                                        class="signature-image"
                                        alt="Approver signature"
                                    >
                                <?php else: ?>
                                    <p class="text-muted small">
                                        No signature uploaded
                                    </p>
                                <?php endif; ?>

                                <small class="text-muted">
                                    <?php echo e($certificate->approved_at
                                        ? \Carbon\Carbon::parse(
                                            $certificate->approved_at
                                        )->format('d M Y H:i')
                                        : ''); ?>

                                </small>
                            <?php else: ?>
                                <p class="text-muted mb-0">
                                    Not approved yet
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            
            <?php if($certificate->comments): ?>
                <section class="certificate-section mt-4">
                    <h5 class="section-title">
                        <i class="fas fa-comment-dots text-primary me-1"></i>
                        Review Comments
                    </h5>

                    <div class="alert alert-light border mb-0">
                        <?php echo e($certificate->comments); ?>

                    </div>
                </section>
            <?php endif; ?>

            
            <section class="certificate-section mt-4">
                <h5 class="section-title">
                    <i class="fas fa-sliders text-primary me-1"></i>
                    Actions
                </h5>

                <div class="d-flex flex-wrap gap-2">
                    <a
                        href="<?php echo e(route('certificates.index')); ?>"
                        class="btn btn-secondary"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back
                    </a>

                    <?php if($canPreviewCertificate): ?>
                        <a
                            href="<?php echo e(route(
                                'certificates.preview',
                                $certificate
                            )); ?>"
                            target="_blank"
                            class="btn btn-info"
                        >
                            <i class="fas fa-eye me-1"></i>
                            Preview Certificate
                        </a>
                    <?php endif; ?>

                    
                    <?php if($canDownloadFinalCertificate): ?>
                        <a
                            href="<?php echo e(route(
                                'certificates.download',
                                [
                                    'certificate' => $certificate,
                                    'force' => true,
                                ]
                            )); ?>"
                            class="btn btn-success"
                        >
                            <i class="fas fa-download me-1"></i>
                            Download Certificate
                        </a>
                    <?php else: ?>
                        <button
                            type="button"
                            class="btn btn-secondary"
                            disabled
                            title="The certificate must be approved before final download"
                        >
                            <i class="fas fa-lock me-1"></i>
                            Download Certificate
                        </button>
                    <?php endif; ?>

                    
                    <?php if($certificate->test_report_attachment_path): ?>
                        <a
                            href="<?php echo e(route(
                                'certificates.download-test-report',
                                $certificate
                            )); ?>"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-file-alt me-1"></i>
                            Download Test Report
                        </a>

                        <a
                            href="<?php echo e(route(
                                'certificates.download-combined',
                                $certificate
                            )); ?>"
                            class="btn btn-warning"
                        >
                            <i class="fas fa-file-pdf me-1"></i>
                            Download Complete
                        </a>
                    <?php else: ?>
                        <a
                            href="<?php echo e(route(
                                'certificates.upload-test-report',
                                $certificate
                            )); ?>"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-file-upload me-1"></i>
                            Upload Test Report
                        </a>
                    <?php endif; ?>

                    
                    <?php if($canCheckCertificate): ?>
                        <button
                            type="button"
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#checkCertificateModal"
                        >
                            <i class="fas fa-check-circle me-1"></i>
                            Check Certificate
                        </button>
                    <?php endif; ?>

                    
                    <?php if($canApproveCertificate): ?>
                        <button
                            type="button"
                            class="btn btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#approveCertificateModal"
                        >
                            <i class="fas fa-check-double me-1"></i>
                            Approve Certificate
                        </button>
                    <?php endif; ?>

                    
                    <?php if($canSendCertificate): ?>
                        <?php if($customerEmail): ?>
                            <form
                                action="<?php echo e(route(
                                    'certificates.send-to-customer',
                                    $certificate
                                )); ?>"
                                method="POST"
                                class="d-inline"
                            >
                                <?php echo csrf_field(); ?>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    onclick="return confirm(
                                        'Send certificate <?php echo e($certificate->certificate_number); ?> to <?php echo e($customerEmail); ?>?'
                                    )"
                                >
                                    <i class="fas fa-envelope me-1"></i>
                                    Send to Customer
                                </button>
                            </form>
                        <?php else: ?>
                            <button
                                type="button"
                                class="btn btn-secondary"
                                disabled
                                title="The customer does not have an email address"
                            >
                                <i class="fas fa-envelope me-1"></i>
                                Send to Customer
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    
                    <?php if(
                        in_array(
                            $certificate->status,
                            ['sent', 'issued'],
                            true
                        )
                    ): ?>
                        <span class="btn btn-outline-success disabled">
                            <i class="fas fa-circle-check me-1"></i>
                            Sent
                            <?php echo e($certificate->sent_at
                                ? \Carbon\Carbon::parse(
                                    $certificate->sent_at
                                )->format('d M Y H:i')
                                : ''); ?>

                        </span>
                    <?php endif; ?>

                    
                    <?php if($isSuperAdmin): ?>
                        <form
                            action="<?php echo e(route(
                                'certificates.regenerate',
                                $certificate
                            )); ?>"
                            method="POST"
                            class="d-inline"
                        >
                            <?php echo csrf_field(); ?>

                            <button
                                type="submit"
                                class="btn btn-warning"
                                onclick="return confirm(
                                    'Regenerate this certificate PDF?'
                                )"
                            >
                                <i class="fas fa-sync me-1"></i>
                                Regenerate PDF
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>

            
            <section class="certificate-section mt-4">
                <h5 class="section-title">
                    <i class="fas fa-history text-primary me-1"></i>
                    Status Timeline
                </h5>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Action</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>
                                    <i class="fas fa-file text-info me-1"></i>
                                    Generated
                                </td>

                                <td>
                                    <?php echo e($certificate->generatedBy?->name
                                        ?? 'N/A'); ?>

                                </td>

                                <td>
                                    <?php echo e($certificate->generated_at
                                        ? \Carbon\Carbon::parse(
                                            $certificate->generated_at
                                        )->format('d M Y H:i')
                                        : 'Pending'); ?>

                                </td>

                                <td>
                                    <span
                                        class="badge bg-<?php echo e($currentStatusLevel >= 1 ? 'success' : 'secondary'); ?>"
                                    >
                                        <?php echo e($currentStatusLevel >= 1
                                            ? 'Completed'
                                            : 'Pending'); ?>

                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <i class="fas fa-check-circle text-primary me-1"></i>
                                    Checked
                                </td>

                                <td>
                                    <?php echo e($certificate->checkedBy?->name
                                        ?? 'N/A'); ?>

                                </td>

                                <td>
                                    <?php echo e($certificate->checked_at
                                        ? \Carbon\Carbon::parse(
                                            $certificate->checked_at
                                        )->format('d M Y H:i')
                                        : 'Pending'); ?>

                                </td>

                                <td>
                                    <span
                                        class="badge bg-<?php echo e($currentStatusLevel >= 2 ? 'success' : 'secondary'); ?>"
                                    >
                                        <?php echo e($currentStatusLevel >= 2
                                            ? 'Completed'
                                            : 'Pending'); ?>

                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <i class="fas fa-user-check text-success me-1"></i>
                                    Approved
                                </td>

                                <td>
                                    <?php echo e($certificate->approvedBy?->name
                                        ?? 'N/A'); ?>

                                </td>

                                <td>
                                    <?php echo e($certificate->approved_at
                                        ? \Carbon\Carbon::parse(
                                            $certificate->approved_at
                                        )->format('d M Y H:i')
                                        : 'Pending'); ?>

                                </td>

                                <td>
                                    <span
                                        class="badge bg-<?php echo e($currentStatusLevel >= 3 ? 'success' : 'secondary'); ?>"
                                    >
                                        <?php echo e($currentStatusLevel >= 3
                                            ? 'Completed'
                                            : 'Pending'); ?>

                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <i class="fas fa-envelope text-success me-1"></i>
                                    Sent
                                </td>

                                <td>
                                    <?php echo e($certificate->sentBy?->name
                                        ?? 'N/A'); ?>

                                </td>

                                <td>
                                    <?php echo e($certificate->sent_at
                                        ? \Carbon\Carbon::parse(
                                            $certificate->sent_at
                                        )->format('d M Y H:i')
                                        : 'Pending'); ?>

                                </td>

                                <td>
                                    <span
                                        class="badge bg-<?php echo e($currentStatusLevel >= 4 ? 'success' : 'secondary'); ?>"
                                    >
                                        <?php echo e($currentStatusLevel >= 4
                                            ? 'Completed'
                                            : 'Pending'); ?>

                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>


<style>
    #checkCertificateModal .modal-dialog {
        max-width: min(1000px, calc(100vw - 24px));
        height: calc(100dvh - 24px);
        max-height: calc(100dvh - 24px);
        margin: 12px auto;
    }
    #checkCertificateModal .modal-content {
        display: flex;
        flex-direction: column;
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }
    /* The form wraps the header/body/footer, so it must also be flex. */
    #checkCertificateModal .modal-content > form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    #checkCertificateModal .modal-header,
    #checkCertificateModal .modal-footer {
        flex: 0 0 auto;
        background: #fff;
        z-index: 1;
    }
    #checkCertificateModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto !important;
        overflow-x: hidden;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #8a99ac #edf1f5;
    }
    #checkCertificateModal .modal-body::-webkit-scrollbar { width: 10px; }
    #checkCertificateModal .modal-body::-webkit-scrollbar-track { background: #edf1f5; }
    #checkCertificateModal .modal-body::-webkit-scrollbar-thumb {
        background: #8a99ac;
        border-radius: 8px;
        border: 2px solid #edf1f5;
    }
    #checkCertificateModal textarea { resize: vertical; }
    @media (max-width: 576px) {
        #checkCertificateModal .modal-dialog {
            max-width: calc(100vw - 12px);
            height: calc(100dvh - 12px);
            max-height: calc(100dvh - 12px);
            margin: 6px auto;
        }
        #checkCertificateModal .modal-body { padding: 14px; }
        #checkCertificateModal .modal-footer { padding: 10px 14px; }
    }
</style>


<?php if($certificate->status === 'checked' && $isLabManager && \Illuminate\Support\Facades\Route::has('certificates.return-for-statement-review')): ?>
    <div class="alert alert-warning mt-3">
        <strong>Statements missing or need correction?</strong>
        Return this certificate for supervisor review before attempting approval.
        <form action="<?php echo e(route('certificates.return-for-statement-review', $certificate)); ?>" method="POST" class="mt-2">
            <?php echo csrf_field(); ?>
            <label for="return_review_reason" class="form-label">Reason for return</label>
            <textarea id="return_review_reason" name="reason" rows="2" minlength="10" maxlength="2000" required class="form-control mb-2" placeholder="Describe the missing or incorrect statement and required correction."></textarea>
            <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Return this certificate to generated status for supervisor review?')">
                Return for Statement Review
            </button>
        </form>
    </div>
<?php endif; ?>


<?php if($canCheckCertificate): ?>
    <div
        class="modal fade"
        id="checkCertificateModal"
        tabindex="-1"
        aria-labelledby="checkCertificateModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form
                    action="<?php echo e(route(
                        'certificates.check',
                        $certificate
                    )); ?>"
                    method="POST"
                >
                    <?php echo csrf_field(); ?>

                    <div class="modal-header">
                        <h5
                            class="modal-title"
                            id="checkCertificateModalLabel"
                        >
                            <i class="fas fa-check-circle text-primary me-1"></i>
                            Check Certificate
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            Confirm that you have reviewed certificate
                            <strong>
                                <?php echo e($certificate->certificate_number); ?>

                            </strong>.
                        </div>

                        <?php echo $__env->make('admin.certificates.partials.statement-review', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="mb-3">
                            <label
                                for="check_comments"
                                class="form-label fw-bold"
                            >
                                Checking Comments
                            </label>

                            <textarea
                                id="check_comments"
                                name="check_notes"
                                class="form-control"
                                rows="4"
                                maxlength="5000"
                                placeholder="Enter optional checking comments"
                            ><?php echo e(old('check_notes')); ?></textarea>

                            <small class="text-muted">
                                Comments will be retained in the
                                certificate review record.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-check-circle me-1"></i>
                            Confirm Checked
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>


<?php if($canApproveCertificate): ?>
    <div
        class="modal fade"
        id="approveCertificateModal"
        tabindex="-1"
        aria-labelledby="approveCertificateModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <form
                    action="<?php echo e(route(
                        'certificates.approve',
                        $certificate
                    )); ?>"
                    method="POST"
                >
                    <?php echo csrf_field(); ?>

                    <div class="modal-header">
                        <h5
                            class="modal-title"
                            id="approveCertificateModalLabel"
                        >
                            <i class="fas fa-check-double text-success me-1"></i>
                            Approve Certificate
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Approving this certificate will make it
                            available for final download and customer
                            delivery.
                        </div>

                        <p>
                            Certificate:
                            <strong>
                                <?php echo e($certificate->certificate_number); ?>

                            </strong>
                        </p>

                        <p>
                            Checked by:
                            <strong>
                                <?php echo e($certificate->checkedBy?->name
                                    ?? 'N/A'); ?>

                            </strong>
                        </p>

                        <div class="mb-3">
                            <label
                                for="approval_comments"
                                class="form-label fw-bold"
                            >
                                Approval Comments
                            </label>

                            <textarea
                                id="approval_comments"
                                name="comments"
                                class="form-control"
                                rows="4"
                                maxlength="5000"
                                placeholder="Enter optional approval comments"
                            ><?php echo e(old('comments')); ?></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            <i class="fas fa-check-double me-1"></i>
                            Confirm Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .certificate-show-page .card {
        border-radius: 14px;
    }

    .certificate-show-page .certificate-section {
        padding: 1.25rem;
        border: 1px solid #dce5f0;
        border-radius: 14px;
        background-color: #ffffff;
        box-shadow: 0 7px 20px rgba(15, 23, 42, 0.04);
    }

    .certificate-show-page .section-title {
        margin-bottom: 1rem;
        color: #172033;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .certificate-show-page .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .certificate-show-page .detail-grid-four {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .certificate-show-page .detail-grid > div {
        min-width: 0;
        padding: 12px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background-color: #f8fafc;
    }

    .certificate-show-page .detail-grid small {
        display: block;
        margin-bottom: 4px;
        color: #68768a;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .certificate-show-page .detail-grid strong {
        display: block;
        color: #172033;
        overflow-wrap: anywhere;
    }

    .certificate-workflow {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        width: 100%;
        padding: 15px 0;
    }

    .workflow-step {
        flex: 0 0 100px;
        text-align: center;
    }

    .workflow-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin: 0 auto;
        color: #ffffff;
        background-color: #adb5bd;
        border-radius: 50%;
        font-size: 18px;
        transition:
            background-color 0.25s ease,
            box-shadow 0.25s ease;
    }

    .workflow-icon.completed {
        background-color: #198754;
        box-shadow: 0 0 0 5px rgba(25, 135, 84, 0.14);
    }

    .workflow-step p {
        margin: 9px 0 0;
        color: #495057;
        font-size: 13px;
        font-weight: 700;
    }

    .workflow-line {
        flex: 1;
        max-width: 170px;
        height: 4px;
        margin-top: 21px;
        background-color: #dee2e6;
        transition: background-color 0.25s ease;
    }

    .workflow-line.completed {
        background-color: #198754;
    }

    .signature-card {
        height: 100%;
        padding: 18px;
        text-align: center;
        border: 1px solid #dce5f0;
        border-radius: 12px;
        background-color: #f8fafc;
    }

    .signature-card h6 {
        color: #172033;
        font-weight: 700;
    }

    .signature-image {
        display: block;
        max-width: 100%;
        max-height: 60px;
        margin: 8px auto;
        object-fit: contain;
    }

    .trial-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .trial-badges .badge {
        font-size: 0.68rem;
    }

    .certificate-show-page table th {
        white-space: nowrap;
    }

    @media (max-width: 991.98px) {
        .certificate-show-page .detail-grid-four {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .certificate-workflow {
            align-items: stretch;
            flex-direction: column;
            gap: 5px;
        }

        .workflow-step {
            display: flex;
            flex: none;
            align-items: center;
            gap: 12px;
            width: 100%;
            text-align: left;
        }

        .workflow-icon {
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            margin: 0;
        }

        .workflow-step p {
            margin: 0;
        }

        .workflow-line {
            flex: none;
            width: 4px;
            height: 24px;
            margin: 0 0 0 19px;
        }
    }

    @media (max-width: 575.98px) {
        .certificate-show-page .detail-grid,
        .certificate-show-page .detail-grid-four {
            grid-template-columns: 1fr;
        }

        .certificate-show-page .card-header h2 {
            font-size: 1.3rem;
        }

        .certificate-show-page .d-flex.flex-wrap.gap-2 .btn,
        .certificate-show-page .d-flex.flex-wrap.gap-2 form {
            width: 100%;
        }

        .certificate-show-page .d-flex.flex-wrap.gap-2 form .btn {
            width: 100%;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\project\ncckplclims\resources\views/admin/certificates/show.blade.php ENDPATH**/ ?>