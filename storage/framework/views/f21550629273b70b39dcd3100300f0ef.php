

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold"><?php echo e($department->name); ?></h1>
            <p class="text-slate-500">Department details</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Print</button>
            <a href="<?php echo e(route('departments.edit', $department)); ?>"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Edit</a>
        </div>
    </div>

    <div class="p-6 bg-white shadow rounded-2xl">
        <p><strong>Name:</strong> <?php echo e($department->name); ?></p>
        <p class="mt-2"><strong>Total Assets:</strong> <?php echo e($department->items->count()); ?></p>

        <div class="mt-6">
            <h2 class="mb-4 text-xl font-semibold">Assets in this department</h2>
            <div class="space-y-2">
                <?php $__empty_1 = true; $__currentLoopData = $department->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="pb-2 border-b"><?php echo e($item->name); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No assets linked to this department.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/departments/show.blade.php ENDPATH**/ ?>