

<?php $__env->startSection('content'); ?>
    <h1 class="text-3xl font-bold mb-6">Edit User</h1>

    <form method="POST" action="<?php echo e(route('users.update', $user)); ?>"
        class="rounded-2xl bg-white p-6 shadow grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" placeholder="Full Name"
            class="rounded-lg border px-4 py-2">
        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" placeholder="Email"
            class="rounded-lg border px-4 py-2">

        <select name="role" class="rounded-lg border px-4 py-2">
            <option value="staff" <?php if(old('role', $user->role) === 'staff'): echo 'selected'; endif; ?>>Staff</option>
            <option value="asset_officer" <?php if(old('role', $user->role) === 'asset_officer'): echo 'selected'; endif; ?>>Asset Officer</option>
            <option value="manager" <?php if(old('role', $user->role) === 'manager'): echo 'selected'; endif; ?>>Manager</option>
            <option value="admin" <?php if(old('role', $user->role) === 'admin'): echo 'selected'; endif; ?>>Admin</option>
        </select>

        <div></div>

        <input type="password" name="password" placeholder="New Password (optional)" class="rounded-lg border px-4 py-2">
        <input type="password" name="password_confirmation" placeholder="Confirm New Password"
            class="rounded-lg border px-4 py-2">

        <div class="md:col-span-2 flex gap-3">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Update User</button>
            <a href="<?php echo e(route('users.index')); ?>" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Cancel</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/users/edit.blade.php ENDPATH**/ ?>