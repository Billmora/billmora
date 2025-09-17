<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\Settings;
use App\Http\Controllers\Admin\Users;
use Illuminate\Support\Facades\Route;

/**
 * Admin interface routes.
 *
 * Prefix: /admin
 */
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {
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
            Route::put('/', [Settings\General\CompanyController::class, 'update'])->name('admin.settings.general.company.update');
            Route::get('/ordering', [Settings\General\OrderingController::class, 'index'])->name('admin.settings.general.ordering');
            Route::put('/ordering', [Settings\General\OrderingController::class, 'update'])->name('admin.settings.general.ordering.update');
            Route::get('/invoice', [Settings\General\InvoiceController::class, 'index'])->name('admin.settings.general.invoice');
            Route::put('/invoice', [Settings\General\InvoiceController::class, 'update'])->name('admin.settings.general.invoice.update');
            Route::get('/credit', [Settings\General\CreditController::class, 'index'])->name('admin.settings.general.credit');
            Route::put('/credit', [Settings\General\CreditController::class, 'update'])->name('admin.settings.general.credit.update');
            Route::get('/affiliate', [Settings\General\AffiliateController::class, 'index'])->name('admin.settings.general.affiliate');
            Route::put('/affiliate', [Settings\General\AffiliateController::class, 'update'])->name('admin.settings.general.affiliate.update');
            Route::get('/term', [Settings\General\TermController::class, 'index'])->name('admin.settings.general.term');
            Route::put('/term', [Settings\General\TermController::class, 'update'])->name('admin.settings.general.term.update');
            Route::get('/social', [Settings\General\SocialController::class, 'index'])->name('admin.settings.general.social');
            Route::put('/social', [Settings\General\SocialController::class, 'update'])->name('admin.settings.general.social.update');
        });

        /**
         * Admin mail settings interface routes.
         *
         * Prefix: /admin/settings/mail
         */
        Route::group(['prefix' => 'mail'], function () {
            Route::get('/', [Settings\Mail\MailerController::class, 'index'])->name('admin.settings.mail.mailer');
            Route::put('/', [Settings\Mail\MailerController::class, 'update'])->name('admin.settings.mail.mailer.update');
            Route::post('/test', [Settings\Mail\MailerController::class, 'test'])->name('admin.settings.mail.mailer.test');
            Route::get('/template', [Settings\Mail\TemplateController::class, 'index'])->name('admin.settings.mail.template');
            Route::get('/template/{id}/edit', [Settings\Mail\TemplateController::class, 'edit'])->name('admin.settings.mail.template.edit');
            Route::put('/template/{id}/edit', [Settings\Mail\TemplateController::class, 'update'])->name('admin.settings.mail.template.update');
            Route::get('/broadcast', [Settings\Mail\BroadcastController::class, 'index'])->name('admin.settings.mail.broadcast');
            Route::get('/broadcast/create', [Settings\Mail\BroadcastController::class, 'create'])->name('admin.settings.mail.broadcast.create');
            Route::post('/broadcast', [Settings\Mail\BroadcastController::class, 'store'])->name('admin.settings.mail.broadcast.store');
            Route::get('/broadcast/{id}/edit', [Settings\Mail\BroadcastController::class, 'edit'])->name('admin.settings.mail.broadcast.edit');
            Route::put('/broadcast/{id}/edit', [Settings\Mail\BroadcastController::class, 'update'])->name('admin.settings.mail.broadcast.update');
            Route::delete('/broadcast/{id}', [Settings\Mail\BroadcastController::class, 'destroy'])->name('admin.settings.mail.broadcast.destroy');
        });

        /**
         * Admin authentication settings interface routes.
         *
         * Prefix: /admin/settings/auth
         */
        Route::group(['prefix' => 'auth'], function () {
            Route::get('/', [Settings\Auth\UserController::class, 'index'])->name('admin.settings.auth.user');
            Route::put('/', [Settings\Auth\UserController::class, 'update'])->name('admin.settings.auth.user.update');
        });

        /**
         * Admin captcha settings interface routes.
         *
         * Prefix: /admin/settings/captcha
         */
        Route::group(['prefix' => 'captcha'], function () {
            Route::get('/', [Settings\Captcha\ProviderController::class, 'index'])->name('admin.settings.captcha.provider');
            Route::put('/', [Settings\Captcha\ProviderController::class, 'update'])->name('admin.settings.captcha.provider.update');
            Route::get('/placement', [Settings\Captcha\PlacementController::class, 'index'])->name('admin.settings.captcha.placement');
            Route::put('/placement', [Settings\Captcha\PlacementController::class, 'update'])->name('admin.settings.captcha.placement.update');
        });

        
        /**
         * Admin role and permission settings interface routes.
         *
         * Prefix: /admin/settings/roles
         */
        Route::group(['prefix' => 'roles'], function () {
            Route::get('/', [Settings\RoleController::class, 'index'])->name('admin.settings.roles');
            Route::get('/create', [Settings\RoleController::class, 'create'])->name('admin.settings.roles.create');
            Route::post('/', [Settings\RoleController::class, 'store'])->name('admin.settings.roles.store');
            Route::get('/{id}/edit', [Settings\RoleController::class, 'edit'])->name('admin.settings.roles.edit');
            Route::put('/{id}', [Settings\RoleController::class, 'update'])->name('admin.settings.roles.update');
            Route::delete('/{id}', [Settings\RoleController::class, 'destroy'])->name('admin.settings.roles.destroy');
        });
    });

    /**
     * Admin users interface routes.
     *
     * Prefix: /admin/users
     */
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [Admin\UsersController::class, 'index'])->name('admin.users');
        Route::get('/create', [Admin\UsersController::class, 'create'])->name('admin.users.create');
        Route::post('/create', [Admin\UsersController::class, 'store'])->name('admin.users.store');
        Route::post('/{id}/verify', [Admin\UsersController::class, 'verify'])->name('admin.users.verify');
        Route::get('/{id}/summary', [Users\Edit\SummaryController::class, 'index'])->name('admin.users.summary');
        Route::post('/{id}/impersonate', [Users\Edit\SummaryController::class, 'impersonate'])->name('admin.users.impersonate');
        Route::get('/{id}/profile', [Users\Edit\ProfileController::class, 'index'])->name('admin.users.profile');
        Route::put('/{id}/profile', [Users\Edit\ProfileController::class, 'update'])->name('admin.users.profile.update');
    });

    /**
     * Admin quick search routes.
     *
     * Prefix: /admin/quick-search
     */
    Route::get('/quick-search', [Admin\QuickSearchController::class, 'search'])->name('admin.quick-search');
});
