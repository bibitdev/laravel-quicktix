<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\LoginController;

// Halaman login (GET /)
Route::get('/', function () {
    return view('pages.auth.login');
})->name('login'); // nama route ini 'login' untuk form login

// Proses login & logout
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes (hanya untuk user yang sudah login)
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/home', function () {
        $total_user = \App\Models\User::count();
        return view('pages.dashboard', ['type_menu' => 'home'], compact('total_user'));
    })->name('home');

    // Resource routes
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);

    // Transaksi
    Route::get('/transaksi', [TransactionController::class, 'list'])->name('transaksi.index');
    Route::get('/transaksi/{id}/cetak', [TransactionController::class, 'cetak'])->name('transaksi.cetak');
    Route::post('/transactions/print', [TransactionController::class, 'print']);

    // Download recap bulanan
    Route::get('/recap/{month}/download', [TransactionController::class, 'downloadRecap'])->name('recap.download');
});
