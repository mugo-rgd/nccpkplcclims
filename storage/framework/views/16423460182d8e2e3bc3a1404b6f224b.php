<?php $__env->startSection('title', 'Quotation #' . ($quote->quote_number ?? $quote->id)); ?>

<?php $__env->startSection('content'); ?>
<?php
    // Resolve the related calibration even when the controller does not pass it separately.
    $calibration = $calibration ?? $quote->calibration;
?>
<div class="container-fluid py-4">
    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e(session('success')); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>Unable to process quotation</strong>

        <ul class="mb-0 mt-2">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
<?php endif; ?>

<?php if($quote->isCancelled()): ?>
    <div class="alert alert-warning mt-3">
        <i class="fas fa-ban me-2"></i>

        This quotation has been cancelled and is
        retained for audit purposes.

        <?php if(!empty($quote->metadata['replaced_by_quote_id'])): ?>
            <a href="<?php echo e(route(
                'admin.quotes.show',
                $quote->metadata['replaced_by_quote_id']
            )); ?>" class="fw-bold">
                View Replacement Quotation
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo e(session('error')); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>

    <?php if(session('info')): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>
            <?php echo e(session('info')); ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h3 class="mb-1">
                        <i class="fas fa-file-invoice-dollar text-success me-2"></i>
                        Quotation #<?php echo e($quote->quote_number ?? $quote->id); ?>

                    </h3>

                    <small class="text-muted">
                        Created:
                        <?php echo e(optional($quote->created_at)->format('d M Y, H:i') ?? 'N/A'); ?>

                    </small>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a
                        href="<?php echo e(url()->previous()); ?>"
                        class="btn btn-outline-secondary"
                    >
                        <i class="fas fa-arrow-left me-1"></i>
                        Back
                    </a>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.view')): ?>
                        <?php if($quote->is_external && Route::has('admin.payments.index')): ?>
                            <a href="<?php echo e(route('admin.payments.index', $quote)); ?>"
                               class="btn btn-outline-success">
                                <i class="fas fa-money-check-alt me-1"></i>
                                Manage Payments
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <a
                        href="<?php echo e(route('admin.quotes.download', $quote)); ?>"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-file-pdf me-1"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-3 p-lg-4">
            <div class="row g-4 mb-4">
                
                <div class="col-lg-6">
                    <h5 class="mb-3">
                        <i class="fas fa-building text-primary me-2"></i>
                        Customer Information
                    </h5>

                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Company
                                    </small>

                                    <span class="fw-semibold">
                                        <?php echo e($quote->customer?->company_name ?? 'N/A'); ?>

                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Contact Person
                                    </small>

                                    <span class="fw-semibold">
                                        <?php echo e($quote->customer?->contact_person ?? 'N/A'); ?>

                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Phone
                                    </small>

                                    <?php if(($quote->customer?->company_phone ?: $quote->customer?->phone)): ?>
                                        <a
                                            href="tel:<?php echo e(($quote->customer->company_phone ?: $quote->customer->phone)); ?>"
                                            class="text-decoration-none fw-semibold"
                                        >
                                            <?php echo e(($quote->customer->company_phone ?: $quote->customer->phone)); ?>

                                        </a>
                                    <?php else: ?>
                                        <span class="fw-semibold">N/A</span>
                                    <?php endif; ?>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Email
                                    </small>

                                    <?php if(($quote->customer?->company_email ?: $quote->customer?->email)): ?>
                                        <a
                                            href="mailto:<?php echo e(($quote->customer->company_email ?: $quote->customer->email)); ?>"
                                            class="text-decoration-none fw-semibold text-break"
                                        >
                                            <?php echo e(($quote->customer->company_email ?: $quote->customer->email)); ?>

                                        </a>
                                    <?php else: ?>
                                        <span class="fw-semibold">N/A</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-lg-6">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Quotation Details
                    </h5>

                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Quotation Number
                                    </small>

                                    <span class="fw-semibold">
                                        <?php echo e($quote->quote_number ?? 'N/A'); ?>

                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Status
                                    </small>

                                    <span class="badge bg-<?php echo e($quote->status_color); ?>">
                                        <?php echo e($quote->status_label); ?>

                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Valid Until
                                    </small>

                                    <span class="fw-semibold">
                                        <?php echo e($quote->valid_until?->format('d M Y') ?? 'N/A'); ?>

                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Currency
                                    </small>

                                    <span class="fw-semibold">
                                        <?php echo e($quote->currency_code); ?>

                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Created At
                                    </small>

                                    <span class="fw-semibold">
                                        <?php echo e($quote->created_at?->format('d M Y, H:i') ?? 'N/A'); ?>

                                    </span>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">
                                        Last Updated
                                    </small>

                                    <span class="fw-semibold">
                                        <?php echo e($quote->updated_at?->format('d M Y, H:i') ?? 'N/A'); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
     <div class="mt-5 mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-list text-primary me-2"></i>
            Quotation Items
        </h5>

        <span class="badge bg-primary px-3 py-2">
            <?php echo e(count($quote->items_data)); ?>

            <?php echo e(count($quote->items_data) === 1 ? 'item' : 'items'); ?>

        </span>
    </div>
</div>

            <div class="table-responsive border rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
<tr>
    <th style="width:60px;">#</th>
    <th>Equipment Name</th>
    <th>Serial Number</th>
    <th>Description</th>
    <th class="text-end">Qty</th>
    <th class="text-end">Unit Price</th>
    <th class="text-end">Total</th>
</tr>
</thead>

                    <tbody>
<?php $__empty_1 = true; $__currentLoopData = $quote->items_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

<?php
    $equipmentName = $item['equipment_name']
        ?? $calibration?->equipment_name
        ?? $calibration?->equipment?->name
        ?? 'N/A';

    $serialNumber = $item['serial_number']
        ?? $calibration?->serial_number
        ?? $calibration?->equipment?->serial_number
        ?? 'N/A';

    $description = $item['description']
        ?? 'Calibration Service';

    $quantity = (float) ($item['quantity'] ?? 1);

    $unitPrice = (float) (
        $item['unit_price']
        ?? $item['price']
        ?? $item['rate']
        ?? 0
    );

    $itemTotal = (float) (
        $item['total']
        ?? $item['amount']
        ?? ($quantity * $unitPrice)
    );
?>

<tr>

    <td><?php echo e($loop->iteration); ?></td>

    <td>
        <strong><?php echo e($equipmentName); ?></strong>
    </td>

    <td>
        <?php echo e($serialNumber); ?>

    </td>

    <td>
        <?php echo e($description); ?>


        <?php if(!empty($item['details'])): ?>
            <br>
            <small class="text-muted">
                <?php echo e($item['details']); ?>

            </small>
        <?php endif; ?>
    </td>

    <td class="text-end">
        <?php echo e(number_format($quantity,0)); ?>

    </td>

    <td class="text-end">
        <?php echo e($quote->currency_code); ?>

        <?php echo e(number_format($unitPrice,2)); ?>

    </td>

    <td class="text-end fw-bold">
        <?php echo e($quote->currency_code); ?>

        <?php echo e(number_format($itemTotal,2)); ?>

    </td>

</tr>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

<tr>
    <td colspan="7" class="text-center py-4">
        No quotation items found.
    </td>
</tr>

<?php endif; ?>
</tbody>

                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end">
                                <strong>Subtotal</strong>
                            </td>

                            <td class="text-end">
                                <strong>
                                    <?php echo e($quote->formatted_subtotal); ?>

                                </strong>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="6" class="text-end">
                                <strong>
                                    Tax (<?php echo e(number_format((float) $quote->tax_rate, 2)); ?>%)
                                </strong>
                            </td>

                            <td class="text-end">
                                <strong>
                                    <?php echo e($quote->formatted_tax); ?>

                                </strong>
                            </td>
                        </tr>

                        <?php if((float) $quote->discount_amount > 0): ?>
                            <tr>
                                <td colspan="6" class="text-end">
                                    <strong>Discount</strong>
                                </td>

                                <td class="text-end text-danger">
                                    <strong>
                                        -<?php echo e($quote->formatted_discount); ?>

                                    </strong>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr class="table-success">
                            <td colspan="6" class="text-end fs-5">
                                <strong>Total Amount</strong>
                            </td>

                            <td class="text-end fs-5">
                                <strong>
                                    <?php echo e($quote->formatted_total); ?>

                                </strong>
                            </td>
                        </tr>

                            <tr>
                                <td colspan="6" class="text-end">
                                    <strong>Amount Paid</strong>
                                </td>

                                <td class="text-end text-success">
                                    <strong>
                                        <?php echo e($quote->formatted_amount_paid); ?>

                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="6" class="text-end">
                                    <strong>Balance Due</strong>
                                </td>

                                <td class="text-end <?php echo e($quote->balance_due > 0 ? 'text-danger' : 'text-success'); ?>">
                                    <strong>
                                        <?php echo e($quote->formatted_balance_due); ?>

                                    </strong>
                                </td>
                            </tr>
                    </tfoot>
                </table>
            </div>

            
            <?php if($quote->notes || $quote->terms): ?>
                <div class="row g-4 mt-2">
                    <?php if($quote->notes): ?>
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-sticky-note text-warning me-2"></i>
                                        Notes
                                    </h5>

                                    <div
                                        class="text-muted"
                                        style="white-space: pre-line;"
                                    ><?php echo e($quote->notes); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($quote->terms): ?>
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h5>
                                        <i class="fas fa-file-contract text-primary me-2"></i>
                                        Terms & Conditions
                                    </h5>

                                    <div
                                        class="text-muted"
                                        style="white-space: pre-line;"
                                    ><?php echo e($quote->terms); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <?php if($calibration): ?>
                <div class="mt-4">
                    <h5 class="mb-3">
                        <i class="fas fa-tools text-info me-2"></i>
                        Related Calibration
                    </h5>

                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-9">
                                    <div class="row g-3">
                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">
                                                Calibration ID
                                            </small>

                                            <span class="fw-semibold">
                                                #<?php echo e($calibration->id); ?>

                                            </span>
                                        </div>

                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">
                                                Equipment
                                            </small>

                                            <span class="fw-semibold">
                                                <?php echo e($calibration->equipment_name
                                                    ?? $calibration?->equipment?->name
                                                    ?? 'N/A'); ?>

                                            </span>
                                        </div>

                                        <div class="col-sm-4">
                                            <small class="text-muted d-block">
                                                Status
                                            </small>

                                            <span class="badge bg-secondary">
                                                <?php echo e(ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $calibration->status ?? 'N/A'
                                                        )
                                                    )); ?>

                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 text-lg-end">
                                    <?php if(Route::has('calibrations.show')): ?>
                                        <a
                                            href="<?php echo e(route('calibrations.show', $calibration)); ?>"
                                            class="btn btn-info"
                                        >
                                            <i class="fas fa-eye me-1"></i>
                                            View Calibration
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            

<?php
    $canReplaceQuotation =
        in_array($quote->status, [
            \App\Models\Quote::STATUS_DRAFT,
            \App\Models\Quote::STATUS_APPROVED,
            \App\Models\Quote::STATUS_SENT,
            \App\Models\Quote::STATUS_REJECTED,
        ], true)
        && (float) ($quote->amount_paid ?? 0) <= 0
        && auth()->user()->hasAnyRole([
            'super_admin',
            'lab_manager',
        ]);
?>

<?php if($canReplaceQuotation): ?>
    <div class="card border-warning mt-4 shadow-sm">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0 text-dark">
                <i class="fas fa-exchange-alt me-2"></i>
                Cancel &amp; Replace Quotation
            </h5>
        </div>

        <div class="card-body">
            <p class="text-muted mb-3">
                Cancel this quotation and create a new draft
                using the customer's currently approved VAT rate.
                The original quotation will be retained for
                audit purposes.
            </p>

            <form
                action="<?php echo e(route('admin.quotes.cancel-and-replace', $quote)); ?>"
                method="POST"
                onsubmit="return confirm('Cancel quotation <?php echo e($quote->quote_number); ?> and create a replacement? This action cannot be undone automatically.');"
            >
                <?php echo csrf_field(); ?>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sync-alt me-2"></i>
                        Cancel &amp; Create Replacement
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>


           

<?php if($quote->approved_at || $quote->sent_at || $quote->rejected_at): ?>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Quotation Decision History</h5></div>
        <div class="card-body">
            <?php if($quote->approved_at): ?>
                <p class="mb-2"><strong>Approved:</strong> <?php echo e($quote->approved_at->format('d M Y, H:i')); ?>

                    <?php if($quote->approver): ?> by <?php echo e($quote->approver->name); ?> <?php endif; ?>
                </p>
                <?php if($quote->approval_notes): ?><p class="text-muted" style="white-space:pre-line"><?php echo e($quote->approval_notes); ?></p><?php endif; ?>
            <?php endif; ?>
            <?php if($quote->sent_at): ?><p class="mb-2"><strong>Sent:</strong> <?php echo e($quote->sent_at->format('d M Y, H:i')); ?></p><?php endif; ?>
            <?php if($quote->rejected_at): ?>
                <p class="mb-2"><strong>Rejected:</strong> <?php echo e($quote->rejected_at->format('d M Y, H:i')); ?></p>
                <p class="text-danger" style="white-space:pre-line"><?php echo e($quote->rejection_reason); ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if($quote->isApproved() && $quote->is_external): ?>
    <div class="alert alert-info mt-4">
        <i class="fas fa-paper-plane me-2"></i>
        This quotation has been approved and is ready to be sent to the customer.
        Use the authorized Send Quotation action on the calibration page.
        <?php if($calibration && Route::has('calibrations.show')): ?>
            <a href="<?php echo e(route('calibrations.show', $calibration)); ?>" class="alert-link">View Calibration</a>
        <?php endif; ?>
    </div>
<?php endif; ?>


<?php
    $canReview = auth()->user()?->hasAnyRole([
        'supervisor',
        'lab_manager',
        'super_admin',
    ]) ?? false;
?>

<?php if($canReview && ($quote->canBeApproved() || $quote->canBeRejected())): ?>
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-clipboard-check me-2"></i>
                Quotation Review and Decision
            </h5>
        </div>

        <div class="card-body">

            <?php if($quote->canBeApproved() && !$quote->isExpiredByDate()): ?>
                <form
                    action="<?php echo e(route('admin.quotes.approve', $quote)); ?>"
                    method="POST"
                    class="mb-4"
                >
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>

                    <label for="approval_notes" class="form-label">
                        Approval Notes (Optional)
                    </label>

                    <textarea
                        id="approval_notes"
                        name="approval_notes"
                        class="form-control mb-3"
                        rows="3"
                        maxlength="2000"
                    ><?php echo e(old('approval_notes')); ?></textarea>

                    <button type="submit" class="btn btn-success"
                        onclick="return confirm('Approve this quotation?')">
                        <i class="fas fa-check-circle me-1"></i>
                        Approve Quotation
                    </button>
                </form>
            <?php endif; ?>

            <?php if($quote->canBeRejected()): ?>
                <form
                    action="<?php echo e(route('admin.quotes.reject', $quote)); ?>"
                    method="POST"
                >
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>

                    <label for="rejection_reason" class="form-label">
                        Rejection Reason
                        <span class="text-danger">*</span>
                    </label>

                    <textarea
                        id="rejection_reason"
                        name="rejection_reason"
                        class="form-control mb-3"
                        rows="3"
                        minlength="5"
                        maxlength="2000"
                        required
                    ><?php echo e(old('rejection_reason')); ?></textarea>

                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Reject this quotation?')">
                        <i class="fas fa-times-circle me-1"></i>
                        Reject Quotation
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\project\ncckplclims\resources\views/admin/quotes/show.blade.php ENDPATH**/ ?>