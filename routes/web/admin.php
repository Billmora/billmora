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
        Route::get('/{id}/activity', [Admin\Audits\UserController::class, 'index'])->name('admin.users.activity');
        Route::get('/{id}/activity/{activity}', [Admin\Audits\UserController::class, 'show'])->name('admin.users.activity.show');
        Route::post('/{id}/activity/export', [Admin\Audits\UserController::class, 'export'])->name('admin.users.activity.export');
        Route::post('/{id}/activity/clear', [Admin\Audits\UserController::class, 'clear'])->name('admin.users.activity.clear');
    });

    /**
     * Admin orders interface routes.
     *
     * Prefix: /admin/orders
     */
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [Admin\OrdersController::class, 'index'])->name('admin.orders');
        Route::get('/create', [Admin\OrdersController::class, 'create'])->name('admin.orders.create');
        Route::post('/create', [Admin\OrdersController::class, 'store'])->name('admin.orders.store');
        Route::get('/{order:order_number}/edit', [Admin\OrdersController::class, 'edit'])->name('admin.orders.edit');
        Route::put('/{order:order_number}/edit', [Admin\OrdersController::class, 'update'])->name('admin.orders.update');
        Route::delete('/{order:order_number}', [Admin\OrdersController::class, 'destroy'])->name('admin.orders.destroy');
    });

    /**
     * Admin services interface routes.
     *
     * Prefix: /admin/services
     */
    Route::group(['prefix' => 'services'], function () {
        Route::get('/', [Admin\ServicesController::class, 'index'])->name('admin.services');
        Route::get('/{service}/edit', [Admin\ServicesController::class, 'edit'])->name('admin.services.edit');
        Route::put('/{service}/edit', [Admin\ServicesController::class, 'update'])->name('admin.services.update');
        Route::delete('/{service}', [Admin\ServicesController::class, 'destroy'])->name('admin.services.destroy');

        Route::get('/cancellation', [Admin\Services\CancellationController::class, 'index'])->name('admin.services.cancellations');
        Route::get('/cancellation/{cancellation}/edit', [Admin\Services\CancellationController::class, 'edit'])->name('admin.services.cancellations.edit');
        Route::post('/cancellation/{cancellation}/approve', [Admin\Services\CancellationController::class, 'approve'])->name('admin.services.cancellations.approve');
        Route::post('/cancellation/{cancellation}/reject', [Admin\Services\CancellationController::class, 'reject'])->name('admin.services.cancellations.reject');
        Route::delete('/cancellation/{cancellation}', [Admin\Services\CancellationController::class, 'destroy'])->name('admin.services.cancellations.destroy');

        Route::post('/{service}/create', [Admin\Services\ProvisioningController::class, 'create'])->name('admin.services.create');
        Route::post('/{service}/suspend', [Admin\Services\ProvisioningController::class, 'suspend'])->name('admin.services.suspend');
        Route::post('/{service}/unsuspend', [Admin\Services\ProvisioningController::class, 'unsuspend'])->name('admin.services.unsuspend');
        Route::post('/{service}/terminate', [Admin\Services\ProvisioningController::class, 'terminate'])->name('admin.services.terminate');
        Route::post('/{service}/renew', [Admin\Services\ProvisioningController::class, 'renew'])->name('admin.services.renew');
        Route::post('/{service}/scale', [Admin\Services\ProvisioningController::class, 'scale'])->name('admin.services.scale');
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

        Route::get('/{invoice:invoice_number}/transaction', [Admin\Invoices\TransactionController::class, 'index'])->name('admin.invoices.transaction');
        Route::get('/{invoice:invoice_number}/transaction/create', [Admin\Invoices\TransactionController::class, 'create'])->name('admin.invoices.transaction.create');
        Route::post('/{invoice:invoice_number}/transaction/create', [Admin\Invoices\TransactionController::class, 'store'])->name('admin.invoices.transaction.store');
        Route::delete('/{invoice:invoice_number}/transaction/{transaction}', [Admin\Invoices\TransactionController::class, 'destroy'])->name('admin.invoices.transaction.destroy');

        Route::get('/{invoice:invoice_number}/refund', [Admin\Invoices\RefundController::class, 'index'])->name('admin.invoices.refund');
        Route::post('/{invoice:invoice_number}/refund', [Admin\Invoices\RefundController::class, 'store'])->name('admin.invoices.refund.store');
    });

    /**
     * Admin transactions interface routes.
     *
     * Prefix: /admin/transactions
     */
    Route::group(['prefix' => 'transactions'], function () {
        Route::get('/', [Admin\TransactionsController::class, 'index'])->name('admin.transactions');
        Route::get('/create', [Admin\TransactionsController::class, 'create'])->name('admin.transactions.create');
        Route::post('/create', [Admin\TransactionsController::class, 'store'])->name('admin.transactions.store');
        Route::delete('/{transaction}', [Admin\TransactionsController::class, 'destroy'])->name('admin.transactions.destroy');
    });

    /**
     * Admin broadcasts interface routes.
     *
     * Prefix: /admin/broadcasts
     */
    Route::group(['prefix' => 'broadcasts'], function () {
        Route::get('/', [Admin\BroadcastsController::class, 'index'])->name('admin.broadcasts');
        Route::get('/create', [Admin\BroadcastsController::class, 'create'])->name('admin.broadcasts.create');
        Route::post('/create', [Admin\BroadcastsController::class, 'store'])->name('admin.broadcasts.store');
        Route::get('/{id}/edit', [Admin\BroadcastsController::class, 'edit'])->name('admin.broadcasts.edit');
        Route::put('/{id}/edit', [Admin\BroadcastsController::class, 'update'])->name('admin.broadcasts.update');
        Route::delete('/{id}', [Admin\BroadcastsController::class, 'destroy'])->name('admin.broadcasts.destroy');
    });

    /**
     * Admin tickets interface routes.
     *
     * Prefix: /admin/tickets
     */
    Route::group(['prefix' => 'tickets'], function () {
        Route::get('/', [Admin\TicketsController::class, 'index'])->name('admin.tickets');
        Route::get('/create', [Admin\TicketsController::class, 'create'])->name('admin.tickets.create');
        Route::post('/create', [Admin\TicketsController::class, 'store'])->name('admin.tickets.store');
        Route::get('/{ticket:ticket_number}/edit', [Admin\TicketsController::class, 'edit'])->name('admin.tickets.edit');
        Route::put('/{ticket:ticket_number}/edit', [Admin\TicketsController::class, 'update'])->name('admin.tickets.update');
        Route::patch('/{ticket:ticket_number}', [Admin\TicketsController::class, 'close'])->name('admin.tickets.close');
        Route::delete('/{ticket:ticket_number}', [Admin\TicketsController::class, 'destroy'])->name('admin.tickets.destroy');

        Route::get('/{ticket:ticket_number}/reply', [Admin\Tickets\ReplyController::class, 'index'])->name('admin.tickets.reply');
        Route::post('/{ticket:ticket_number}/reply', [Admin\Tickets\ReplyController::class, 'send'])->name('admin.tickets.reply.send');
        Route::delete('/{ticket:ticket_number}/reply/{message}', [Admin\Tickets\ReplyController::class, 'destroy'])->name('admin.tickets.reply.destroy');
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

        Route::get('/{id}/provisioning', [Admin\Packages\ProvisioningController::class, 'index'])->name('admin.packages.provisioning');
        Route::put('/{id}/provisioning', [Admin\Packages\ProvisioningController::class, 'update'])->name('admin.packages.provisioning.update');

        Route::get('/{package}/scaling', [Admin\Packages\ScalingController::class, 'index'])->name('admin.packages.scaling');
        Route::put('/{package}/scaling', [Admin\Packages\ScalingController::class, 'update'])->name('admin.packages.scaling.update');

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
     * Admin provisionings interface routes.
     *
     * Prefix: /admin/provisionings
     */
    Route::group(['prefix' => 'provisionings'], function () {
        Route::get('/', [Admin\ProvisioningsController::class, 'index'])->name('admin.provisionings');
        Route::get('/create', [Admin\ProvisioningsController::class, 'create'])->name('admin.provisionings.create');
        Route::post('/create', [Admin\ProvisioningsController::class, 'store'])->name('admin.provisionings.store');
        Route::get('/{provisioning}/edit', [Admin\ProvisioningsController::class, 'edit'])->name('admin.provisionings.edit');
        Route::put('/{provisioning}/edit', [Admin\ProvisioningsController::class, 'update'])->name('admin.provisionings.update');
        Route::post('/{provisioning}/test', [Admin\ProvisioningsController::class, 'testConnection'])->name('admin.provisionings.test');
        Route::delete('/{provisioning}', [Admin\ProvisioningsController::class, 'destroy'])->name('admin.provisionings.destroy');
    });

    /**
     * Admin gateways interface routes.
     *
     * Prefix: /admin/gateways
     */
    Route::group(['prefix' => 'gateways'], function () {
        Route::get('/', [Admin\GatewaysController::class, 'index'])->name('admin.gateways');
        Route::get('/create', [Admin\GatewaysController::class, 'create'])->name('admin.gateways.create');
        Route::post('/create', [Admin\GatewaysController::class, 'store'])->name('admin.gateways.store');
        Route::get('/{gateway}/edit', [Admin\GatewaysController::class, 'edit'])->name('admin.gateways.edit');
        Route::put('/{gateway}/edit', [Admin\GatewaysController::class, 'update'])->name('admin.gateways.update');
        Route::delete('/{gateway}', [Admin\GatewaysController::class, 'destroy'])->name('admin.gateways.destroy');
    });

    /**
     * Admin modules interface routes.
     *
     * Prefix: /admin/modules
     */
    Route::group(['prefix' => 'modules'], function () {
        Route::get('/', [Admin\ModulesControler::class, 'index'])->name('admin.modules');
        Route::get('/create', [Admin\ModulesControler::class, 'create'])->name('admin.modules.create');
        Route::post('/create', [Admin\ModulesControler::class, 'store'])->name('admin.modules.store');
        Route::get('/{module}/edit', [Admin\ModulesControler::class, 'edit'])->name('admin.modules.edit');
        Route::put('/{module}/edit', [Admin\ModulesControler::class, 'update'])->name('admin.modules.update');
        Route::delete('/{module}', [Admin\ModulesControler::class, 'destroy'])->name('admin.modules.destroy');
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
            Route::patch('/', [Settings\General\CompanyController::class, 'update'])->name('admin.settings.general.company.update');
            Route::get('/ordering', [Settings\General\OrderingController::class, 'index'])->name('admin.settings.general.ordering');
            Route::patch('/ordering', [Settings\General\OrderingController::class, 'update'])->name('admin.settings.general.ordering.update');
            Route::get('/invoice', [Settings\General\InvoiceController::class, 'index'])->name('admin.settings.general.invoice');
            Route::patch('/invoice', [Settings\General\InvoiceController::class, 'update'])->name('admin.settings.general.invoice.update');
            Route::get('/credit', [Settings\General\CreditController::class, 'index'])->name('admin.settings.general.credit');
            Route::patch('/credit', [Settings\General\CreditController::class, 'update'])->name('admin.settings.general.credit.update');
            Route::get('/affiliate', [Settings\General\AffiliateController::class, 'index'])->name('admin.settings.general.affiliate');
            Route::patch('/affiliate', [Settings\General\AffiliateController::class, 'update'])->name('admin.settings.general.affiliate.update');
            Route::get('/term', [Settings\General\TermController::class, 'index'])->name('admin.settings.general.term');
            Route::patch('/term', [Settings\General\TermController::class, 'update'])->name('admin.settings.general.term.update');
            Route::get('/social', [Settings\General\SocialController::class, 'index'])->name('admin.settings.general.social');
            Route::patch('/social', [Settings\General\SocialController::class, 'update'])->name('admin.settings.general.social.update');
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
            Route::get('/notification', [Settings\Mail\NotificationController::class, 'index'])->name('admin.settings.mail.notification');
            Route::get('/notification/{id}/edit', [Settings\Mail\NotificationController::class, 'edit'])->name('admin.settings.mail.notification.edit');
            Route::put('/notification/{id}/edit', [Settings\Mail\NotificationController::class, 'update'])->name('admin.settings.mail.update');
        });

        /**
         * Admin authentication settings interface routes.
         *
         * Prefix: /admin/settings/auth
         */
        Route::group(['prefix' => 'auth'], function () {
            Route::get('/', [Settings\Auth\UserController::class, 'index'])->name('admin.settings.auth.user');
            Route::patch('/', [Settings\Auth\UserController::class, 'update'])->name('admin.settings.auth.user.update');
        });

        /**
         * Admin captcha settings interface routes.
         *
         * Prefix: /admin/settings/captcha
         */
        Route::group(['prefix' => 'captcha'], function () {
            Route::get('/', [Settings\Captcha\ProviderController::class, 'index'])->name('admin.settings.captcha.provider');
            Route::patch('/', [Settings\Captcha\ProviderController::class, 'update'])->name('admin.settings.captcha.provider.update');
            Route::get('/placement', [Settings\Captcha\PlacementController::class, 'index'])->name('admin.settings.captcha.placement');
            Route::patch('/placement', [Settings\Captcha\PlacementController::class, 'update'])->name('admin.settings.captcha.placement.update');
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

        /**
         * Admin ticket settings interface routes.
         *
         * Prefix: /admin/settings/ticket
         */
        Route::group(['prefix' => 'ticket'], function () {
            Route::get('/', [Settings\Ticket\TicketingController::class, 'index'])->name('admin.settings.ticket.ticketing');
            Route::patch('/', [Settings\Ticket\TicketingController::class, 'update'])->name('admin.settings.ticket.ticketing.update');
            Route::get('/piping', [Settings\Ticket\PipingController::class, 'index'])->name('admin.settings.ticket.piping');
            Route::patch('/piping', [Settings\Ticket\PipingController::class, 'update'])->name('admin.settings.ticket.piping.update');
            Route::get('/notify', [Settings\Ticket\NotifyController::class, 'index'])->name('admin.settings.ticket.notify');
            Route::patch('/notify', [Settings\Ticket\NotifyController::class, 'update'])->name('admin.settings.ticket.notify.update');
        });
    });

    /**
     * Admin plugins interface routes.
     *
     * Prefix: /admin/plugins
     */
    Route::group(['prefix' => 'plugins'], function () {
        Route::get('/', [Admin\PluginsController::class, 'index'])->name('admin.plugins');
        Route::post('/install', [Admin\PluginsController::class, 'install'])->name('admin.plugins.install');
        Route::post('/{identifier}/update', [Admin\PluginsController::class, 'update'])->name('admin.plugins.update');
        Route::delete('/{identifier}/uninstall', [Admin\PluginsController::class, 'uninstall'])->name('admin.plugins.uninstall');
    });

    /**
     * Admin audits interface routes.
     *
     * Prefix: /admin/audits
     */
    Route::group(['prefix' => 'audits'], function () {
        Route::get('/', [Admin\AuditsController::class, 'index'])->name('admin.audits');

        /**
         * Admin email audits interface routes.
         *
         * Prefix: /admin/audits/email
         */
        Route::group(['prefix' => 'email'], function () {
            Route::get('/', [Audits\EmailController::class, 'index'])->name('admin.audits.email');
            Route::get('/{id}', [Audits\EmailController::class, 'show'])->name('admin.audits.email.show');
            Route::get('/{id}/preview', [Audits\EmailController::class, 'preview'])->name('admin.audits.email.preview');
            Route::post('/export', [Audits\EmailController::class, 'export'])->name('admin.audits.email.export');
            Route::post('/clear', [Audits\EmailController::class, 'clear'])->name('admin.audits.email.clear');
        });

        /**
         * Admin user audits interface routes.
         *
         * Prefix: /admin/audits/user
         */
        Route::group(['prefix' => 'user'], function () {
            Route::get('/', [Audits\UserController::class, 'index'])->name('admin.audits.user');
            Route::post('/export', [Audits\UserController::class, 'export'])->name('admin.audits.user.export');
        });

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
