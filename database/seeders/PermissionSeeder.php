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
            'settings.general.view',
            'settings.general.update',
            'settings.mail.view',
            'settings.mail.update',
            'settings.mail.template.view',
            'settings.mail.template.update',
            'settings.mail.broadcast.view',
            'settings.mail.broadcast.create',
            'settings.mail.broadcast.update',
            'settings.mail.broadcast.delete',
            'settings.roles.view',
            'settings.roles.create',
            'settings.roles.update',
            'settings.roles.delete',
        ];

        /**
         * Insert or update each permission record.
         */
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
