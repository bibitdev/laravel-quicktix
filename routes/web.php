<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\LoginController;


// Login page
Route::get('/', function () {
    return view('pages.auth.login');
});

// Login & logout
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        $total_user = \App\Models\User::count();
        return view('pages.dashboard', ['type_menu' => 'home'], compact('total_user'));
    })->name('home');

    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);

    Route::get('/transaksi', [TransactionController::class, 'list'])->name('transaksi.index');
    Route::get('/transaksi/{id}/cetak', [TransactionController::class, 'cetak'])->name('transaksi.cetak');
    Route::post('/transactions/print', [TransactionController::class, 'print']);

    Route::get('/recap/{month}/download', [TransactionController::class, 'downloadRecap'])->name('recap.download');
});
