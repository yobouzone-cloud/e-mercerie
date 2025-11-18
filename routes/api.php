<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\MerchantSupplyController;
use App\Http\Controllers\PriceComparisonController;
// use App\Http\Controllers\MerchantOrderController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/supplies', [SupplyController::class, 'index']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:mercerie')->prefix('merchant')->group(function () {
        Route::get('/supplies', [MerchantSupplyController::class, 'index']);
        Route::post('/supplies', [MerchantSupplyController::class, 'store']);
        Route::put('/supplies/{id}', [MerchantSupplyController::class, 'update']);
        // Accept or reject an order assigned to this mercerie
        // Route::post('/orders/{id}/accept', [MerchantOrderController::class, 'accept']);
        // Route::post('/orders/{id}/reject', [MerchantOrderController::class, 'reject']);
    });

    Route::middleware('role:couturier')->prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        // Estimation des prix par mercerie pour une sélection de fournitures
        Route::post('/estimate', [\App\Http\Controllers\MerchantSupplyController::class, 'estimateForCouturier']);
    });

    // Backwards-compatible aliases: some clients call /api/order (singular)
    // Ensure aliases enforce the same role requirement as /api/orders
    // Route::middleware('role:couturier')->group(function () {
    //     Route::get('/order', [OrderController::class, 'index']);
    //     Route::post('/order', [OrderController::class, 'store']);
    // });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/price-comparison', [PriceComparisonController::class, 'compare']);
});

// Public endpoint to fetch quarters for a city (used by the profile form)
Route::get('/cities/{city}/quarters', function (\App\Models\City $city) {
    return response()->json($city->quarters()->select('id','name')->orderBy('name')->get());
});
