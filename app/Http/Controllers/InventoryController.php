<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class InventoryController extends Controller
{
    /**
     * =============================
     * DISPLAY ALL PRODUCTS
     * =============================
     */
    public function index(Request $request)
    {
        $query = Item::query();

        // SEARCH (name, brand, code)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('part_name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%')
                  ->orWhere('part_no', 'like', '%' . $request->search . '%');
            });
        }

        // FILTER: BRAND
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // FILTER: PRODUCT CODE
        if ($request->filled('part_no')) {
            $query->where('part_no', 'like', '%' . $request->part_no . '%');
        }

        // PAGINATION
        $items = $query->latest()->paginate(10)->withQueryString();

        return view('items', compact('items'));
    }

    /**
     * =============================
     * STORE NEW PRODUCT
     * =============================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_no' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'part_name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        Item::create($validated);

        return redirect()->route('items')
            ->with('success', 'Product added successfully');
    }

    /**
     * =============================
     * UPDATE PRODUCT
     * =============================
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'part_no' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'part_name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('products')
            ->with('success', 'Product updated successfully');
    }

    /**
     * =============================
     * DELETE PRODUCT
     * =============================
     */
    public function destroy($id)
    {
        Item::findOrFail($id)->delete();

        return redirect()->route('items')
            ->with('success', 'Product deleted successfully');
    }
}