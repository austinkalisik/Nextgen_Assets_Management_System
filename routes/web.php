<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController; // FIX ADDED
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    return redirect('/dashboard');
});

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {

    /**
     * =============================
     * DASHBOARD
     * =============================
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CUSTOMERS
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');


    /**
     * =============================
     * CATEGORIES
     * =============================
     */
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    /**
     * =============================
     * SUPPLIERS
     * =============================
     */
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    /**
     * =============================
     * PRODUCTS (ITEMS)
     * =============================
     */
    Route::get('/products', [InventoryController::class, 'index'])->name('products');
    Route::post('/products', [InventoryController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [InventoryController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [InventoryController::class, 'destroy'])->name('products.destroy');
    /**
     * =============================
     * USERS
     * =============================
     */
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    /**
     * =============================
     * SETTINGS
     * =============================
     */
    // SETTINGS
Route::get('/settings', [SettingController::class, 'index'])->name('settings');
Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');


    /**
     * =============================
     * REPORTS
     * =============================
     */
    Route::view('/reports', 'reports')->name('reports');

});