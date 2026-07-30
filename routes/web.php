<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Главная страница - редирект
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('goods')
        : redirect()->route('login');
});

// ==================== ПОДКЛЮЧАЕМ ФАЙЛЫ ====================
require __DIR__.'/auth.php';
require __DIR__.'/export.php';
require __DIR__.'/pages.php';
require __DIR__.'/categories.php';
require __DIR__.'/warehouses.php';
require __DIR__.'/cars.php';
require __DIR__.'/products.php';
require __DIR__.'/stock.php';
require __DIR__.'/sales.php';
require __DIR__.'/receipts.php';
require __DIR__.'/debtors.php';
require __DIR__.'/analytics.php';