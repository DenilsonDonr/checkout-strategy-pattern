<?php

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/',        [CheckoutController::class, 'index'])->name('index');
    Route::post('/',       [CheckoutController::class, 'store'])->name('store');
    Route::get('/success/{orderId}', [CheckoutController::class, 'success'])->name('success');
});