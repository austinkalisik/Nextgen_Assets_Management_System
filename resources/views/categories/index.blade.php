@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Categories</h1>
            <p class="text-slate-500">Manage asset categories</p>
        </div>
        <a href="{{ route('categories.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">+ Add
            Category</a>
    </div>

    <form method="GET" action="{{ route('categories.index') }}" class="mb-6 rounded-2xl bg-white p-4 shadow flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..."
            class="flex-1 rounded-lg border px-4 py-2">
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-white font-semibold">Search</button>
    </form>

    <div class="rounded-2xl bg-white shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-left">Assets</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $category->name }}</td>
                        <td class="px-4 py-3">{{ $category->description }}</td>
                        <td class="px-4 py-3">{{ $category->items_count }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('categories.show', $category) }}"
                                class="rounded bg-slate-700 px-3 py-1 text-white text-xs">View</a>
                            <a href="{{ route('categories.edit', $category) }}"
                                class="rounded bg-blue-600 px-3 py-1 text-white text-xs">Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded bg-red-600 px-3 py-1 text-white text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
@endsection