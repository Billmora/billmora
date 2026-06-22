<?php

return [
    'tabs' => [
        'summary' => 'Summary',
        'profile' => 'Profile',
        'services' => 'Services',
        'invoices' => 'Invoices',
        'credits' => 'Credits',
        'tickets' => 'Tickets',
        'activity' => 'Activity',
    ],

    'active_orders_label' => 'Active Orders',
    'cancelled_orders_label' => 'Cancelled Orders',
    'total_orders_label' => 'Total Orders',

    'email_verification_alert_label' => 'Pending Email Verification',
    'email_verification_alert_helper' => 'This user\'s email address has not been verified yet. You may verify it manually if needed.',
    'email_verification_alert_success' => 'Email address has been successfully verified.',
    'email_verification_not_found' => 'No pending email verification found for this user.',
    'marked_as_verified' => 'Marked as Verified',

    'impersonate_self_error'    => 'You cannot sign in as the same user you are currently signed in as.',
    'impersonate_admin_error'   => 'You cannot impersonate an administrator or staff member.',
    'impersonate_start_success' => 'You are now viewing as :email. Use the banner to exit.',
    'impersonate_exit_success'  => 'You have exited impersonation and returned to your admin account.',
    'impersonate_exit_error'    => 'No active impersonation session found.',
    'impersonate_banner_text'   => 'You are in impersonation mode. Admin account: :admin',
    'impersonate_exit_button'   => 'Exit Impersonate',
    'impersonate_confirm_title' => 'Sign In as User',
    'impersonate_confirm_description' => 'You are about to sign in as :name (:email). You can exit impersonation at any time using the banner in the client area.',

    'self_delete_error' => 'You cannot delete your own account.',

    'credit_currency_label' => 'Currency',
    'credit_formatted_label' => 'Formatted Balance',
    'credit_balance_label' => 'Credit Balance',
    'credit_balance_helper' => 'Enter the credit amount available for this currency.',
];