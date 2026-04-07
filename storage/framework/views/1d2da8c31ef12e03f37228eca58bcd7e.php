

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Settings</h1>
        <p class="text-slate-500">Manage system configuration</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Add / Update Setting</h2>
            <form method="POST" action="<?php echo e(route('settings.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <input type="text" name="key" placeholder="Setting Key" class="w-full px-4 py-2 border rounded-lg">
                <textarea name="value" placeholder="Setting Value" class="w-full px-4 py-2 border rounded-lg"></textarea>
                <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Save Setting</button>
            </form>
        </div>

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Current Settings</h2>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 space-y-3 border rounded-lg">
                        <form method="POST" action="<?php echo e(route('settings.update', $setting->key)); ?>" class="space-y-2">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="font-semibold"><?php echo e($setting->key); ?></div>
                            <textarea name="value" class="w-full px-4 py-2 border rounded-lg"><?php echo e($setting->value); ?></textarea>
                            <button class="px-3 py-1 text-sm text-white bg-blue-600 rounded">Update</button>
                        </form>

                        <form method="POST" action="<?php echo e(route('settings.destroy', $setting->key)); ?>"
                            onsubmit="return confirm('Delete this setting?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="px-3 py-1 text-sm text-white bg-red-600 rounded">Delete</button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-500">No settings found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/settings/index.blade.php ENDPATH**/ ?>