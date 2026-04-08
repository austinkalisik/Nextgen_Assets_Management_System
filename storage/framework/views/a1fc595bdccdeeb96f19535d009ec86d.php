

<?php $__env->startSection('content'); ?>
    <?php if($dashboardMode === 'admin'): ?>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-4xl font-bold text-slate-900">Admin Dashboard</h1>
                <p class="mt-1 text-slate-500">Operational control center for assets, assignments, users, and system activity.
                </p>
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

        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3 xl:grid-cols-6">
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Total Assets</p>
                <h2 class="mt-2 text-3xl font-bold"><?php echo e($totalAssets); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Available</p>
                <h2 class="mt-2 text-3xl font-bold text-emerald-600"><?php echo e($availableAssets); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Assigned</p>
                <h2 class="mt-2 text-3xl font-bold text-amber-500"><?php echo e($assignedAssets); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Maintenance</p>
                <h2 class="mt-2 text-3xl font-bold text-rose-500"><?php echo e($maintenanceAssets); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Low Stock</p>
                <h2 class="mt-2 text-3xl font-bold text-orange-500"><?php echo e($lowStockAssets); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Overdue</p>
                <h2 class="mt-2 text-3xl font-bold text-red-600"><?php echo e($overdueAssignments); ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-3">
            <div class="bg-white shadow xl:col-span-2 rounded-2xl">
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
                                <th class="px-6 py-3 text-left">Assigned At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-b">
                                    <td class="px-6 py-3"><?php echo e($assignment->item?->name ?? '-'); ?></td>
                                    <td class="px-6 py-3"><?php echo e($assignment->user?->name ?? '-'); ?></td>
                                    <td class="px-6 py-3"><?php echo e($assignment->department?->name ?? '-'); ?></td>
                                    <td class="px-6 py-3"><?php echo e($assignment->assigned_at?->format('d M Y H:i')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-slate-500">No recent assignments.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">System Activity</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="pb-3 border-b">
                            <p class="font-semibold text-slate-900"><?php echo e(ucfirst(str_replace('_', ' ', $activity->action))); ?></p>
                            <p class="text-slate-600"><?php echo e($activity->item?->name ?? 'Unknown asset'); ?></p>
                            <p class="text-xs text-slate-400"><?php echo e($activity->user?->name ?? 'System'); ?> •
                                <?php echo e($activity->created_at?->format('d M Y H:i')); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-slate-500">No recent activity.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Category Summary</h2>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <?php $__currentLoopData = $categorySummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between pb-2 border-b">
                            <span><?php echo e($category->name); ?></span>
                            <span class="font-semibold"><?php echo e($category->items_count); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Department Summary</h2>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <?php $__currentLoopData = $departmentSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between pb-2 border-b">
                            <span><?php echo e($department->name); ?></span>
                            <span class="font-semibold"><?php echo e($department->items_count); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">User Roles</h2>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Admins</span>
                        <span class="font-semibold"><?php echo e($usersByRole['admin'] ?? 0); ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Managers</span>
                        <span class="font-semibold"><?php echo e($usersByRole['manager'] ?? 0); ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Asset Officers</span>
                        <span class="font-semibold"><?php echo e($usersByRole['asset_officer'] ?? 0); ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Staff</span>
                        <span class="font-semibold"><?php echo e($usersByRole['staff'] ?? 0); ?></span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span>Active Assignments</span>
                        <span class="font-semibold"><?php echo e($activeAssignments); ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-slate-900">My Workspace</h1>
            <p class="mt-1 text-slate-500">Personal asset overview, assignment tracking, and recent activity.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">My Active Assets</p>
                <h2 class="mt-2 text-3xl font-bold"><?php echo e($myAssignedAssetsCount); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Overdue Assets</p>
                <h2 class="mt-2 text-3xl font-bold text-red-600"><?php echo e($myOverdueAssignmentsCount); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Departments Used</p>
                <h2 class="mt-2 text-3xl font-bold text-blue-600"><?php echo e($myDepartmentCount); ?></h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Assets In Maintenance</p>
                <h2 class="mt-2 text-3xl font-bold text-amber-500"><?php echo e($myAssetsInMaintenance); ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-2">
            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">My Active Assignments</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $myActiveAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 border rounded-xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900"><?php echo e($assignment->item?->name ?? '-'); ?></p>
                                    <p class="mt-1 text-slate-500"><?php echo e($assignment->department?->name ?? '-'); ?></p>
                                    <p class="mt-2 text-xs text-slate-400">
                                        Assigned <?php echo e($assignment->assigned_at?->format('d M Y H:i')); ?>

                                    </p>
                                </div>
                                <?php if($assignment->item): ?>
                                    <a href="<?php echo e(route('items.show', $assignment->item)); ?>"
                                        class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs text-white font-semibold">
                                        View Asset
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-slate-500">No active assignments.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">My Recent Activity</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <?php $__empty_1 = true; $__currentLoopData = $myRecentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="pb-3 border-b">
                            <p class="font-semibold text-slate-900"><?php echo e(ucfirst(str_replace('_', ' ', $activity->action))); ?></p>
                            <p class="text-slate-600"><?php echo e($activity->item?->name ?? 'Unknown asset'); ?></p>
                            <p class="text-xs text-slate-400"><?php echo e($activity->created_at?->format('d M Y H:i')); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-slate-500">No activity found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">My Assignment History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Asset</th>
                            <th class="px-6 py-3 text-left">Department</th>
                            <th class="px-6 py-3 text-left">Assigned At</th>
                            <th class="px-6 py-3 text-left">Returned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $myAssignmentHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b">
                                <td class="px-6 py-3"><?php echo e($assignment->item?->name ?? '-'); ?></td>
                                <td class="px-6 py-3"><?php echo e($assignment->department?->name ?? '-'); ?></td>
                                <td class="px-6 py-3"><?php echo e($assignment->assigned_at?->format('d M Y H:i')); ?></td>
                                <td class="px-6 py-3"><?php echo e($assignment->returned_at?->format('d M Y H:i') ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-500">No assignment history.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/dashboard/index.blade.php ENDPATH**/ ?>