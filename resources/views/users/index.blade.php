@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold">Users</h1>
            <p class="text-slate-500">Manage staff and access roles</p>
        </div>
        <a href="{{ route('users.create') }}" class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">+ Add
            User</a>
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="flex gap-3 p-4 mb-6 bg-white shadow rounded-2xl">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
            class="flex-1 px-4 py-2 border rounded-lg">
        <button class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">Search</button>
    </form>

    <div class="overflow-x-auto bg-white shadow rounded-2xl">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Assignments</th>
                    <th class="px-4 py-3 text-left">Active</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b">
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                        <td class="px-4 py-3">{{ $user->assignments_count }}</td>
                        <td class="px-4 py-3">{{ $user->active_assignments_count }}</td>
                        <td class="flex gap-2 px-4 py-3">
                            <a href="{{ route('users.show', $user) }}"
                                class="px-3 py-1 text-xs text-white rounded bg-slate-700">View</a>
                            <a href="{{ route('users.edit', $user) }}"
                                class="px-3 py-1 text-xs text-white bg-blue-600 rounded">Edit</a>
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                onsubmit="return confirm('Delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 text-xs text-white bg-red-600 rounded">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection