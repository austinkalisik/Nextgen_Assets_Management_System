<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>NextGen Assets</title>
    <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.jsx']); ?>
</head>

<body class="bg-slate-100">
    <div id="app"></div>
</body>

</html><?php /**PATH A:\Project\Nextgen-assets-management-system\resources\views/spa.blade.php ENDPATH**/ ?>