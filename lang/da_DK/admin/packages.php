<?php

return [
    'tabs' => [
        'summary' => 'Summary',
        'pricing' => 'Pricing',
        'provisioning' => 'Provisioning',
        'scaling' => 'Scaling',
    ],

    'catalog_label' => 'Catalog',
    'catalog_helper' => 'Select the catalog to which this package belongs.',
    'name_label' => 'Name',
    'name_helper' => 'Enter the name of the product package.',
    'slug_label' => 'Slug',
    'slug_helper' => 'Enter a unique URL-friendly identifier for this package. It will be used in product URLs.',
    'description_label' => 'Description',
    'description_helper' => 'Provide a brief description of the package. HTML element supported.',
    'icon_label' => 'Icon',
    'icon_helper' => 'Choose an icon file to represent the package.',
    'stock_label' => 'Stock',
    'stock_helper' => 'Enter the total available stock for this package. Use -1 for unlimited stock.',
    'per_user_limit_label' => 'Per User Limit',
    'per_user_limit_helper' => 'Set the maximum number of this package a single user can purchase. Use -1 for no limit.',
    'allow_cancellation_label' => 'Allow Cancellation',
    'allow_cancellation_helper' => 'Enable to let customers request service cancellation for this package.',
    'allow_quantity_label' => 'Allow Quantity',
    'allow_quantity_helper' => 'Enable to let customers purchase multiple or single quantities of this package.',
    'status_label' => 'Status',
    'status_helper' => 'Set the status of the package to visible or hidden.',
    'status_options' => [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
    ],

    'pricing' => [
        'name_label' => 'Name',
        'name_helper' => 'Enter the name for this pricing option.',
        'type_label' => 'Type',
        'type_helper' => 'Select the type of pricing for this package.',
        'time_interval_label' => 'Time Interval',
        'time_interval_helper' => 'Choose the billing interval for this pricing option.',
        'billing_period_label' => 'Billing Period',
        'billing_period_helper' => 'Select how often the customer will be billed for this package.',
        'currency_code_label' => 'Currency Code',
        'currency_code_helper' => 'Displays the currency used for this price. This value cannot be changed here.',
        'price_label' => 'Price',
        'price_helper' => 'Set the price for this package in the selected currency.',
        'setup_fee_label' => 'Setup Fee',
        'setup_fee_helper' => 'Enter any one-time setup fee for this pricing option.',
        'enabled_label' => 'Enabled?',
        'enabled_helper' => 'Enable or disable this pricing option without deleting it.',
    ],

    'scaling' => [
        'target_packages_label' => 'Scalable To',
        'target_packages_helper' => 'Select the packages that this package can be scaled up or down to.',
    ],

    'provisioning' => [
        'instance_label' => 'Provisioning Instance',
        'instance_helper' => 'Select the provisioning instance to link with this package.',
        'unavailable_schema' => 'This provisioning instance has no schema options for packages.',
    ],
];