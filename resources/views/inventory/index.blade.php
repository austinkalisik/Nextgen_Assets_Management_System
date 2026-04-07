@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Inventory Management</h1>
        <p class="text-slate-500">Manage stock in and stock out for assets</p>
    </div>

    <form method="GET" action="{{ route('inventory.index') }}" class="flex gap-3 p-4 mb-6 bg-white shadow rounded-2xl">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search inventory..."
            class="flex-1 px-4 py-2 border rounded-lg">
        <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Search</button>
    </form>

    <div class="overflow-x-auto bg-white shadow rounded-2xl">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Asset</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Qty</th>
                    <th class="px-4 py-3 text-left">Stock In</th>
                    <th class="px-4 py-3 text-left">Stock Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $item->name }}</td>
                        <td class="px-4 py-3">{{ $item->category->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $item->quantity }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('inventory.stock-in', $item) }}" class="flex gap-2">
                                @csrf
                                <input type="number" name="quantity" min="1" class="w-24 px-2 py-1 border rounded" required>
                                <button class="px-3 py-1 text-xs text-white bg-green-600 rounded">In</button>
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('inventory.stock-out', $item) }}" class="flex gap-2">
                                @csrf
                                <input type="number" name="quantity" min="1" class="w-24 px-2 py-1 border rounded" required>
                                <button class="px-3 py-1 text-xs text-white bg-red-600 rounded">Out</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No inventory records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection