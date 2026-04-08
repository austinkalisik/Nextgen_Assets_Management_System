@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ $item->name }}</h1>
            <p class="text-slate-500">Asset details, assignment trail, and activity history</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()"
                class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Print</button>
            <a href="{{ route('items.edit', $item) }}"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Edit</a>
            <a href="{{ route('items.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="p-6 space-y-3 bg-white shadow rounded-2xl">
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

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Assignment History</h2>
            <div class="space-y-3 text-sm">
                @forelse($item->assignments as $assignment)
                    <div class="pb-2 border-b">
                        <p class="font-medium">{{ $assignment->user->name ?? '-' }}</p>
                        <p class="text-slate-500">
                            {{ $assignment->department->name ?? '-' }}
                            • Assigned {{ optional($assignment->assigned_at)->format('d M Y H:i') }}
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

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Activity Log</h2>
            <div class="space-y-3 text-sm">
                @forelse($item->assetLogs as $log)
                    <div class="pb-2 border-b">
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</p>
                        <p class="text-slate-500">
                            {{ $log->user?->name ?? 'System' }} • {{ optional($log->created_at)->format('d M Y H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-slate-500">No activity found for this asset.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection