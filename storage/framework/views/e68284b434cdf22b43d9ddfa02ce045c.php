

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Create Assets</h1>
        <p class="text-slate-500">Add one or many asset records in a single submission</p>
    </div>

    <form method="POST" action="<?php echo e(route('items.store')); ?>" class="p-6 bg-white shadow rounded-2xl">
        <?php echo csrf_field(); ?>

        <div id="asset-rows" class="space-y-6">
            <div class="p-4 border asset-row rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-700">Asset Entry 1</h3>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input type="text" name="rows[0][name]" placeholder="Asset Name" class="px-4 py-2 border rounded-lg"
                        required>
                    <input type="text" name="rows[0][asset_tag]" placeholder="Asset Tag (leave blank for auto)"
                        class="px-4 py-2 border rounded-lg">
                    <input type="text" name="rows[0][serial_number]" placeholder="Serial Number"
                        class="px-4 py-2 border rounded-lg">
                    <input type="text" name="rows[0][location]" placeholder="Location" class="px-4 py-2 border rounded-lg">
                    <input type="date" name="rows[0][purchase_date]" class="px-4 py-2 border rounded-lg">

                    <select name="rows[0][category_id]" class="px-4 py-2 border rounded-lg" required>
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[0][supplier_id]" class="px-4 py-2 border rounded-lg" required>
                        <option value="">Select Supplier</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[0][department_id]" class="px-4 py-2 border rounded-lg" required>
                        <option value="">Select Department</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->id); ?>"><?php echo e($department->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[0][status]" class="px-4 py-2 border rounded-lg" required>
                        <option value="available">Available</option>
                        <option value="assigned">Assigned</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="button" onclick="addAssetRow()"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">
                + Add Another Asset
            </button>

            <button type="submit" class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">
                Save Assets
            </button>

            <a href="<?php echo e(route('items.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">
                Cancel
            </a>
        </div>
    </form>

    <script>
        let assetRowIndex = 1;

        function addAssetRow() {
            const container = document.getElementById('asset-rows');

            const row = document.createElement('div');
            row.className = 'asset-row border rounded-xl p-4 mt-4';
            row.innerHTML = `
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-700">Asset Entry ${assetRowIndex + 1}</h3>
                    <button type="button" onclick="this.closest('.asset-row').remove()" class="px-3 py-1 text-sm text-white bg-red-500 rounded">
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input type="text" name="rows[${assetRowIndex}][name]" placeholder="Asset Name" class="px-4 py-2 border rounded-lg" required>
                    <input type="text" name="rows[${assetRowIndex}][asset_tag]" placeholder="Asset Tag (leave blank for auto)" class="px-4 py-2 border rounded-lg">
                    <input type="text" name="rows[${assetRowIndex}][serial_number]" placeholder="Serial Number" class="px-4 py-2 border rounded-lg">
                    <input type="text" name="rows[${assetRowIndex}][location]" placeholder="Location" class="px-4 py-2 border rounded-lg">
                    <input type="date" name="rows[${assetRowIndex}][purchase_date]" class="px-4 py-2 border rounded-lg">

                    <select name="rows[${assetRowIndex}][category_id]" class="px-4 py-2 border rounded-lg" required>
                        <option value="">Select Category</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[${assetRowIndex}][supplier_id]" class="px-4 py-2 border rounded-lg" required>
                        <option value="">Select Supplier</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[${assetRowIndex}][department_id]" class="px-4 py-2 border rounded-lg" required>
                        <option value="">Select Department</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->id); ?>"><?php echo e($department->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[${assetRowIndex}][status]" class="px-4 py-2 border rounded-lg" required>
                        <option value="available">Available</option>
                        <option value="assigned">Assigned</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
            `;

            container.appendChild(row);
            assetRowIndex++;
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/items/create.blade.php ENDPATH**/ ?>