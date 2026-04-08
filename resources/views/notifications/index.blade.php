@extends('layouts.app')

@section('content')
    <meta http-equiv="refresh" content="30">

    <div class="flex items-center justify-between mb-6">
        <>
            <h1 class="text-3xl font-bold">Notification Center</h1>
            <p class="text-slate-500">Live operational alerts, assignment monitoring, and recent system activity</p>
    </div>

        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">
                Mark All Read
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Unread Alerts</p>
            <h2 class="mt-2 text-3xl font-bold text-red-500">{{ $unreadCount }}</h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Low Stock</p>
            <h2 class="mt-2 text-3xl font-bold text-orange-500">{{ $lowStockItems->count() }}</h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Maintenance</p>
            <h2 class="mt-2 text-3xl font-bold text-amber-500">{{ $maintenanceItems->count() }}</h2>
        </div>
        <div class="p-5 bg-white shadow rounded-2xl">
            <p class="text-sm text-slate-500">Overdue Assignments</p>
            <h2 class="mt-2 text-3xl font-bold text-rose-500">{{ $overdueAssignments->count() }}</h2>
        </div>
    </div>

    <div class="mb-6 bg-white shadow rounded-2xl">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Active Alerts Feed</h2>
        </div>
        <div class="p-6 space-y-3">
            @forelse($alerts as $alert)
                @php
                    $isRead = in_array($alert['id'], session('read_notifications', []));
                    $badgeClasses = match ($alert['type']) {
                        'critical' => 'bg-red-100 text-red-700',
                        'warning' => 'bg-amber-100 text-amber-700',
                        default => 'bg-blue-100 text-blue-700',
                    };
                @endphp

                <a href="{{ $alert['url'] }}"
                    class="block rounded-xl border p-4 transition hover:border-slate-400 hover:shadow-sm {{ $isRead ? 'bg-slate-50 border-slate-200 opacity-75' : 'bg-white border-slate-300' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClasses }}">
                                    {{ ucfirst($alert['type']) }}
                                </span>
                                @if(!$isRead)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                        New
                                    </span>
                                @endif
                            </div>

                            <h3 class="font-semibold text-slate-900">{{ $alert['title'] }}</h3>
                            <p class="mt-1 text-sm text-slate-700">{{ $alert['message'] }}</p>
                            <p class="mt-2 text-xs text-slate-500">{{ $alert['meta'] }}</p>
                        </div>

                        <div class="text-xs font-medium text-blue-600 whitespace-nowrap">
                            Open →
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-slate-500">No active alerts right now.</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-2">
        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Low Stock Assets</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @forelse($lowStockItems as $item)
                    <a href="{{ route('items.show', $item) }}" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium">{{ $item->name }}</p>
                        <p class="text-slate-500">
                            Qty: {{ $item->quantity }} • {{ $item->department?->name ?? '-' }}
                        </p>
                    </a>
                @empty
                    <p class="text-slate-500">No low stock alerts.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Maintenance Assets</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @forelse($maintenanceItems as $item)
                    <a href="{{ route('items.show', $item) }}" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium">{{ $item->name }}</p>
                        <p class="text-slate-500">
                            {{ $item->category?->name ?? '-' }} • {{ $item->department?->name ?? '-' }}
                        </p>
                    </a>
                @empty
                    <p class="text-slate-500">No maintenance alerts.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-2">
        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Overdue Assignments</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @forelse($overdueAssignments as $assignment)
                    <a href="{{ route('assignments.index') }}" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium">{{ $assignment->item?->name ?? '-' }}</p>
                        <p class="text-slate-500">
                            {{ $assignment->user?->name ?? '-' }} • {{ $assignment->department?->name ?? '-' }}
                            • Assigned {{ optional($assignment->assigned_at)->format('d M Y') }}
                        </p>
                    </a>
                @empty
                    <p class="text-slate-500">No overdue assignments.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Recent Assignments</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                @forelse($recentAssignments as $assignment)
                    <a href="{{ route('assignments.index') }}" class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                        <p class="font-medium">{{ $assignment->item?->name ?? '-' }}</p>
                        <p class="text-slate-500">
                            {{ $assignment->user?->name ?? '-' }}
                            • {{ optional($assignment->assigned_at)->format('d M Y H:i') }}
                        </p>
                    </a>
                @empty
                    <p class="text-slate-500">No recent assignments.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-2xl">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Recent Activity Log</h2>
        </div>
        <div class="p-6 space-y-3 text-sm">
            @forelse($recentActivity as $activity)
                <a href="{{ $activity->item ? route('items.show', $activity->item) : route('dashboard') }}"
                    class="block px-1 pb-2 border-b rounded hover:bg-slate-50">
                    <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                    <p class="text-slate-500">
                        {{ $activity->item?->name ?? 'Unknown asset' }} by {{ $activity->user?->name ?? 'System' }}
                        • {{ optional($activity->created_at)->format('d M Y H:i') }}
                    </p>
                </a>
            @empty
                <p class="text-slate-500">No recent activity found.</p>
            @endforelse
        </div>
    </div>
@endsection