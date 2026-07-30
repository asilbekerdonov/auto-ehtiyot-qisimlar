<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('receipts')->name('receipts.')->group(function () {
    Route::get('/', [ReceiptController::class, 'selectCar'])->name('cars');
    Route::get('/cars/{car}', [ReceiptController::class, 'carParts'])->name('parts');
    Route::post('/add', [ReceiptController::class, 'addStock'])->name('add');
});