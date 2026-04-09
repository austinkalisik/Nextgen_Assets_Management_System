<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Item;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Item::with(['category', 'supplier', 'department'])->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(10)->withQueryString();

        return view('inventory.index', compact('items'));
    }

    public function stockIn(Request $request, Item $item, SystemNotificationService $notificationService): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item->increment('quantity', $validated['quantity']);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'stock_in',
        ]);

        $notificationService->notifyAdmins(
            'success',
            'Stock increased',
            $item->name . ' stock increased by ' . $validated['quantity'] . '.',
            route('inventory.index'),
            Item::class,
            $item->id
        );

        return back()->with('success', 'Stock added successfully.');
    }

    public function stockOut(Request $request, Item $item, SystemNotificationService $notificationService): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($item->quantity < $validated['quantity']) {
            return back()->with('error', 'Stock out cannot exceed current quantity.');
        }

        $item->decrement('quantity', $validated['quantity']);
        $item->refresh();

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'stock_out',
        ]);

        $notificationService->notifyAdmins(
            'warning',
            'Stock reduced',
            $item->name . ' stock reduced by ' . $validated['quantity'] . '. Current qty: ' . $item->quantity . '.',
            route('inventory.index'),
            Item::class,
            $item->id
        );

        if ($item->quantity <= 3) {
            $notificationService->notifyAdmins(
                'critical',
                'Low stock alert',
                $item->name . ' is low in stock. Current qty: ' . $item->quantity . '.',
                route('items.show', $item),
                Item::class,
                $item->id
            );
        }

        return back()->with('success', 'Stock removed successfully.');
    }
}