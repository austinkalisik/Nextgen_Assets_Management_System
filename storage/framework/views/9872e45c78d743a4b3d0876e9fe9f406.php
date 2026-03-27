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

    <div class="space-y-8">

        <!-- HEADER -->
        <div>
            <h1 class="text-3xl font-bold text-slate-800">
                System Settings
            </h1>
            <p class="text-sm text-gray-500">
                Configure your application preferences
            </p>
        </div>

        <!-- SETTINGS CARD -->
        <div class="max-w-3xl p-6 bg-white shadow rounded-2xl">

            <!-- SUCCESS MESSAGE -->
            <?php if(session('success')): ?>
                <div class="p-3 mb-4 text-green-700 bg-green-100 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('settings.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="space-y-5">

                    <!-- APP NAME -->
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-600">
                            Application Name
                        </label>
                        <input type="text" name="app_name" value="<?php echo e(old('app_name', $settings->app_name ?? 'NextGen Assets')); ?>"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                        
                        <input type="email" name="admin_email" value="<?php echo e(old('admin_email', $settings->admin_email ?? Auth::user()->email)); ?>"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                    </div>

                    <!-- ADMIN EMAIL -->
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-600">
                            Admin Email
                        </label>
                        <input type="email" name="admin_email"
                            value="<?php echo e($settings->admin_email ?? Auth::user()->email); ?>"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                    </div>

                    <!-- STATUS -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">System Status</span>
                        <span class="text-sm font-semibold text-green-500">Active</span>
                    </div>

                    <!-- BUTTON -->
                    <div class="pt-4">
                        <button
                            class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Save Settings
                        </button>
                    </div>

                </div>

            </form>

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
<?php endif; ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/settings.blade.php ENDPATH**/ ?>