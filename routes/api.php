<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\WebhookController;

// Public endpoints
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0',
    ]);
});

// Authenticated API endpoints
Route::middleware(['api.auth', 'throttle.api:100,1', 'log.api'])->group(function () {
    // Orders
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::post('/orders', [OrderApiController::class, 'store']);
    Route::get('/orders/{orderNumber}', [OrderApiController::class, 'show']);
    Route::delete('/orders/{orderNumber}', [OrderApiController::class, 'destroy']);
    
    // Products
    Route::get('/products', [OrderApiController::class, 'products']);
    Route::get('/products/{sku}', [ProductApiController::class, 'show']);
    Route::get('/products/category/{category}', [ProductApiController::class, 'byCategory']);
    
    // Customer
    Route::get('/customer/profile', [CustomerApiController::class, 'profile']);
    Route::put('/customer/profile', [CustomerApiController::class, 'updateProfile']);
    Route::get('/customer/balance', [CustomerApiController::class, 'balance']);
    Route::get('/customer/invoices', [CustomerApiController::class, 'invoices']);
});

// Webhook endpoints (dla zewnętrznych systemów)
Route::post('/webhooks/order-status', [WebhookController::class, 'orderStatus']);
Route::post('/webhooks/payment-confirmation', [WebhookController::class, 'paymentConfirmation']);
