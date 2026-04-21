<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/profile-photo/{user}', [ProfileController::class, 'showPhoto'])
    ->name('profile.photo.show');

Route::get('/', function () {
    return view('spa');
});

Route::get('/login', function () {
    return view('spa');
})->name('login');

Route::get('/{any}', function () {
    return view('spa');
})->where('any', '^(?!api|storage|build).*$');
