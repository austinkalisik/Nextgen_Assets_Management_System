@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Assign Asset</h1>
        <p class="text-slate-500">Assign an available asset to a user</p>
    </div>

    <div class="p-6 bg-white shadow rounded-2xl">
        @if($items->isEmpty())
            <div class="px-4 py-3 border rounded-lg border-amber-200 bg-amber-50 text-amber-700">
                No available assets to assign right now.
            </div>

            <div class="mt-4">
                <a href="{{ route('assignments.index') }}" class="px-4 py-2 font-semibold text-white rounded-lg bg-slate-900">
                    Back to Assignments
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('assignments.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf

                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium">Asset</label>
                    <select name="item_id" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select Asset</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>
                                {{ $item->name }}
                                @if($item->asset_tag) - {{ $item->asset_tag }} @endif
                                @if($item->category) - {{ $item->category->name }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">User</label>
                    <select name="user_id" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} - {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Department</label>
                    <select name="department_id" class="w-full px-4 py-2 border rounded-lg" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium">Assigned At</label>
                    <input type="datetime-local" name="assigned_at"
                        value="{{ old('assigned_at', now()->format('Y-m-d\TH:i')) }}" class="w-full px-4 py-2 border rounded-lg"
                        required>
                </div>

                <div class="flex gap-3 md:col-span-2">
                    <button class="px-4 py-2 font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Assign Asset
                    </button>
                    <a href="{{ route('assignments.index') }}" class="px-4 py-2 font-semibold rounded-lg bg-slate-200">
                        Cancel
                    </a>
                </div>
            </form>
        @endif
    </div>
@endsection