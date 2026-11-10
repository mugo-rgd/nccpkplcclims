<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-money-check-alt me-2 text-primary"></i>
                Payment Management
            </h2>
            <div class="text-muted">
                Quotation: <strong><?php echo e($quote->quote_number); ?></strong>
            </div>
        </div>

        <?php if(Route::has('admin.payments.dashboard')): ?>
            <a href="<?php echo e(route('admin.payments.dashboard')); ?>"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Payments Dashboard
            </a>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small mb-2">Quotation Total</div>
                    <h4 class="mb-0">
                        KES <?php echo e(number_format((float)$quote->total_amount, 2)); ?>

                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small mb-2">Confirmed Amount Paid</div>
                    <h4 class="mb-0 text-success">
                        KES <?php echo e(number_format((float)$quote->amount_paid, 2)); ?>

                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small mb-2">Outstanding Balance</div>
                    <h4 class="mb-0 text-danger">
                        KES <?php echo e(number_format((float)$quote->balance_due, 2)); ?>

                    </h4>
                    <span class="badge bg-secondary mt-2">
                        <?php echo e(ucwords(str_replace('_', ' ', $quote->status))); ?>

                    </span>
                </div>
            </div>
        </div>
    </div>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.record')): ?>
        <?php if($quote->canReceivePayment()): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Record Manual Payment
                    </h5>
                </div>

                <div class="card-body">
                    <form method="POST"
                          action="<?php echo e(route('admin.payments.store', $quote)); ?>"
                          enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Payment Method</label>
                                <select name="method"
                                        class="form-select <?php $__errorArgs = ['method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        required>
                                    <?php $__currentLoopData = ['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'cheque' => 'Cheque']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"
                                            <?php if(old('method', 'bank_transfer') === $value): echo 'selected'; endif; ?>>
                                            <?php echo e($label); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Amount (KES)</label>
                                <input type="number"
                                       name="amount"
                                       step="0.01"
                                       min="0.01"
                                       max="<?php echo e($quote->balance_due); ?>"
                                       value="<?php echo e(old('amount')); ?>"
                                       class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Payment Date</label>
                                <input type="date"
                                       name="payment_date"
                                       value="<?php echo e(old('payment_date', now()->toDateString())); ?>"
                                       max="<?php echo e(now()->toDateString()); ?>"
                                       class="form-control <?php $__errorArgs = ['payment_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Transaction Reference</label>
                                <input type="text"
                                       name="reference"
                                       maxlength="120"
                                       value="<?php echo e(old('reference')); ?>"
                                       class="form-control <?php $__errorArgs = ['reference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       required>
                                <?php $__errorArgs = ['reference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Receipt (PDF/JPG/PNG, max 5MB)</label>
                                <input type="file"
                                       name="receipt"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="form-control <?php $__errorArgs = ['receipt'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Cheque Clearance Evidence</label>
                                <input type="file"
                                       name="clearance"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="form-control">
                                <div class="form-text">
                                    Required before a cheque payment can be confirmed.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <textarea name="notes"
                                          class="form-control"
                                          rows="2"><?php echo e(old('notes')); ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-paper-plane me-1"></i>
                            Submit for Verification
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Payment Transactions</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Evidence</th>
                            <th>Recorded By</th>
                            <th>Verified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <?php echo e($payment->payment_date?->format('d M Y') ?? '—'); ?>

                                </td>

                                <td>
                                    <?php echo e(ucwords(str_replace('_', ' ', $payment->method))); ?>

                                </td>

                                <td>
                                    <span class="font-monospace">
                                        <?php echo e($payment->reference ?? '—'); ?>

                                    </span>
                                </td>

                                <td class="text-end text-nowrap">
                                    KES <?php echo e(number_format((float)$payment->amount, 2)); ?>

                                </td>

                                <td>
                                    <?php switch($payment->status):
                                        case ('confirmed'): ?>
                                            <span class="badge bg-success">Confirmed</span>
                                            <?php break; ?>
                                        <?php case ('pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            <?php break; ?>
                                        <?php case ('rejected'): ?>
                                            <span class="badge bg-danger">Rejected</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge bg-secondary">
                                                <?php echo e(ucfirst($payment->status)); ?>

                                            </span>
                                    <?php endswitch; ?>
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.receipts.download')): ?>
                                            <?php if($payment->receipt_path && Route::has('admin.payments.receipt')): ?>
                                                <a href="<?php echo e(route('admin.payments.receipt', $payment)); ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-download me-1"></i>
                                                    Receipt
                                                </a>
                                            <?php endif; ?>

                                            <?php if($payment->clearance_path && Route::has('admin.payments.clearance')): ?>
                                                <a href="<?php echo e(route('admin.payments.clearance', $payment)); ?>"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    Clearance
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td><?php echo e($payment->recorder?->name ?? 'System / M-Pesa'); ?></td>

                                <td>
                                    <?php echo e($payment->confirmer?->name ?? '—'); ?>

                                </td>

                                <td>
                                    <?php if($payment->status === 'pending' && $payment->method !== 'mpesa'): ?>
                                        <div class="d-flex flex-wrap align-items-start gap-2">

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.confirm')): ?>
                                                <?php if(
                                                    $payment->recorded_by !== null &&
                                                    (int)$payment->recorded_by !== (int)auth()->id()
                                                ): ?>
                                                    <form method="POST"
                                                          action="<?php echo e(route('admin.payments.confirm', $payment)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit"
                                                                class="btn btn-sm btn-success"
                                                                onclick="return confirm('Confirm that funds have been independently verified?')">
                                                            <i class="fas fa-check me-1"></i>
                                                            Confirm
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.reject')): ?>
                                                <form method="POST"
                                                      action="<?php echo e(route('admin.payments.reject', $payment)); ?>"
                                                      class="d-flex gap-1">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="text"
                                                           name="rejection_reason"
                                                           maxlength="2000"
                                                           class="form-control form-control-sm"
                                                           placeholder="Rejection reason"
                                                           aria-label="Rejection reason"
                                                           required>
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Reject this payment?')">
                                                        Reject
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                        </div>
                                    <?php elseif($payment->status === 'confirmed'): ?>
                                        <span class="text-success small">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Verified
                                        </span>
                                    <?php elseif($payment->status === 'rejected'): ?>
                                        <span class="text-danger small">Rejected</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    No payment transactions have been recorded.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo e($payments->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\project\ncckplclims\resources\views/admin/payments/index.blade.php ENDPATH**/ ?>