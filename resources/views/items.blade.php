<x-app-layout>

    <div class="space-y-8">

        <!-- HEADER -->
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Products Management</h1>
            <p class="text-sm text-gray-500">Manage your inventory efficiently</p>
        </div>

        <!-- SUCCESS -->
        @if(session('success'))
            <div class="p-4 text-green-700 bg-green-100 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- ERRORS -->
        @if ($errors->any())
            <div class="p-4 text-red-700 bg-red-100 rounded-lg">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- SEARCH + ADD -->
        <div class="p-6 space-y-4 bg-white border shadow rounded-xl">

            <!-- SEARCH -->
            <form method="GET" action="{{ route('products') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                    class="w-full px-4 py-2 border rounded-lg">

                <button class="px-4 py-2 text-white bg-blue-600 rounded-lg">
                    Search
                </button>
            </form>

            <!-- ADD PRODUCT -->
            <form method="POST" action="{{ route('products.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-5">
                @csrf

                <input name="part_no" placeholder="Code" class="px-3 py-2 border rounded-lg" required>
                <input name="brand" placeholder="Brand" class="px-3 py-2 border rounded-lg" required>
                <input name="part_name" placeholder="Name" class="px-3 py-2 border rounded-lg" required>
                <input name="description" placeholder="Description" class="px-3 py-2 border rounded-lg" required>

                <button class="text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + Add
                </button>
            </form>

        </div>

        <!-- TABLE -->
        <div class="overflow-hidden bg-white border shadow rounded-2xl">

            <!-- HEADER -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b bg-slate-50">

                <h3 class="font-semibold text-gray-700">All Products</h3>

                <!-- FILTER -->
                <form method="GET" action="{{ route('products') }}" class="flex gap-2">

                    <input type="text" name="part_no" value="{{ request('part_no') }}" placeholder="Filter by Code"
                        class="px-3 py-2 text-sm border rounded-lg">

                    <button class="px-4 py-2 text-white bg-gray-700 rounded-lg">
                        Filter
                    </button>

                </form>

                <span class="text-sm text-gray-400">
                    {{ $items->total() }} total
                </span>

            </div>

            <!-- TABLE -->
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-600 uppercase bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left">Code</th>
                        <th class="px-6 py-4 text-left">Brand</th>
                        <th class="px-6 py-4 text-left">Name</th>
                        <th class="px-6 py-4 text-left">Description</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50">

                            <td class="px-6 py-4">{{ $item->part_no }}</td>
                            <td class="px-6 py-4">{{ $item->brand }}</td>
                            <td class="px-6 py-4">{{ $item->part_name }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $item->description }}</td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">

                                    <a href="#" class="px-3 py-1 text-xs text-white bg-blue-500 rounded-lg">
                                        Edit
                                    </a>

                                    <form method="POST" action="{{ route('products.destroy', $item->id) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this product?')"
                                            class="px-3 py-1 text-xs text-white bg-red-500 rounded-lg">
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-400">
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- PAGINATION -->
            <div class="p-4">
                {{ $items->links() }}
            </div>

        </div>

    </div>

</x-app-layout>