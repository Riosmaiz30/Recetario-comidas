<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Http\Controllers\DashboardController::class)->name('dashboard');

Route::resource('ingredients', App\Http\Controllers\IngredientController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::resource('recipes', App\Http\Controllers\RecipeController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::get('/rates', [App\Http\Controllers\ExchangeRateController::class, 'index'])->name('rates.index');
Route::post('/rates', [App\Http\Controllers\ExchangeRateController::class, 'store'])->name('rates.store');
Route::put('/rates/base-currency', [App\Http\Controllers\ExchangeRateController::class, 'updateBaseCurrency'])
    ->name('rates.base-currency');
