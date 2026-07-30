<?php

use App\Http\Controllers\AddController;
use App\Http\Controllers\GoodsController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
  
    Route::get('/add', [AddController::class, 'index'])->name('add');
    Route::get('/goods', [GoodsController::class, 'index'])->name('goods');
    Route::get('/stock', [StockController::class, 'index'])->name('stock');
    Route::get('/add', [AddController::class, 'index'])->name('add');
});