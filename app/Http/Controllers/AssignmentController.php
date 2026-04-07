<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Assignment;
use App\Models\Department;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Assignment::with(['item', 'user', 'department'])->latest('assigned_at');

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('returned_at');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('returned_at');
            }
        }

        $assignments = $query->paginate(10)->withQueryString();

        return view('assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        $activeItemIds = Assignment::whereNull('returned_at')->pluck('item_id');

        $items = Item::with(['category', 'department'])
            ->whereNotIn('id', $activeItemIds)
            ->where('status', '!=', 'retired')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('assignments.create', compact('items', 'users', 'departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'user_id' => ['required', 'exists:users,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'assigned_at' => ['required', 'date'],
        ]);

        $alreadyAssigned = Assignment::where('item_id', $validated['item_id'])
            ->whereNull('returned_at')
            ->exists();

        if ($alreadyAssigned) {
            return back()
                ->with('error', 'This asset is already assigned.')
                ->withInput();
        }

        $assignment = Assignment::create($validated);

        $assignment->item->update([
            'status' => 'assigned',
            'department_id' => $validated['department_id'],
        ]);

        AssetLog::create([
            'item_id' => $validated['item_id'],
            'user_id' => \Auth::id() ?? 1,
            'action' => 'assigned',
        ]);

        return redirect()
            ->route('assignments.index')
            ->with('success', 'Asset assigned successfully.');
    }

    public function return(Request $request, Assignment $assignment): RedirectResponse
    {
        if ($assignment->returned_at) {
            return back()->with('error', 'This assignment has already been returned.');
        }

        $assignment->update([
            'returned_at' => now(),
        ]);

        $hasOtherActive = Assignment::where('item_id', $assignment->item_id)
            ->whereNull('returned_at')
            ->where('id', '!=', $assignment->id)
            ->exists();

        if (! $hasOtherActive) {
            $assignment->item->update(['status' => 'available']);
        }

        AssetLog::create([
            'item_id' => $assignment->item_id,
            'user_id' => \Auth::id() ?? 1,
            'action' => 'returned',
        ]);

        return redirect()
            ->route('assignments.index')
            ->with('success', 'Asset returned successfully.');
    }
}