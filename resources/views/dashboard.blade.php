<x-app-layout>

    ```
    <div class="space-y-8">

        <!-- HEADER -->
        <div class="flex items-center justify-between">

            <!-- LEFT SIDE -->
            <div>
                @php
                    $hour = now()->format('H');
                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                @endphp

                <h1 class="text-3xl font-bold text-slate-800">
                    {{ $greeting }}, {{ Auth::user()->name ?? 'User' }}
                </h1>

                <p class="text-sm text-gray-500">
                    Asset overview and system activity
                </p>
            </div>

            <!-- RIGHT SIDE -->
            <div class="flex items-center gap-4">

                <!-- PORT MORESBY TIME -->
                <div class="px-4 py-2 text-sm font-semibold bg-gray-100 rounded-lg shadow">
                    🕒 <span id="png-time" class="text-indigo-600"></span>
                </div>

                <!-- SEARCH -->
                <form method="GET" action="{{ route('assets') }}" class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search assets..."
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
        @if(isset($lowStockAssets) && $lowStockAssets > 0)
            <div class="p-4 text-yellow-800 bg-yellow-100 rounded-lg">
                ⚠️ {{ $lowStockAssets }} assets are low in stock
            </div>
        @endif

        <!-- STATS -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">

            <div class="p-6 text-white bg-blue-600 shadow rounded-xl">
                <p class="text-sm">Total Assets</p>
                <h2 class="text-3xl font-bold">{{ $totalAssets }}</h2>
            </div>

            <div class="p-6 text-white bg-green-600 shadow rounded-xl">
                <p class="text-sm">Available</p>
                <h2 class="text-3xl font-bold">{{ $availableAssets }}</h2>
            </div>

            <div class="p-6 text-white bg-yellow-500 shadow rounded-xl">
                <p class="text-sm">Assigned</p>
                <h2 class="text-3xl font-bold">{{ $assignedAssets }}</h2>
            </div>

            <div class="p-6 text-white bg-red-500 shadow rounded-xl">
                <p class="text-sm">Maintenance</p>
                <h2 class="text-3xl font-bold">{{ $maintenanceAssets }}</h2>
            </div>

        </div>

        <!-- MAIN GRID -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- RECENT ASSETS -->
            <div class="p-6 bg-white shadow rounded-xl lg:col-span-2">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700">Recent Assets</h3>

                    <a href="{{ route('assets') }}" class="text-sm text-blue-500 hover:underline">
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
                        @forelse($latestAssets as $asset)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3">{{ $asset->part_name }}</td>
                                <td class="py-3">{{ $asset->brand }}</td>

                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded
                                        @if($asset->status == 'available') bg-green-100 text-green-600
                                        @elseif($asset->status == 'assigned') bg-yellow-100 text-yellow-600
                                        @elseif($asset->status == 'maintenance') bg-red-100 text-red-600
                                        @endif
                                    ">
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                </td>

                                <td class="py-3 text-gray-400">
                                    {{ optional($asset->created_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-400">
                                    No assets found
                                </td>
                            </tr>
                        @endforelse
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
                            <span class="font-semibold">{{ $totalAssets }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Suppliers</span>
                            <span class="font-semibold">{{ $totalSuppliers }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Categories</span>
                            <span class="font-semibold">{{ $totalCategories }}</span>
                        </div>
                    </div>
                </div>

                <!-- MONTHLY -->
                @if(isset($monthlyAssets))
                    <div class="p-6 bg-white shadow rounded-xl">
                        <h3 class="mb-4 font-semibold text-gray-700">Monthly Activity</h3>

                        <ul class="space-y-1 text-sm text-gray-600">
                            @foreach($monthlyAssets as $month => $count)
                                <li>Month {{ $month + 1 }}: {{ $count }} assets</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>

        </div>

    </div>
    ```

</x-app-layout>