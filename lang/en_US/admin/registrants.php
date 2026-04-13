<?php

return [
    'domain_label' => 'Domain',
    'domain_helper' => 'Enter the domain name without http/https (e.g. example.com).',
    'status_label' => 'Status',
    'status_helper' => 'Set the current status of this registration.',
    'user_label' => 'User',
    'user_helper' => 'Select the client that owns this domain.',
    'tld_label' => 'TLD',
    'tld_helper' => 'Select the TLD extension for this domain.',
    'registrar_label' => 'Registrar',
    'registrar_helper' => 'Select the registrar plugin managing this domain.',
    'registration_type_label' => 'Registration Type',
    'registration_type_helper' => 'Choose whether this is a new registration or a transfer.',
    'years_label' => 'Years',
    'years_helper' => 'Number of years for this registration.',
    'expires_label' => 'Expires At',
    'expires_helper' => 'Set when this domain registration expires.',
    'auto_renew_label' => 'Auto Renew',
    'auto_renew_helper' => 'Automatically renew this domain before expiration.',
    'whois_privacy_label' => 'WHOIS Privacy',
    'whois_privacy_helper' => 'Enable WHOIS privacy protection for this domain.',
    'nameservers_label' => 'Nameservers',
    'nameservers_helper' => 'Custom nameservers for this domain.',
    'epp_code_label' => 'EPP Code',
    'number_label' => 'Registrant Number',
    'number_helper' => 'System generated unique identifier.',
    'price_label' => 'Price',
    'price_helper' => 'The recurring price or initial cost of this domain.',
    'go_to_user' => 'Go to User',

    'registrar_actions_label' => 'Registrar Actions',
    'registrar_create_label' => 'Create',
    'registrar_transfer_label' => 'Transfer',
    'registrar_renew_label' => 'Force Renew',
    'registrar_sync_label' => 'Sync Status',

    'registrar' => [
        'create' => [
            'success' => 'Domain registered successfully.',
            'failed' => 'Registration failed: :message',
            'invalid_status' => 'Domain must be pending to be registered.',
        ],
        'transfer' => [
            'success' => 'Domain transfer initiated successfully.',
            'failed' => 'Transfer failed: :message',
            'invalid_status' => 'Domain must be pending transfer.',
        ],
        'renew' => [
            'success' => 'Domain renewed successfully.',
            'failed' => 'Renewal failed: :message',
            'invalid_status' => 'Only active or expired domains can be renewed.',
        ],
        'sync' => [
            'success' => 'Domain status synced from registrar.',
            'failed' => 'Sync failed: :message',
        ],
    ],

    'delete' => [
        'active_registrant' => 'Cannot delete an active domain. Please terminate it first.',
    ],
];
