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
    'scale_label' => 'Scaling Service',
    'scale_package_helper' => 'Choose a package want to scale to',
    'scale_variant_helper' => 'Choose a variant want to scale to',

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

    'scaling' => [
        'must_be_active' => 'Only active services can be scaled.',
        'unpaid_invoice_exists' => 'Please pay all outstanding invoices before making changes to this service.',
        'service_overdue' => 'Your service has expired. Please renew it before requesting a scale.',
        'invalid_package' => 'The selected package is not valid for this service.',
        'no_variants' => 'This package has no variant options.',
        'no_variant_changes' => 'Please change at least one option to proceed.',
        'downgrade_success' => 'Your service has been downgraded and will take effect on the next billing cycle.',
        'upgrade_success' => 'Upgrade invoice has been created. Complete your payment to apply the changes.',
        'invoice_item_description' => ':old_package scale to :new_package (Prorated for :days day(s))',
    ],

    'provisioning' => [
        'not_found' => 'Provisioning provider not found.',
        'unavailable' => 'Provisioning provider unavailable.',
    ],
];