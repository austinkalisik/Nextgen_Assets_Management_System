<aside class="w-64 min-h-screen text-white bg-slate-950">
    <div class="px-6 py-6 border-b border-slate-800">
        <h1 class="text-2xl font-bold leading-tight">
            {{ $appSettings['system_name'] ?? 'NextGen Assets' }}
        </h1>
        <p class="text-sm text-slate-400">
            {{ $appSettings['system_tagline'] ?? 'Management System' }}
        </p>
    </div>

    <nav class="px-4 py-6 space-y-2 text-sm">
        <a href="{{ route('dashboard') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800' : '' }}">Dashboard</a>
        <a href="{{ route('items.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('items.*') ? 'bg-slate-800' : '' }}">Assets</a>
        <a href="{{ route('assignments.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('assignments.*') ? 'bg-slate-800' : '' }}">Assignments</a>
        <a href="{{ route('inventory.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('inventory.*') ? 'bg-slate-800' : '' }}">Inventory</a>
        <a href="{{ route('suppliers.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('suppliers.*') ? 'bg-slate-800' : '' }}">Suppliers</a>
        <a href="{{ route('categories.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('categories.*') ? 'bg-slate-800' : '' }}">Categories</a>
        <a href="{{ route('departments.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('departments.*') ? 'bg-slate-800' : '' }}">Departments</a>
        <a href="{{ route('users.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('users.*') ? 'bg-slate-800' : '' }}">Users</a>
        <a href="{{ route('settings.index') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('settings.*') ? 'bg-slate-800' : '' }}">Settings</a>
        <a href="{{ route('profile.edit') }}"
            class="block px-4 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('profile.*') ? 'bg-slate-800' : '' }}">Profile</a>
    </nav>

    <div class="px-4 mt-8">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full px-4 py-2 text-sm font-semibold bg-red-500 rounded-lg hover:bg-red-600">
                Logout
            </button>
        </form>
    </div>
</aside>