<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\LoginController;

// Halaman login
Route::get('/', function () {
    return view('pages.auth.login');
})->name('login.page');

// Login & Logout
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/home', function () {
        $total_user = \App\Models\User::count();
        return view('pages.dashboard', ['type_menu' => 'home'], compact('total_user'));
    })->name('home');

    // --- Shared Access (Admin & Staff): hanya index ---
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('products', [ProductController::class, 'index'])->name('products.index');

    // --- Admin Only: full CRUD ---
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['index']);
        Route::resource('categories', CategoryController::class)->except(['index']);
        Route::resource('products', ProductController::class)->except(['index']);
    });

    // --- Transaksi ---
    Route::get('/transaksi', [TransactionController::class, 'list'])->name('transaksi.index');
    Route::get('/transaksi/{id}/cetak', [TransactionController::class, 'cetak'])->name('transaksi.cetak');
    Route::post('/transactions/print', [TransactionController::class, 'print']);

    // --- Download Recap (bisa dikunci juga khusus admin kalau perlu) ---
    Route::get('/recap/{month}/download', [TransactionController::class, 'downloadRecap'])->name('recap.download');
});
