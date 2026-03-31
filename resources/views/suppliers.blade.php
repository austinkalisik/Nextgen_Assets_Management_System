<x-app-layout>

    <div class="space-y-8">

        <!-- HEADER -->
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Suppliers Management</h1>
            <p class="text-sm text-gray-500">Manage and track all your suppliers</p>
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
            <form method="GET" action="{{ route('suppliers') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search suppliers..."
                    class="w-full px-4 py-2 border rounded-lg">
                   
                <button class="px-4 py-2 text-white bg-blue-600 rounded-lg">
                    Search
                </button>
            </form>

            <!-- ADD -->
            <form method="POST" action="{{ route('suppliers.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3">
                @csrf

                <input name="name" placeholder="Supplier Name" class="px-3 py-2 border rounded-lg" required>
                <input name="email" placeholder="Email" class="px-3 py-2 border rounded-lg" required>

                <button class="text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + Add Supplier
                </button>
            </form>

        </div>

        <!-- TABLE -->
        <div class="overflow-hidden bg-white border shadow rounded-2xl">

            <!-- HEADER -->
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b bg-slate-50">

                <h3 class="font-semibold text-gray-700">All Suppliers</h3>

                <!-- FILTER -->
                <form method="GET" action="{{ route('suppliers') }}" class="flex gap-2">

                    <input type="text" name="name" value="{{ request('name') }}" placeholder="Filter by Name"
                        class="px-3 py-2 text-sm border rounded-lg">

                    <input type="text" name="email" value="{{ request('email') }}" placeholder="Filter by Email"
                        class="px-3 py-2 text-sm border rounded-lg">

                    <button class="px-4 py-2 text-white bg-gray-700 rounded-lg">
                        Filter
                    </button>

                </form>

                <span class="text-sm text-gray-400">
                    {{ $suppliers->total() }} total
                </span>

            </div>

            <!-- TABLE -->
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-600 uppercase bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left">Name</th>
                        <th class="px-6 py-4 text-left">Email</th>
                        <th class="px-6 py-4 text-left">Products</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-50">

                            <td class="px-6 py-4 font-medium">{{ $supplier->name }}</td>

                            <td class="px-6 py-4 text-gray-600">{{ $supplier->email }}</td>

                            <!-- ITEM COUNT -->
                            <td class="px-6 py-4 text-gray-500">
                                {{ $supplier->items_count ?? 0 }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">

                                <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                    class="px-3 py-1 text-xs text-white bg-blue-500 rounded-lg hover:bg-blue-600">
                                    Edit
                                </a>

                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this supplier?')"
                                            class="px-3 py-1 text-xs text-white bg-red-500 rounded-lg">
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-400">
                                No suppliers found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- PAGINATION -->
            <div class="p-4">
                {{ $suppliers->links() }}
            </div>

        </div>

    </div>

</x-app-layout>