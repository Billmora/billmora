<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            'admin' => [],
            'client' => [
                'auth_logo_url' => 'https://media.billmora.com/logo/main-invert-bgnone.png',
                'auth_message_title' => 'Grow your business with Billmora!',
                'auth_message_description' => 'Billmora — short for Billing Management, Operation, and Recurring Automation — is a free, open-source platform built for hosting providers and service businesses that need full control over their billing infrastructure without paying recurring licensing fees.',
                
                'billmora_bg' => '#ffffff',
                'billmora_text' => '#0f172a',
                
                'billmora_1' => '#f4f7fe',
                'billmora_2' => '#eceeff',
                'billmora_3' => '#e0e2ff',
                'billmora_4' => '#d6d9ff',
                'billmora_5' => '#cdd0ff',
                'billmora_6' => '#c3c6ff',

                'billmora_primary_50' => '#f0f0ff',
                'billmora_primary_100' => '#e0e0ff',
                'billmora_primary_200' => '#c2c2ff',
                'billmora_primary_300' => '#9494ff',
                'billmora_primary_400' => '#7b71f9',
                'billmora_primary_500' => '#7267ef',
                'billmora_primary_600' => '#6659e0',
                'billmora_primary_700' => '#5345cc',
                'billmora_primary_800' => '#4338a8',
                'billmora_primary_900' => '#383087',
                'billmora_primary_950' => '#211c4d',
            ],
            'portal' => [],
            'email' => [],
            'invoice' => [],
        ];

        foreach ($themes as $type => $config) {
            Theme::updateOrCreate(
                [
                    'type' => $type, 
                    'provider' => 'moraine'
                ],
                [
                    'name' => 'Moraine',
                    'is_active' => true,
                    'is_core' => true,
                    'config' => $config,
                ]
            );
        }
    }
}
