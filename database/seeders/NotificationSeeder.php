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
            [
                'key' => 'user_login_detected',
                'name' => 'New Login Detected',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'ip_address' => 'IP Address',
                    'user_agent' => 'Browser / OS Information',
                    'login_time' => 'Time of Login',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Security Alert: New Login to Your Account',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-warning">
                                <p>We noticed a new login to your <strong>{company_name}</strong> account.</p>
                            </div>
                            <br />
                            <p>Here are the details of the login:</p>
                            <table>
                                <tr>
                                    <td>IP Address</td>
                                    <td>{ip_address}</td>
                                </tr>
                                <tr>
                                    <td>Device/Browser</td>
                                    <td>{user_agent}</td>
                                </tr>
                                <tr>
                                    <td>Time</td>
                                    <td>{login_time}</td>
                                </tr>
                            </table>
                            <br />
                            <p>If this was you, you can safely ignore this email.</p>
                            <br />
                            <p class="text-danger"><strong>If you did not authorize this login</strong>, please change your password immediately and contact our team to secure your account.</p>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'order_created',
                'name' => 'Order Confirmation',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'order_number' => 'Order Number (e.g. ORD-0001)',
                    'package_name' => 'Purchased Package Name',
                    'order_total' => 'Total Amount of the Order',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Order Confirmation - {order_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-success">
                                <p>Thank you for your order! We have received your order <strong>{order_number}</strong> and it is currently being processed.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Package</td>
                                    <td>{package_name}</td>
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td>{order_total}</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>If you have any unpaid invoices associated with this order, your service will be automatically activated once the payment is successfully verified.</p>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'service_provisioning_activated',
                'name' => 'Service Provisioning Activated',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'service_name' => 'Name of the service / package',
                    'recurring_amount' => 'Recurring amount to be paid',
                    'billing_cycle' => 'Billing cycle (e.g., Monthly, Yearly)',
                    'next_due_date' => 'Next due date of the service',
                    'service_url' => 'Direct link to manage the service',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Service Activated - {service_name}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-success">
                                <p>Great news! Your service <strong>{service_name}</strong> has been successfully activated and is now ready to use.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Service Name</td>
                                    <td>{service_name}</td>
                                </tr>
                                <tr>
                                    <td>Recurring Amount</td>
                                    <td>{recurring_amount}</td>
                                </tr>
                                <tr>
                                    <td>Billing Cycle</td>
                                    <td>{billing_cycle}</td>
                                </tr>
                                <tr>
                                    <td>Next Due Date</td>
                                    <td>{next_due_date}</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>You can manage your service, view details, and access control panels by logging into your client area:</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{service_url}" target="_blank" class="btn btn-primary">Manage Service</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'service_provisioning_failed',
                'name' => 'Service Provisioning Failed',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'service_name' => 'Name of the service / package',
                    'action' => 'The action that failed (e.g., Create, Suspend)',
                    'error_message' => 'Technical error message (Internal use)',
                    'service_url' => 'Direct link to manage the service',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Service Update Delayed - {service_name}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-warning">
                                <p>We wanted to let you know that there is a slight delay in processing your service. Our system encountered a temporary issue during the <strong>{action}</strong> process.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Service Name</td>
                                    <td>{service_name}</td>
                                </tr>
                                <tr>
                                    <td>Pending Action</td>
                                    <td>{action}</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p><strong>Rest assured, our technical team has been automatically notified of this issue and is working to resolve it manually.</strong> You do not need to take any further action at this time.</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{service_url}" target="_blank" class="btn btn-primary">View Service Status</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                            
                            HTML,
                    ],
                ],
            ],
            [
                'key' => 'service_provisioning_suspended',
                'name' => 'Service Suspended',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'service_name' => 'Name of the service / package',
                    'service_url' => 'Direct link to manage the service',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Service Suspended - {service_name}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-danger">
                                <p>This is a notification that your service <strong>{service_name}</strong> has been suspended.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Service Name</td>
                                    <td>{service_name}</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td style="color: #e74c3c;">Suspended</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>This is usually due to an overdue invoice. To reactivate your service and avoid permanent data loss (termination), please log in to your client area and settle any pending balances.</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{service_url}" target="_blank" class="btn btn-primary">Manage Service</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'service_provisioning_unsuspended',
                'name' => 'Service Reactivated (Unsuspended)',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'service_name' => 'Name of the service / package',
                    'service_url' => 'Direct link to manage the service',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Service Reactivated - {service_name}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-success">
                                <p>Great news! Your service <strong>{service_name}</strong> has been successfully reactivated and is now fully operational.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Service Name</td>
                                    <td>{service_name}</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td style="color: #2ecc71;">Active</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>You can manage your service by logging into your client area:</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{service_url}" target="_blank" class="btn btn-primary">Manage Service</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'service_provisioning_terminated',
                'name' => 'Service Terminated',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'service_name' => 'Name of the service / package',
                    'service_url' => 'Direct link to the service history',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Service Terminated - {service_name}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-danger">
                                <p>This is a formal notification that your service <strong>{service_name}</strong> has been permanently terminated.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Service Name</td>
                                    <td>{service_name}</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td style="color: #c0392b;">Terminated</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>As per our terms of service, all data associated with this service has been completely removed from our servers and cannot be recovered. If this termination was due to an overdue invoice, please note that paying the invoice now will not restore your service data.</p>
                            <br />
                            
                            <p>You can still view your service history in your client area:</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{service_url}" target="_blank" class="btn btn-primary">View Service History</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'service_provisioning_renewed',
                'name' => 'Service Renewed',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'service_name' => 'Name of the service / package',
                    'next_due_date' => 'The new next due date',
                    'service_url' => 'Direct link to manage the service',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Service Renewed - {service_name}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-success">
                                <p>Thank you for your continued business! Your service <strong>{service_name}</strong> has been successfully renewed.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Service Name</td>
                                    <td>{service_name}</td>
                                </tr>
                                <tr>
                                    <td>New Due Date</td>
                                    <td>{next_due_date}</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>You can manage your service by logging into your client area:</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{service_url}" target="_blank" class="btn btn-primary">Manage Service</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'service_provisioning_scaled',
                'name' => 'Service Scaled',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'service_name' => 'Name of the service / package',
                    'recurring_amount' => 'New recurring amount to be paid',
                    'billing_cycle' => 'Billing cycle (e.g., Monthly, Yearly)',
                    'service_url' => 'Direct link to manage the service',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Service Scaled Successfully - {service_name}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-success">
                                <p>Great news! Your service <strong>{service_name}</strong> has been successfully scaled. The new resources or features have been applied to your account and are now active.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Service Name</td>
                                    <td>{service_name}</td>
                                </tr>
                                <tr>
                                    <td>New Recurring Amount</td>
                                    <td>{recurring_amount}</td>
                                </tr>
                                <tr>
                                    <td>Billing Cycle</td>
                                    <td>{billing_cycle}</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>You can verify your new service limits and manage your service by logging into your client area:</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{service_url}" target="_blank" class="btn btn-primary">Manage Service</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_created',
                'name' => 'New Invoice Created',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number (e.g. INV-0001)',
                    'total_amount' => 'Total Amount with Currency',
                    'due_date' => 'Invoice Due Date',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'New Invoice Generated - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-info">
                                <p>A new invoice <strong>{invoice_number}</strong> has been generated for your account.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Amount Due</td>
                                    <td>{total_amount}</td>
                                </tr>
                                <tr>
                                    <td>Due Date</td>
                                    <td>{due_date}</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>You can view and pay your invoice by clicking the button below:</p>
                            <br />
                            <div class="text-center">
                                <a href="{invoice_url}" target="_blank" class="btn btn-primary">View & Pay Invoice</a>
                            </div>
                            <br /><br />
                            <p>If you have any questions regarding this invoice, please contact our team.</p>
                            
                            <hr class="divider" />
                            <p class="text-muted" style="font-size: 14px;">If the button doesn't work, copy and paste this link into your browser:</p>
                            <p class="text-muted" style="font-size: 14px;"><a href="{invoice_url}">{invoice_url}</a></p>
                            <br />
                            
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_paid',
                'name' => 'Invoice Paid Receipt',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number (e.g. INV-0001)',
                    'paid_at' => 'Payment Date',
                    'payment_method' => 'Payment Method / Gateway',
                    'invoice_items_table' => 'Dynamic table containing invoice items and totals',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Payment Receipt - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-success">
                                <p><strong>Thank You!</strong> We have successfully received your payment for invoice <strong>{invoice_number}</strong>.</p>
                            </div>
                            <br />
                            
                            {invoice_items_table}
                            <br />
                            
                            <p><strong>Payment Date:</strong> {paid_at}</p>
                            <p><strong>Payment Method:</strong> {payment_method}</p>
                            <br />
                            <p>You can view or download your PDF receipt by clicking the button below:</p>
                            <br />
                            <div class="text-center">
                                <a href="{invoice_url}" target="_blank" class="btn btn-primary">View Receipt</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_refunded',
                'name' => 'Invoice Refunded',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number (e.g. INV-0001)',
                    'invoice_total' => 'Total amount of the invoice',
                    'refunded_amount' => 'The amount that was refunded',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Refund Processed - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-warning">
                                <p>A refund has been successfully processed for your invoice <strong>{invoice_number}</strong>.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Invoice Total</td>
                                    <td>{invoice_total}</td>
                                </tr>
                                <tr>
                                    <td style="color: #6d7178;"><strong>Amount Refunded</strong></td>
                                    <td>
                                        <strong><span style="font-size: 16px; color: #e74c3c;">{refunded_amount}</span></strong>
                                    </td>
                                </tr>
                            </table>
                            
                            <br />
                            <p>Please note that it may take a few business days for the funds to appear in your account, depending on the payment method used.</p>
                            <br />
                            <div class="text-center">
                                <a href="{invoice_url}" target="_blank" class="btn btn-primary">View Invoice</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_generated',
                'name' => 'Invoice Generated',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number (e.g. INV-0001)',
                    'due_date' => 'Due Date of the invoice',
                    'invoice_items_table' => 'Pre-rendered table containing invoice items',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Upcoming Renewal Invoice - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-info">
                                <p>This is a billing notice that your service is due for renewal. A new invoice <strong>{invoice_number}</strong> has been generated for your upcoming billing cycle.</p>
                            </div>
                            <br />
                            
                            {invoice_items_table}
                            
                            <br />
                            <p>To ensure your service remains active without interruption, please complete the payment before the due date: <strong>{due_date}</strong>.</p>
                            <br />
                            <div class="text-center">
                                <a href="{invoice_url}" target="_blank" class="btn btn-primary">Pay Invoice Now</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_reminder',
                'name' => 'Invoice Payment Reminder',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number',
                    'due_date' => 'Due Date of the invoice',
                    'invoice_items_table' => 'Pre-rendered table containing invoice items',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Invoice Payment Reminder - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p><br />
                            <div class="alert alert-info">
                                <p>This is a friendly reminder that your invoice <strong>{invoice_number}</strong> will be due on <strong>{due_date}</strong>. Please ensure payment is made to avoid any service interruptions.</p>
                            </div><br />
                            {invoice_items_table}<br />
                            <div class="text-center"><a href="{invoice_url}" target="_blank" class="btn btn-primary">Pay Invoice Now</a></div><br />
                            <p>Best Regards,</p><p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_overdue_1',
                'name' => 'First Overdue Notice',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number',
                    'due_date' => 'Due Date of the invoice',
                    'invoice_items_table' => 'Pre-rendered table containing invoice items',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'First Overdue Notice - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p><br />
                            <div class="alert alert-warning">
                                <p>This is the first notice that your invoice <strong>{invoice_number}</strong> is now overdue. The original due date was <strong>{due_date}</strong>.</p>
                            </div><br />
                            {invoice_items_table}<br />
                            <div class="text-center"><a href="{invoice_url}" target="_blank" class="btn btn-primary">Pay Invoice Now</a></div><br />
                            <p>Best Regards,</p><p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_overdue_2',
                'name' => 'Second Overdue Notice',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number',
                    'due_date' => 'Due Date of the invoice',
                    'invoice_items_table' => 'Pre-rendered table containing invoice items',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Second Overdue Notice - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p><br />
                            <div class="alert alert-warning">
                                <p>This is the second notice regarding your overdue invoice <strong>{invoice_number}</strong>. If payment is not received promptly, your active services may be suspended.</p>
                            </div><br />
                            {invoice_items_table}<br />
                            <div class="text-center"><a href="{invoice_url}" target="_blank" class="btn btn-primary">Pay Invoice Now</a></div><br />
                            <p>Best Regards,</p><p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'invoice_overdue_3',
                'name' => 'Third (Final) Overdue Notice',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'invoice_number' => 'Invoice Number',
                    'due_date' => 'Due Date of the invoice',
                    'invoice_items_table' => 'Pre-rendered table containing invoice items',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'URGENT: Final Overdue Notice - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p><br />
                            <div class="alert alert-danger">
                                <p>This is the final notice regarding your severely overdue invoice <strong>{invoice_number}</strong>. Immediate payment is required to prevent permanent termination of your services.</p>
                            </div><br />
                            {invoice_items_table}<br />
                            <div class="text-center"><a href="{invoice_url}" target="_blank" class="btn btn-primary">Pay Invoice Now</a></div><br />
                            <p>Best Regards,</p><p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'transaction_recorded',
                'name' => 'Transaction Recorded (Receipt)',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'transaction_type' => 'Payment or Refund',
                    'transaction_amount' => 'Amount of transaction',
                    'transaction_date' => 'Date of transaction',
                    'transaction_description' => 'Description of transaction',
                    'transaction_reference' => 'Gateway Reference ID',
                    'invoice_number' => 'Related Invoice Number',
                    'invoice_url' => 'Direct link to the invoice',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'New {transaction_type} Recorded - {invoice_number}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <p>A new <strong>{transaction_type}</strong> has been successfully recorded on your account.</p>
                            <br />
                            
                            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                                <tr>
                                    <td style="color: #6d7178;">Invoice</td>
                                    <td style="font-weight: 600; color: #333;">{invoice_number}</td>
                                </tr>
                                <tr>
                                    <td style="color: #6d7178;">Description</td>
                                    <td>{transaction_description}</td>
                                </tr>
                                <tr>
                                    <td style="color: #6d7178;">Date</td>
                                    <td>{transaction_date}</td>
                                </tr>
                                <tr>
                                    <td style="color: #6d7178;">Reference</td>
                                    <td>{transaction_reference}</td>
                                </tr>
                                <tr>
                                    <td style="color: #6d7178;"><strong>{transaction_type} Amount</strong></td>
                                    <td>
                                        <strong><span style="font-size: 16px; color: #7267ef;">{transaction_amount}</span></strong>
                                    </td>
                                </tr>
                            </table>
                            
                            <br />
                            <p>You can view your updated invoice by clicking the button below:</p>
                            <br />
                            <div class="text-center">
                                <a href="{invoice_url}" target="_blank" class="btn btn-primary">View Invoice</a>
                            </div>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'ticket_created',
                'name' => 'Ticket Created',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'ticket_number' => 'Ticket Number (e.g. TKT-0001)',
                    'ticket_subject' => 'Ticket Subject',
                    'ticket_department' => 'Ticket Department',
                    'ticket_url' => 'Link to the ticket',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Ticket Opened - {ticket_subject}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <p>A ticket has been opened for your account.</p>
                            <br />
                            <table>
                                <tr>
                                    <td>Ticket Number</td>
                                    <td>{ticket_number}</td>
                                </tr>
                                <tr>
                                    <td>Subject</td>
                                    <td>{ticket_subject}</td>
                                </tr>
                                <tr>
                                    <td>Department</td>
                                    <td>{ticket_department}</td>
                                </tr>
                            </table>
                            <br />
                            <p>We will review your request and get back to you as soon as possible. You can track the progress or add more details by clicking the button below:</p>
                            <br />
                            <div class="text-center">
                                <a href="{ticket_url}" target="_blank" class="btn btn-primary">View Ticket</a>
                            </div>
                            <br />
                            <hr class="divider" />
                            <p class="text-muted" style="font-size: 14px;">If the button doesn't work, copy and paste this link into your browser:</p>
                            <p class="text-muted" style="font-size: 14px;"><a href="{ticket_url}">{ticket_url}</a></p>
                            <br />
                            <p>Best Regards,</p>
                            <p>{company_name}</p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'ticket_replied',
                'name' => 'Support Ticket Replied',
                'placeholder' => [
                    'recipient_name' => 'Client or Staff Name',
                    'company_name' => 'Company Name',
                    'ticket_number' => 'Ticket Number (e.g. TKT-0001)',
                    'ticket_subject' => 'Ticket Subject',
                    'ticket_status' => 'Current Ticket Status',
                    'reply_content' => 'The full reply message (HTML supported)',
                    'ticket_url' => 'Link to the ticket',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'New Reply - [#{ticket_number}] {ticket_subject}',
                        'body' => <<<HTML
                            <div>
                                {reply_content}
                            </div>
                            
                            <hr class="divider" />
                            
                            <p><strong>Ticket Number:</strong> #{ticket_number}</p>
                            <p><strong>Subject:</strong> {ticket_subject}</p>
                            <p><strong>Status:</strong> {ticket_status}</p>
                            <p><strong>Ticket URL:</strong> <a href="{ticket_url}">{ticket_url}</a></p>
                        HTML,
                    ],
                ],
            ],
            [
                'key' => 'ticket_closed',
                'name' => 'Ticket Closed',
                'placeholder' => [
                    'client_name' => 'Client Name',
                    'company_name' => 'Company Name',
                    'ticket_subject' => 'Ticket Subject',
                    'ticket_number' => 'Ticket Number (e.g., TKT-0001)',
                    'ticket_url' => 'Direct link to the ticket',
                ],
                'translations' => [
                    'en_US' => [
                        'subject' => 'Ticket Closed - [#{ticket_number}] {ticket_subject}',
                        'body' => <<<HTML
                            <p>Hello, <strong>{client_name}</strong>!</p>
                            <br />
                            <div class="alert alert-info">
                                <p>This is a notification that your support ticket <strong>#{ticket_number}</strong> has been marked as closed.</p>
                            </div>
                            <br />
                            
                            <table>
                                <tr>
                                    <td>Subject</td>
                                    <td>{ticket_subject}</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td style="color: #6d7178;">Closed</td>
                                </tr>
                            </table>
                            <br />
                            
                            <p>We hope your issue was resolved satisfactorily! If you still need assistance with this specific issue, you can usually reply to the ticket to reopen it. For any new issues, please open a new support ticket.</p>
                            <br />
                            
                            <div class="text-center">
                                <a href="{ticket_url}" target="_blank" class="btn btn-primary">View Ticket</a>
                            </div>
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
