<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\Client\User;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'maintenance']], function () {
  Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('client.dashboard');
});

Route::group(['prefix' => 'auth'], function () {
  Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [Auth\LoginController::class, 'index'])->name('client.login');
    Route::post('/login', [Auth\LoginController::class, 'login'])->name('client.login.store');

    Route::get('/register', [Auth\RegisterController::class, 'index'])->name('client.register');
    Route::post('/register', [Auth\RegisterController::class, 'register'])->name('client.register.store');

    Route::get('/email/verify/{token}', [Auth\EmailVerificationController::class, 'handle'])->name('client.email.verify');
    Route::post('/email/resend', [Auth\EmailVerificationController::class, 'resend'])->name('client.email.resend');

    Route::get('/password/forgot', [Auth\Password\ForgotController::class, 'index'])->name('client.password.forgot');
    Route::post('/password/forgot', [Auth\Password\ForgotController::class, 'store'])->name('client.password.forgot.store');

    Route::get('/password/reset/{token}', [Auth\Password\ResetController::class, 'index'])->name('client.password.reset');
    Route::post('/password/reset', [Auth\Password\ResetController::class, 'store'])->name('client.password.reset.store');
  });

  Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [Auth\LoginController::class, 'logout'])->name('client.logout');
  });
});

Route::group(['prefix' => 'user', 'middleware' => 'auth'], function () {
  Route::get('/account', [User\AccountController::class, 'index'])->name('client.user.account');
});
