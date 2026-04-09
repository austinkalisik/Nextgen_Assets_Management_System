

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Inventory Management</h1>
        <p class="text-slate-500">Manage stock movement and monitor quantity levels</p>
    </div>

    <form method="GET" action="<?php echo e(route('inventory.index')); ?>" class="flex gap-3 p-4 mb-6 bg-white shadow rounded-2xl">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search inventory..."
            class="flex-1 px-4 py-2 border rounded-lg">
        <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Search</button>
    </form>

    <div class="overflow-hidden bg-white shadow rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Asset</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Quantity</th>
                        <th class="px-4 py-3 text-left">Level</th>
                        <th class="px-4 py-3 text-left">Stock In</th>
                        <th class="px-4 py-3 text-left">Stock Out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium"><?php echo e($item->name); ?></td>
                            <td class="px-4 py-3"><?php echo e($item->category?->name ?? '-'); ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo e($item->quantity); ?></td>
                            <td class="px-4 py-3">
                                <?php if($item->quantity <= 3): ?>
                                    <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">Low</span>
                                <?php elseif($item->quantity <= 10): ?>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Moderate</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Healthy</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="<?php echo e(route('inventory.stock-in', $item)); ?>" class="flex gap-2">
                                    <?php echo csrf_field(); ?>
                                    <input type="number" name="quantity" min="1" class="w-24 px-2 py-1 border rounded" required>
                                    <button class="px-3 py-1 text-xs text-white bg-green-600 rounded">In</button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="<?php echo e(route('inventory.stock-out', $item)); ?>" class="flex gap-2">
                                    <?php echo csrf_field(); ?>
                                    <input type="number" name="quantity" min="1" class="w-24 px-2 py-1 border rounded" required>
                                    <button class="px-3 py-1 text-xs text-white bg-red-600 rounded">Out</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No inventory records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><?php echo e($items->links()); ?></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/inventory/index.blade.php ENDPATH**/ ?>