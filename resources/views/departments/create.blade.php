@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Create Department</h1>

    <form method="POST" action="{{ route('departments.store') }}" class="rounded-2xl bg-white p-6 shadow space-y-4">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Department Name"
            class="w-full rounded-lg border px-4 py-2">

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Save Department</button>
            <a href="{{ route('departments.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Cancel</a>
        </div>
    </form>
@endsection