<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/profile-photo/{user}', [ProfileController::class, 'showPhoto'])
    ->name('profile.photo.show');

Route::get('/', function () {
    return view('spa');
});

Route::get('/dashboard', function () {
    return view('spa');
})->name('dashboard');

Route::get('/reports/system-demo-report/download', function () {
    $path = public_path('reports/nextgen-asset-management-system-demo-report.pdf');

    abort_unless(file_exists($path), 404);

    return response()->download($path, 'nextgen-asset-management-system-demo-report.pdf');
})->name('reports.system-demo-report.download');

Route::get('/reports/system-demo-report/view', function () {
    $path = public_path('reports/nextgen-asset-management-system-demo-report.pdf');

    abort_unless(file_exists($path), 404);

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('reports.system-demo-report.view');

Route::any('/register', function () {
    abort(404);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('spa');
    })->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/{any}', function () {
    return view('spa');
})->where('any', '^(?!api|storage|build).*$');
