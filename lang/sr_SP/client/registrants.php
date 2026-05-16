<?php

return [
    'domain_label' => 'Domain',
    'status_label' => 'Status',
    'registrant_number_label' => 'Registrant Number',
    'registration_date_label' => 'Registration Date',
    'expires_label' => 'Expires At',
    'auto_renew_label' => 'Auto Renew',
    'whois_privacy_label' => 'WHOIS Privacy',
    'nameservers_label' => 'Nameservers',
    'epp_code_label' => 'EPP Code',

    'action' => [
        'overview' => 'Overview',
        'nameservers' => 'Nameservers',
        'epp_code' => 'EPP Code',
        'whois_privacy' => 'WHOIS Privacy',
    ],

    'nameservers' => [
        'updated' => 'Nameservers updated successfully.',
        'failed' => 'Failed to update nameservers: :message',
    ],

    'epp' => [
        'retrieved' => 'EPP code retrieved successfully.',
        'failed' => 'Failed to retrieve EPP code: :message',
    ],

    'privacy' => [
        'enabled' => 'WHOIS privacy enabled.',
        'disabled' => 'WHOIS privacy disabled.',
        'failed' => 'Failed to update WHOIS privacy: :message',
    ],

    'auto_renew' => [
        'enabled' => 'Auto-renewal enabled.',
        'disabled' => 'Auto-renewal disabled.',
    ],
];
