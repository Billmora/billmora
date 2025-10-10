<?php

return [
    'title' => 'Currency settings',
    'description' => 'Configure currency settings for the system.',

    'currency_table' => [
        'code' => 'Code',
        'prefix' => 'Prefix',
        'suffix' => 'Suffix',
        'format' => 'Format',
        'base_rate' => 'Base Rate',
    ],
    
    'currency_action' => [
        'set_default' => 'Set Default',
    ],

    'currency_alert' => [
        'set_default_success' => 'Currency :code has been set as default.',
    ],

    'currency_code_label' => 'Currency Code',
    'currency_code_helper' => 'Enter the ISO currency code (e.g., USD, EUR, IDR).',
    'currency_prefix_label' => 'Currency Prefix',
    'currency_prefix_helper' => 'Enter the symbol or text to appear before the amount.',
    'currency_suffix_label' => 'Currency Code',
    'currency_suffix_helper' => 'Enter the symbol or text to appear after the amount.',
    'currency_format_label' => 'Currency Format',
    'currency_format_helper' => 'Select how currency values should be displayed.',
    'currency_base_rate_label' => 'Base Conversion Rate',
    'currency_base_rate_helper' => 'Set the conversion rate relative to the base currency.',
];