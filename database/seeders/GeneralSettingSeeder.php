<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['category' => 'general', 'key' => 'company_name', 'value' => 'Billmora'],
            ['category' => 'general', 'key' => 'company_portal_theme', 'value' => 'default'],
            ['category' => 'general', 'key' => 'company_client_theme', 'value' => 'default'],
            ['category' => 'general', 'key' => 'company_logo', 'value' => 'https://media.billmora.com/billmora-icon.svg'],
            ['category' => 'general', 'key' => 'company_favicon', 'value' => 'https://media.billmora.com/billmora-icon.svg'],
            ['category' => 'general', 'key' => 'company_description', 'value' => 'Free and Open source Billing Management Operations & Recurring Automation.'],
            ['category' => 'general', 'key' => 'company_portal', 'value' => true],
            ['category' => 'general', 'key' => 'company_date_format', 'value' => 'd/m/Y'],
            ['category' => 'general', 'key' => 'company_maintenance', 'value' => false],
            ['category' => 'general', 'key' => 'company_maintenance_url', 'value' => null],
            ['category' => 'general', 'key' => 'company_maintenance_message', 'value' => 'We are currently performing maintenance and will be back shortly.'],
            ['category' => 'general', 'key' => 'ordering_redirect', 'value' => 'payment'],
            ['category' => 'general', 'key' => 'ordering_grace', 'value' => 0],
            ['category' => 'general', 'key' => 'ordering_tos', 'value' => true],
            ['category' => 'general', 'key' => 'ordering_notes', 'value' => false],
            ['category' => 'general', 'key' => 'invoice_pdf', 'value' => false],
            ['category' => 'general', 'key' => 'invoice_pdf_size', 'value' => 'A4'],
            ['category' => 'general', 'key' => 'invoice_pdf_font', 'value' => 'Plus Jakarta Sans'],
            ['category' => 'general', 'key' => 'invoice_mass_payment', 'value' => true],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['category' => $setting['category'], 'key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
