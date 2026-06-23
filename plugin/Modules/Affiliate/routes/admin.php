<?php

use Illuminate\Support\Facades\Route;
use Plugins\Modules\Affiliate\Http\Controllers\Admin\AffiliateController;
use Plugins\Modules\Affiliate\Http\Controllers\Admin\CommissionController;
use Plugins\Modules\Affiliate\Http\Controllers\Admin\MemberController;
use Plugins\Modules\Affiliate\Http\Controllers\Admin\WithdrawalController;

Route::middleware('permission:modules.affiliate.manage')->group(function () {
    Route::get('/', [AffiliateController::class, 'index'])->name('index');

    Route::get('/members', [MemberController::class, 'index'])->name('members');
    Route::post('/members/{member}/suspend', [MemberController::class, 'suspend'])->name('members.suspend');
    Route::post('/members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');

    Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions');
    Route::post('/commissions/{commission}/approve', [CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('/commissions/{commission}/reject', [CommissionController::class, 'reject'])->name('commissions.reject');

    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');
});
