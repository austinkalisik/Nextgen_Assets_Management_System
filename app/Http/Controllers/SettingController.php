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

        $systemName = DB::table('settings')->where('key', 'system_name')->value('value') ?? 'NextGen Assets';
        $systemTagline = DB::table('settings')->where('key', 'system_tagline')->value('value') ?? 'Management System';

        return view('settings.index', compact('settings', 'systemName', 'systemTagline'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'system_name' => ['required', 'string', 'max:255'],
            'system_tagline' => ['required', 'string', 'max:255'],
        ]);

        DB::table('settings')->updateOrInsert(
            ['key' => 'system_name'],
            [
                'value' => $validated['system_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'system_tagline'],
            [
                'value' => $validated['system_tagline'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()
            ->route('settings.index')
            ->with('success', 'System branding updated successfully.');
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

        return redirect()
            ->route('settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    public function destroy(string $key): RedirectResponse
    {
        DB::table('settings')->where('key', $key)->delete();

        return redirect()
            ->route('settings.index')
            ->with('success', 'Setting deleted successfully.');
    }
}