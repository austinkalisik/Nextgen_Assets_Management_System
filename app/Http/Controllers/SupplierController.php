<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * =============================
     * DISPLAY SUPPLIERS + SEARCH + FILTER
     * =============================
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        // GLOBAL SEARCH (SAFE GROUPING)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // FILTER: NAME
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // FILTER: EMAIL
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // LOAD ITEMS COUNT (for future dashboard stats)
        $suppliers = $query->withCount('items')
                           ->latest()
                           ->paginate(10)
                           ->withQueryString();

        return view('suppliers', compact('suppliers'));
    }

    /**
     * =============================
     * STORE NEW SUPPLIER
     * =============================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers')
            ->with('success', 'Supplier added successfully');
    }

     /**
     * =============================
     * UPDATE SUPPLIER
     * =============================
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers')
            ->with('success', 'Supplier updated successfully');
    }

     /**
     * =============================
     * EDIT SUPPLIER
     * =============================
     */
    public function edit($id)
{
    $supplier = Supplier::findOrFail($id);

    return view('suppliers-edit', compact('supplier'));
}



    /**
     * =============================
     * DELETE SUPPLIER
     * =============================
     */
    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();

        return back()->with('success', 'Supplier deleted successfully');
    }
}