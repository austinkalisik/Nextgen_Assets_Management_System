

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Assign Assets</h1>
        <p class="text-slate-500">Assign one or many available assets in a single submission</p>
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
            <form method="POST" action="<?php echo e(route('assignments.store')); ?>">
                <?php echo csrf_field(); ?>

                <div id="assignment-rows" class="space-y-6">
                    <div class="p-4 border assignment-row rounded-xl">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-700">Assignment Entry 1</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <select name="rows[0][item_id]" class="w-full px-4 py-2 border rounded-lg" required>
                                <option value="">Select Asset</option>
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($item->id); ?>">
                                        <?php echo e($item->name); ?> <?php if($item->asset_tag): ?> - <?php echo e($item->asset_tag); ?> <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <select name="rows[0][user_id]" class="w-full px-4 py-2 border rounded-lg" required>
                                <option value="">Select User</option>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>">
                                        <?php echo e($user->name); ?> - <?php echo e($user->email); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <select name="rows[0][department_id]" class="w-full px-4 py-2 border rounded-lg" required>
                                <option value="">Select Department</option>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($department->id); ?>">
                                        <?php echo e($department->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <input type="datetime-local" name="rows[0][assigned_at]" value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>"
                                class="w-full px-4 py-2 border rounded-lg" required>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="addAssignmentRow()"
                        class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">
                        + Add Another Assignment
                    </button>

                    <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Save Assignments
                    </button>

                    <a href="<?php echo e(route('assignments.index')); ?>" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">
                        Cancel
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        let assignmentRowIndex = 1;

        function addAssignmentRow() {
            const container = document.getElementById('assignment-rows');

            const row = document.createElement('div');
            row.className = 'assignment-row border rounded-xl p-4 mt-4';
            row.innerHTML = `
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-700">Assignment Entry ${assignmentRowIndex + 1}</h3>
                    <button type="button" onclick="this.closest('.assignment-row').remove()" class="px-3 py-1 text-sm text-white bg-red-500 rounded">
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <select name="rows[${assignmentRowIndex}][item_id]" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select Asset</option>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>">
                                <?php echo e($item->name); ?> <?php if($item->asset_tag): ?> - <?php echo e($item->asset_tag); ?> <?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[${assignmentRowIndex}][user_id]" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select User</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>">
                                <?php echo e($user->name); ?> - <?php echo e($user->email); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="rows[${assignmentRowIndex}][department_id]" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select Department</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($department->id); ?>">
                                <?php echo e($department->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <input
                        type="datetime-local"
                        name="rows[${assignmentRowIndex}][assigned_at]"
                        value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>"
                        class="w-full px-4 py-2 border rounded-lg"
                        required
                    >
                </div>
            `;

            container.appendChild(row);
            assignmentRowIndex++;
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/assignments/create.blade.php ENDPATH**/ ?>