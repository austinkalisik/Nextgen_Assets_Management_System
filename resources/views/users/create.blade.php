@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Create User</h1>
        <p class="text-slate-500">Add a new staff account and assign a system role</p>
    </div>

    <form method="POST" action="{{ route('users.store') }}"
        class="grid grid-cols-1 gap-4 p-6 bg-white shadow rounded-2xl md:grid-cols-2">
        @csrf

        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" class="px-4 py-2 border rounded-lg"
            required>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address"
            class="px-4 py-2 border rounded-lg" required>

        <select name="role" class="px-4 py-2 border rounded-lg" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="asset_officer">Asset Officer</option>
            <option value="staff">Staff</option>
        </select>

        <input type="password" name="password" placeholder="Password" class="px-4 py-2 border rounded-lg" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password"
            class="px-4 py-2 border rounded-lg md:col-span-2" required>

        <div class="flex gap-3 md:col-span-2">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Create User</button>
            <a href="{{ route('users.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Cancel</a>
        </div>
    </form>
@endsection