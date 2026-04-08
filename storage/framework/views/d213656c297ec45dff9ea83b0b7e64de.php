

<?php $__env->startSection('content'); ?>
    <meta http-equiv="refresh" content="30">

    <div class="flex items-center justify-between mb-6">
        <>
            <h1 class="text-3xl font-bold">Notification Center</h1>
            <p class="text-slate-500">Live operational alerts, assignment monitoring, and recent system activity</p>
    </div>

        <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>">
            <?php echo csrf_field(); ?>
            <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">
                Mark All Read
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Unread Alerts</p>
            <h2 class="mt-2 text-3xl font-bold text-red-500"><?php echo e($unreadCount); ?></h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Low Stock</p>
            <h2 class="mt-2 text-3xl font-bold text-orange-500"><?php echo e($lowStockItems->count()); ?></h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Maintenance</p>
            <h2 class="mt-2 text-3xl font-bold text-amber-500"><?php echo e($maintenanceItems->count()); ?></h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Overdue Assignments</p>
            <h2 class="mt-2 text-3xl font-bold text-rose-500"><?php echo e($overdueAssignments->count()); ?></h2>
        </div>
    </div>

    <div class="mb-6 bg-white shadow rounded-2xl">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Active Alerts Feed</h2>
        </div>
        <div class="p-6 space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isRead = in_array($alert['id'], session('read_notifications', []));
                    $badgeClasses = match ($alert['type']) {
                        'critical' => 'bg-red-100 text-red-700',
                        'warning' => 'bg-amber-100 text-amber-700',
                        default => 'bg-blue-100 text-blue-700',
                    };
                ?>

                <a href="<?php echo e($alert['url']); ?>"
                    class="block rounded-xl border p-4 transition hover:border-slate-400 hover:shadow-sm <?php echo e($isRead ? 'bg-slate-50 border-slate-200 opacity-75' : 'bg-white border-slate-300'); ?>">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold <?php echo e($badgeClasses); ?>">
                                    <?php echo e(ucfirst($alert['type'])); ?>

                                </span>
                                <?php if(!$isRead): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                        New
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3 class="font-semibold text-slate-900"><?php echo e($alert['title']); ?></h3>
                            <p class="mt-1 text-sm text-slate-700"><?php echo e($alert['message']); ?></p>
                            <p class="mt-2 text-xs text-slate-500"><?php echo e($alert['meta']); ?></p>
                        </div>

                        <div class="text-xs font-medium text-blue-600 whitespace-nowrap">
                            Open →
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-500">No active alerts right now.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-2">
        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Low Stock Assets</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $lowStockItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('items.show', $item)); ?>" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium"><?php echo e($item->name); ?></p>
                        <p class="text-slate-500">
                            Qty: <?php echo e($item->quantity); ?> • <?php echo e($item->department?->name ?? '-'); ?>

                        </p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No low stock alerts.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Maintenance Assets</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $maintenanceItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('items.show', $item)); ?>" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium"><?php echo e($item->name); ?></p>
                        <p class="text-slate-500">
                            <?php echo e($item->category?->name ?? '-'); ?> • <?php echo e($item->department?->name ?? '-'); ?>

                        </p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No maintenance alerts.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-2">
        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Overdue Assignments</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $overdueAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('assignments.index')); ?>" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium"><?php echo e($assignment->item?->name ?? '-'); ?></p>
                        <p class="text-slate-500">
                            <?php echo e($assignment->user?->name ?? '-'); ?> • <?php echo e($assignment->department?->name ?? '-'); ?>

                            • Assigned <?php echo e(optional($assignment->assigned_at)->format('d M Y')); ?>

                        </p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No overdue assignments.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Recent Assignments</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $recentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('assignments.index')); ?>" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium"><?php echo e($assignment->item?->name ?? '-'); ?></p>
                        <p class="text-slate-500">
                            <?php echo e($assignment->user?->name ?? '-'); ?>

                            • <?php echo e(optional($assignment->assigned_at)->format('d M Y H:i')); ?>

                        </p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No recent assignments.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-2xl">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Recent Activity Log</h2>
        </div>
        <div class="p-6 space-y-3 text-sm">
            <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e($activity->item ? route('items.show', $activity->item) : route('dashboard')); ?>"
                    class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                    <p class="font-medium"><?php echo e(ucfirst(str_replace('_', ' ', $activity->action))); ?></p>
                    <p class="text-slate-500">
                        <?php echo e($activity->item?->name ?? 'Unknown asset'); ?> by <?php echo e($activity->user?->name ?? 'System'); ?>

                        • <?php echo e(optional($activity->created_at)->format('d M Y H:i')); ?>

                    </p>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-500">No recent activity found.</p>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/notifications/index.blade.php ENDPATH**/ ?>