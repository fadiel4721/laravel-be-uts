<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\ReportController;

// Route login dan register
Route::post('/register', App\Http\Controllers\Api\RegisterController::class);
Route::post('/login', App\Http\Controllers\Api\LoginController::class);
Route::post('/logout', App\Http\Controllers\Api\LogoutController::class);

// Route untuk user yang sedang login
Route::middleware('jwt.auth')->get('/user', function (Request $request) {
    return $request->user();
});

// Route API dengan proteksi middleware JWT
Route::group(['middleware' => ['jwt.auth']], function () {
    // Endpoint untuk produk
    Route::apiResource('products', ProductController::class);

    // Endpoint untuk order
    Route::apiResource('orders', OrderController::class); // Endpoints untuk Order (create, show, update, delete)
    Route::get('orders/history', [OrderController::class, 'history']);
    Route::get('orders/{kasir_id}', [OrderController::class, 'show']);
    Route::post('loyalty/discount-code', [LoyaltyController::class, 'storeDiscountCode']);
    Route::get('orders/{kasir_id}/history', [OrderController::class, 'getOrdersByKasirId']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('validate-discount', [OrderController::class, 'validateDiscount']);
    // Endpoint untuk membatalkan order
    Route::post('orders/{order_id}/cancel', [OrderController::class, 'cancelOrder']);


    // Endpoint untuk kategori produk
    Route::get('list-categories', [CategoryController::class, 'index']);

    // Endpoint untuk laporan
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/product-sales', [ReportController::class, 'productSales']);
    Route::get('/reports/close-cashier', [ReportController::class, 'closeCashier']);

    // Endpoint untuk loyalty
    Route::apiResource('loyalty', LoyaltyController::class);
    Route::get('loyalty/show', [LoyaltyController::class, 'show']);
    Route::get('loyalty/discount-code', [LoyaltyController::class, 'getDiscountCode']);
    Route::post('loyalty/update-discount', [LoyaltyController::class, 'updateDiscountCode']);
    Route::post('loyalty/upgrade-level', [LoyaltyController::class, 'upgradeLevelAndAssignDiscount']);
});


// Refresh token route
Route::post('/refresh-token', [LoginController::class, 'refreshToken']);
