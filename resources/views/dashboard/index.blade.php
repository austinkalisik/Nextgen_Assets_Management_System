@extends('layouts.app')

@section('content')
    @if($dashboardMode === 'admin')
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-4xl font-bold text-slate-900">Admin Dashboard</h1>
                <p class="mt-1 text-slate-500">Operational control center for assets, assignments, users, and system activity.
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('items.create') }}"
                    class="rounded-xl bg-blue-600 px-4 py-2.5 text-white font-semibold hover:bg-blue-700">
                    + Add Asset
                </a>
                <a href="{{ route('assignments.create') }}"
                    class="rounded-xl bg-slate-900 px-4 py-2.5 text-white font-semibold hover:bg-slate-800">
                    + Assign Asset
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3 xl:grid-cols-6">
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Total Assets</p>
                <h2 class="mt-2 text-3xl font-bold">{{ $totalAssets }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Available</p>
                <h2 class="mt-2 text-3xl font-bold text-emerald-600">{{ $availableAssets }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Assigned</p>
                <h2 class="mt-2 text-3xl font-bold text-amber-500">{{ $assignedAssets }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Maintenance</p>
                <h2 class="mt-2 text-3xl font-bold text-rose-500">{{ $maintenanceAssets }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Low Stock</p>
                <h2 class="mt-2 text-3xl font-bold text-orange-500">{{ $lowStockAssets }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Overdue</p>
                <h2 class="mt-2 text-3xl font-bold text-red-600">{{ $overdueAssignments }}</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-3">
            <div class="bg-white shadow xl:col-span-2 rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Recent Assignments</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left">Asset</th>
                                <th class="px-6 py-3 text-left">User</th>
                                <th class="px-6 py-3 text-left">Department</th>
                                <th class="px-6 py-3 text-left">Assigned At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAssignments as $assignment)
                                <tr class="border-b">
                                    <td class="px-6 py-3">{{ $assignment->item?->name ?? '-' }}</td>
                                    <td class="px-6 py-3">{{ $assignment->user?->name ?? '-' }}</td>
                                    <td class="px-6 py-3">{{ $assignment->department?->name ?? '-' }}</td>
                                    <td class="px-6 py-3">{{ $assignment->assigned_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-slate-500">No recent assignments.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">System Activity</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    @forelse($recentActivity as $activity)
                        <div class="pb-3 border-b">
                            <p class="font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                            <p class="text-slate-600">{{ $activity->item?->name ?? 'Unknown asset' }}</p>
                            <p class="text-xs text-slate-400">{{ $activity->user?->name ?? 'System' }} •
                                {{ $activity->created_at?->format('d M Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500">No recent activity.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Category Summary</h2>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    @foreach($categorySummary as $category)
                        <div class="flex items-center justify-between pb-2 border-b">
                            <span>{{ $category->name }}</span>
                            <span class="font-semibold">{{ $category->items_count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">Department Summary</h2>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    @foreach($departmentSummary as $department)
                        <div class="flex items-center justify-between pb-2 border-b">
                            <span>{{ $department->name }}</span>
                            <span class="font-semibold">{{ $department->items_count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">User Roles</h2>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Admins</span>
                        <span class="font-semibold">{{ $usersByRole['admin'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Managers</span>
                        <span class="font-semibold">{{ $usersByRole['manager'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Asset Officers</span>
                        <span class="font-semibold">{{ $usersByRole['asset_officer'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b">
                        <span>Staff</span>
                        <span class="font-semibold">{{ $usersByRole['staff'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span>Active Assignments</span>
                        <span class="font-semibold">{{ $activeAssignments }}</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-slate-900">My Workspace</h1>
            <p class="mt-1 text-slate-500">Personal asset overview, assignment tracking, and recent activity.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">My Active Assets</p>
                <h2 class="mt-2 text-3xl font-bold">{{ $myAssignedAssetsCount }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Overdue Assets</p>
                <h2 class="mt-2 text-3xl font-bold text-red-600">{{ $myOverdueAssignmentsCount }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Departments Used</p>
                <h2 class="mt-2 text-3xl font-bold text-blue-600">{{ $myDepartmentCount }}</h2>
            </div>
            <div class="p-5 bg-white shadow rounded-2xl">
                <p class="text-sm text-slate-500">Assets In Maintenance</p>
                <h2 class="mt-2 text-3xl font-bold text-amber-500">{{ $myAssetsInMaintenance }}</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 mb-6 xl:grid-cols-2">
            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">My Active Assignments</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    @forelse($myActiveAssignments as $assignment)
                        <div class="p-4 border rounded-xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $assignment->item?->name ?? '-' }}</p>
                                    <p class="mt-1 text-slate-500">{{ $assignment->department?->name ?? '-' }}</p>
                                    <p class="mt-2 text-xs text-slate-400">
                                        Assigned {{ $assignment->assigned_at?->format('d M Y H:i') }}
                                    </p>
                                </div>
                                @if($assignment->item)
                                    <a href="{{ route('items.show', $assignment->item) }}"
                                        class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs text-white font-semibold">
                                        View Asset
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500">No active assignments.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow rounded-2xl">
                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold">My Recent Activity</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    @forelse($myRecentActivity as $activity)
                        <div class="pb-3 border-b">
                            <p class="font-semibold text-slate-900">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                            <p class="text-slate-600">{{ $activity->item?->name ?? 'Unknown asset' }}</p>
                            <p class="text-xs text-slate-400">{{ $activity->created_at?->format('d M Y H:i') }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500">No activity found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-2xl">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">My Assignment History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left">Asset</th>
                            <th class="px-6 py-3 text-left">Department</th>
                            <th class="px-6 py-3 text-left">Assigned At</th>
                            <th class="px-6 py-3 text-left">Returned At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myAssignmentHistory as $assignment)
                            <tr class="border-b">
                                <td class="px-6 py-3">{{ $assignment->item?->name ?? '-' }}</td>
                                <td class="px-6 py-3">{{ $assignment->department?->name ?? '-' }}</td>
                                <td class="px-6 py-3">{{ $assignment->assigned_at?->format('d M Y H:i') }}</td>
                                <td class="px-6 py-3">{{ $assignment->returned_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-slate-500">No assignment history.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection