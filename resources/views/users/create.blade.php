@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Create User</h1>

    <form method="POST" action="{{ route('users.store') }}"
        class="rounded-2xl bg-white p-6 shadow grid grid-cols-1 md:grid-cols-2 gap-4">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name"
            class="rounded-lg border px-4 py-2">
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="rounded-lg border px-4 py-2">

        <select name="role" class="rounded-lg border px-4 py-2">
            <option value="staff">Staff</option>
            <option value="asset_officer">Asset Officer</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
        </select>

        <div></div>

        <input type="password" name="password" placeholder="Password" class="rounded-lg border px-4 py-2">
        <input type="password" name="password_confirmation" placeholder="Confirm Password"
            class="rounded-lg border px-4 py-2">

        <div class="md:col-span-2 flex gap-3">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Save User</button>
            <a href="{{ route('users.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Cancel</a>
        </div>
    </form>
@endsection