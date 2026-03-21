<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <div class="space-y-10">

        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Assets Management
                </h1>
                <p class="text-sm text-gray-500">
                    Add, manage, and track all your assets efficiently
                </p>
            </div>
        </div>

        <!-- ADD FORM -->
        <div class="max-w-6xl p-6 bg-white border shadow-lg rounded-2xl">

            <form method="POST" action="<?php echo e(route('items.store')); ?>" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <?php echo csrf_field(); ?>

                <input name="part_no" placeholder="Asset ID" required
                    class="px-4 py-3 text-sm border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500">

                <input name="brand" placeholder="Brand" required
                    class="px-4 py-3 text-sm border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500">

                <input name="part_name" placeholder="Asset Name" required
                    class="px-4 py-3 text-sm border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500">

                <input name="description" placeholder="Description" required
                    class="px-4 py-3 text-sm border rounded-lg bg-gray-50 focus:ring-2 focus:ring-blue-500">

                <button
                    class="px-4 py-3 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                    + Add
                </button>
            </form>

        </div>

        <!-- TABLE -->
        <div class="overflow-hidden bg-white border shadow-lg rounded-2xl">

            <!-- HEADER -->
            <div class="flex items-center justify-between px-6 py-4 border-b bg-slate-50">
                <h3 class="font-semibold text-gray-700">All Assets</h3>

                <span class="text-sm text-gray-400">
                    <?php echo e($items->count()); ?> total
                </span>
            </div>

            <table class="w-full text-sm">
                <thead class="text-xs text-gray-600 uppercase bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left">Asset ID</th>
                        <th class="px-6 py-4 text-left">Brand</th>
                        <th class="px-6 py-4 text-left">Asset Name</th>
                        <th class="px-6 py-4 text-left">Description</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="transition hover:bg-slate-50">

                                        <!-- INLINE EDIT FORM -->
                                        <form method="POST" action="<?php echo e(route('items.update', $item->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>

                                            <td class="px-6 py-4">
                                                <input name="part_no" value="<?php echo e($item->part_no); ?>"
                                                    class="w-full px-2 py-1 text-sm border rounded">
                                            </td>

                                            <td class="px-6 py-4">
                                                <input name="brand" value="<?php echo e($item->brand); ?>"
                                                    class="w-full px-2 py-1 text-sm border rounded">
                                            </td>

                                            <td class="px-6 py-4">
                                                <input name="part_name" value="<?php echo e($item->part_name); ?>"
                                                    class="w-full px-2 py-1 text-sm border rounded">
                                            </td>

                                            <td class="px-6 py-4">
                                                <input name="description" value="<?php echo e($item->description); ?>"
                                                    class="w-full px-2 py-1 text-sm border rounded">
                                            </td>

                                            <!-- ACTIONS -->
                                            <td class="px-6 py-4 text-center">

                                                <div class="flex justify-center gap-2">

                                                    <!-- SAVE -->
                                                    <button
                                                        class="px-3 py-1 text-xs font-semibold text-white bg-green-500 rounded-lg hover:bg-green-600">
                                                        Save
                                                    </button>
                                        </form>

                                        <!-- DELETE -->
                                        <form method="POST" action="<?php echo e(route('items.destroy', $item->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>

                                            <button onclick="return confirm('Delete this asset?')"
                                                class="px-3 py-1 text-xs font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600">
                                                Delete
                                            </button>
                                        </form>

                        </div>

                        </td>

                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="py-12 text-center text-gray-400">
                    No assets found
                </td>
            </tr>
        <?php endif; ?>

        </tbody>

        </table>

    </div>

    </div>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/items.blade.php ENDPATH**/ ?>