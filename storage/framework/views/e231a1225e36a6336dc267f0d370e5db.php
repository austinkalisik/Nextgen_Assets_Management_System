<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(config('app.name', 'NextGen Assets Management System')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 text-slate-800">
    <div class="flex min-h-screen">
        <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="flex-1 p-6">
            <div class="mx-auto max-w-7xl">
                <?php
                    $lowStockCount = \App\Models\Item::where('quantity', '<=', 3)->count();
                    $activeAssignmentsCount = \App\Models\Assignment::whereNull('returned_at')->count();
                    $todayActivityCount = \App\Models\AssetLog::whereDate('created_at', today())->count();

                    $notificationItems = collect([
                        $lowStockCount > 0 ? $lowStockCount . ' low stock asset(s)' : null,
                        $activeAssignmentsCount > 0 ? $activeAssignmentsCount . ' active assignment(s)' : null,
                        $todayActivityCount > 0 ? $todayActivityCount . ' activity log(s) today' : null,
                    ])->filter()->values();

                    $notificationCount = $notificationItems->count();
                ?>

                <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
                    <form method="GET" action="<?php echo e(route('items.index')); ?>" class="w-full max-w-2xl">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                                placeholder="Search assets, tags, serial numbers, locations..."
                                class="w-full py-3 pr-4 bg-white border shadow-sm rounded-2xl border-slate-200 pl-11 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        </div>
                    </form>

                    <div class="flex items-center gap-3">
                        <div class="relative group">
                            <button type="button"
                                class="relative px-4 py-3 bg-white border shadow-sm rounded-2xl border-slate-200 hover:bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9" />
                                </svg>

                                <?php if($notificationCount > 0): ?>
                                    <span
                                        class="absolute -right-1 -top-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-semibold text-white">
                                        <?php echo e($notificationCount); ?>

                                    </span>
                                <?php endif; ?>
                            </button>

                            <div
                                class="absolute right-0 z-50 invisible p-4 mt-2 transition bg-white border shadow-xl opacity-0 w-80 rounded-2xl border-slate-200 group-hover:visible group-hover:opacity-100">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
                                    <span class="text-xs text-slate-400"><?php echo e(now()->format('d M Y')); ?></span>
                                </div>

                                <div class="space-y-3 text-sm">
                                    <?php $__empty_1 = true; $__currentLoopData = $notificationItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="px-3 py-2 rounded-xl bg-slate-50 text-slate-700">
                                            <?php echo e($notice); ?>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="px-3 py-2 rounded-xl bg-slate-50 text-slate-500">
                                            No current alerts.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-white border shadow-sm rounded-2xl border-slate-200">
                            <div class="text-sm font-semibold text-slate-900"><?php echo e(auth()->user()->name ?? 'User'); ?></div>
                            <div class="text-xs text-slate-500">
                                <?php echo e(ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'staff'))); ?></div>
                        </div>
                    </div>
                </div>

                <?php if(session('success')): ?>
                    <div class="px-4 py-3 mb-4 text-green-700 border border-green-200 rounded-xl bg-green-50">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="px-4 py-3 mb-4 text-red-700 border border-red-200 rounded-xl bg-red-50">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="px-4 py-3 mb-4 text-red-700 border border-red-200 rounded-xl bg-red-50">
                        <p class="mb-2 font-semibold">Please fix the following errors:</p>
                        <ul class="text-sm list-disc list-inside">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>
</body>

</html><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/layouts/app.blade.php ENDPATH**/ ?>