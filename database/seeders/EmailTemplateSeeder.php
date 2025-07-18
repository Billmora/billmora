<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'test_message',
                'name' => 'Test Message',
                'subject' => 'Welcome to Billmora!',
                'body' => <<<'BODY'
                <p>Hello, {name}!</p>
                <p>This is a test email to verify the configuration.</p>
                {signature}
                BODY,
                'placeholder' => [
                    'name' => 'Client name',
                    'signature' => 'Global signature',
                ],
            ],
            [
                'key' => 'user_registration',
                'name' => 'User Registration',
                'subject' => 'Account has been created, verify your email',
                'body' => <<<'BODY'
                <p>Hello, {name}!</p>
                <p>Welcome to {company_name} and thank you for registering with us!</p>

                <p>Please click on the link below to verify your email address. This is required to confirm ownership of the email address.</p>
                <a href="{verify_url}" target="_blank">Verify Email</a>

                <p>If you're having trouble, try copying and pasting the following URL into your browser:</p>
                <a href="{verify_url}" target="_blank">{verify_url}</a>

                <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{company_url}" target="_blank">Client Area</a> to request a new link.</p>

                {signature}
                BODY,
                'placeholder' => [
                    'name' => 'Client name',
                    'signature' => 'Global signature',
                    'company_name' => 'Name of company',
                    'company_url' => 'URL of company',
                    'verify_url' => 'Email verification URL',
                ],
            ],
            [
                'key' => 'user_resend_verification',
                'name' => 'User Resend Verification',
                'subject' => 'Verify your email address',
                'body' => <<<'BODY'
                <p>Hello, {name}!</p>

                <p>Please click on the link below to verify your email address. This is required to confirm ownership of the email address.</p>
                <a href="{verify_url}" target="_blank">Verify Email</a>

                <p>If you're having trouble, try copying and pasting the following URL into your browser:</p>
                <a href="{verify_url}" target="_blank">{verify_url}</a>

                <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{company_url}" target="_blank">Client Area</a> to request a new link.</p>

                {signature}
                BODY,
                'placeholder' => [
                    'name' => 'Client name',
                    'signature' => 'Global signature',
                    'company_name' => 'Name of company',
                    'company_url' => 'URL of company',
                    'verify_url' => 'Email verification URL',
                ],
            ],
            [
                'key' => 'user_password_reset',
                'name' => 'User Password Reset',
                'subject' => 'Reset Your Password',
                'body' => <<<'BODY'
                <p>Hello, {name}!</p>

                <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
                
                <p>Please click the link below to set a new password.</p>
                <a href="{reset_url}" target="_blank">Reset Password</a>

                <p>If you're having trouble, try copying and pasting the following URL into your browser:</p>
                <a href="{reset_url}" target="_blank">{reset_url}</a>


                <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{company_url}" target="_blank">Client Area</a> to request a new link.</p>

                {signature}
                BODY,
                'placeholder' => [
                    'name' => 'Client name',
                    'signature' => 'Global signature',
                    'company_name' => 'Name of company',
                    'company_url' => 'URL of company',
                    'reset_url' => 'User password reset URL',
                ],
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['key' => $template['key']],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'placeholder' => $template['placeholder'],
                ]
            );
        }
    }
}
