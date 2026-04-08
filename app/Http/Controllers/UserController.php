<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403, 'Only administrators can manage users.');
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $query = User::query()
            ->withCount(['assignments', 'activeAssignments', 'assetLogs'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->ensureAdmin();

        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,manager,asset_officer,staff'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->ensureAdmin();

        $user->load([
            'assignments.item',
            'assignments.department',
            'activeAssignments.item',
            'activeAssignments.department',
            'assetLogs.item',
        ]);

        $recentLogs = $user->assetLogs()
            ->with('item')
            ->latest()
            ->take(15)
            ->get();

        return view('users.show', compact('user', 'recentLogs'));
    }

    public function edit(User $user): View
    {
        $this->ensureAdmin();

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,manager,asset_officer,staff'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        if ((int) Auth::id() === (int) $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->activeAssignments()->exists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Cannot delete user with active assignments.');
        }

        if ($user->assignments()->exists()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Cannot delete user with assignment history.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}