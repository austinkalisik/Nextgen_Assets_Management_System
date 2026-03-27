<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\Category;

class DashboardController extends Controller
{
    public function index()
    {
        // =============================
        // TOTAL COUNTS
        // =============================
        $totalProducts = Item::count();
        $totalSuppliers = Supplier::count();
        $totalCategories = Category::count();

        // =============================
        // TOTAL BRANDS
        // =============================
        $totalBrands = Item::distinct('brand')->count('brand');

        // =============================
        // FIX: LATEST PRODUCTS (IMPORTANT)
        // =============================
        $latestProducts = Item::latest()->take(5)->get();

        // =============================
        // MONTHLY DATA
        // =============================
        $monthlyProducts = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyProducts[] = Item::whereMonth('created_at', $i)->count();
        }

        // =============================
        // SEND DATA TO VIEW
        // =============================
        return view('dashboard', compact(
            'totalProducts',
            'totalSuppliers',
            'totalCategories',
            'totalBrands',
            'latestProducts', //  CRITICAL
            'monthlyProducts'
        ));
    }
}