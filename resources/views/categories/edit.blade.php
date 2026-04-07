@extends('layouts.app')

@section('content')
    <h1 class="mb-6 text-3xl font-bold">Edit Category</h1>

    <form method="POST" action="{{ route('categories.update', $category) }}"
        class="p-6 space-y-4 bg-white shadow rounded-2xl">
        @csrf
        @method('PUT')

        <input type="text" name="name" value="{{ old('name', $category->name) }}" placeholder="Category Name"
            class="w-full px-4 py-2 border rounded-lg">
        <textarea name="description" placeholder="Description"
            class="w-full px-4 py-2 border rounded-lg">{{ old('description', $category->description) }}</textarea>

        <div class="flex gap-3">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Update Category</button>
            <a href="{{ route('categories.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Cancel</a>
        </div>
    </form>
@endsection