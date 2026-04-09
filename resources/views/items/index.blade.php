@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Assets</h1>
            <p class="text-slate-500">Manage asset records, ownership, status, and lifecycle</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('items.create') }}"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + Add Assets
            </a>
            <a href="{{ route('assignments.create') }}"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900 hover:bg-slate-800">
                Assign Assets
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('items.index') }}"
        class="grid grid-cols-1 gap-4 p-4 mb-6 bg-white shadow rounded-2xl md:grid-cols-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search assets..."
            class="px-4 py-2 border rounded-lg">

        <select name="status" class="px-4 py-2 border rounded-lg">
            <option value="">All Status</option>
            <option value="available" @selected(request('status') === 'available')>Available</option>
            <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
            <option value="maintenance" @selected(request('status') === 'maintenance')>Maintenance</option>
            <option value="retired" @selected(request('status') === 'retired')>Retired</option>
        </select>

        <select name="category_id" class="px-4 py-2 border rounded-lg">
            <option value="">All Categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <select name="department_id" class="px-4 py-2 border rounded-lg">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>

        <div class="flex gap-3 md:col-span-4">
            <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Filter</button>
            <a href="{{ route('items.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden bg-white shadow rounded-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Asset</th>
                        <th class="px-4 py-3 text-left">Tag</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Department</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Assigned To</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr class="border-b">
                            <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                            <td class="px-4 py-3">{{ $item->asset_tag ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->supplier?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->department?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ ucfirst($item->status) }}</td>
                            <td class="px-4 py-3">{{ $item->activeAssignment?->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('items.show', $item) }}"
                                        class="px-3 py-1 text-xs text-white rounded bg-slate-700">View</a>
                                    <a href="{{ route('items.edit', $item) }}"
                                        class="px-3 py-1 text-xs text-white bg-blue-600 rounded">Edit</a>
                                    <form method="POST" action="{{ route('items.destroy', $item) }}"
                                        onsubmit="return confirm('Delete this asset?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1 text-xs text-white bg-red-600 rounded">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">No assets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection