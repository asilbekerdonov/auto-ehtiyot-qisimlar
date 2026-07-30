<?php

use App\Http\Controllers\DebtorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('debtors')->name('debtors.')->group(function () {
    Route::get('/', [DebtorController::class, 'index'])->name('index');
    Route::post('/{customer}/pay', [DebtorController::class, 'pay'])->name('pay');
});