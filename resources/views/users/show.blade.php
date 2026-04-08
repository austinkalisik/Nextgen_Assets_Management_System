@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ $user->name }}</h1>
            <p class="text-slate-500">User account details, assignments, and activity tracking</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('users.edit', $user) }}"
                class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Edit</a>
            <a href="{{ route('users.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="p-6 space-y-3 bg-white shadow rounded-2xl">
            <div><strong>Name:</strong> {{ $user->name }}</div>
            <div><strong>Email:</strong> {{ $user->email }}</div>
            <div><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $user->role)) }}</div>
            <div><strong>Total Assignments:</strong> {{ $user->assignments->count() }}</div>
            <div><strong>Active Assignments:</strong> {{ $user->activeAssignments->count() }}</div>
            <div><strong>Activity Logs:</strong> {{ $user->assetLogs->count() }}</div>
        </div>

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Active Assignments</h2>
            <div class="space-y-3 text-sm">
                @forelse($user->activeAssignments as $assignment)
                    <div class="pb-2 border-b">
                        <p class="font-medium">{{ $assignment->item?->name ?? '-' }}</p>
                        <p class="text-slate-500">
                            {{ $assignment->department?->name ?? '-' }} •
                            {{ optional($assignment->assigned_at)->format('d M Y H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-slate-500">No active assignments.</p>
                @endforelse
            </div>
        </div>

        <div class="p-6 bg-white shadow rounded-2xl">
            <h2 class="mb-4 text-xl font-semibold">Recent Activity</h2>
            <div class="space-y-3 text-sm">
                @forelse($recentLogs as $log)
                    <div class="pb-2 border-b">
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</p>
                        <p class="text-slate-500">
                            {{ $log->item?->name ?? 'Unknown asset' }} •
                            {{ optional($log->created_at)->format('d M Y H:i') }}
                        </p>
                    </div>
                @empty
                    <p class="text-slate-500">No recent activity recorded.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="p-6 mt-6 bg-white shadow rounded-2xl">
        <h2 class="mb-4 text-xl font-semibold">Assignment History</h2>
        <div class="space-y-3 text-sm">
            @forelse($user->assignments as $assignment)
                <div class="pb-2 border-b">
                    <p class="font-medium">{{ $assignment->item?->name ?? '-' }}</p>
                    <p class="text-slate-500">
                        {{ $assignment->department?->name ?? '-' }} •
                        Assigned {{ optional($assignment->assigned_at)->format('d M Y H:i') }}
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
@endsection