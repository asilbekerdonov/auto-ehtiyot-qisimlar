<?php

use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SalesController::class, 'selectCar'])->name('cars');
    Route::get('/cars/{car}', [SalesController::class, 'carParts'])->name('parts');
    Route::get('/cart', [SalesController::class, 'cart'])->name('cart');
    
    Route::post('/cart/add', [SalesController::class, 'addToCart'])->name('cart.add');
    Route::patch('/cart/{key}', [SalesController::class, 'updateCartItem'])->name('cart.update');
    Route::delete('/cart/{key}', [SalesController::class, 'removeFromCart'])->name('cart.remove');
    
    Route::post('/checkout', [SalesController::class, 'checkout'])->name('checkout');
});