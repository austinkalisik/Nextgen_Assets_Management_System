

<?php $__env->startSection('content'); ?>
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Settings</h1>
        <p class="text-slate-500">Manage system branding and configuration</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="text-xl font-semibold mb-4">System Branding</h2>

            <form method="POST" action="<?php echo e(route('settings.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-sm font-medium mb-1">System Name</label>
                    <input type="text" name="system_name" value="<?php echo e(old('system_name', $systemName)); ?>"
                        placeholder="NextGen Assets" class="w-full rounded-lg border px-4 py-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">System Tagline</label>
                    <input type="text" name="system_tagline" value="<?php echo e(old('system_tagline', $systemTagline)); ?>"
                        placeholder="Management System" class="w-full rounded-lg border px-4 py-2" required>
                </div>

                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">
                    Save Branding
                </button>
            </form>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="text-xl font-semibold mb-4">Preview</h2>

            <div class="rounded-2xl border border-slate-200 bg-slate-950 p-6 text-white">
                <h3 class="text-2xl font-bold leading-tight">
                    <?php echo e($systemName); ?>

                </h3>
                <p class="text-sm text-slate-400 mt-1">
                    <?php echo e($systemTagline); ?>

                </p>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Saved Keys</h3>
                <div class="space-y-2 text-sm">
                    <div class="rounded-lg bg-slate-50 px-3 py-2 border">
                        <strong>system_name</strong>: <?php echo e($systemName); ?>

                    </div>
                    <div class="rounded-lg bg-slate-50 px-3 py-2 border">
                        <strong>system_tagline</strong>: <?php echo e($systemTagline); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/settings/index.blade.php ENDPATH**/ ?>