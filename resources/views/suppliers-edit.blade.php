<x-app-layout>

    <div class="max-w-xl p-6 mx-auto mt-10 bg-white shadow rounded-xl">

        <h2 class="mb-4 text-xl font-bold">Edit Supplier</h2>

        <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="text-sm text-gray-600">Name</label>
                <input type="text" name="name" value="{{ $supplier->name }}" class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div class="mb-3">
                <label class="text-sm text-gray-600">Email</label>
                <input type="email" name="email" value="{{ $supplier->email }}"
                    class="w-full px-3 py-2 border rounded-lg">
            </div>

            <button class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700">
                Update Supplier
            </button>

        </form>

    </div>

</x-app-layout>