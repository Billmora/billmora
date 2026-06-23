<?php

use Illuminate\Support\Facades\Route;
use Plugins\Modules\Affiliate\Http\Controllers\Client\DashboardController;
use Plugins\Modules\Affiliate\Http\Controllers\Client\WithdrawalController;

Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::post('/join', [DashboardController::class, 'join'])->name('join');

    Route::post('/withdrawal', [WithdrawalController::class, 'store'])->name('withdrawal.store');
});
