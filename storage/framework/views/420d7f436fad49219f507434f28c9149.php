

<?php $__env->startSection('content'); ?>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-slate-900">User Administration</h1>
            <p class="mt-1 text-slate-500">Provision accounts, assign roles, and monitor system ownership.</p>
        </div>

        <a href="<?php echo e(route('users.create')); ?>"
            class="rounded-xl bg-blue-600 px-4 py-2.5 text-white font-semibold hover:bg-blue-700">
            + Add User
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-sm text-slate-500">Total Users</p>
            <h2 class="mt-2 text-3xl font-bold"><?php echo e($users->total()); ?></h2>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-sm text-slate-500">Admins</p>
            <h2 class="mt-2 text-3xl font-bold text-red-600"><?php echo e($users->where('role', 'admin')->count()); ?></h2>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-sm text-slate-500">Asset Officers</p>
            <h2 class="mt-2 text-3xl font-bold text-blue-600"><?php echo e($users->where('role', 'asset_officer')->count()); ?></h2>
        </div>
        <div class="rounded-2xl bg-white p-5 shadow">
            <p class="text-sm text-slate-500">Managers</p>
            <h2 class="mt-2 text-3xl font-bold text-amber-500"><?php echo e($users->where('role', 'manager')->count()); ?></h2>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('users.index')); ?>" class="mb-6 rounded-2xl bg-white p-4 shadow flex gap-3">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name, email, or role..."
            class="flex-1 rounded-lg border px-4 py-2">
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-white font-semibold">Search</button>
    </form>

    <div class="rounded-2xl bg-white shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Assignment History</th>
                        <th class="px-4 py-3 text-left">Active Assignments</th>
                        <th class="px-4 py-3 text-left">Activity Logs</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium"><?php echo e($user->name); ?></td>
                            <td class="px-4 py-3"><?php echo e($user->email); ?></td>
                            <td class="px-4 py-3"><?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?></td>
                            <td class="px-4 py-3"><?php echo e($user->assignments_count); ?></td>
                            <td class="px-4 py-3"><?php echo e($user->active_assignments_count); ?></td>
                            <td class="px-4 py-3"><?php echo e($user->asset_logs_count); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="<?php echo e(route('users.show', $user)); ?>"
                                        class="rounded bg-slate-700 px-3 py-1 text-white text-xs">View</a>
                                    <a href="<?php echo e(route('users.edit', $user)); ?>"
                                        class="rounded bg-blue-600 px-3 py-1 text-white text-xs">Edit</a>

                                    <?php if(auth()->id() !== $user->id): ?>
                                        <form method="POST" action="<?php echo e(route('users.destroy', $user)); ?>"
                                            onsubmit="return confirm('Delete this user?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="rounded bg-red-600 px-3 py-1 text-white text-xs">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <?php echo e($users->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/users/index.blade.php ENDPATH**/ ?>