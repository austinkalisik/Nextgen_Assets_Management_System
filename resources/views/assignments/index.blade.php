@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Assignments</h1>
            <p class="text-slate-500">Track asset assignments and returns</p>
        </div>
        <a href="{{ route('assignments.create') }}" class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">+
            Assign Asset</a>
    </div>

    <form method="GET" action="{{ route('assignments.index') }}" class="flex gap-3 p-4 mb-6 bg-white shadow rounded-2xl">
        <select name="status" class="px-4 py-2 border rounded-lg">
            <option value="">All</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="returned" @selected(request('status') === 'returned')>Returned</option>
        </select>
        <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Filter</button>
    </form>

    <div class="overflow-x-auto bg-white shadow rounded-2xl">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Asset</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Department</th>
                    <th class="px-4 py-3 text-left">Assigned At</th>
                    <th class="px-4 py-3 text-left">Returned At</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $assignment->item->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $assignment->user->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $assignment->department->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ optional($assignment->assigned_at)->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ optional($assignment->returned_at)->format('d M Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if(!$assignment->returned_at)
                                <form method="POST" action="{{ route('assignments.return', $assignment) }}">
                                    @csrf
                                    <button class="px-3 py-1 text-xs text-white bg-green-600 rounded">Return</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-500">Completed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No assignments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $assignments->links() }}</div>
@endsection