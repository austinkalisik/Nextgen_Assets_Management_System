

<?php $__env->startSection('content'); ?>
    <h1 class="text-3xl font-bold mb-6">Edit Department</h1>

    <form method="POST" action="<?php echo e(route('departments.update', $department)); ?>"
        class="rounded-2xl bg-white p-6 shadow space-y-4">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="text" name="name" value="<?php echo e(old('name', $department->name)); ?>" placeholder="Department Name"
            class="w-full rounded-lg border px-4 py-2">

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Update Department</button>
            <a href="<?php echo e(route('departments.index')); ?>" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Cancel</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/departments/edit.blade.php ENDPATH**/ ?>