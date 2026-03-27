
<x-app-layout>

    <div class="space-y-10">

        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Dashboard
                </h1>
                <p class="text-sm text-gray-500">
                    Overview of your assets and system activity
                </p>
            </div>

            <!-- QUICK ACTION -->
            <a href="/products"
                class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700">
                + Add Asset
            </a>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

            <div class="p-6 text-white shadow-lg rounded-2xl bg-gradient-to-r from-blue-500 to-blue-700">
                <p class="text-sm opacity-80">Total Assets</p>
                <h2 class="text-3xl font-bold">{{ $totalProducts }}</h2>
            </div>

            <div class="p-6 text-white shadow-lg rounded-2xl bg-gradient-to-r from-purple-500 to-purple-700">
                <p class="text-sm opacity-80">Total Brands</p>
                <h2 class="text-3xl font-bold">{{ $totalBrands }}</h2>
            </div>

            <div class="p-6 text-white shadow-lg rounded-2xl bg-gradient-to-r from-green-500 to-green-700">
                <p class="text-sm opacity-80">Recently Added</p>
                <h2 class="text-3xl font-bold">{{ $latestProducts->count() }}</h2>
            </div>

        </div>

        <!-- GRID -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- RECENT ASSETS -->
            <div class="p-6 bg-white shadow-lg lg:col-span-2 rounded-2xl">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700">
                        Recent Assets
                    </h3>

                    <a href="/products" class="text-sm text-blue-500 hover:underline">
                        View all →
                    </a>
                </div>

                <div class="space-y-3">

                    @forelse($latestProducts as $product)
                        <div
                            class="flex items-center justify-between p-4 transition rounded-lg bg-slate-50 hover:bg-slate-100">

                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ $product->part_name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $product->brand }}
                                </p>
                            </div>

                            <span class="text-xs text-gray-400">
                                {{ optional($product->created_at)->diffForHumans() ?? 'N/A' }}
                            </span>

                        </div>
                    @empty
                        <div class="py-6 text-center text-gray-400">
                            No recent assets found
                        </div>
                    @endforelse

                </div>

            </div>

            <!-- SYSTEM SUMMARY -->
            <div class="p-6 bg-white shadow-lg rounded-2xl">

                <h3 class="mb-4 font-semibold text-gray-700">
                    System Summary
                </h3>

                <div class="space-y-4 text-sm">

                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Assets</span>
                        <span class="font-semibold">{{ $totalProducts }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Brands</span>
                        <span class="font-semibold">{{ $totalBrands }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">System Status</span>
                        <span class="font-semibold text-green-500">Active</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Update</span>
                        <span class="font-semibold text-gray-700">
                            {{ now()->format('M d, Y') }}
                        </span>
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="mt-6 space-y-2">

                    <a href="/products"
                        class="block w-full px-4 py-2 text-sm text-center rounded-lg bg-slate-100 hover:bg-slate-200">
                        Manage Assets
                    </a>

                    <a href="/settings"
                        class="block w-full px-4 py-2 text-sm text-center rounded-lg bg-slate-100 hover:bg-slate-200">
                        Settings
                    </a>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>