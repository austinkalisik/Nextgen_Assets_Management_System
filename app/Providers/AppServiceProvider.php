<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $appSettings = [
                'system_name' => 'Nextgen Assets Management System',
                'system_tagline' => 'Owned by Nextgen Technology',
            ];

            try {
                if (Schema::hasTable('settings')) {
                    $settings = DB::table('settings')
                        ->whereIn('key', ['system_name', 'system_tagline'])
                        ->pluck('value', 'key')
                        ->toArray();

                    $appSettings['system_name'] = $settings['system_name'] ?? $appSettings['system_name'];
                    $appSettings['system_tagline'] = $settings['system_tagline'] ?? $appSettings['system_tagline'];
                }
            } catch (\Throwable $e) {
                //
            }

            $view->with('appSettings', $appSettings);
        });
    }
}
