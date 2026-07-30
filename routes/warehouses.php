<?php

use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('warehouses')->name('warehouses.')->group(function () {
    Route::post('/', [WarehouseController::class, 'store'])->name('store');
    Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
});