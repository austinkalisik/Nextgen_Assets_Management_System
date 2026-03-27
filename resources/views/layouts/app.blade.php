
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        try {
            $appName = \Illuminate\Support\Facades\DB::table('settings')->value('app_name') ?? 'NextGen Assets';
        } catch (\Exception $e) {
            $appName = 'NextGen Assets';
        }
    @endphp

    <title>{{ $appName }}</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="text-gray-800 bg-slate-100">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 p-6 text-gray-300 bg-slate-950">

            <h2 class="mb-10 text-xl font-bold text-white">
                {{ $appName }}
            </h2>

            <nav class="space-y-2 text-sm">

                <a href="/dashboard" class="block px-4 py-2 rounded hover:bg-slate-800">Dashboard</a>
                <a href="/products" class="block px-4 py-2 rounded hover:bg-slate-800">Products</a>
                <a href="/suppliers" class="block px-4 py-2 rounded hover:bg-slate-800">Suppliers</a>
                <a href="/categories" class="block px-4 py-2 rounded hover:bg-slate-800">Categories</a>
                <a href="/users" class="block px-4 py-2 rounded hover:bg-slate-800">Users</a>
                <a href="/settings" class="block px-4 py-2 rounded hover:bg-slate-800">Settings</a>

            </nav>

            <div class="mt-10 text-xs text-gray-500">
                © {{ date('Y') }} {{ $appName }}
            </div>

        </aside>

        <!-- MAIN -->
        <div class="flex-1">

            <!-- HEADER -->
            <div class="flex justify-between p-4 bg-white border-b">
                <div>
                    <h1 class="font-semibold">Dashboard</h1>
                    <p class="text-sm text-gray-500">
                        Welcome back, {{ Auth::user()->name ?? 'User' }}
                    </p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="px-3 py-1 text-white bg-red-500 rounded">
                        Logout
                    </button>
                </form>
            </div>

            <!-- CONTENT -->
            <main class="p-6">
                {{ $slot }}
            </main>

        </div>

    </div>

</body>

</html>