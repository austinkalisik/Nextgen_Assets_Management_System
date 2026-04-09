@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Edit User</h1>
        <p class="text-slate-500">Update account details and access role</p>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}"
        class="rounded-2xl bg-white p-6 shadow grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        @method('PUT')

        <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Full Name"
            class="rounded-lg border px-4 py-2" required>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Email Address"
            class="rounded-lg border px-4 py-2" required>

        <select name="role" class="rounded-lg border px-4 py-2" required>
            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
            <option value="manager" @selected(old('role', $user->role) === 'manager')>Manager</option>
            <option value="asset_officer" @selected(old('role', $user->role) === 'asset_officer')>Asset Officer</option>
            <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
        </select>

        <input type="password" name="password" placeholder="New Password (optional)" class="rounded-lg border px-4 py-2">
        <input type="password" name="password_confirmation" placeholder="Confirm New Password"
            class="rounded-lg border px-4 py-2 md:col-span-2">

        <div class="md:col-span-2 flex gap-3">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Update User</button>
            <a href="{{ route('users.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Cancel</a>
        </div>
    </form>
@endsection