

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold"><?php echo e($item->name); ?></h1>
            <p class="text-slate-500">Asset details, assignment trail, and activity history</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Print</button>
            <a href="<?php echo e(route('items.edit', $item)); ?>"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Edit</a>
            <a href="<?php echo e(route('items.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="p-6 space-y-3 bg-white shadow rounded-2xl">
            <div><strong>Name:</strong> <?php echo e($item->name); ?></div>
            <div><strong>Category:</strong> <?php echo e($item->category?->name ?? '-'); ?></div>
            <div><strong>Supplier:</strong> <?php echo e($item->supplier?->name ?? '-'); ?></div>
            <div><strong>Department:</strong> <?php echo e($item->department?->name ?? '-'); ?></div>
            <div><strong>Asset Tag:</strong> <?php echo e($item->asset_tag ?? '-'); ?></div>
            <div><strong>Serial Number:</strong> <?php echo e($item->serial_number ?? '-'); ?></div>
            <div><strong>Status:</strong> <?php echo e(ucfirst($item->status)); ?></div>
            <div><strong>Assigned To:</strong> <?php echo e($item->activeAssignment?->user?->name ?? '-'); ?></div>
            <div><strong>Location:</strong> <?php echo e($item->location ?? '-'); ?></div>
            <div><strong>Purchase Date:</strong> <?php echo e(optional($item->purchase_date)->format('d M Y') ?? '-'); ?></div>
            <div><strong>Quantity:</strong> <?php echo e($item->quantity); ?></div>
        </div>

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Assignment History</h2>
            <div class="space-y-3 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $item->assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="pb-2 border-b">
                        <p class="font-medium"><?php echo e($assignment->user->name ?? '-'); ?></p>
                        <p class="text-slate-500">
                            <?php echo e($assignment->department->name ?? '-'); ?>

                            • Assigned <?php echo e(optional($assignment->assigned_at)->format('d M Y H:i')); ?>

                            <?php if($assignment->returned_at): ?>
                                • Returned <?php echo e(optional($assignment->returned_at)->format('d M Y H:i')); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No assignment history found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Activity Log</h2>
            <div class="space-y-3 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $item->assetLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="pb-2 border-b">
                        <p class="font-medium"><?php echo e(ucfirst(str_replace('_', ' ', $log->action))); ?></p>
                        <p class="text-slate-500">
                            <?php echo e($log->user?->name ?? 'System'); ?> • <?php echo e(optional($log->created_at)->format('d M Y H:i')); ?>

                        </p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No activity found for this asset.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/items/show.blade.php ENDPATH**/ ?>