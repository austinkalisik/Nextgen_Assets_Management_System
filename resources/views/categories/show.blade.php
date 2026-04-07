@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
            <p class="text-slate-500">Category details</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Print</button>
            <a href="{{ route('categories.edit', $category) }}"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Edit</a>
            <a href="{{ route('categories.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Back</a>
        </div>
    </d>

    <div class="p-6 mb-6 bg-white shadow rounded-2xl">
        <p><strong>Name:</strong> {{ $category->name }}</p>
        <p class="mt-2"><strong>Description:</strong> {{ $category->description ?? '-' }}</p>
        <p class="mt-2"><strong>Total Assets:</strong> {{ $category->items->count() }}</p>
    </div>

    <div class="p-6 bg-white shadow rounded-2xl">
        <h2 class="mb-4 text-xl font-semibold">Assets in this category</h2>
        <div class="space-y-2">
            @forelse($category->items as $item)
                <div class="pb-2 border-b">
                    {{ $item->name }} — {{ $item->asset_tag ?? 'No tag' }}
                </div>
            @empty
                <p class="text-slate-500">No assets linked to this category.</p>
            @endforelse
        </div>
    </div>
@endsection