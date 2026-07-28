<?php

use App\Http\Controllers\AddController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoodsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Главная страница - редирект на товары или логин
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('goods')  // Изменено с dashboard на goods
        : redirect()->route('login');
});

// Маршруты аутентификации
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// Защищенные маршруты (только для авторизованных)
Route::middleware('auth')->group(function () {
    // Основные страницы
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    
    // Страницы с контроллерами
    Route::get('/goods', [GoodsController::class, 'index'])->name('goods');
    Route::get('/stock', [StockController::class, 'index'])->name('stock');
    Route::get('/add', [AddController::class, 'index'])->name('add');
    
    // Дополнительные страницы (если нужны)
    Route::view('/debtors', 'pages.debtors')->name('debtors');
    Route::view('/sales', 'pages.sales')->name('sales');
    Route::view('/analytics', 'pages.analytics')->name('analytics');
    
    // === CRUD для категорий ===
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });
    
    // === CRUD для складов ===
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::post('/', [WarehouseController::class, 'store'])->name('store');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
    });
    
    // === CRUD для автомобилей ===
    Route::prefix('cars')->name('cars.')->group(function () {
        Route::post('/', [CarController::class, 'store'])->name('store');
        Route::delete('/{car}', [CarController::class, 'destroy'])->name('destroy');
    });
    
    // === CRUD для продуктов ===
    Route::prefix('products')->name('products.')->group(function () {
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
});