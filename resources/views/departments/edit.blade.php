@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Edit Department</h1>

    <form method="POST" action="{{ route('departments.update', $department) }}"
        class="rounded-2xl bg-white p-6 shadow space-y-4">
        @csrf
        @method('PUT')
        <input type="text" name="name" value="{{ old('name', $department->name) }}" placeholder="Department Name"
            class="w-full rounded-lg border px-4 py-2">

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Update Department</button>
            <a href="{{ route('departments.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Cancel</a>
        </div>
    </form>
@endsection