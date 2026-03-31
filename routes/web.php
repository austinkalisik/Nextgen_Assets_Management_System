<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ItemController;

Route::get('/', fn () => redirect()->route('dashboard'));

require __DIR__ . '/auth.php';
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ASSETS
    Route::get('/assets', [ItemController::class, 'index'])->name('assets');
    Route::get('/products', [ItemController::class, 'index'])->name('products');

    Route::post('/assets', [ItemController::class, 'store'])->name('assets.store');
    Route::put('/assets/{id}', [ItemController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{id}', [ItemController::class, 'destroy'])->name('assets.destroy');

    //  EXPORT CSV (ADMIN ONLY)
    Route::get('/assets/export', [ItemController::class, 'export'])
        
        ->name('assets.export');

    // CATEGORIES
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // SUPPLIERS
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    //Auth
    Route::get('/suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');

    // USERS
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // SETTINGS
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

});