<?php

use App\Http\Controllers;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{lang}', [Controllers\LanguageController::class, 'update'])->name('common.language.update');

Route::group(['prefix' => 'auth', 'middleware' => 'guest'], function () {
    Route::get('/login', [Auth\LoginController::class, 'index'])->name('client.login');
});