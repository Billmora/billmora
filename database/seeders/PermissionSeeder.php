<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Run seeder of permission.
         *
         * Seeds initial data into the 'permission'.
         */
        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.impersonate',
            'orders.view',
            'orders.create',
            'orders.update',
            'orders.delete',
            'services.view',
            'services.update',
            'services.delete',
            'services.cancellations.view',
            'services.cancellations.approve',
            'services.cancellations.reject',
            'services.cancellations.delete',
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'transactions.view',
            'transactions.create',
            'transactions.delete',
            'broadcasts.view',
            'broadcasts.create',
            'broadcasts.update',
            'broadcasts.delete',
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
            'tickets.reply',
            'catalogs.view',
            'catalogs.create',
            'catalogs.update',
            'catalogs.delete',
            'packages.view',
            'packages.create',
            'packages.update',
            'packages.delete',
            'variants.view',
            'variants.create',
            'variants.update',
            'variants.delete',
            'coupons.view',
            'coupons.create',
            'coupons.update',
            'coupons.delete',
            'provisionings.view',
            'provisionings.create',
            'provisionings.update',
            'provisionings.delete',
            'gateways.view',
            'gateways.create',
            'gateways.update',
            'gateways.delete',
            'modules.view',
            'modules.create',
            'modules.update',
            'modules.delete',
            'settings.general.view',
            'settings.general.update',
            'settings.mail.view',
            'settings.mail.update',
            'settings.auth.view',
            'settings.auth.update',
            'settings.captcha.view',
            'settings.captcha.update',
            'settings.roles.view',
            'settings.roles.create',
            'settings.roles.update',
            'settings.roles.delete',
            'settings.currencies.view',
            'settings.currencies.create',
            'settings.currencies.update',
            'settings.currencies.delete',
            'settings.ticket.view',
            'settings.ticket.update',
            'settings.automation.view',
            'settings.automation.update',
            'plugins.view',
            'plugins.install',
            'plugins.update',
            'plugins.uninstall',
            'tasks.view',
            'tasks.retry',
            'tasks.dismiss',
            'audit.email.history.view',
            'audit.email.history.export',
            'audit.email.history.delete',
            'audit.user.activity.view',
            'audit.user.activity.export',
            'audit.user.activity.delete',
            'audit.system.logs.view',
            'audit.system.logs.export',
            'audit.system.logs.delete',
        ];

        /**
         * Insert or update each permission record.
         */
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
