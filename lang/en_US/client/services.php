<?php

return [
    'package_label' => 'Package',
    'catalog_label' => 'Catalog',
    'registration_label' => 'Registration Date',
    'billing_cycle_label' => 'Billing Cycle',
    'price_label' => 'Price',
    'setup_fee_label' => 'Setup Fee',
    'expires_label' => 'Expires At',
    'variant_label' => 'Variant Options',
    'configuration_label' => 'Additional Configuration',
    'cancel_label' => 'Cancellation Request',

    'action' => [
        'overview' => 'Overview',
        'scale' => 'Scale',
        'cancel' => 'Cancel',

        'unavailable' => 'Page not found or action does not require input.',
        'invalid_type' => 'Invalid action type configured for rendering.',
        'success' => 'Action processed successfully.',
        'failed' => 'Action failed to process :message',
    ],

    'cancellation' => [
        'type_label' => 'Cancellation Type',
        'reason_label' => 'Cancellation Reason',

        'requested' => 'Cancellation request has been submitted.',
        'pending' => 'This service has a pending cancellation request. Please contact support if you wish to withdraw it.',
    ],

    'provisioning' => [
        'not_found' => 'Provisioning provider not found.',
        'unavailable' => 'Provisioning provider unavailable.',
    ],
];