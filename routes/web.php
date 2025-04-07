<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\Portal;
use Illuminate\Support\Facades\Route;

Route::get('/', [Portal\PortalController::class, 'index'])->name('portal.index');
Route::post('/preference', [Portal\PreferenceController::class, 'update'])->name('preference.update');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('client.dashboard');
});

Route::group(['prefix' => 'auth'], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/login', [Auth\LoginController::class, 'index'])->name('client.login');
    });
});