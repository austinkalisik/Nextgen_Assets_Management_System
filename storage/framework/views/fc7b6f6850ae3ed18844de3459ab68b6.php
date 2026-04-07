

<?php $__env->startSection('content'); ?>
    <h1 class="mb-6 text-3xl font-bold">Create Supplier</h1>

    <form method="POST" action="<?php echo e(route('suppliers.store')); ?>"
        class="grid grid-cols-1 gap-4 p-6 bg-white shadow rounded-2xl md:grid-cols-2">
        <?php echo csrf_field(); ?>
        <input type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="Supplier Name"
            class="px-4 py-2 border rounded-lg">
        <input type="email" name="email" value="<?php echo e(old('email')); ?>" placeholder="Email" class="px-4 py-2 border rounded-lg">
        <input type="text" name="phone" value="<?php echo e(old('phone')); ?>" placeholder="Phone" class="px-4 py-2 border rounded-lg">

        <div class="flex gap-3 md:col-span-2">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Save Supplier</button>
            <a href="<?php echo e(route('suppliers.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Cancel</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/suppliers/create.blade.php ENDPATH**/ ?>