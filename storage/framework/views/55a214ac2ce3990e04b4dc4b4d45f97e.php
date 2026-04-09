

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Assignments</h1>
            <p class="text-slate-500">Track active allocations, returns, and accountability</p>
        </div>

        <a href="<?php echo e(route('assignments.create')); ?>"
            class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            + Assign Assets
        </a>
    </div>

    <form method="GET" action="<?php echo e(route('assignments.index')); ?>" class="flex gap-3 p-4 mb-6 bg-white shadow rounded-2xl">
        <select name="status" class="px-4 py-2 border rounded-lg">
            <option value="">All Records</option>
            <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Active</option>
            <option value="returned" <?php if(request('status') === 'returned'): echo 'selected'; endif; ?>>Returned</option>
        </select>

        <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Filter</button>
    </form>

    <div class="overflow-hidden bg-white shadow rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Asset</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left">Assigned At</th>
                        <th class="px-4 py-3 text-left">Returned At</th>
                        <th class="px-4 py-3 text-left">State</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium"><?php echo e($assignment->item?->name ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($assignment->user?->name ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($assignment->department?->name ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($assignment->assigned_at?->format('d M Y H:i')); ?></td>
                            <td class="px-4 py-3"><?php echo e($assignment->returned_at?->format('d M Y H:i') ?? '-'); ?></td>
                            <td class="px-4 py-3">
                                <?php if(!$assignment->returned_at): ?>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Active</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Returned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if(!$assignment->returned_at): ?>
                                    <form method="POST" action="<?php echo e(route('assignments.return', $assignment)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="px-3 py-1 text-xs text-white bg-green-600 rounded">Return</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No assignments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><?php echo e($assignments->links()); ?></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/assignments/index.blade.php ENDPATH**/ ?>