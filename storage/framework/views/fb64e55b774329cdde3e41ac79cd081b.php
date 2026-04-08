<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Edit Profile</h1>
        <p class="text-slate-500">Update your account details</p>
    </div>

    <form method="POST" action="<?php echo e(route('profile.update')); ?>"
        class="grid grid-cols-1 gap-4 p-6 bg-white shadow rounded-2xl md:grid-cols-2">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PATCH'); ?>

        <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" placeholder="Full Name"
            class="px-4 py-2 border rounded-lg">
        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" placeholder="Email"
            class="px-4 py-2 border rounded-lg">
        <input type="password" name="password" placeholder="New Password (optional)" class="px-4 py-2 border rounded-lg">
        <input type="password" name="password_confirmation" placeholder="Confirm New Password"
            class="px-4 py-2 border rounded-lg">

        <div class="md:col-span-2">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Update Profile</button>
        </div>
    </form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/profile/edit.blade.php ENDPATH**/ ?>