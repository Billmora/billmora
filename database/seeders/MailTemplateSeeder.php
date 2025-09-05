<?php

namespace Database\Seeders;

use App\Models\MailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MailTemplateSeeder extends Seeder
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
                'placeholder' => [
                    'client_name' => 'Client name',
                    'company_name' => 'Company name',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Welcome to Billmora!',
                        'body' => <<<HTML
                            <p>Hello, {client_name}!</p>
                            <br />
                            <p>This is a test email to verify the configuration.</p>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'user_registration',
                'name' => 'User Registration',
                'placeholder' => [
                    'client_name' => 'Client name',
                    'company_name' => 'Company name',
                    'verify_url' => 'Email verification URL',
                    'clientarea_url' => 'Client Area URL',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Account has been created, verify your Email',
                        'body' => <<<HTML
                            <p>Hello, {client_name}!</p>
                            <p>Welcome to {company_name} and thank you for registering with us!</p>
                            <br />
                            <p>Please click on the link below to verify your email address. This is required to confirm ownership of the email address.</p>
                            <a href="{verify_url}" target="_blank">Verify Email</a>
                            <br /><br />
                            <p>If you're having trouble, try copying and pasting the following URL into your browser:</p>
                            <a href="{verify_url}" target="_blank">{verify_url}</a>
                            <br /><br />
                            <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{clientarea_url}" target="_blank">Client Area</a> to request a new link.</p>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'user_resend_verification',
                'name' => 'User Resend Verification',
                'placeholder' => [
                    'client_name' => 'Client name',
                    'company_name' => 'Company name',
                    'verify_url' => 'Email verification URL',
                    'clientarea_url' => 'Client Area URL',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Verify your Email address',
                        'body' => <<<HTML
                            <p>Hello, {name}!</p>
                            <br />
                            <p>Please click on the link below to verify your email address. This is required to confirm ownership of the email address.</p>
                            <a href="{verify_url}" target="_blank">Verify Email</a>
                            <br /><br />
                            <p>If you're having trouble, try copying and pasting the following URL into your browser:</p>
                            <a href="{verify_url}" target="_blank">{verify_url}</a>
                            <br /><br />
                            <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{clientarea_url}" target="_blank">Client Area</a> to request a new link.</p>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'user_password_reset',
                'name' => 'User Password Reset',
                'placeholder' => [
                    'client_name' => 'Client name',
                    'company_name' => 'Company name',
                    'reset_url' => 'Password reset URL',
                    'clientarea_url' => 'Client Area URL',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Reset Your Password',
                        'body' => <<<HTML
                            <p>Hello, {client_name}!</p>
                            <br />
                            <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
                            <br />
                            <p>Please click the link below to set a new password.</p>
                            <a href="{reset_url}" target="_blank">Reset Password</a>
                            <br /><br />
                            <p>If you're having trouble, try copying and pasting the following URL into your browser:</p>
                            <a href="{reset_url}" target="_blank">{reset_url}</a>
                            <br /><br />
                            <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{clientarea_url}" target="_blank">Client Area</a> to request a new link.</p>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
        ];

        foreach ($templates as $template) {
            $mail = MailTemplate::updateOrCreate(
                ['key' => $template['key']],
                [
                    'name' => $template['name'],
                    'placeholder' => $template['placeholder'],
                ]
            );

            foreach ($template['translations'] as $lang => $data) {
                $mail->translations()->updateOrCreate(
                    ['lang' => $lang],
                    $data
                );
            }
        }
    }
}
