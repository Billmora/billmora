<?php

return [
    'tld_label' => 'TLD',
    'tld_helper' => 'Top-level domain extension (e.g. .com, .net).',
    'register_price_label' => 'Register Price',
    'transfer_price_label' => 'Transfer Price',
    'renew_price_label' => 'Renew Price',
    'min_years_label' => 'Minimum Years',
    'min_years_helper' => 'Minimum number of years for registration.',
    'max_years_label' => 'Maximum Years',
    'max_years_helper' => 'Maximum number of years for registration.',
    'grace_period_label' => 'Grace Period (Days)',
    'grace_period_helper' => 'Number of days after expiration before the domain enters redemption.',
    'redemption_period_label' => 'Redemption Period (Days)',
    'redemption_period_helper' => 'Number of days after grace period before the domain is terminated.',
    'whois_privacy_label' => 'WHOIS Privacy',
    'whois_privacy_helper' => 'Does this TLD support WHOIS privacy protection?',
    'registrar_label' => 'Default Registrar',
    'registrar_helper' => 'Select the default registrar for this TLD.',
    'status_label' => 'Status',
    'status_helper' => 'Set whether this TLD is visible in the store.',

    'pricing_label' => 'Pricing by Currency',
    'pricing_helper' => 'Define register, transfer, and renew pricing for each currency.',
    'currency_code_label' => 'Currency',
    'enabled_label' => 'Enabled',
    'enabled_helper' => 'Toggle to enable pricing for this currency.',

    'delete' => [
        'has_registrants' => 'Cannot delete TLD that has active registrants.',
    ],
];
