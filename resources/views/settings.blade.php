<x-app-layout>

    <div class="space-y-8">

        <!-- HEADER -->
        <div>
            <h1 class="text-3xl font-bold text-slate-800">
                System Settings
            </h1>
            <p class="text-sm text-gray-500">
                Configure your application preferences
            </p>
        </div>

        <!-- SETTINGS CARD -->
        <div class="max-w-3xl p-6 bg-white shadow rounded-2xl">

            <!-- SUCCESS MESSAGE -->
            @if(session('success'))
                <div class="p-3 mb-4 text-green-700 bg-green-100 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('settings.store') }}">
                @csrf

                <div class="space-y-5">

                    <!-- APP NAME -->
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-600">
                            Application Name
                        </label>
                        <input type="text" name="app_name" value="{{ old('app_name', $settings->app_name ?? 'NextGen Assets') }}"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                        
                        <input type="email" name="admin_email" value="{{ old('admin_email', $settings->admin_email ?? Auth::user()->email) }}"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                    </div>

                    <!-- ADMIN EMAIL -->
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-600">
                            Admin Email
                        </label>
                        <input type="email" name="admin_email"
                            value="{{ $settings->admin_email ?? Auth::user()->email }}"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                    </div>

                    <!-- STATUS -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">System Status</span>
                        <span class="text-sm font-semibold text-green-500">Active</span>
                    </div>

                    <!-- BUTTON -->
                    <div class="pt-4">
                        <button
                            class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Save Settings
                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

</x-app-layout>