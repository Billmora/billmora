<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = [
            [
                'key' => 'test_message',
                'name' => 'Test Message',
                'placeholder' => [
                    'client_name' => 'Client name',
                    'company_name' => 'Company name',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Welcome to Billmora! (System Test)',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            
                            <h2>1. Alerts / Callouts</h2>
                            <div class="alert alert-success">
                                <p><strong>Success!</strong> Your email configuration is working perfectly.</p>
                            </div>
                            <div class="alert alert-info">
                                <p><strong>Info:</strong> This test message showcases all available email styles.</p>
                            </div>
                            <div class="alert alert-warning">
                                <p><strong>Warning:</strong> Please verify these components in your email client (Gmail, Outlook, etc).</p>
                            </div>
                            <div class="alert alert-danger">
                                <p><strong>Danger:</strong> This is how an error or suspension notice will appear.</p>
                            </div>
                            <br />

                            <h2>2. Data Table Layout</h2>
                            <table>
                                <tr>
                                    <td>Product/Service</td>
                                    <td>Billmora Enterprise</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td class="text-success">Active</td>
                                </tr>
                                <tr>
                                    <td>Unpaid Balance</td>
                                    <td class="text-danger">Rp0 IDR</td>
                                </tr>
                            </table>
                            <br />

                            <h2>3. Action Buttons</h2>
                            <p>Below are the examples of call-to-action buttons:</p>
                            <br />
                            <div class="text-center">
                                <a href="https://billmora.com" target="_blank" class="btn btn-primary">Primary Btn</a>
                                <a href="https://billmora.com" target="_blank" class="btn btn-success">Success Btn</a>
                                <a href="https://billmora.com" target="_blank" class="btn btn-danger">Danger Btn</a>
                            </div>
                            <br /><br />
                            
                            <hr class="divider" />
                            
                            <h2>4. Typography & Utilities</h2>
                            <p class="text-center text-muted" style="font-size: 14px;">This is an example of muted, centered text. Usually used for footers or disclaimers.</p>
                            <p class="text-center text-muted" style="font-size: 14px;">Visit <a href="https://billmora.com" target="_blank">https://billmora.com</a> for more information.</p>
                            <br />
                            
                            <p>Best Regards,</p>
                            <p><strong>{company_name}</strong></p>
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
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <p>Welcome to <strong>{company_name}</strong> and thank you for registering with us!</p>
                            <br />
                            <p>Please click on the button below to verify your email address. This is required to confirm ownership of the email address.</p>
                            <br />
                            <div class="text-center">
                                <a href="{verify_url}" target="_blank" class="btn btn-primary">Verify Email</a>
                            </div>
                            <br /><br />
                            <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{clientarea_url}" target="_blank">Client Area</a> to request a new link.</p>
                            
                            <hr class="divider" />
                            <p class="text-muted" style="font-size: 14px;">If you're having trouble, try copying and pasting the following URL into your browser:</p>
                            <p class="text-muted" style="font-size: 14px;"><a href="{verify_url}" target="_blank">{verify_url}</a></p>
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
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <p>Please click on the button below to verify your email address. This is required to confirm ownership of the email address.</p>
                            <br />
                            <div class="text-center">
                                <a href="{verify_url}" target="_blank" class="btn btn-primary">Verify Email</a>
                            </div>
                            <br /><br />
                            <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{clientarea_url}" target="_blank">Client Area</a> to request a new link.</p>
                            
                            <hr class="divider" />
                            <p class="text-muted" style="font-size: 14px;">If you're having trouble, try copying and pasting the following URL into your browser:</p>
                            <p class="text-muted" style="font-size: 14px;"><a href="{verify_url}" target="_blank">{verify_url}</a></p>
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
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-warning">
                                <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
                            </div>
                            <br />
                            <p>Please click the button below to set a new password.</p>
                            <br />
                            <div class="text-center">
                                <a href="{reset_url}" target="_blank" class="btn btn-primary">Reset Password</a>
                            </div>
                            <br /><br />
                            <p>This link is valid for 60 minutes only. If it has expired, login to our <a href="{clientarea_url}" target="_blank">Client Area</a> to request a new link.</p>
                            
                            <hr class="divider" />
                            <p class="text-muted" style="font-size: 14px;">If you're having trouble, try copying and pasting the following URL into your browser:</p>
                            <p class="text-muted" style="font-size: 14px;"><a href="{reset_url}" target="_blank">{reset_url}</a></p>
                            <br />
                            
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
        ];

        foreach ($notifications as $notification) {
            $mail = Notification::updateOrCreate(
                ['key' => $notification['key']],
                [
                    'name' => $notification['name'],
                    'placeholder' => $notification['placeholder'],
                ]
            );

            foreach ($notification['translations'] as $lang => $data) {
                $mail->translations()->updateOrCreate(
                    ['lang' => $lang],
                    $data
                );
            }
        }
    }
}
