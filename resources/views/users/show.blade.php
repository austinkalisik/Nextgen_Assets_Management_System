@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold mb-6">{{ $user->name }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white p-6 shadow space-y-2">
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
            <p><strong>Total Assignments:</strong> {{ $user->assignments->count() }}</p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow">
            <h2 class="text-xl font-semibold mb-4">Assignment History</h2>
            <div class="space-y-2 text-sm">
                @forelse($user->assignments as $assignment)
                    <div class="border-b pb-2">
                        {{ $assignment->item->name ?? '-' }}
                        • {{ optional($assignment->assigned_at)->format('d M Y') }}
                    </div>
                @empty
                    <p class="text-slate-500">No assignments found.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection