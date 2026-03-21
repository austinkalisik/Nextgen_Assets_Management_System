<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class InventoryController extends Controller
{
    public function index()
    {
        $items = Item::latest()->paginate(10);
        return view('items', compact('items'));
    }

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
            ->with('success', 'Item added successfully');
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'part_no' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            '_name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $item->update($validated);

        return redirect()->route('items')
            ->with('success', 'Item updated successfully');
    }

    public function destroy($id)
    {
        Item::findOrFail($id)->delete();

        return redirect()->route('items')
            ->with('success', 'Item deleted successfully');
    }
}
