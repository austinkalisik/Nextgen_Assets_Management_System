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
        <div class="flex items-center justify-between">

            <!-- LEFT SIDE -->
            <div>
                <?php
                    $hour = now()->format('H');
                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                ?>

                <h1 class="text-3xl font-bold text-slate-800">
                    <?php echo e($greeting); ?>, <?php echo e(Auth::user()->name ?? 'User'); ?>

                </h1>

                <p class="text-sm text-gray-500">
                    Asset overview and system activity
                </p>
            </div>

            <!-- RIGHT SIDE -->
            <div class="flex items-center gap-4">

                <!-- PORT MORESBY TIME -->
                <div class="px-4 py-2 text-sm font-semibold bg-gray-100 rounded-lg shadow">
                     <span id="png-time" class="text-indigo-600"></span>
                </div>

                <!-- SEARCH -->
                <form method="GET" action="<?php echo e(route('assets')); ?>" class="flex gap-2">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search assets..."
                        class="w-64 px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200">

                    <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Search
                    </button>
                </form>

            </div>

        </div>

        <!-- TIME SCRIPT -->
        <script>
            function updatePNGTime() {
                const options = {
                    timeZone: "Pacific/Port_Moresby",
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit",
                    hour12: true
                };

                const formatter = new Intl.DateTimeFormat([], options);
                document.getElementById("png-time").textContent = formatter.format(new Date());
            }

            setInterval(updatePNGTime, 1000);
            updatePNGTime();
        </script>

        <!-- ALERT -->
        <?php if(isset($lowStockAssets) && $lowStockAssets > 0): ?>
            <div class="p-4 text-yellow-800 bg-yellow-100 rounded-lg">
                 <?php echo e($lowStockAssets); ?> assets are low in stock
            </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">

            <div class="p-6 text-white bg-blue-600 shadow rounded-xl">
                <p class="text-sm">Total Assets</p>
                <h2 class="text-3xl font-bold"><?php echo e($totalAssets); ?></h2>
            </div>

            <div class="p-6 text-white bg-green-600 shadow rounded-xl">
                <p class="text-sm">Available</p>
                <h2 class="text-3xl font-bold"><?php echo e($availableAssets); ?></h2>
            </div>

            <div class="p-6 text-white bg-yellow-500 shadow rounded-xl">
                <p class="text-sm">Assigned</p>
                <h2 class="text-3xl font-bold"><?php echo e($assignedAssets); ?></h2>
            </div>

            <div class="p-6 text-white bg-red-500 shadow rounded-xl">
                <p class="text-sm">Maintenance    </p>
                
            </div>

        </div>

        <!-- MAIN GRID -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- RECENT ASSETS -->
            <div class="p-6 bg-white shadow rounded-xl lg:col-span-2">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700">Recent Assets</h3>

                    <a href="<?php echo e(route('assets')); ?>" class="text-sm text-blue-500 hover:underline">
                        View all →
                    </a>
                </div>

                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500 border-b">
                        <tr>
                            <th>Name</th>
                            <th>Brand</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $latestAssets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3"><?php echo e($asset->part_name); ?></td>
                                <td class="py-3"><?php echo e($asset->brand); ?></td>

                                <td class="py-3">
                                    <spa class="px-2 py-1 text-xs rounded
                                        <?php if($asset->status == 'available'): ?> bg-green-100 text-green-600
                                        <?php elseif($asset->status == 'assigned'): ?> bg-yellow-100 text-yellow-600
                                        <?php elseif($asset->status == 'maintenance'): ?> bg-red-100 text-red-600
                                        <?php endif; ?> ">
                                    
                                        <?php echo e(ucfirst($asset->status)); ?>

                                    </span>
                                </td>

                                <td class="py-3 text-gray-400">
                                    <?php echo e(optional($asset->created_at)->diffForHumans()); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-400">
                                    No assets found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>

            <!-- SIDE PANEL -->
            <div class="space-y-6">

                <!-- SUMMARY -->
                <div class="p-6 bg-white shadow rounded-xl">
                    <h3 class="mb-4 font-semibold text-gray-700">System Summary</h3>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Total Assets</span>
                            <span class="font-semibold"><?php echo e($totalAssets); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Suppliers</span>
                            <span class="font-semibold"><?php echo e($totalSuppliers); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Categories</span>
                            <span class="font-semibold"><?php echo e($totalCategories); ?></span>
                        </div>
                    </div>
                </div>

                <!-- MONTHLY -->
                <?php if(isset($monthlyAssets)): ?>
                    <div class="p-6 bg-white shadow rounded-xl">
                        <h3 class="mb-4 font-semibold text-gray-700">Monthly Activity</h3>

                        <ul class="space-y-1 text-sm text-gray-600">
                            <?php $__currentLoopData = $monthlyAssets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>Month <?php echo e($month + 1); ?>: <?php echo e($count); ?> assets</li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

            </div>

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
<?php endif; ?><?php /**PATH C:\Users\akalisik\Project\backend\resources\views/dashboard.blade.php ENDPATH**/ ?>