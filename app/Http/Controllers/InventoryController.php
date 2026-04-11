<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Assignment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'supplier', 'department'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhereHas('category', fn($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        $items = $query->paginate(10)->withQueryString();

        return view('inventory.index', compact('items'));
    }

    public function stockOut(Request $request, Item $item)
{
    $validated = $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    //  Prevent negative stock
    if ($validated['quantity'] > $item->quantity) {
        return back()->with('error', 'Not enough stock available.');
    }

    // 🔻 Decrease stock
    $item->decrement('quantity', $validated['quantity']);
    $item->refresh();

    //  Auto status update
    $item->syncAutomatedStatus();

    //  Log
    AssetLog::create([
        'item_id' => $item->id,
        'user_id' => Auth::id() ?? 1,
        'action' => 'stock_out',
        'notes' => "Stock out: -{$validated['quantity']}",
    ]);

    return back()->with('success', 'Stock out recorded.');
}

public function stockIn(Request $request, Item $item)
{
    $validated = $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    // 🔺 Increase stock
    $item->increment('quantity', $validated['quantity']);
    $item->refresh();

    //  Auto status update
    $item->syncAutomatedStatus();

    // Log
    AssetLog::create([
        'item_id' => $item->id,
        'user_id' => Auth::id() ?? 1,
        'action' => 'stock_in',
        'notes' => "Stock in: +{$validated['quantity']}",
    ]);

    return back()->with('success', 'Stock updated.');
}

}