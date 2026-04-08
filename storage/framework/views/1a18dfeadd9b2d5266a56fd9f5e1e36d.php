

<?php $__env->startSection('content'); ?>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Suppliers</h1>
            <p class="text-slate-500">Manage suppliers and vendors</p>
        </div>
        <a href="<?php echo e(route('suppliers.create')); ?>" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">+ Add
            Supplier</a>
    </div>

    <form method="GET" action="<?php echo e(route('suppliers.index')); ?>" class="mb-6 rounded-2xl bg-white p-4 shadow flex gap-3">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search suppliers..."
            class="flex-1 rounded-lg border px-4 py-2">
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-white font-semibold">Search</button>
    </form>

    <div class="rounded-2xl bg-white shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Assets</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b">
                        <td class="px-4 py-3"><?php echo e($supplier->name); ?></td>
                        <td class="px-4 py-3"><?php echo e($supplier->email); ?></td>
                        <td class="px-4 py-3"><?php echo e($supplier->phone); ?></td>
                        <td class="px-4 py-3"><?php echo e($supplier->items_count); ?></td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="<?php echo e(route('suppliers.show', $supplier)); ?>"
                                class="rounded bg-slate-700 px-3 py-1 text-white text-xs">View</a>
                            <a href="<?php echo e(route('suppliers.edit', $supplier)); ?>"
                                class="rounded bg-blue-600 px-3 py-1 text-white text-xs">Edit</a>
                            <form method="POST" action="<?php echo e(route('suppliers.destroy', $supplier)); ?>"
                                onsubmit="return confirm('Delete this supplier?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button class="rounded bg-red-600 px-3 py-1 text-white text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No suppliers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4"><?php echo e($suppliers->links()); ?></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/suppliers/index.blade.php ENDPATH**/ ?>