@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ $department->name }}</h1>
            <p class="text-slate-500">Department details</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Print</button>
            <a href="{{ route('departments.edit', $department) }}"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Edit</a>
        </div>
    </div>

    <div class="p-6 bg-white shadow rounded-2xl">
        <p><strong>Name:</strong> {{ $department->name }}</p>
        <p class="mt-2"><strong>Total Assets:</strong> {{ $department->items->count() }}</p>

        <div class="mt-6">
            <h2 class="mb-4 text-xl font-semibold">Assets in this department</h2>
            <div class="space-y-2">
                @forelse($department->items as $item)
                    <div class="pb-2 border-b">{{ $item->name }}</div>
                @empty
                    <p class="text-slate-500">No assets linked to this department.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection