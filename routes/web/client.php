<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\Client;
use App\Http\Controllers\Client\Account;
use Illuminate\Support\Facades\Route;

/**
 * Client interface routes.
 */
Route::group(['middleware' => ['auth', 'maintenance', '2fa']], function () {
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

        Route::get('/credits', [Account\CreditController::class, 'index'])->name('client.account.credits');
        Route::post('/credits/topup', [Account\CreditController::class, 'topup'])->name('client.account.credits.topup');
    });
});

/**
 * Client store routes.
 * 
 * Prefix: /store
 */
Route::group(['prefix' => 'store', 'middleware' => ['maintenance']], function () {
    Route::get('/', [Client\StoreController::class, 'index'])->name('client.store');
    Route::get('/{catalog:slug}', [Client\Store\CatalogController::class, 'index'])->name('client.store.catalog');
    Route::get('/{catalog:slug}/{package:slug}', [Client\Store\PackageController::class, 'show'])->name('client.store.catalog.package');
});

/**
 * Client store routes.
 * 
 * Prefix: /checkout
 */
Route::group(['prefix' => 'checkout', 'middleware' => ['maintenance']], function () {
    Route::get('/cart', [Client\Checkout\CartController::class, 'index'])->name('client.checkout.cart');
    Route::post('/cart', [Client\Checkout\CartController::class, 'add'])->name('client.checkout.cart.add');
    Route::post('/cart/{id}/update', [Client\Checkout\CartController::class, 'update'])->name('client.checkout.cart.update');
    Route::post('/cart/{id}/remove', [Client\Checkout\CartController::class, 'remove'])->name('client.checkout.cart.remove');

    Route::post('/coupon/check', [Client\Checkout\CouponController::class, 'check'])->name('client.checkout.coupon.check');
    Route::post('/coupon/remove', [Client\Checkout\CouponController::class, 'remove'])->name('client.checkout.coupon.remove');

    Route::post('/process', [Client\CheckoutController::class, 'process'])->name('client.checkout.process');
    Route::get('/complete', [Client\CheckoutController::class, 'complete'])->name('client.checkout.complete');
});

/**
 * Client services routes.
 * 
 * Prefix: /services
 */
Route::group(['prefix' => 'services', 'middleware' => ['auth', 'maintenance', '2fa']], function () {
    Route::get('/', [Client\ServicesController::class, 'index'])->name('client.services');
    Route::get('/{service:service_number}', [Client\ServicesController::class, 'show'])->name('client.services.show');

    Route::get('/{service:service_number}/cancellation', [Client\Services\CancellationController::class, 'create'])->name('client.services.cancellation.create');
    Route::post('/{service:service_number}/cancellation', [Client\Services\CancellationController::class, 'store'])->name('client.services.cancellation.store');

    Route::get('/{service:service_number}/scaling', [Client\Services\ScalingController::class, 'show'])->name('client.services.scaling.show');
    Route::post('/{service:service_number}/scaling', [Client\Services\ScalingController::class, 'store'])->name('client.services.scaling.store');

    Route::get('/{service:service_number}/provisioning/{slug}', [Client\Services\ProvisioningController::class, 'show'])->name('client.services.provisioning.show');
    Route::any('/{service:service_number}/provisioning/{slug}/handle', [Client\Services\ProvisioningController::class, 'handle'])->name('client.services.provisioning.handle');
});

/**
 * Client invoices routes.
 * 
 * Prefix: /invoices
 */
Route::group(['prefix' => 'invoices', 'middleware' => ['auth', 'maintenance', '2fa']], function () {
    Route::get('/', [Client\InvoicesController::class, 'index'])->name('client.invoices');
    Route::get('/{invoice:invoice_number}', [Client\InvoicesController::class, 'show'])->name('client.invoices.show');
    Route::get('/{invoice:invoice_number}/download', [Client\InvoicesController::class, 'download'])->name('client.invoices.download');
    Route::get('/{invoice:invoice_number}/pay', [Client\PaymentController::class, 'process'])->name('client.invoices.pay');
});

/**
 * Client tickets routes.
 * 
 * Prefix: /tickets
 */
Route::group(['prefix' => 'tickets', 'middleware' => ['auth', 'maintenance', '2fa']], function () {
    Route::get('/', [Client\TicketsController::class, 'index'])->name('client.tickets');
    Route::get('/create', [Client\TicketsController::class, 'create'])->name('client.tickets.create');
    Route::post('/create', [Client\TicketsController::class, 'store'])->name('client.tickets.store');
    Route::patch('/{ticket:ticket_number}', [Client\TicketsController::class, 'close'])->name('client.tickets.close');

    Route::get('/{ticket:ticket_number}/reply', [Client\Tickets\ReplyController::class, 'index'])->name('client.tickets.reply');
    Route::post('/{ticket:ticket_number}/reply', [Client\Tickets\ReplyController::class, 'send'])->name('client.tickets.reply.send');
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