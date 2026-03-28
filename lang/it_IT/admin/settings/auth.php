<?php

return [
    'title' => 'Authentication settings',
    'description' => 'Manage user registration and social logins.',
    'tabs' => [
        'user' => 'User',
        'social' => 'Social Login',
    ],

    'user_registration_label' => 'User Registration',
    'user_registration_helper' => 'Allow new users to register for an account in the client area.',
    'user_require_verified_label' => 'User Require Verified',
    'user_require_verified_helper' => 'Require users to verify their email address before accessing the client portal.',
    'user_require_two_factor_label' => 'Require Two-Factor Authentication',
    'user_require_two_factor_helper' => 'Force all users to enable Two-Factor Authentication (2FA) for increased account security.',
    'user_registration_disabled_inputs_label' => 'Disabled Registration Inputs',
    'user_registration_disabled_inputs_helper' => 'Select the fields to hide from the registration form. Users will not see or fill in these fields.',
    'user_billing_required_inputs_label' => 'Required Billing Information Inputs',
    'user_billing_required_inputs_helper' => 'Select the fields that users must fill in as required. Unselected fields remain optional.',

    'social' => [
        'title' => 'Social Login',
        'google' => 'Google',
        'discord' => 'Discord',
        'github' => 'GitHub',
        'enabled_label' => 'Enabled',
        'enabled_helper' => 'Allow users to login using :provider.',
        'client_id_label' => 'Client ID',
        'client_secret_label' => 'Client Secret',
    ],
];