<?php

use App\Http\Controllers\POSController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Rute Login POS
Route::get('/pos/login', function () {
    return view('pos.login');
})->name('login'); // Gunakan 'login' agar middleware auth bisa menemukannya

Route::post('/pos/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        if (auth()->user()->isAdmin()) {
            return redirect()->intended(
                $request->input('redirect', 'pos.index') === 'admin' 
                    ? '/admin' 
                    : route('pos.index')
            );
        }

        return redirect()->route('pos.index');
    }

    return back()->withErrors([
        'email' => 'Kredensial tidak valid.',
    ]);
})->name('pos.login.post');

// Rute POS (memerlukan autentikasi)
Route::middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
    Route::post('/pos/orders', [POSController::class, 'store'])->name('pos.orders.store');
    Route::get('/pos/products', [POSController::class, 'getProducts'])->name('pos.products.get');

    // Logout POS
    Route::post('/pos/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login'); // Gunakan 'login' agar sesuai dengan perubahan di atas
    })->name('pos.logout');

    // Tampilkan struk pesanan
    Route::get('/pos/receipt/{order}', [POSController::class, 'showReceipt'])
        ->name('pos.receipt.show');
});

// Rute Order (dapat digunakan untuk admin atau role lain)
Route::get('/orders/history', [POSController::class, 'getOrderHistory'])->name('orders.history');
Route::get('/orders/{id}', [POSController::class, 'getOrder'])->name('orders.show');

Route::post('/pos/orders/{id}/cancel', [POSController::class, 'cancelOrder'])->name('pos.orders.cancel');
