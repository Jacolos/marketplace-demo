<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;

// Przekierowanie z głównej na dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Orders - wszystkie trasy
Route::resource('orders', OrderController::class);
Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

// Products - wszystkie trasy
Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
Route::resource('products', ProductController::class);
Route::post('/products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');

// Customers - wszystkie trasy
Route::resource('customers', CustomerController::class);
Route::post('/customers/{customer}/regenerate-api-key', [CustomerController::class, 'regenerateApiKey'])->name('customers.regenerate-api-key');
Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
Route::patch('/customers/{customer}/clear-balance', [CustomerController::class, 'clearBalance'])->name('customers.clearBalance');
Route::patch('/customers/{customer}/update-credit-limit', [CustomerController::class, 'updateCreditLimit'])->name('customers.updateCreditLimit');

// Livewire routes
Route::get('/search', function () {
    return view('products.search');
})->name('products.search');


