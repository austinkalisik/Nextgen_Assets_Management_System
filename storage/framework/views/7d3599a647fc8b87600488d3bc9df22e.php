

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Assign Asset</h1>
        <p class="text-slate-500">Assign an available asset to a user</p>
    </div>

    <div class="p-6 bg-white shadow rounded-2xl">
        <?php if($items->isEmpty()): ?>
            <div class="px-4 py-3 border rounded-lg border-amber-200 bg-amber-50 text-amber-700">
                No available assets to assign right now.
            </div>

            <div class="mt-4">
                <a href="<?php echo e(route('assignments.index')); ?>" class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">
                    Back to Assignments
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo e(route('assignments.store')); ?>" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <?php echo csrf_field(); ?>

                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium">Asset</label>
                    <select name="item_id" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select Asset</option>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>" <?php if(old('item_id') == $item->id): echo 'selected'; endif; ?>>
                                <?php echo e($item->name); ?>

                                <?php if($item->asset_tag): ?> - <?php echo e($item->asset_tag); ?> <?php endif; ?>
                                <?php if($item->category): ?> - <?php echo e($item->category->name); ?> <?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">User</label>
                    <select name="user_id" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select User</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>" <?php if(old('user_id') == $user->id): echo 'selected'; endif; ?>>
                                <?php echo e($user->name); ?> - <?php echo e($user->email); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Department</label>
                    <select name="department_id" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select Department</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->id); ?>" <?php if(old('department_id') == $department->id): echo 'selected'; endif; ?>>
                                <?php echo e($department->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Assigned At</label>
                    <input type="datetime-local" name="assigned_at"
                        value="<?php echo e(old('assigned_at', now()->format('Y-m-d\TH:i'))); ?>" class="w-full px-4 py-2 border rounded-lg"
                        required>
                </div>

                <div class="flex gap-3 md:col-span-2">
                    <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Assign Asset
                    </button>
                    <a href="<?php echo e(route('assignments.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">
                        Cancel
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/assignments/create.blade.php ENDPATH**/ ?>