<?php $__env->startSection('title', 'Payment Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-1"><i class="fas fa-money-check-alt text-primary me-2"></i>Payment Management</h2>
            <p class="text-muted mb-0">Finance and Accounts · Payments across quotations</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Confirmed receipts (KES)</small><h4><?php echo e(number_format((float) $summary['confirmed_total'], 2)); ?></h4></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Pending verification</small><h4><?php echo e(number_format($summary['pending_count'])); ?></h4></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Confirmed transactions</small><h4><?php echo e(number_format($summary['confirmed_count'])); ?></h4></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><small class="text-muted">Outstanding sent quotations (KES)</small><h4><?php echo e(number_format((float) $summary['outstanding_total'], 2)); ?></h4></div></div></div>
    </div>

    <div class="card mb-3"><div class="card-body">
        <form method="GET" action="<?php echo e(route('admin.payments.dashboard')); ?>" class="row g-2 align-items-end">
            <div class="col-md-4"><label class="form-label">Quotation or reference</label><input class="form-control" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search quotation / transaction"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All statuses</option><?php $__currentLoopData = ['pending'=>'Pending', 'confirmed'=>'Confirmed', 'rejected'=>'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            <div class="col-md-3"><label class="form-label">Method</label><select class="form-select" name="method"><option value="">All methods</option><?php $__currentLoopData = ['bank_transfer'=>'Bank transfer', 'cash'=>'Cash', 'cheque'=>'Cheque', 'mpesa'=>'M-Pesa']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(request('method') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary" type="submit">Filter</button><a class="btn btn-outline-secondary" href="<?php echo e(route('admin.payments.dashboard')); ?>">Reset</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Date</th><th>Quotation</th><th>Method</th><th>Reference</th><th class="text-end">Amount (KES)</th><th>Status</th><th>Recorded by</th><th>Receipt</th><th>Action</th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($payment->created_at?->format('d M Y H:i') ?? '—'); ?></td>
                <td><?php echo e($payment->quote?->quote_number ?? 'Quotation unavailable'); ?></td>
                <td><?php echo e(ucwords(str_replace('_', ' ', $payment->method))); ?></td>
                <td><?php echo e($payment->reference); ?></td>
                <td class="text-end"><?php echo e(number_format((float) $payment->amount, 2)); ?></td>
                <td><span class="badge bg-<?php echo e($payment->status === 'confirmed' ? 'success' : ($payment->status === 'pending' ? 'warning text-dark' : 'secondary')); ?>"><?php echo e(ucfirst($payment->status)); ?></span></td>
                <td><?php echo e($payment->recorder?->name ?? '—'); ?></td>
                <td><?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('payments.receipts.download')): ?> <?php if($payment->receipt_path && Route::has('admin.payments.receipt')): ?><a href="<?php echo e(route('admin.payments.receipt', $payment)); ?>">Receipt</a><?php else: ?> — <?php endif; ?> <?php else: ?> — <?php endif; ?></td>
                <td><?php if($payment->quote && Route::has('admin.payments.index')): ?><a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.payments.index', $payment->quote)); ?>">Manage</a><?php else: ?> — <?php endif; ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No payment transactions match your filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div></div><div class="card-footer"><?php echo e($transactions->links()); ?></div></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH G:\project\ncckplclims\resources\views/admin/payments/dashboard.blade.php ENDPATH**/ ?>