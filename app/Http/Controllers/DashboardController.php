<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->isAssetOfficer() || $user->isManager()) {
            return $this->operationsDashboard($user);
        }

        return $this->userWorkspace($user);
    }

    protected function operationsDashboard(User $user): View
    {
        return view('dashboard.index', [
            'dashboardMode' => 'operations',
            'user' => $user,

            'totalAssets' => Item::count(),
            'availableAssets' => Item::where('status', 'available')->count(),
            'assignedAssets' => Item::where('status', 'assigned')->count(),
            'maintenanceAssets' => Item::where('status', 'maintenance')->count(),
            'retiredAssets' => Item::where('status', 'retired')->count(),
            'lowStockAssets' => Item::where('quantity', '<=', 3)->count(),

            'recentAssignments' => Assignment::with(['item', 'user', 'department'])
                ->latest('assigned_at')
                ->take(8)
                ->get(),

            'recentActivity' => AssetLog::with(['item', 'user'])
                ->latest()
                ->take(12)
                ->get(),

            'categorySummary' => Category::withCount('items')
                ->orderBy('name')
                ->get(),

            'departmentSummary' => Department::withCount('items')
                ->orderBy('name')
                ->get(),

            'usersByRole' => User::selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role'),

            'activeAssignments' => Assignment::whereNull('returned_at')->count(),

            'overdueAssignments' => Assignment::whereNull('returned_at')
                ->whereDate('assigned_at', '<=', now()->subDays(14))
                ->count(),

            'unreadNotifications' => SystemNotification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }

    protected function userWorkspace(User $user): View
    {
        $myActiveAssignments = Assignment::with(['item', 'department'])
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->latest('assigned_at')
            ->get();

        return view('dashboard.index', [
            'dashboardMode' => 'user',
            'user' => $user,

            'myActiveAssignments' => $myActiveAssignments,

            'myAssignmentHistory' => Assignment::with(['item', 'department'])
                ->where('user_id', $user->id)
                ->latest('assigned_at')
                ->take(10)
                ->get(),

            'myRecentActivity' => AssetLog::with('item')
                ->where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get(),

            'myNotifications' => SystemNotification::where('user_id', $user->id)
                ->latest()
                ->take(6)
                ->get(),

            'myAssignedAssetsCount' => $myActiveAssignments->count(),

            'myOverdueAssignmentsCount' => Assignment::where('user_id', $user->id)
                ->whereNull('returned_at')
                ->whereDate('assigned_at', '<=', now()->subDays(14))
                ->count(),

            'myDepartmentCount' => Assignment::where('user_id', $user->id)
                ->whereNotNull('department_id')
                ->distinct('department_id')
                ->count('department_id'),

            'myAssetsInMaintenance' => Item::whereHas('activeAssignment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'maintenance')->count(),
        ]);
    }
}