<?php

use App\Http\Controllers\Api;
use App\Http\Controllers\Api\Gateway;
use App\Http\Middleware\ApiRateLimiter;
use App\Http\Middleware\CheckApiTokenAbility;
use App\Http\Middleware\CheckApiWhitelistIp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/gateways/{plugin}/webhook', [Gateway\CallbackController::class, 'handleWebhook'])->name('api.gateways.webhook');


Route::post('/domains/check', [Api\DomainsController::class, 'checkAvailability'])->name('api.domains.check');

Route::prefix('v1')->middleware(['auth:sanctum', CheckApiWhitelistIp::class, ApiRateLimiter::class])->group(function () {


    Route::middleware(CheckApiTokenAbility::class . ':users.view')->group(function () {
        Route::get('/users', [Api\UsersController::class, 'index'])->name('api.v1.users.index');
        Route::get('/users/{user}', [Api\UsersController::class, 'show'])->name('api.v1.users.show');
    });
    Route::post('/users', [Api\UsersController::class, 'store'])->middleware(CheckApiTokenAbility::class . ':users.create')->name('api.v1.users.store');
    Route::put('/users/{user}', [Api\UsersController::class, 'update'])->middleware(CheckApiTokenAbility::class . ':users.update')->name('api.v1.users.update');
    Route::delete('/users/{user}', [Api\UsersController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':users.delete')->name('api.v1.users.delete');


    Route::middleware(CheckApiTokenAbility::class . ':services.view')->group(function () {
        Route::get('/services', [Api\ServicesController::class, 'index'])->name('api.v1.services.index');
        Route::get('/services/{service}', [Api\ServicesController::class, 'show'])->name('api.v1.services.show');
    });
    Route::put('/services/{service}', [Api\ServicesController::class, 'update'])->middleware(CheckApiTokenAbility::class . ':services.update')->name('api.v1.services.update');
    Route::delete('/services/{service}', [Api\ServicesController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':services.delete')->name('api.v1.services.delete');


    Route::middleware(CheckApiTokenAbility::class . ':invoices.view')->group(function () {
        Route::get('/invoices', [Api\InvoicesController::class, 'index'])->name('api.v1.invoices.index');
        Route::get('/invoices/{invoice}', [Api\InvoicesController::class, 'show'])->name('api.v1.invoices.show');
    });
    Route::post('/invoices', [Api\InvoicesController::class, 'store'])->middleware(CheckApiTokenAbility::class . ':invoices.create')->name('api.v1.invoices.store');
    Route::put('/invoices/{invoice}', [Api\InvoicesController::class, 'update'])->middleware(CheckApiTokenAbility::class . ':invoices.update')->name('api.v1.invoices.update');
    Route::delete('/invoices/{invoice}', [Api\InvoicesController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':invoices.delete')->name('api.v1.invoices.delete');


    Route::middleware(CheckApiTokenAbility::class . ':orders.view')->group(function () {
        Route::get('/orders', [Api\OrdersController::class, 'index'])->name('api.v1.orders.index');
        Route::get('/orders/{order}', [Api\OrdersController::class, 'show'])->name('api.v1.orders.show');
    });
    Route::delete('/orders/{order}', [Api\OrdersController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':orders.delete')->name('api.v1.orders.delete');


    Route::middleware(CheckApiTokenAbility::class . ':tickets.view')->group(function () {
        Route::get('/tickets', [Api\TicketsController::class, 'index'])->name('api.v1.tickets.index');
        Route::get('/tickets/{ticket}', [Api\TicketsController::class, 'show'])->name('api.v1.tickets.show');
    });
    Route::post('/tickets', [Api\TicketsController::class, 'store'])->middleware(CheckApiTokenAbility::class . ':tickets.create')->name('api.v1.tickets.store');
    Route::put('/tickets/{ticket}', [Api\TicketsController::class, 'update'])->middleware(CheckApiTokenAbility::class . ':tickets.update')->name('api.v1.tickets.update');
    Route::delete('/tickets/{ticket}', [Api\TicketsController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':tickets.delete')->name('api.v1.tickets.delete');


    Route::middleware(CheckApiTokenAbility::class . ':packages.view')->group(function () {
        Route::get('/packages', [Api\PackagesController::class, 'index'])->name('api.v1.packages.index');
        Route::get('/packages/{package}', [Api\PackagesController::class, 'show'])->name('api.v1.packages.show');
    });
    Route::post('/packages', [Api\PackagesController::class, 'store'])->middleware(CheckApiTokenAbility::class . ':packages.create')->name('api.v1.packages.store');
    Route::put('/packages/{package}', [Api\PackagesController::class, 'update'])->middleware(CheckApiTokenAbility::class . ':packages.update')->name('api.v1.packages.update');
    Route::delete('/packages/{package}', [Api\PackagesController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':packages.delete')->name('api.v1.packages.delete');


    Route::middleware(CheckApiTokenAbility::class . ':catalogs.view')->group(function () {
        Route::get('/catalogs', [Api\CatalogsController::class, 'index'])->name('api.v1.catalogs.index');
        Route::get('/catalogs/{catalog}', [Api\CatalogsController::class, 'show'])->name('api.v1.catalogs.show');
    });
    Route::post('/catalogs', [Api\CatalogsController::class, 'store'])->middleware(CheckApiTokenAbility::class . ':catalogs.create')->name('api.v1.catalogs.store');
    Route::put('/catalogs/{catalog}', [Api\CatalogsController::class, 'update'])->middleware(CheckApiTokenAbility::class . ':catalogs.update')->name('api.v1.catalogs.update');
    Route::delete('/catalogs/{catalog}', [Api\CatalogsController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':catalogs.delete')->name('api.v1.catalogs.delete');


    Route::middleware(CheckApiTokenAbility::class . ':variants.view')->group(function () {
        Route::get('/variants', [Api\VariantsController::class, 'index'])->name('api.v1.variants.index');
        Route::get('/variants/{variant}', [Api\VariantsController::class, 'show'])->name('api.v1.variants.show');
    });
    Route::post('/variants', [Api\VariantsController::class, 'store'])->middleware(CheckApiTokenAbility::class . ':variants.create')->name('api.v1.variants.store');
    Route::put('/variants/{variant}', [Api\VariantsController::class, 'update'])->middleware(CheckApiTokenAbility::class . ':variants.update')->name('api.v1.variants.update');
    Route::delete('/variants/{variant}', [Api\VariantsController::class, 'destroy'])->middleware(CheckApiTokenAbility::class . ':variants.delete')->name('api.v1.variants.delete');


    Route::middleware(CheckApiTokenAbility::class . ':registrants.view')->group(function () {
        Route::get('/registrants', [Api\RegistrantsController::class, 'index'])->name('api.v1.registrants.index');
        Route::get('/registrants/{registrant}', [Api\RegistrantsController::class, 'show'])->name('api.v1.registrants.show');
    });


    Route::middleware(CheckApiTokenAbility::class . ':tlds.view')->group(function () {
        Route::get('/tlds', [Api\TldsController::class, 'index'])->name('api.v1.tlds.index');
        Route::get('/tlds/{tld}', [Api\TldsController::class, 'show'])->name('api.v1.tlds.show');
    });
});