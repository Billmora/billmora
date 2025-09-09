<?php

return [
    'title' => 'Captcha settings',
    'description' => 'Configure captcha settings for the system.',
    'tabs' => [
        'provider' => 'Provider',
    ],

    'provider_type_label' => 'Captcha Provider',
    'provider_type_helper' => 'Select the captcha service provider to use for form verification.',
    'provider_site_key_label' => 'Site Key',
    'provider_site_key_helper' => 'Enter the site key provided by your captcha service.',
    'provider_secret_key_label' => 'Secret Key',
    'provider_secret_key_helper' => 'Enter the secret key provided by your captcha service.',

    'placements_enabled_forms_label' => 'Enabled Forms',
    'placements_enabled_forms_helper' => 'Select the forms where captcha verification should be enabled.',
]; 