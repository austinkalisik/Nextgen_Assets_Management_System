@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Suppliers</h1>
            <p class="text-slate-500">Manage suppliers and vendors</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">+ Add
            Supplier</a>
    </div>

    <form method="GET" action="{{ route('suppliers.index') }}" class="mb-6 rounded-2xl bg-white p-4 shadow flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search suppliers..."
            class="flex-1 rounded-lg border px-4 py-2">
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-white font-semibold">Search</button>
    </form>

    <div class="rounded-2xl bg-white shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Assets</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $supplier->name }}</td>
                        <td class="px-4 py-3">{{ $supplier->email }}</td>
                        <td class="px-4 py-3">{{ $supplier->phone }}</td>
                        <td class="px-4 py-3">{{ $supplier->items_count }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}"
                                class="rounded bg-slate-700 px-3 py-1 text-white text-xs">View</a>
                            <a href="{{ route('suppliers.edit', $supplier) }}"
                                class="rounded bg-blue-600 px-3 py-1 text-white text-xs">Edit</a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                onsubmit="return confirm('Delete this supplier?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded bg-red-600 px-3 py-1 text-white text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No suppliers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $suppliers->links() }}</div>
@endsection