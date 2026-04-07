<?php $__env->startSection('content'); ?>
    <div class="p-8 bg-white shadow-xl rounded-2xl">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900">Create Account</h1>
            <p class="mt-1 text-sm text-slate-500">Register to access NextGen Assets</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="px-4 py-3 mb-4 text-sm text-red-700 border border-red-200 rounded-lg bg-red-50">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('register.submit')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>

            <div>
                <label class="block mb-1 text-sm font-medium">Full Name</label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                    class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">Email</label>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                    class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Register
            </button>
        </form>

        <div class="mt-5 text-sm text-center text-slate-600">
            Already have an account?
            <a href="<?php echo e(route('login')); ?>" class="font-semibold text-blue-600 hover:underline">
                Login
            </a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/auth/register.blade.php ENDPATH**/ ?>