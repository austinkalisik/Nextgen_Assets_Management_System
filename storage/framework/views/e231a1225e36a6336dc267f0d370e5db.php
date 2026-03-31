<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
try {
    $appName = \Illuminate\Support\Facades\DB::table('settings')->value('app_name') ?? 'NextGen Assets';
} catch (\Exception $e) {
    $appName = 'NextGen Assets';
}
    ?>

    <title><?php echo e($appName); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body x-data="{ darkMode: false }" :class="darkMode ? 'bg-gray-900 text-white' : 'bg-slate-100 text-gray-800'">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 p-6 text-gray-300 bg-slate-950">

            <h2 class="mb-10 text-xl font-bold text-white">
                <?php echo e($appName); ?>

            </h2>

            <?php $route = request()->path(); ?>

            

            <nav class="space-y-2 text-sm">

                <a href="/dashboard"
                    class="block px-4 py-2 rounded <?php echo e(str_contains($route, 'dashboard') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800'); ?>">
                    Dashboard
                </a>

                <a href="/products"
                    class="block px-4 py-2 rounded <?php echo e(str_contains($route, 'products') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800'); ?>">
                    Products
                </a>

                <a href="/suppliers"
                    class="block px-4 py-2 rounded <?php echo e(str_contains($route, 'suppliers') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800'); ?>">
                    Suppliers
                </a>

                <a href="/categories"
                    class="block px-4 py-2 rounded <?php echo e(str_contains($route, 'categories') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800'); ?>">
                    Categories
                </a>

                <a href="/users"
                    class="block px-4 py-2 rounded <?php echo e(str_contains($route, 'users') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800'); ?>">
                    Users
                </a>

                <a href="/settings"
                    class="block px-4 py-2 rounded <?php echo e(str_contains($route, 'settings') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800'); ?>">
                    Settings
                </a>

            </nav>

            <div class="mt-10 text-xs text-gray-500">
                © <?php echo e(date('Y')); ?> <?php echo e($appName); ?>

            </div>

        </aside>

        <!-- MAIN -->
        <div class="flex-1">

            <!-- HEADER -->
            <div class="flex items-center justify-between p-4 bg-white border-b shadow-sm">

                <!-- LEFT -->
                <div>
                    <h1 class="text-lg font-semibold">Dashboard</h1>
                    <p class="text-sm text-gray-500">
                        Welcome back, <?php echo e(Auth::user()->name ?? 'User'); ?>

                    </p>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center gap-4">

                    <!--  NOTIFICATIONS -->
                    <div x-data="{ open: false }" class="relative">

                        <button @click="open = !open" class="relative p-2 bg-gray-100 rounded-full hover:bg-gray-200">

                            🔔

                            <?php if(isset($notifications) && count($notifications) > 0): ?>
                                <span class="absolute px-1 text-xs text-white bg-red-500 rounded-full -top-1 -right-1">
                                    <?php echo e(count($notifications)); ?>

                                </span>
                            <?php endif; ?>
                        </button>

                        <div x-show="open" @click.away="open = false"
                            class="absolute right-0 z-50 mt-2 bg-white border shadow-xl w-80 rounded-xl">

                            <div class="p-4 font-semibold border-b">
                                Notifications
                            </div>

                            <div class="overflow-y-auto max-h-80">

                                <?php if(isset($notifications) && count($notifications)): ?>
                                    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="p-3 text-sm border-b hover:bg-gray-50">

                                            <div class="font-medium text-gray-800">
                                                <?php echo e(ucfirst($note->action)); ?>

                                            </div>

                                            <div class="text-gray-600">
                                                <?php echo e($note->item->part_name ?? 'Item'); ?>

                                                by <?php echo e($note->user->name ?? 'System'); ?>

                                            </div>

                                            <div class="text-xs text-gray-400">
                                                <?php echo e($note->created_at->diffForHumans()); ?>

                                            </div>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <div class="p-4 text-sm text-gray-500">
                                        No notifications yet
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                    <!-- DARK MODE -->
                    <button @click="darkMode = !darkMode"
                        class="px-3 py-1 text-sm bg-gray-200 rounded hover:bg-gray-300">
                        Toggle Mode
                    </button>

                    <!-- LOGOUT -->
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="px-3 py-1 text-white bg-red-500 rounded hover:bg-red-600">
                            Logout
                        </button>
                    </form>

                </div>

            </div>

            <!-- CONTENT -->
            <main class="p-6">
                <?php echo e($slot); ?>

            </main>

        </div>

    </div>

    <script src="https://unpkg.com/alpinejs" defer></script>

    <script>
        let typing = false;

        document.addEventListener('keydown', () => typing = true);
        document.addEventListener('keyup', () => typing = false);

        setInterval(() => {
            if (!typing) {
                window.location.reload();
            }
        }, 30000);
    </script>

</body>

</html><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/layouts/app.blade.php ENDPATH**/ ?>