

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Create Asset</h1>
        <p class="text-slate-500">Add a new asset record</p>
    </div>

    <form method="POST" action="<?php echo e(route('items.store')); ?>"
        class="grid grid-cols-1 gap-4 p-6 bg-white shadow rounded-2xl md:grid-cols-2">
        <?php echo csrf_field(); ?>

        <input type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="Asset Name"
            class="px-4 py-2 border rounded-lg">
        <input type="text" name="asset_tag" value="<?php echo e(old('asset_tag')); ?>" placeholder="Asset Tag"
            class="px-4 py-2 border rounded-lg">
        <input type="text" name="serial_number" value="<?php echo e(old('serial_number')); ?>" placeholder="Serial Number"
            class="px-4 py-2 border rounded-lg">
        <input type="text" name="location" value="<?php echo e(old('location')); ?>" placeholder="Location"
            class="px-4 py-2 border rounded-lg">
        <input type="date" name="purchase_date" value="<?php echo e(old('purchase_date')); ?>" class="px-4 py-2 border rounded-lg">
        <input type="number" name="quantity" value="<?php echo e(old('quantity', 1)); ?>" min="1" class="px-4 py-2 border rounded-lg">

        <select name="category_id" class="px-4 py-2 border rounded-lg">
            <option value="">Select Category</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->id); ?>" <?php if(old('category_id') == $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="supplier_id" class="px-4 py-2 border rounded-lg">
            <option value="">Select Supplier</option>
            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($supplier->id); ?>" <?php if(old('supplier_id') == $supplier->id): echo 'selected'; endif; ?>><?php echo e($supplier->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="department_id" class="px-4 py-2 border rounded-lg">
            <option value="">Select Department</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($department->id); ?>" <?php if(old('department_id') == $department->id): echo 'selected'; endif; ?>><?php echo e($department->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="status" class="px-4 py-2 border rounded-lg">
            <option value="available">Available</option>
            <option value="assigned">Assigned</option>
            <option value="maintenance">Maintenance</option>
            <option value="retired">Retired</option>
        </select>

        <div class="flex gap-3 md:col-span-2">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Save Asset</button>
            <a href="<?php echo e(route('items.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Cancel</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/items/create.blade.php ENDPATH**/ ?>