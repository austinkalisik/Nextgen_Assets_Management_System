<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = DB::table('settings')->first();
        return view('settings', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'app_name' => 'required',
            'admin_email' => 'required|email',
        ]);

        DB::table('settings')->updateOrInsert(
            ['id' => 1],
            [
                'app_name' => $request->app_name,
                'admin_email' => $request->admin_email,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Settings updated successfully!');
    }
}