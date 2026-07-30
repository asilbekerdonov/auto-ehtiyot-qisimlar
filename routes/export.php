<?php

use App\Http\Controllers\GoodsController;

Route::get('/goods/export', [GoodsController::class, 'export'])->name('goods.export');