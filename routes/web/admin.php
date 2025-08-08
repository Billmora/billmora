<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\Settings;
use Illuminate\Support\Facades\Route;

/**
 * Admin interface routes.
 *
 * Prefix: /admin
 */
Route::group(['prefix' => 'admin'], function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    /**
     * Admin settings interface routes.
     *
     * Prefix: /admin/settings
     */
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [Admin\SettingsController::class, 'index'])->name('admin.settings');

        /**
         * Admin general settings interface routes.
         *
         * Prefix: /admin/settings/general
         */
        Route::group(['prefix' => 'general'], function () {
            Route::get('/', [Settings\General\CompanyController::class, 'index'])->name('admin.settings.general.company');
            Route::post('/', [Settings\General\CompanyController::class, 'store'])->name('admin.settings.general.company.store');
            Route::get('/ordering', [Settings\General\OrderingController::class, 'index'])->name('admin.settings.general.ordering');
            Route::post('/ordering', [Settings\General\OrderingController::class, 'store'])->name('admin.settings.general.ordering.store');
        });
    });

    /**
     * Admin quick search routes.
     *
     * Prefix: /admin/quick-search
     */
    Route::get('/quick-search', [Admin\QuickSearchController::class, 'search'])->name('admin.quick-search');
});
