<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * Seeds initial data into the 'settings' table.
     * This seeder can be used to populate default application settings.
     *
     * @return void
     */
    public function run(): void
    {
        $settings = [
            /**
             * Run seeder of general category.
             *
             * Seeds initial data into the 'general' category.
             */
            ['category' => 'general', 'key' => 'company_name', 'value' => 'Billmora'],
            ['category' => 'general', 'key' => 'company_logo', 'value' => 'https://media.billmora.com/logo/main-invert-small.png'],
            ['category' => 'general', 'key' => 'company_favicon', 'value' => 'https://media.billmora.com/logo/main-bgnone.svg'],
            ['category' => 'general', 'key' => 'company_description', 'value' => 'Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.'],
            ['category' => 'general', 'key' => 'company_portal', 'value' => true],
            ['category' => 'general', 'key' => 'company_date_format', 'value' => 'd/m/Y'],
            ['category' => 'general', 'key' => 'company_language', 'value' => 'en_US'],
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
            ['category' => 'general', 'key' => 'credit_use', 'value' => false],
            ['category' => 'general', 'key' => 'credit_min_deposit', 'value' => 1],
            ['category' => 'general', 'key' => 'credit_max_deposit', 'value' => 1000000],
            ['category' => 'general', 'key' => 'credit_max', 'value' => 10000000],
            ['category' => 'general', 'key' => 'affiliate_use', 'value' => false],
            ['category' => 'general', 'key' => 'affiliate_min_payment', 'value' => 1],
            ['category' => 'general', 'key' => 'affiliate_reward', 'value' => 5],
            ['category' => 'general', 'key' => 'affiliate_discount', 'value' => 5],
            ['category' => 'general', 'key' => 'term_tos', 'value' => true],
            ['category' => 'general', 'key' => 'term_tos_url', 'value' => null],
            ['category' => 'general', 'key' => 'term_tos_content', 'value' => 'Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.'],
            ['category' => 'general', 'key' => 'term_toc', 'value' => false],
            ['category' => 'general', 'key' => 'term_toc_url', 'value' => null],
            ['category' => 'general', 'key' => 'term_toc_content', 'value' => 'Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.'],
            ['category' => 'general', 'key' => 'term_privacy', 'value' => false],
            ['category' => 'general', 'key' => 'term_privacy_url', 'value' => null],
            ['category' => 'general', 'key' => 'term_privacy_content', 'value' => 'Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.'],
            ['category' => 'general', 'key' => 'social_discord', 'value' => null],
            ['category' => 'general', 'key' => 'social_youtube', 'value' => null],
            ['category' => 'general', 'key' => 'social_whatsapp', 'value' => null],
            ['category' => 'general', 'key' => 'social_instagram', 'value' => null],
            ['category' => 'general', 'key' => 'social_facebook', 'value' => null],
            ['category' => 'general', 'key' => 'social_twitter', 'value' => null],
            ['category' => 'general', 'key' => 'social_linkedin', 'value' => null],
            ['category' => 'general', 'key' => 'social_github', 'value' => null],
            ['category' => 'general', 'key' => 'social_reddit', 'value' => null],
            ['category' => 'general', 'key' => 'social_skype', 'value' => null],
            ['category' => 'general', 'key' => 'social_telegram', 'value' => null],
        ];

        /**
         * Insert or update each setting record.
         *
         * If a setting with the same category and key exists, update its value.
         */
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['category' => $setting['category'], 'key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
