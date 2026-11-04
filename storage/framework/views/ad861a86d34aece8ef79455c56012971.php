<?php $__env->startSection('title', 'Certificates'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h1 class="mb-1">
                        <i class="fas fa-file-pdf text-danger me-2"></i>
                        Certificates
                        <span class="badge bg-secondary"><?php echo e($certificates->total()); ?></span>
                    </h1>
                    <p class="text-muted mb-0">
                        Manage calibration certificates, approvals, and customer delivery.
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-3 g-2">
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <h6 class="card-title">Total</h6>
                            <h3 class="mb-0"><?php echo e($certificates->total()); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body">
                            <h6 class="card-title">Pending</h6>
                            <h3 class="mb-0"><?php echo e($certificates->where('status', 'draft')->count() + $certificates->where('status', 'generated')->count()); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                            <h6 class="card-title">Checked</h6>
                            <h3 class="mb-0"><?php echo e($certificates->where('status', 'checked')->count()); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <h6 class="card-title">Approved/Sent</h6>
                            <h3 class="mb-0"><?php echo e($certificates->where('status', 'approved')->count() + $certificates->where('status', 'sent')->count()); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 g-md-3">
                        <div class="col-md-3 col-sm-6">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search by certificate # or equipment..."
                                   value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status); ?>" <?php echo e(request('status') == $status ? 'selected' : ''); ?>>
                                        <?php echo e(ucfirst($status)); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <a href="<?php echo e(route('certificates.index')); ?>" class="btn btn-secondary btn-sm w-100">
                                <i class="fas fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Certificate #</th>
                                    <th>Equipment</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Test Result</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $certificate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($certificate->id); ?></td>
                                    <td>
                                        <code><?php echo e($certificate->certificate_number); ?></code>
                                    </td>
                                    <td><?php echo e($certificate->equipment_name ?? 'N/A'); ?></td>
                                    <td><?php echo e(optional($certificate->customer)->company_name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($certificate->status_color); ?>">
                                            <?php echo e($certificate->status_label); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo e($certificate->test_result === 'Pass' ? 'success' : 'danger'); ?>">
                                            <?php echo e($certificate->test_result ?? 'N/A'); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($certificate->created_at->format('Y-m-d')); ?></td>
                                    <td>
    <div class="btn-group" role="group">
        <a
            href="<?php echo e(route('certificates.show', $certificate)); ?>"
            class="btn btn-sm btn-info"
            title="View Certificate"
        >
            <i class="fas fa-eye"></i>
        </a>

        <a
            href="<?php echo e(route('certificates.preview', $certificate)); ?>"
            target="_blank"
            class="btn btn-sm btn-primary"
            title="Preview PDF"
        >
            <i class="fas fa-file-pdf"></i>
        </a>

        <a
            href="<?php echo e(route('certificates.download', [
                'certificate' => $certificate,
                'force' => true,
            ])); ?>"
            class="btn btn-sm btn-success"
            title="Regenerate and Download PDF"
        >
            <i class="fas fa-download"></i>
        </a>
    </div>
</td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-file-pdf fa-3x text-muted mb-3 d-block"></i>
                                        <h5 class="text-muted">No certificates found</h5>
                                        <p class="text-muted">Certificates will appear here once generated.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
                <div>
                    <span class="text-muted">
                        Showing <strong><?php echo e($certificates->firstItem() ?? 0); ?></strong>
                        to <strong><?php echo e($certificates->lastItem() ?? 0); ?></strong>
                        of <strong><?php echo e($certificates->total()); ?></strong> certificates
                    </span>
                </div>
                <div>
                    <?php echo e($certificates->appends(request()->query())->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\project\ncckplclims\resources\views/admin/certificates/index.blade.php ENDPATH**/ ?>