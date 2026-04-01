<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Models\AssetLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;

class ItemController extends Controller
{   
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Item::with(['category', 'supplier', 'activeAssignment.user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('part_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        $categories = Category::all();
        $suppliers = Supplier::all();
        $users = User::all();

        $logs = AssetLog::with(['user','item'])->latest()->take(20)->get();

        return view('items', compact(
            'items',
            'categories',
            'suppliers',
            'users',
            'search',
            'logs'
        ));
    }
       
    public function create()
    {
        return view('items-create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_no' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'part_name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $item = Item::create([
            'part_no' => $request->part_no,
            'brand' => $request->brand,
            'part_name' => $request->part_name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'status' => 'available',
        ]);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'action' => 'created',
            'new_values' => json_encode($item->toArray()),
        ]);

        return redirect()->back()->with('success', 'Asset created successfully');
    }

  public function update(Request $request, $id)
{
    $item = Item::findOrFail($id);

    $oldData = $item->toArray();

    $userId = $request->assigned_to;

    // =============================
    // ASSIGN
    // =============================
    if ($request->filled('assigned_to')) {

        // Close previous assignment
        if ($item->activeAssignment) {
            $item->activeAssignment->update([
                'returned_at' => now()
            ]);
        }

        // Create new assignment
        Assignment::create([
            'item_id' => $item->id,
            'user_id' => $userId,
            'assigned_at' => now(),
        ]);

        // Compatibility (DO NOT REMOVE)
        $item->update([
            'assigned_to' => $userId,
            'status' => 'assigned'
        ]);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'action' => 'assigned',
            'new_values' => json_encode([
                'assigned_to' => $userId
            ]),
        ]);

    } else {

        // =============================
        // RETURN
        // =============================
        if ($item->activeAssignment) {
            $item->activeAssignment->update([
                'returned_at' => now()
            ]);
        }

        $item->update([
            'assigned_to' => null,
            'status' => 'available'
        ]);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'action' => 'returned',
        ]);
    }

    return redirect()->back()->with('success', 'Asset updated successfully');
}
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'old_values' => json_encode($item->toArray()),
        ]);

        $item->delete();

        return redirect()->back()->with('success', 'Asset deleted successfully');
    }

    // CSV EXPORT
 public function export()
{
    $items = Item::with(['activeAssignment.user'])->get();

    $filename = "assets.csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate",
        "Expires" => "0"
    ];

    $callback = function () use ($items) {
        $file = fopen('php://output', 'w');

        // HEADER
        fputcsv($file, [
            'Code',
            'Brand',
            'Name',
            'Assigned To',
            'Status'
        ]);

        foreach ($items as $item) {

            $assignedUser = optional(optional($item->activeAssignment)->user)->name ?? '-';

            fputcsv($file, [
                $item->part_no,
                $item->brand,
                $item->part_name,
                $assignedUser,
                $item->status
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}