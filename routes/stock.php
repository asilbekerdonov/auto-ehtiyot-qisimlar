<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('stock')->name('stock.')->group(function () {
    Route::put('/{quantity}', [StockController::class, 'update'])->name('update');
    Route::delete('/{quantity}', [StockController::class, 'destroy'])->name('destroy');
});