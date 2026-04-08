<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = Item::query()
            ->with(['category', 'supplier', 'department', 'activeAssignment.user'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn ($sub) => $sub->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $items = $query->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('items.index', compact('items', 'categories', 'departments'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('items.create', compact('categories', 'suppliers', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.name' => ['required', 'string', 'max:255'],
            'rows.*.category_id' => ['required', 'exists:categories,id'],
            'rows.*.supplier_id' => ['required', 'exists:suppliers,id'],
            'rows.*.department_id' => ['required', 'exists:departments,id'],
            'rows.*.asset_tag' => ['nullable', 'string', 'max:255'],
            'rows.*.serial_number' => ['nullable', 'string', 'max:255'],
            'rows.*.status' => ['required', 'in:available,assigned,maintenance,retired'],
            'rows.*.location' => ['nullable', 'string', 'max:255'],
            'rows.*.purchase_date' => ['nullable', 'date'],
        ]);

        $createdCount = 0;

        foreach ($request->rows as $row) {
            $item = Item::create([
                'name' => $row['name'],
                'category_id' => $row['category_id'],
                'supplier_id' => $row['supplier_id'],
                'department_id' => $row['department_id'],
                'asset_tag' => !empty($row['asset_tag']) ? $row['asset_tag'] : null,
                'serial_number' => !empty($row['serial_number']) ? $row['serial_number'] : null,
                'quantity' => 1,
                'status' => $row['status'],
                'location' => $row['location'] ?? null,
                'purchase_date' => $row['purchase_date'] ?? null,
            ]);

            if (!$item->asset_tag) {
                $item->update([
                    'asset_tag' => 'NGA-' . str_pad((string) $item->id, 5, '0', STR_PAD_LEFT),
                ]);
            }

            AssetLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id() ?? 1,
                'action' => 'created',
            ]);

            $createdCount++;
        }

        return redirect()
            ->route('items.index')
            ->with('success', "{$createdCount} asset(s) created successfully.");
    }

    public function show(Item $item): View
    {
        $item->load([
            'category',
            'supplier',
            'department',
            'assignments.user',
            'assignments.department',
            'assetLogs.user',
            'activeAssignment.user',
        ]);

        return view('items.show', compact('item'));
    }

    public function edit(Item $item): View
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('items.edit', compact('item', 'categories', 'suppliers', 'departments'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'asset_tag' => ['nullable', 'string', 'max:255', 'unique:items,asset_tag,' . $item->id],
            'serial_number' => ['nullable', 'string', 'max:255', 'unique:items,serial_number,' . $item->id],
            'quantity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:available,assigned,maintenance,retired'],
            'location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
        ]);

        $item->update($validated);

        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'updated',
        ]);

        return redirect()
            ->route('items.index')
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        AssetLog::create([
            'item_id' => $item->id,
            'user_id' => Auth::id() ?? 1,
            'action' => 'deleted',
        ]);

        $item->delete();

        return redirect()
            ->route('items.index')
            ->with('success', 'Asset deleted successfully.');
    }
}