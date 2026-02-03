<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\Audits;
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
     * Admin users interface routes.
     *
     * Prefix: /admin/users
     */
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [Admin\UsersController::class, 'index'])->name('admin.users');
        Route::get('/create', [Admin\UsersController::class, 'create'])->name('admin.users.create');
        Route::post('/create', [Admin\UsersController::class, 'store'])->name('admin.users.store');
        Route::post('/{id}/verify', [Admin\UsersController::class, 'verify'])->name('admin.users.verify');
        Route::get('/{id}/summary', [Users\SummaryController::class, 'index'])->name('admin.users.summary');
        Route::post('/{id}/impersonate', [Users\SummaryController::class, 'impersonate'])->name('admin.users.impersonate');
        Route::get('/{id}/profile', [Users\ProfileController::class, 'index'])->name('admin.users.profile');
        Route::put('/{id}/profile', [Users\ProfileController::class, 'update'])->name('admin.users.profile.update');
        Route::delete('/{id}', [Admin\UsersController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/{id}/activity', [Users\ActivityController::class, 'index'])->name('admin.users.activity');
        Route::get('/{id}/activity/{activity}', [Users\ActivityController::class, 'show'])->name('admin.users.activity.show');
        Route::post('/{id}/activity/export', [Users\ActivityController::class, 'export'])->name('admin.users.activity.export');
        Route::post('/{id}/activity/clear', [Users\ActivityController::class, 'clear'])->name('admin.users.activity.clear');
    });

    /**
     * Admin invoices interface routes.
     *
     * Prefix: /admin/invoices
     */
    Route::group(['prefix' => 'invoices'], function () {
        Route::get('/', [Admin\InvoicesController::class, 'index'])->name('admin.invoices');
        Route::get('/create', [Admin\InvoicesController::class, 'create'])->name('admin.invoices.create');
        Route::post('/create', [Admin\InvoicesController::class, 'store'])->name('admin.invoices.store');
        Route::get('/{invoice:invoice_number}/edit', [Admin\InvoicesController::class, 'edit'])->name('admin.invoices.edit');
        Route::put('/{invoice:invoice_number}/edit', [Admin\InvoicesController::class, 'update'])->name('admin.invoices.update');
        Route::delete('/{invoice:invoice_number}', [Admin\InvoicesController::class, 'destroy'])->name('admin.invoices.destroy');
        Route::get('/{invoice:invoice_number}/download', [Admin\InvoicesController::class, 'download'])->name('admin.invoices.download');
    });

    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [Admin\OrdersController::class, 'index'])->name('admin.orders');
        Route::get('/create', [Admin\OrdersController::class, 'create'])->name('admin.orders.create');
        Route::post('/create', [Admin\OrdersController::class, 'store'])->name('admin.orders.store');
        Route::get('/{order:order_number}/edit', [Admin\OrdersController::class, 'edit'])->name('admin.orders.edit');
        Route::put('/{order:order_number}/edit', [Admin\OrdersController::class, 'update'])->name('admin.orders.update');
    });

    /**
     * Admin catalogs interface routes.
     *
     * Prefix: /admin/catalogs
     */
    Route::group(['prefix' => 'catalogs'], function () {
        Route::get('/', [Admin\CatalogsController::class, 'index'])->name('admin.catalogs');
        Route::get('/create', [Admin\CatalogsController::class, 'create'])->name('admin.catalogs.create');
        Route::post('/create', [Admin\CatalogsController::class, 'store'])->name('admin.catalogs.store');
        Route::get('/{id}/edit', [Admin\CatalogsController::class, 'edit'])->name('admin.catalogs.edit');
        Route::put('/{id}/edit', [Admin\CatalogsController::class, 'update'])->name('admin.catalogs.update');
        Route::delete('/{id}', [Admin\CatalogsController::class, 'destroy'])->name('admin.catalogs.destroy');
    });

    /**
     * Admin packages interface routes.
     *
     * Prefix: /admin/packages
     */
    Route::group(['prefix' => 'packages'], function () {
        Route::get('/', [Admin\PackagesController::class, 'index'])->name('admin.packages');
        Route::get('/create', [Admin\PackagesController::class, 'create'])->name('admin.packages.create');
        Route::post('/create', [Admin\PackagesController::class, 'store'])->name('admin.packages.store');
        Route::get('/{id}/edit', [Admin\PackagesController::class, 'edit'])->name('admin.packages.edit');
        Route::put('/{id}/edit', [Admin\PackagesController::class, 'update'])->name('admin.packages.update');
        Route::get('/{id}/pricing', [Admin\Packages\PricingController::class, 'index'])->name('admin.packages.pricing');
        Route::get('/{id}/pricing/create', [Admin\Packages\PricingController::class, 'create'])->name('admin.packages.pricing.create');
        Route::post('/{id}/pricing/create', [Admin\Packages\PricingController::class, 'store'])->name('admin.packages.pricing.store');
        Route::get('/{id}/pricing/{pricing:id}/edit', [Admin\Packages\PricingController::class, 'edit'])->name('admin.packages.pricing.edit');
        Route::put('/{id}/pricing/{pricing:id}/edit', [Admin\Packages\PricingController::class, 'update'])->name('admin.packages.pricing.update');
        Route::delete('/{id}/pricing/{pricing:id}', [Admin\Packages\PricingController::class, 'destroy'])->name('admin.packages.pricing.destroy');
        Route::delete('/{id}', [Admin\PackagesController::class, 'destroy'])->name('admin.packages.destroy');
    });

    /**
     * Admin variants interface routes.
     *
     * Prefix: /admin/variants
     */
    Route::group(['prefix' => 'variants'], function () {
        Route::get('/', [Admin\VariantsController::class, 'index'])->name('admin.variants');
        Route::get('/create', [Admin\VariantsController::class, 'create'])->name('admin.variants.create');
        Route::post('/create', [Admin\VariantsController::class, 'store'])->name('admin.variants.store');
        Route::get('/{id}/edit', [Admin\VariantsController::class, 'edit'])->name('admin.variants.edit');
        Route::put('/{id}/edit', [Admin\VariantsController::class, 'update'])->name('admin.variants.update');
        Route::delete('/{id}', [Admin\VariantsController::class, 'destroy'])->name('admin.variants.destroy');
        Route::get('/{id}/options', [Admin\Variants\OptionController::class, 'index'])->name('admin.variants.options');
        Route::get('/{id}/options/create', [Admin\Variants\OptionController::class, 'create'])->name('admin.variants.options.create');
        Route::post('/{id}/options/create', [Admin\Variants\OptionController::class, 'store'])->name('admin.variants.options.store');
        Route::get('/{id}/options/{option:id}/edit', [Admin\Variants\OptionController::class, 'edit'])->name('admin.variants.options.edit');
        Route::put('/{id}/options/{option:id}/edit', [Admin\Variants\OptionController::class, 'update'])->name('admin.variants.options.update');
        Route::delete('/{id}/{option:id}', [Admin\Variants\OptionController::class, 'destroy'])->name('admin.variants.options.destroy');
    });

    /**
     * Admin coupons interface routes.
     *
     * Prefix: /admin/coupons
     */
    Route::group(['prefix' => 'coupons'], function () {
        Route::get('/', [Admin\CouponsController::class, 'index'])->name('admin.coupons');
        Route::get('/create', [Admin\CouponsController::class, 'create'])->name('admin.coupons.create');
        Route::post('/create', [Admin\CouponsController::class, 'store'])->name('admin.coupons.store');
        Route::get('/{id}/edit', [Admin\CouponsController::class, 'edit'])->name('admin.coupons.edit');
        Route::put('/{id}/edit', [Admin\CouponsController::class, 'update'])->name('admin.coupons.update');
        Route::delete('/{id}', [Admin\CouponsController::class, 'destroy'])->name('admin.coupons.destroy');
    });

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
            Route::get('/history', [Settings\Mail\HistoryController::class, 'index'])->name('admin.settings.mail.history');
            Route::get('/history/{id}', [Settings\Mail\HistoryController::class, 'show'])->name('admin.settings.mail.history.show');
            Route::get('/history/{id}/preview', [Settings\Mail\HistoryController::class, 'preview'])->name('admin.settings.mail.history.preview');
            Route::post('/history/export', [Settings\Mail\HistoryController::class, 'export'])->name('admin.settings.mail.history.export');
            Route::post('/history/clear', [Settings\Mail\HistoryController::class, 'clear'])->name('admin.settings.mail.history.clear');
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

        /**
         * Admin currency settings interface routes.
         *
         * Prefix: /admin/settings/currency
         */
        Route::group(['prefix' => 'currencies'], function () {
            Route::get('/', [Settings\CurrencyController::class, 'index'])->name('admin.settings.currencies');
            Route::get('/create', [Settings\CurrencyController::class, 'create'])->name('admin.settings.currencies.create');
            Route::post('/', [Settings\CurrencyController::class, 'store'])->name('admin.settings.currencies.store');
            Route::get('/{id}/edit', [Settings\CurrencyController::class, 'edit'])->name('admin.settings.currencies.edit');
            Route::post('/{id}', [Settings\CurrencyController::class, 'update'])->name('admin.settings.currencies.update');
            Route::delete('/{id}', [Settings\CurrencyController::class, 'destroy'])->name('admin.settings.currencies.destroy');
        });
    });

    /**
     * Admin audits interface routes.
     *
     * Prefix: /admin/audits
     */
    Route::group(['prefix' => 'audits'], function () {
        Route::get('/', [Admin\AuditsController::class, 'index'])->name('admin.audits');

        /**
         * Admin system audits interface routes.
         *
         * Prefix: /admin/audits/system
         */
        Route::group(['prefix' => 'system'], function () {
            Route::get('/', [Audits\SystemController::class, 'index'])->name('admin.audits.system');
            Route::get('/{id}', [Audits\SystemController::class, 'show'])->name('admin.audits.system.show');
            Route::post('/export', [Audits\SystemController::class, 'export'])->name('admin.audits.system.export');
            Route::post('/clear', [Audits\SystemController::class, 'clear'])->name('admin.audits.system.clear');
        });
    });

    /**
     * Admin quick search routes.
     *
     * Prefix: /admin/quick-search
     */
    Route::get('/quick-search', [Admin\QuickSearchController::class, 'search'])->name('admin.quick-search');
});
