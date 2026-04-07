<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalAssets = Item::count();
        $availableAssets = Item::where('status', 'available')->count();
        $maintenanceAssets = Item::where('status', 'maintenance')->count();
        $assignedAssets = Assignment::whereNull('returned_at')->count();
        $lowStockAssets = Item::where('quantity', '<=', 3)->count();

        $recentAssignments = Assignment::with(['item', 'user', 'department'])
            ->latest('assigned_at')
            ->take(8)
            ->get();

        $recentActivity = AssetLog::with(['item', 'user'])
            ->latest()
            ->take(8)
            ->get();

        $categorySummary = Category::withCount('items')->orderBy('name')->get();
        $departmentSummary = Department::withCount('items')->orderBy('name')->get();

        $notificationCount =
            AssetLog::whereDate('created_at', today())->count() +
            Item::where('quantity', '<=', 3)->count() +
            Assignment::whereNull('returned_at')->count();

        return view('dashboard.index', compact(
            'totalAssets',
            'availableAssets',
            'maintenanceAssets',
            'assignedAssets',
            'lowStockAssets',
            'recentAssignments',
            'recentActivity',
            'categorySummary',
            'departmentSummary',
            'notificationCount'
        ))->with([
            'totalSuppliers' => Supplier::count(),
            'totalCategories' => Category::count(),
            'totalDepartments' => Department::count(),
        ]);
    }
}