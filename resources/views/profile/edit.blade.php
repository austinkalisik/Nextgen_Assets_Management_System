@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Edit Profile</h1>
        <p class="text-slate-500">Update your account details</p>
    </div>

    <form method="POST" action="{{ route('profile.update') }}"
        class="grid grid-cols-1 gap-4 p-6 bg-white shadow rounded-2xl md:grid-cols-2">
        @csrf
        @method('PATCH')

        <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Full Name"
            class="px-4 py-2 border rounded-lg">
        <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Email"
            class="px-4 py-2 border rounded-lg">
        <input type="password" name="password" placeholder="New Password (optional)" class="px-4 py-2 border rounded-lg">
        <input type="password" name="password_confirmation" placeholder="Confirm New Password"
            class="px-4 py-2 border rounded-lg">

        <div class="md:col-span-2">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Update Profile</button>
        </div>
    </form>
@endsection