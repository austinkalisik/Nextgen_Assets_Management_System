<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = DB::table('settings')->orderBy('key')->get();

        return view('settings.index', compact('settings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => $validated['key']],
            [
                'value' => $validated['value'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->route('settings.index')->with('success', 'Setting saved successfully.');
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'value' => ['nullable', 'string'],
        ]);

        DB::table('settings')
            ->where('key', $key)
            ->update([
                'value' => $validated['value'],
                'updated_at' => now(),
            ]);

        return redirect()->route('settings.index')->with('success', 'Setting updated successfully.');
    }

    public function destroy(string $key): RedirectResponse
    {
        DB::table('settings')->where('key', $key)->delete();

        return redirect()->route('settings.index')->with('success', 'Setting deleted successfully.');
    }
}