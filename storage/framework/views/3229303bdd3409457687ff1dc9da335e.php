<aside class="w-64 bg-slate-950 text-white min-h-screen">
    <div class="px-6 py-6 border-b border-slate-800">
        <h1 class="text-2xl font-bold leading-tight">NextGen Assets</h1>
        <p class="text-sm text-slate-400">Management System</p>
    </div>

    <nav class="px-4 py-6 space-y-2 text-sm">
        <a href="<?php echo e(route('dashboard')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('dashboard') ? 'bg-slate-800' : ''); ?>">Dashboard</a>
        <a href="<?php echo e(route('items.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('items.*') ? 'bg-slate-800' : ''); ?>">Assets</a>
        <a href="<?php echo e(route('assignments.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('assignments.*') ? 'bg-slate-800' : ''); ?>">Assignments</a>
        <a href="<?php echo e(route('inventory.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('inventory.*') ? 'bg-slate-800' : ''); ?>">Inventory</a>
        <a href="<?php echo e(route('suppliers.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('suppliers.*') ? 'bg-slate-800' : ''); ?>">Suppliers</a>
        <a href="<?php echo e(route('categories.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('categories.*') ? 'bg-slate-800' : ''); ?>">Categories</a>
        <a href="<?php echo e(route('departments.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('departments.*') ? 'bg-slate-800' : ''); ?>">Departments</a>
        <a href="<?php echo e(route('users.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('users.*') ? 'bg-slate-800' : ''); ?>">Users</a>
        <a href="<?php echo e(route('settings.index')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('settings.*') ? 'bg-slate-800' : ''); ?>">Settings</a>
        <a href="<?php echo e(route('profile.edit')); ?>"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 <?php echo e(request()->routeIs('profile.*') ? 'bg-slate-800' : ''); ?>">Profile</a>
    </nav>

    <div class="px-4 mt-8">
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="w-full rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold hover:bg-red-600">
                Logout
            </button>
        </form>
    </div>
</aside><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>