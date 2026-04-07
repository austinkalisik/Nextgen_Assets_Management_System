@extends('layouts.app')

@section('content')
    <h1 class="mb-6 text-3xl font-bold">Create Supplier</h1>

    <form method="POST" action="{{ route('suppliers.store') }}"
        class="grid grid-cols-1 gap-4 p-6 bg-white shadow rounded-2xl md:grid-cols-2">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Supplier Name"
            class="px-4 py-2 border rounded-lg">
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="px-4 py-2 border rounded-lg">
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone" class="px-4 py-2 border rounded-lg">

        <div class="flex gap-3 md:col-span-2">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Save Supplier</button>
            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Cancel</a>
        </div>
    </form>
@endsection