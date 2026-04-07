

<?php $__env->startSection('content'); ?>
    <h1 class="text-3xl font-bold mb-6"><?php echo e($user->name); ?></h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white p-6 shadow space-y-2">
            <p><strong>Name:</strong> <?php echo e($user->name); ?></p>
            <p><strong>Email:</strong> <?php echo e($user->email); ?></p>
            <p><strong>Role:</strong> <?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?></p>
            <p><strong>Total Assignments:</strong> <?php echo e($user->assignments->count()); ?></p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="text-xl font-semibold mb-4">Assignment History</h2>
            <div class="space-y-2 text-sm">
                <?php $__empty_1 = true; $__currentLoopData = $user->assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border-b pb-2">
                        <?php echo e($assignment->item->name ?? '-'); ?>

                        • <?php echo e(optional($assignment->assigned_at)->format('d M Y')); ?>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No assignments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/users/show.blade.php ENDPATH**/ ?>