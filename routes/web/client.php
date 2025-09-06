<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\Client\Account;
use Illuminate\Support\Facades\Route;

/**
 * Client interface routes.
 */
Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('client.dashboard');

    /**
     * Client account routes.
     * 
     * Prefix: /account
     */
    Route::group(['prefix' => 'account'], function () {
        Route::get('/settings', [Account\SettingsController::class, 'index'])->name('client.account.settings');
        Route::put('/settings', [Account\SettingsController::class, 'update'])->name('client.account.settings.update');

        Route::get('/security', [Account\SecurityController::class, 'index'])->name('client.account.security');
        Route::put('/security/email', [Account\SecurityController::class, 'updateEmail'])->name('client.account.security.email.update');
        Route::put('/security/password', [Account\SecurityController::class, 'updatePassword'])->name('client.account.security.password.update');
    });
});

/**
 * Client authentication routes.
 * 
 * Prefix: /auth
 */
Route::group(['prefix' => 'auth'], function () {
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/login', [Auth\LoginController::class, 'index'])->name('client.login');
        Route::post('/login', [Auth\LoginController::class, 'store'])->name('client.login.store');

        Route::get('/register', [Auth\RegisterController::class, 'index'])->name('client.register');
        Route::post('/register', [Auth\RegisterController::class, 'store'])->name('client.register.store');

        Route::get('/email/verify/{token}', [Auth\EmailVerificationController::class, 'verify'])->name('client.email.verify');
        Route::post('/email/resend', [Auth\EmailVerificationController::class, 'resend'])->name('client.email.resend');

        Route::get('/password/forgot', [Auth\Password\ForgotController::class, 'index'])->name('client.password.forgot');
        Route::post('/password/forgot', [Auth\Password\ForgotController::class, 'store'])->name('client.password.forgot.store');

        Route::get('/password/reset/{token}', [Auth\Password\ResetController::class, 'index'])->name('client.password.reset');
        Route::post('/password/reset', [Auth\Password\ResetController::class, 'store'])->name('client.password.reset.store');
    });

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/two-factor/setup', [Auth\TwoFactor\SetupController::class, 'index'])->name('client.two-factor.setup');
        Route::post('/two-factor/setup', [Auth\TwoFactor\SetupController::class, 'store'])->name('client.two-factor.setup.store');
        
        Route::get('/two-factor/backup', [Auth\TwoFactor\BackupController::class, 'index'])->name('client.two-factor.backup');
        Route::post('/two-factor/backup', [Auth\TwoFactor\BackupController::class, 'store'])->name('client.two-factor.backup.store');
        Route::post('/two-factor/backup/download', [Auth\TwoFactor\BackupController::class, 'download'])->name('client.two-factor.backup.download');
        
        Route::get('/two-factor/verify', [Auth\TwoFactor\VerifyController::class, 'index'])->name('client.two-factor.verify');
        Route::post('/two-factor/verify', [Auth\TwoFactor\VerifyController::class, 'store'])->name('client.two-factor.verify.store');
        
        Route::get('/two-factor/recovery', [Auth\TwoFactor\RecoveryController::class, 'index'])->name('client.two-factor.recovery');
        Route::post('/two-factor/recovery', [Auth\TwoFactor\RecoveryController::class, 'store'])->name('client.two-factor.recovery.store');

        Route::post('/two-factor/disable', [Auth\TwoFactor\SetupController::class, 'disable'])->name('client.two-factor.disable');

        Route::post('/logout', [Auth\LoginController::class, 'logout'])->name('client.logout.store');
    });
});