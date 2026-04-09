@php
    $currentUser = auth()->user();

    $navItems = [
        [
            'label' => 'Main',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => route('dashboard'),
                    'active' => request()->routeIs('dashboard'),
                ],
                [
                    'label' => 'Assets',
                    'route' => route('items.index'),
                    'active' => request()->routeIs('items.*'),
                ],
                [
                    'label' => 'Assignments',
                    'route' => route('assignments.index'),
                    'active' => request()->routeIs('assignments.*'),
                ],
                [
                    'label' => 'Inventory',
                    'route' => route('inventory.index'),
                    'active' => request()->routeIs('inventory.*'),
                ],
            ],
        ],
        [
            'label' => 'Operations',
            'items' => [
                [
                    'label' => 'Suppliers',
                    'route' => route('suppliers.index'),
                    'active' => request()->routeIs('suppliers.*'),
                ],
                [
                    'label' => 'Categories',
                    'route' => route('categories.index'),
                    'active' => request()->routeIs('categories.*'),
                ],
                [
                    'label' => 'Departments',
                    'route' => route('departments.index'),
                    'active' => request()->routeIs('departments.*'),
                ],
            ],
        ],
        [
            'label' => 'Personal',
            'items' => [
                [
                    'label' => 'Profile',
                    'route' => route('profile.edit'),
                    'active' => request()->routeIs('profile.*'),
                ],
            ],
        ],
    ];

    if ($currentUser && $currentUser->isAdmin()) {
        $navItems[] = [
            'label' => 'Administration',
            'items' => [
                [
                    'label' => 'User Administration',
                    'route' => route('users.index'),
                    'active' => request()->routeIs('users.*'),
                ],
                [
                    'label' => 'Settings',
                    'route' => route('settings.index'),
                    'active' => request()->routeIs('settings.*'),
                ],
            ],
        ];
    }
@endphp

<aside class="flex min-h-screen w-72 flex-col bg-slate-950 text-white shadow-2xl">
    <div class="border-b border-slate-800 px-6 py-5">
        <h1 class="text-2xl font-bold tracking-tight">
            {{ $appSettings['system_name'] ?? 'NextGen Assets' }}
        </h1>
        <p class="text-sm text-slate-400">
            {{ $appSettings['system_tagline'] ?? 'Management System' }}
        </p>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-5">
        @foreach($navItems as $group)
            <div class="mb-6">
                <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                    {{ $group['label'] }}
                </div>

                <div class="space-y-1.5">
                    @foreach($group['items'] as $item)
                            <a href="{{ $item['route'] }}" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition
                                       {{ $item['active']
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                {{ $item['label'] }}
                            </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-t border-slate-800 px-4 py-4">
        <div class="mb-3 rounded-xl border border-slate-800 bg-slate-900 p-3">
            <div class="text-sm font-semibold text-white">{{ $currentUser->name ?? 'User' }}</div>
            <div class="text-xs text-slate-400">{{ ucfirst(str_replace('_', ' ', $currentUser->role ?? 'staff')) }}
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-600">
                Logout
            </button>
        </form>
    </div>
</aside>