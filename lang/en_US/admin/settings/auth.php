<?php

return [
    'title' => 'Auth settings',
    'description' => 'Configure authentication settings for the system.',
    'tabs' => [
        'user' => 'User',
    ],

    'user_registration_label' => 'User Registration',
    'user_registration_helper' => 'Allow new users to register for an account in the client area.',
    'user_require_verified_label' => 'User Require Verified',
    'user_require_verified_helper' => 'Require users to verify their email address before accessing the client portal.',
    'user_require_two_factor_label' => 'Require Two-Factor Authentication',
    'user_require_two_factor_helper' => 'Force all users to enable Two-Factor Authentication (2FA) for increased account security.',
    'user_registration_disabled_inputs_label' => 'Disabled Registration Inputs',
    'user_registration_disabled_inputs_helper' => 'Select the fields to hide from the registration form. Users will not see or fill in these fields.',
    'user_registration_required_inputs_label' => 'Required Registration Inputs',
    'user_registration_required_inputs_helper' => 'Select the fields that new users must fill in during registration. Unselected fields remain optional.',
];