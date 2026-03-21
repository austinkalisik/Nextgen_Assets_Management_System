<?php

namespace App\Http\Controllers;

use App\Models\Item;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $totalBrands = Item::distinct('brand')->count('brand');
        $latestItems = Item::latest()->take(5)->get();

        // ✅ FIXED: correct range
        $months = collect(range(0, 5))->map(function ($i) {
            return now()->subMonths($i)->format('M');
        })->reverse()->values();

        $itemsPerMonth = collect(range(0, 5))->map(function ($i) {
            return Item::whereMonth('created_at', now()->subMonths($i)->month)->count();
        })->reverse()->values();

        return view('dashboard', [
            'totalItems' => $totalItems,
            'totalBrands' => $totalBrands,
            'latestItems' => $latestItems,
            'months' => $months,
            'itemsPerMonth' => $itemsPerMonth,
        ]);
    }
}