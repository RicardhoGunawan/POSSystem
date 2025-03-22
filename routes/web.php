<?php

use App\Http\Controllers\POSController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Auth\POSAuthController;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Auth Routes
Route::get('/pos/login', [POSAuthController::class, 'showLoginForm'])->name('login');
Route::post('/pos/login', action: [POSAuthController::class, 'login'])->name('pos.login.post');

// POS Routes (authenticated)
Route::middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('/pos/products', [POSController::class, 'getProducts'])->name('pos.products.get');
    Route::post('/pos/orders', [OrderController::class, 'store'])->name('pos.orders.store');
    Route::get('/pos/receipt/{order}', [POSController::class, 'showReceipt'])->name('pos.receipt.show');
    Route::post('/pos/logout', [POSAuthController::class, 'logout'])->name('pos.logout');
});

// Order Routes
Route::get('/orders/history', [OrderController::class, 'getOrderHistory'])->name('orders.history');
Route::get('/orders/{id}', [OrderController::class, 'getOrder'])->name('orders.show');
Route::post('/pos/orders/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('pos.orders.cancel');

// tax routes
Route::get('/get-tax', function () {
    $tax = StoreSetting::first()?->tax_percentage ?? 0;

    return response()->json([
        'tax_percentage' => (float) $tax * 100 // Konversi string ke float dan dikalikan 100 jika disimpan dalam bentuk desimal
    ]);
})->name('get.tax');
