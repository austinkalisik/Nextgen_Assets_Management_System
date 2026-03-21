<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <div class="space-y-10">

        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Dashboard
                </h1>
                <p class="text-sm text-gray-500">
                    Overview of your assets and system activity
                </p>
            </div>

            <!-- QUICK ACTION -->
            <a href="/items"
                class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                + Add Asset
            </a>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

            <div class="p-6 text-white shadow-lg rounded-2xl bg-gradient-to-r from-blue-500 to-blue-700">
                <p class="text-sm opacity-80">Total Assets</p>
                <h2 class="text-3xl font-bold"><?php echo e($totalItems); ?></h2>
            </div>

            <div class="p-6 text-white shadow-lg rounded-2xl bg-gradient-to-r from-purple-500 to-purple-700">
                <p class="text-sm opacity-80">Total Brands</p>
                <h2 class="text-3xl font-bold"><?php echo e($totalBrands); ?></h2>
            </div>

            <div class="p-6 text-white shadow-lg rounded-2xl bg-gradient-to-r from-green-500 to-green-700">
                <p class="text-sm opacity-80">Recently Added</p>
                <h2 class="text-3xl font-bold"><?php echo e($latestItems->count()); ?></h2>
            </div>

        </div>

        <!-- GRID -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- RECENT ASSETS -->
            <div class="p-6 bg-white shadow-lg lg:col-span-2 rounded-2xl">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700">
                        Recent Assets
                    </h3>

                    <a href="/items" class="text-sm text-blue-500 hover:underline">
                        View all →
                    </a>
                </div>

                <div class="space-y-3">

                    <?php $__empty_1 = true; $__currentLoopData = $latestItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div
                            class="flex items-center justify-between p-4 transition rounded-lg bg-slate-50 hover:bg-slate-100">

                            <div>
                                <p class="font-medium text-gray-800">
                                    <?php echo e($item->part_name); ?>

                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($item->brand); ?>

                                </p>
                            </div>

                            <span class="text-xs text-gray-400">
                                <?php echo e($item->created_at->diffForHumans()); ?>

                            </span>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="py-6 text-center text-gray-400">
                            No recent assets found
                        </div>
                    <?php endif; ?>

                </div>

            </div>

            <!-- SYSTEM SUMMARY -->
            <div class="p-6 bg-white shadow-lg rounded-2xl">

                <h3 class="mb-4 font-semibold text-gray-700">
                    System Summary
                </h3>

                <div class="space-y-4 text-sm">

                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Assets</span>
                        <span class="font-semibold"><?php echo e($totalItems); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Brands</span>
                        <span class="font-semibold"><?php echo e($totalBrands); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">System Status</span>
                        <span class="font-semibold text-green-500">Active</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Update</span>
                        <span class="font-semibold text-gray-700">
                            <?php echo e(now()->format('M d, Y')); ?>

                        </span>
                    </div>

                </div>

                <!-- MINI ACTIONS -->
                <div class="mt-6 space-y-2">

                    <a href="/items"
                        class="block w-full px-4 py-2 text-sm text-center rounded-lg bg-slate-100 hover:bg-slate-200">
                        Manage Assets
                    </a>

                    <a href="#"
                        class="block w-full px-4 py-2 text-sm text-center rounded-lg bg-slate-100 hover:bg-slate-200">
                        Settings
                    </a>

                </div>

            </div>

        </div>

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/dashboard.blade.php ENDPATH**/ ?>