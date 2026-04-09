<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::withCount('items')->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request, SystemNotificationService $notificationService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        $supplier = Supplier::create($validated);

        $notificationService->notifyAdmins(
            'success',
            'Supplier created',
            $supplier->name . ' was added as a supplier.',
            route('suppliers.show', $supplier),
            Supplier::class,
            $supplier->id
        );

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load('items.category', 'items.department');

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier, SystemNotificationService $notificationService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        $supplier->update($validated);

        $notificationService->notifyAdmins(
            'info',
            'Supplier updated',
            $supplier->name . ' supplier details were updated.',
            route('suppliers.show', $supplier),
            Supplier::class,
            $supplier->id
        );

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier, SystemNotificationService $notificationService): RedirectResponse
    {
        if ($supplier->items()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'Cannot delete supplier linked to assets.');
        }

        $name = $supplier->name;

        $supplier->delete();

        $notificationService->notifyAdmins(
            'warning',
            'Supplier deleted',
            $name . ' was removed from suppliers.',
            route('suppliers.index'),
            Supplier::class,
            null
        );

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}