

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-4xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-slate-500">Welcome back, <?php echo e(auth()->user()->name ?? 'User'); ?></p>
        </div>
        <div class="flex gap-3">
            <a href="<?php echo e(route('items.create')); ?>"
                class="rounded-xl bg-blue-600 px-4 py-2.5 text-white font-semibold hover:bg-blue-700">
                + Add Asset
            </a>
            <a href="<?php echo e(route('assignments.create')); ?>"
                class="rounded-xl bg-slate-900 px-4 py-2.5 text-white font-semibold hover:bg-slate-800">
                + Assign Asset
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 xl:grid-cols-5">
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Total Assets</p>
            <h2 class="mt-2 text-3xl font-bold"><?php echo e($totalAssets); ?></h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Available</p>
            <h2 class="mt-2 text-3xl font-bold text-green-600"><?php echo e($availableAssets); ?></h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Assigned</p>
            <h2 class="mt-2 text-3xl font-bold text-amber-500"><?php echo e($assignedAssets); ?></h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Maintenance</p>
            <h2 class="mt-2 text-3xl font-bold text-red-500"><?php echo e($maintenanceAssets); ?></h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Low Stock</p>
            <h2 class="mt-2 text-3xl font-bold text-orange-500"><?php echo e($lowStockAssets); ?></h2>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-2">
        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Recent Assignments</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Asset</th>
                            <th class="px-6 py-3 text-left">User</th>
                            <th class="px-6 py-3 text-left">Department</th>
                            <th class="px-6 py-3 text-left">Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?php echo e($assignment->item->name ?? '-'); ?></td>
                                <td class="px-6 py-4"><?php echo e($assignment->user->name ?? '-'); ?></td>
                                <td class="px-6 py-4"><?php echo e($assignment->department->name ?? '-'); ?></td>
                                <td class="px-6 py-4"><?php echo e(optional($assignment->assigned_at)->format('d M Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-500">No assignments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Recent Activity</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="pb-2 border-b">
                        <p class="font-medium"><?php echo e(ucfirst(str_replace('_', ' ', $activity->action))); ?></p>
                        <p class="text-slate-500">
                            <?php echo e($activity->item->name ?? 'Unknown asset'); ?>

                            by
                            <?php echo e($activity->user->name ?? 'System'); ?>

                        </p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No recent activity found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-lg font-semibold">Category Summary</h2>
            <div class="space-y-3">
                <?php $__currentLoopData = $categorySummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between pb-2 border-b">
                        <span><?php echo e($category->name); ?></span>
                        <span><?php echo e($category->items_count); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-lg font-semibold">Department Summary</h2>
            <div class="space-y-3">
                <?php $__currentLoopData = $departmentSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between pb-2 border-b">
                        <span><?php echo e($department->name); ?></span>
                        <span><?php echo e($department->items_count); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/dashboard/index.blade.php ENDPATH**/ ?>