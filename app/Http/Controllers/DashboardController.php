<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Assignment;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // =============================
        // SEARCH (SAFE + CLEAN)
        // =============================
        $search = $request->input('search');

        $query = Item::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('part_no', 'like', "%{$search}%");
            });
        }

        // Latest assets (filtered or not)
        $latestAssets = $query->latest()->take(5)->get();

        // =============================
        // CORE COUNTS
        // =============================
        $totalAssets = Item::count();
        $totalSuppliers = Supplier::count();
        $totalCategories = Category::count();

        // =============================
        // STATUS COUNTS (ACTIVE ASSIGNMENTS)
        //(ERD BASED)
        // =============================
       $assignedAssets = Assignment::whereNUll('returned_at')->count();

       //AVAILABLE = TOTAL - ASSIGNED
       $availableAssets = $totalAssets - $assignedAssets;

       // OPTIONAL (KEEP MAINTENANCE IF YOU STILL USE IT)
       
       $maintenanceAssets = Item::where('status', 'maintenance');
        // =============================
        // LOW STOCK ALERT ( CORPORATE)
        // =============================
        $lowStockAssets = 0;

        try {
            $lowStockAssets = Item::where('quantity', '<', 5)->count();
        } catch (\Exception $e) {
            $lowStockAssets = 0;
        }

        // =============================
        // MONTHLY ANALYTICS ( BIG FEATURE)
        // =============================
        $monthlyAssets = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyAssets[] = Item::whereMonth('created_at', $i)->count();
        }

        // =============================
        // SEND DATA
        // =============================
        return view('dashboard', compact(
            'totalAssets',
            'totalSuppliers',
            'totalCategories',
            'availableAssets',
            'assignedAssets',
            'maintenanceAssets',
            'latestAssets',
            'lowStockAssets',
            'monthlyAssets',
            'search'
        ));
    }
}