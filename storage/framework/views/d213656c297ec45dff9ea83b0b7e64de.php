

<?php $__env->startSection('content'); ?>
    <meta http-equiv="refresh" content="30">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Notification Center</h1>
            <p class="text-slate-500">Persistent operational alerts and system events</p>
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
            <p class="text-sm text-slate-500">Unread</p>
            <h2 class="mt-2 text-3xl font-bold text-red-600"><?php echo e($unreadCount); ?></h2>
        </div>

        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Critical</p>
            <h2 class="mt-2 text-3xl font-bold text-rose-600"><?php echo e($criticalCount); ?></h2>
        </div>

        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Warnings</p>
            <h2 class="mt-2 text-3xl font-bold text-amber-500"><?php echo e($warningCount); ?></h2>
        </div>

        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Info</p>
            <h2 class="mt-2 text-3xl font-bold text-blue-600"><?php echo e($infoCount); ?></h2>
        </div>
    </div>

    <div class="overflow-hidden bg-white shadow rounded-2xl">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Notifications</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Message</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $typeClasses = match ($notification->type) {
                                'critical' => 'bg-red-100 text-red-700',
                                'warning' => 'bg-amber-100 text-amber-700',
                                'success' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-blue-100 text-blue-700',
                            };
                        ?>

                        <tr class="border-b <?php echo e(is_null($notification->read_at) ? 'bg-blue-50/40' : ''); ?>">
                            <td class="px-4 py-3">
                                <?php if(is_null($notification->read_at)): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                        Unread
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">
                                        Read
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold <?php echo e($typeClasses); ?>">
                                    <?php echo e(ucfirst($notification->type)); ?>

                                </span>
                            </td>

                            <td class="px-4 py-3 font-medium"><?php echo e($notification->title); ?></td>
                            <td class="px-4 py-3 text-slate-600"><?php echo e($notification->message); ?></td>
                            <td class="px-4 py-3 text-slate-500"><?php echo e($notification->created_at?->format('d M Y H:i')); ?></td>

                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="<?php echo e(route('notifications.open', $notification)); ?>"
                                        class="px-3 py-1 text-xs text-white rounded bg-slate-800">
                                        Open
                                    </a>

                                    <?php if(is_null($notification->read_at)): ?>
                                        <form method="POST" action="<?php echo e(route('notifications.read', $notification)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button class="px-3 py-1 text-xs text-white bg-blue-600 rounded">
                                                Mark Read
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                No notifications found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($notifications->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/notifications/index.blade.php ENDPATH**/ ?>