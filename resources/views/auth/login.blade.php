@extends('layouts.guest')

@section('content')
    <div class="p-8 bg-white shadow-xl rounded-2xl">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-slate-900">NextGen Assets Login</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to manage company assets</p>
        </div>

        @if ($errors->any())
            <div class="px-4 py-3 mb-4 text-sm text-red-700 border border-red-200 rounded-lg bg-red-50">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block mb-1 text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300">
                <label for="remember" class="text-sm text-slate-600">Remember me</label>
            </div>

            <button type="submit"
                class="w-full px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Login
            </button>
        </=>
    </div>
@endsection