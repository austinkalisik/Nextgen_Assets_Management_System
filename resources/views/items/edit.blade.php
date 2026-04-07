@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Edit Asset</h1>
        <p class="text-slate-500">Update asset details</p>
    </div>

    <form method="POST" action="{{ route('items.update', $item) }}"
        class="grid grid-cols-1 gap-4 p-6 bg-white shadow rounded-2xl md:grid-cols-2">
        @csrf
        @method('PUT')

        <input type="text" name="name" value="{{ old('name', $item->name) }}" placeholder="Asset Name"
            class="px-4 py-2 border rounded-lg">
        <input type="text" name="asset_tag" value="{{ old('asset_tag', $item->asset_tag) }}" placeholder="Asset Tag"
            class="px-4 py-2 border rounded-lg">
        <input type="text" name="serial_number" value="{{ old('serial_number', $item->serial_number) }}"
            placeholder="Serial Number" class="px-4 py-2 border rounded-lg">
        <input type="text" name="location" value="{{ old('location', $item->location) }}" placeholder="Location"
            class="px-4 py-2 border rounded-lg">
        <input type="date" name="purchase_date"
            value="{{ old('purchase_date', optional($item->purchase_date)->format('Y-m-d')) }}"
            class="px-4 py-2 border rounded-lg">
        <input type="number" name="quantity" value="{{ old('quantity', $item->quantity) }}" min="1"
            class="px-4 py-2 border rounded-lg">

        <select name="category_id" class="px-4 py-2 border rounded-lg">
            <option value="">Select Category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>
                    {{ $category->name }}</option>
            @endforeach
        </select>

        <select name="supplier_id" class="px-4 py-2 border rounded-lg">
            <option value="">Select Supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $item->supplier_id) == $supplier->id)>
                    {{ $supplier->name }}</option>
            @endforeach
        </select>

        <select name="department_id" class="px-4 py-2 border rounded-lg">
            <option value="">Select Department</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $item->department_id) == $department->id)>
                    {{ $department->name }}</option>
            @endforeach
        </select>

        <select name="status" class="px-4 py-2 border rounded-lg">
            <option value="available" @selected(old('status', $item->status) === 'available')>Available</option>
            <option value="assigned" @selected(old('status', $item->status) === 'assigned')>Assigned</option>
            <option value="maintenance" @selected(old('status', $item->status) === 'maintenance')>Maintenance</option>
            <option value="retired" @selected(old('status', $item->status) === 'retired')>Retired</option>
        </select>

        <div class="flex gap-3 md:col-span-2">
            <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg">Update Asset</button>
            <a href="{{ route('items.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">Cancel</a>
        </div>
    </form>
@endsection