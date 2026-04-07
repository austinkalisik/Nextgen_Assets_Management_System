@extends('layouts.app')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Create Category</h1>

    <form method="POST" action="{{ route('categories.store') }}" class="rounded-2xl bg-white p-6 shadow space-y-4">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Category Name"
            class="w-full rounded-lg border px-4 py-2">
        <textarea name="description" placeholder="Description"
            class="w-full rounded-lg border px-4 py-2">{{ old('description') }}</textarea>

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold">Save Category</button>
            <a href="{{ route('categories.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold">Cancel</a>
        </div>
    </form>
@endsection