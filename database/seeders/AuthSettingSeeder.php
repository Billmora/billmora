<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuthSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['category' => 'auth', 'key' => 'user_verified', 'value' => true],
            ['category' => 'auth', 'key' => 'form_disable', 'value' => []],
            ['category' => 'auth', 'key' => 'form_required', 'value' => ["street_address_1", "city", "country", "state", "postcode"]],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['category' => $setting['category'], 'key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
