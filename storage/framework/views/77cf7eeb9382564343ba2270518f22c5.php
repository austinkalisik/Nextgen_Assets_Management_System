

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Assets</h1>
            <p class="text-slate-500">Manage asset records, ownership, status, and lifecycle</p>
        </div>

        <div class="flex gap-3">
            <a href="<?php echo e(route('items.create')); ?>"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + Add Assets
            </a>
            <a href="<?php echo e(route('assignments.create')); ?>"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900 hover:bg-slate-800">
                Assign Assets
            </a>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('items.index')); ?>"
        class="grid grid-cols-1 gap-4 p-4 mb-6 bg-white shadow rounded-2xl md:grid-cols-4">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search assets..."
            class="px-4 py-2 border rounded-lg">

        <select name="status" class="px-4 py-2 border rounded-lg">
            <option value="">All Status</option>
            <option value="available" <?php if(request('status') === 'available'): echo 'selected'; endif; ?>>Available</option>
            <option value="assigned" <?php if(request('status') === 'assigned'): echo 'selected'; endif; ?>>Assigned</option>
            <option value="maintenance" <?php if(request('status') === 'maintenance'): echo 'selected'; endif; ?>>Maintenance</option>
            <option value="retired" <?php if(request('status') === 'retired'): echo 'selected'; endif; ?>>Retired</option>
        </select>

        <select name="category_id" class="px-4 py-2 border rounded-lg">
            <option value="">All Categories</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->id); ?>" <?php if((string) request('category_id') === (string) $category->id): echo 'selected'; endif; ?>>
                    <?php echo e($category->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="department_id" class="px-4 py-2 border rounded-lg">
            <option value="">All Departments</option>
            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($department->id); ?>" <?php if((string) request('department_id') === (string) $department->id): echo 'selected'; endif; ?>>
                    <?php echo e($department->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <div class="flex gap-3 md:col-span-4">
            <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Filter</button>
            <a href="<?php echo e(route('items.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden bg-white shadow rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Asset</th>
                        <th class="px-4 py-3 text-left">Tag</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Assigned To</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium"><?php echo e($item->name); ?></td>
                            <td class="px-4 py-3"><?php echo e($item->asset_tag ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($item->category?->name ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($item->supplier?->name ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($item->department?->name ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e(ucfirst($item->status)); ?></td>
                            <td class="px-4 py-3"><?php echo e($item->activeAssignment?->user?->name ?? '-'); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="<?php echo e(route('items.show', $item)); ?>"
                                        class="px-3 py-1 text-xs text-white rounded bg-slate-700">View</a>
                                    <a href="<?php echo e(route('items.edit', $item)); ?>"
                                        class="px-3 py-1 text-xs text-white bg-blue-600 rounded">Edit</a>
                                    <form method="POST" action="<?php echo e(route('items.destroy', $item)); ?>"
                                        onsubmit="return confirm('Delete this asset?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="px-3 py-1 text-xs text-white bg-red-600 rounded">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">No assets found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($items->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/items/index.blade.php ENDPATH**/ ?>