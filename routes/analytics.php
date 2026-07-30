<?php

use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');