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
            ['category' => 'general', 'key' => 'ordering_tos', 'value' => true],
            ['category' => 'general', 'key' => 'ordering_notes', 'value' => false],
            ['category' => 'general', 'key' => 'ordering_number_increment', 'value' => 1],
            ['category' => 'general', 'key' => 'ordering_number_padding', 'value' => 4],
            ['category' => 'general', 'key' => 'ordering_number_format', 'value' => 'ORD-{number}'],
            ['category' => 'general', 'key' => 'invoice_pdf', 'value' => false],
            ['category' => 'general', 'key' => 'invoice_pdf_size', 'value' => 'A4'],
            ['category' => 'general', 'key' => 'invoice_number_increment', 'value' => 1],
            ['category' => 'general', 'key' => 'invoice_number_padding', 'value' => 4],
            ['category' => 'general', 'key' => 'invoice_number_format', 'value' => 'INV-{number}'],
            ['category' => 'general', 'key' => 'credit_use', 'value' => false],
            ['category' => 'general', 'key' => 'credit_min_deposit', 'value' => 1],
            ['category' => 'general', 'key' => 'credit_max_deposit', 'value' => 1000000],
            ['category' => 'general', 'key' => 'credit_max', 'value' => 10000000],
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
            ['category' => 'general', 'key' => 'misc_admin_pagination', 'value' => 25],
            ['category' => 'general', 'key' => 'misc_client_pagination', 'value' => 15],

            /**
             * Run seeder of authentication category.
             *
             * Seeds initial data into the 'auth' category.
             */
            ['category' => 'auth', 'key' => 'user_registration', 'value' => true],
            ['category' => 'auth', 'key' => 'user_require_verified', 'value' => false],
            ['category' => 'auth', 'key' => 'user_require_two_factor', 'value' => false],
            ['category' => 'auth', 'key' => 'user_registration_disabled_inputs', 'value' => []],
            ['category' => 'auth', 'key' => 'user_billing_required_inputs', 'value' => ["street_address_1", "city", "state", "postcode", "country"]],

            /**
             * Run seeder of captcha category.
             *
             * Seeds initial data into the 'captcha' category.
             */
            ['category' => 'captcha', 'key' => 'provider_type', 'value' => null],
            ['category' => 'captcha', 'key' => 'placements_enabled_forms', 'value' => []],

            /**
             * Run seeder of ticket category.
             *
             * Seeds initial data into the 'ticket' category.
             */
            ['category' => 'ticket', 'key' => 'ticketing_departments', 'value' => ['billing', 'support', 'sales']],
            ['category' => 'ticket', 'key' => 'ticketing_allow_client_close', 'value' => true],
            ['category' => 'ticket', 'key' => 'ticketing_number_increment', 'value' => 1],
            ['category' => 'ticket', 'key' => 'ticketing_number_padding', 'value' => 4],
            ['category' => 'ticket', 'key' => 'ticketing_number_format', 'value' => 'TKT-{number}'],
            ['category' => 'ticket', 'key' => 'ticketing_max_attachment_size', 'value' => 10],
            ['category' => 'ticket', 'key' => 'ticketing_allowed_attachment_types', 'value' => 'jpg,jpeg,png,doc,docx'],
            ['category' => 'ticket', 'key' => 'piping_enabled', 'value' => false],
            ['category' => 'ticket', 'key' => 'notify_client_on_open', 'value' => true],
            ['category' => 'ticket', 'key' => 'notify_client_on_staff_open', 'value' => true],
            ['category' => 'ticket', 'key' => 'notify_client_on_staff_answered', 'value' => true],
            ['category' => 'ticket', 'key' => 'notify_staff_on_client_reply', 'value' => true],
            ['category' => 'ticket', 'key' => 'notify_staff_fallback', 'value' => 'department'],

            /**
             * Run seeder of automation category.
             *
             * Seeds initial data into the 'automation' category.
             */
            ['category' => 'automation', 'key' => 'time_of_day', 'value' => '00:00'],
            ['category' => 'automation', 'key' => 'last_run', 'value' => null],
            ['category' => 'automation', 'key' => 'prune_email_history_days', 'value' => 30],
            ['category' => 'automation', 'key' => 'prune_user_activity_days', 'value' => 30],
            ['category' => 'automation', 'key' => 'prune_system_logs_days', 'value' => 30],
            ['category' => 'automation', 'key' => 'invoice_generation_days', 'value' => 7],
            ['category' => 'automation', 'key' => 'invoice_reminder_days', 'value' => 3],
            ['category' => 'automation', 'key' => 'invoice_overdue_first_days', 'value' => 1],
            ['category' => 'automation', 'key' => 'invoice_overdue_second_days', 'value' => 3],
            ['category' => 'automation', 'key' => 'invoice_overdue_third_days', 'value' => 5],
            ['category' => 'automation', 'key' => 'invoice_auto_cancel_days', 'value' => 14],
            ['category' => 'automation', 'key' => 'service_suspend_days', 'value' => 3],
            ['category' => 'automation', 'key' => 'service_terminate_days', 'value' => 7],
            ['category' => 'automation', 'key' => 'auto_accept_cancellation', 'value' => true],
            ['category' => 'automation', 'key' => 'ticket_close_days', 'value' => 7],
            ['category' => 'automation', 'key' => 'prune_ticket_attachments_days', 'value' => 0],
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
