<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Item::with(['category', 'supplier', 'department'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('asset_tag', 'like', "%{$search}%");
        }

        $items = $query->paginate(10)->withQueryString();

        return view('inventory.index', compact('items'));
    }

    public function stockIn(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->increment('quantity', $validated['quantity']);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => \Auth::id() ?? 1
            ,
            'action' => 'stock_in',
        ]);

        return back()->with('success', 'Stock added successfully.');
    }

    public function stockOut(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($item->quantity < $validated['quantity']) {
            return back()->with('error', 'Stock out cannot exceed current quantity.');
        }

        $item->decrement('quantity', $validated['quantity']);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => \Auth::id() ?? 1,
            'action' => 'stock_out',
        ]);

        return back()->with('success', 'Stock removed successfully.');
    }
}