

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold"><?php echo e($category->name); ?></h1>
            <p class="text-slate-500">Category details</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Print</button>
            <a href="<?php echo e(route('categories.edit', $category)); ?>"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Edit</a>
            <a href="<?php echo e(route('categories.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Back</a>
        </div>
    </d>

    <div class="p-6 mb-6 bg-white shadow rounded-2xl">
        <p><strong>Name:</strong> <?php echo e($category->name); ?></p>
        <p class="mt-2"><strong>Description:</strong> <?php echo e($category->description ?? '-'); ?></p>
        <p class="mt-2"><strong>Total Assets:</strong> <?php echo e($category->items->count()); ?></p>
    </div>

    <div class="p-6 bg-white shadow rounded-2xl">
        <h2 class="mb-4 text-xl font-semibold">Assets in this category</h2>
        <div class="space-y-2">
            <?php $__empty_1 = true; $__currentLoopData = $category->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="pb-2 border-b">
                    <?php echo e($item->name); ?> — <?php echo e($item->asset_tag ?? 'No tag'); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-slate-500">No assets linked to this category.</p>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/categories/show.blade.php ENDPATH**/ ?>