<?php

return [
    'user_label' => 'User',
    'user_helper' => 'Client associated with this service.',
    'name_label' => 'Name',
    'name_helper' => 'Name of this service.',
    'currency_label' => 'Currency',
    'currency_helper' => 'Select currency to determine available packages and pricing.',
    'status_label' => 'Status',
    'status_helper' => 'Set the current status of this service.',
    'recalculate_label' => 'Recalculate on Save?',
    'recalculate_helper' => ' Automatically recalculate price and setup fee based on package selection.',
    'expires_label' => 'Expires At',
    'expires_helper' => 'Set when this service will expire or next due date.',
    'price_label' => 'Price',
    'price_helper' => ' Override price manually, not affected when recalculate is enabled.',
    'setup_fee_label' => 'Setup Fee',
    'setup_fee_helper' => ' Override setup fee manually, not affected when recalculate is enabled.',
    'package_configuration_label' => 'Package Configuration',
    'package_configuration_helper' => 'Define the base package and billing period for this service.',
    'package_label' => 'Package',
    'package_helper' => 'Choose a currency first to view available packages.',
    'billing_cycle_label' => 'Billing Cycle',
    'billing_cycle_helper' => 'Select a package first to view available billing cycles.',
    'variant_option_label' => 'Variant Options',
    'variant_option_helper' => 'Select additional variants are available for the chosen package.',
    
    'catalog_label' => 'Catalog',
    'go_to_user' => 'Go to User',

    'provisioning' => [
        'driver_missing' => 'No provisioning driver assigned to this service.',
        'driver_class_missing' => 'Driver class for :driver not found.',
        'create' => [
            'invalid_status' => 'Service must be pending or terminated to be created.',
            'success' => 'Service created and activated successfully.',
            'failed' => 'Create failed: :message',
        ],
        'suspend' => [
            'invalid_status' => 'Only active services can be suspended.',
            'success' => 'Service suspended successfully.',
            'failed' => 'Suspend failed: :message',
        ],
        'unsuspend' => [
            'invalid_status' => 'Only suspended services can be unsuspended.',
            'success' => 'Service unsuspended successfully.',
            'failed' => 'Unsuspend failed: :message',
        ],
        'terminate' => [
            'already_terminated' => 'Service is already terminated.',
            'success' => 'Service terminated successfully.',
            'failed' => 'Termination failed: :message',
        ],
        'renew' => [
            'invalid_status' => 'Only active or suspended services can be renewed.',
            'success' => 'Service renewed on provider successfully.',
            'failed' => 'Renew failed: :message',
        ],
        'scale' => [
            'invalid_status' => 'Only active services can be scaled.',
            'success' => 'Service scaled successfully.',
            'failed' => 'Scaling failed: :message',
        ],
    ],

    'delete' => [
        'active_services' => 'Cannot delete an active service. Please terminate or cancel it first to ensure remote resources are cleaned up.',
    ],
];