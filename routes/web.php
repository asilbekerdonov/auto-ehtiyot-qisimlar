<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/goods', fn () => view('pages/goods'))->name('goods');
    Route::get('/add', fn () => view('pages/add'))->name('add');
    Route::get('/debtors', fn () => view('pages/debtors'))->name('debtors');
    Route::get('/stock', fn () => view('pages/stock'))->name('stock');
});