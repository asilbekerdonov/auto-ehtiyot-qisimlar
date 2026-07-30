<?php

use App\Http\Controllers\CarController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('cars')->name('cars.')->group(function () {
    Route::post('/', [CarController::class, 'store'])->name('store');
    Route::delete('/{car}', [CarController::class, 'destroy'])->name('destroy');
});