@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">{{ $item->name }}</h1>
            <p class="text-slate-500">Asset details</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="rounded-lg bg-slate-900 px-4 py-2 text-white font-semibold">Print</button>
            <a href="{{ route('items.edit', $item) }}"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Edit</a>
            <a href="{{ route('items.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white p-6 shadow space-y-3">
            <div><strong>Name:</strong> {{ $item->name }}</div>
            <div><strong>Category:</strong> {{ $item->category?->name ?? '-' }}</div>
            <div><strong>Supplier:</strong> {{ $item->supplier?->name ?? '-' }}</div>
            <div><strong>Department:</strong> {{ $item->department?->name ?? '-' }}</div>
            <div><strong>Asset Tag:</strong> {{ $item->asset_tag ?? '-' }}</div>
            <div><strong>Serial Number:</strong> {{ $item->serial_number ?? '-' }}</div>
            <div><strong>Status:</strong> {{ ucfirst($item->status) }}</div>
            <div><strong>Assigned To:</strong> {{ $item->activeAssignment?->user?->name ?? '-' }}</div>
            <div><strong>Location:</strong> {{ $item->location ?? '-' }}</div>
            <div><strong>Purchase Date:</strong> {{ optional($item->purchase_date)->format('d M Y') ?? '-' }}</div>
            <div><strong>Quantity:</strong> {{ $item->quantity }}</div>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="text-xl font-semibold mb-4">Assignment History</h2>
            <div class="space-y-3 text-sm">
                @forelse($item->assignments as $assignment)
                    <div class="border-b pb-2">
                        <p class="font-medium">{{ $assignment->user->name ?? '-' }}</p>
                        <p class="text-slate-500">
                            {{ $assignment->department->name ?? '-' }}
                            • {{ optional($assignment->assigned_at)->format('d M Y H:i') }}
                            @if($assignment->returned_at)
                                • Returned {{ optional($assignment->returned_at)->format('d M Y H:i') }}
                            @endif
                        </p>
                    </div>
                @empty
                    <p class="text-slate-500">No assignment history found.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection