<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', config('app.name', 'KPLC Meter Lab')); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <link rel="stylesheet" href="<?php echo e(asset('css/layout.css')); ?>">


    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>
<div id="app">
    <?php echo $__env->make('layouts.partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="mobile-sidebar-backdrop" id="mobileSidebarBackdrop"></div>

    <div class="container-fluid px-0">
        <div class="row g-0">
            <?php echo $__env->make('layouts.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div id="appMainContent"
                 class="col-md-10 main-content"
                 style="<?php echo e(auth()->check() ? '' : 'margin-left: auto; margin-right: auto; max-width: 1200px;'); ?>">

                <?php echo $__env->make('layouts.partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('layouts.partials.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH G:\project\ncckplclims\resources\views/layouts/app.blade.php ENDPATH**/ ?>