<nav class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">

    <!-- LEFT -->
    <div class="flex items-center gap-6">
        <div class="text-lg font-semibold tracking-tight text-gray-900">
            Inventory
        </div>

        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 transition hover:text-blue-600">
            Dashboard
        </a>

        <a href="#" class="text-sm text-gray-600 transition hover:text-blue-600">
            Items
        </a>
    </div>

    <!-- RIGHT -->
    <div class="flex items-center gap-4 text-sm">

        <span class="text-gray-600">
            {{ Auth::user()->name }}
        </span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="font-medium text-red-500 hover:text-red-600">
                Logout
            </button>
        </form>

    </div>

</nav>
