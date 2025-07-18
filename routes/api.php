<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\TransactionController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//login
Route::post('/login', [AuthController::class, 'login']);
//logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//products
Route::apiResource('/api-products', ProductController::class)->middleware('auth:sanctum');
//categories
Route::apiResource('/api-categories', CategoryController::class)->middleware('auth:sanctum');

//orders
Route::post('/api-orders', [OrderController::class, 'store'])->middleware('auth:sanctum');

//Transactions
Route::get('/recap/monthly', [TransactionController::class, 'index']);

Route::post('/transactions', [TransactionController::class, 'store']);

// Kirim data, generate pdf, dan kirim ke email / balikin link pdf
Route::post('/transactions/print', [TransactionController::class, 'print']);

Route::get('/transaksi', [TransactionController::class, 'list'])->name('transaksi.index.api');
