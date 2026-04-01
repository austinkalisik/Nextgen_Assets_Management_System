<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Models\AssetLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Item::with(['category', 'supplier']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('part_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(10)
                  ->withQueryString()
                  ;
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
       
    //Create function
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

        $assignedTo = $request->input('assigned_to');
        $status = $request->input('status');

        if (!empty($assignedTo)) {
            $status = 'assigned';
        }

        $item->update([
            'assigned_to' => $assignedTo,
            'status' => $status,
        ]);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id(),
            'action' => 'updated',
            'old_values' => json_encode($oldData),
            'new_values' => json_encode($item->fresh()->toArray()),
        ]);

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

    //  CSV EXPORT (FIXED)
    public function export()
    {
        $items = Item::with(['user','category','supplier'])->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'No assets to export');
        }

        $filename = "assets_report_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate",
            "Expires" => "0",
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Code',
                'Brand',
                'Name',
                'Category',
                'Supplier',
                'Assigned To',
                'Status',
                'Created At'
            ]);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->part_no,
                    $item->brand,
                    $item->part_name,
                    optional($item->category)->name,
                    optional($item->supplier)->name,
                    $item->assigned_to ?? '-',
                    strtoupper($item->status),
                    $item->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}