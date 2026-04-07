@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">{{ $supplier->name }}</h1>
            <p class="text-slate-500">Supplier details</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('suppliers.edit', $supplier) }}"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Edit</a>
            <a href="{{ route('suppliers.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white p-6 shadow space-y-3">
            <div><strong>Name:</strong> {{ $supplier->name }}</div>
            <div><strong>Email:</strong> {{ $supplier->email ?? '-' }}</div>
            <div><strong>Phone:</strong> {{ $supplier->phone ?? '-' }}</div>
            <div><strong>Total Assets:</strong> {{ $supplier->items->count() }}</div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Assets supplied</h2>
                <button onclick="window.print()" class="rounded bg-slate-900 px-3 py-2 text-white text-sm">Print</button>
            </div>

            <div class="space-y-2 text-sm">
                @forelse($supplier->items as $item)
                    <div class="border-b pb-2">
                        {{ $item->name }} — {{ $item->asset_tag ?? 'No tag' }}
                    </div>
                @empty
                    <p class="text-slate-500">No assets linked to this supplier.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection