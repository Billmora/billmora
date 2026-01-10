<?php

return [
    'tabs' => [
        'summary' => 'Summary',
        'options' => 'Options',
    ],

    'name_label' => 'Name',
    'name_helper' => 'Enter the name of the package variant.',
    'description_label' => 'Description',
    'description_helper' => 'Internal notes or description for this variant. This content is only visible to admin.',
    'type_label' => 'Type',
    'type_helper' => 'Select the type of this variant.',
    'code_label' => 'Code',
    'code_helper' => 'Enter a identifier used by the system to reference this item. This value should be short and consistent.',
    'status_label' => 'Status',
    'status_helper' => 'Set the status of the variant to visible or hidden.',
    'status_options' => [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
    ],
    'is_upgradable_label' => 'Is Upgradable?',
    'is_upgradable_helper' => 'Enable to allow this variant to be upgraded or changed after purchase.',
    'package_label' => 'Package',
    'package_helper' => 'Select the package this variant belongs to.',

    'options' => [
        'pricing_label' => 'Pricing',
        'pricing_helper' => 'Manage the pricing options for this variant.',
        'name_label' => 'Name',
        'name_helper' => 'Enter the name of the variant option.',
        'value_label' => 'Value',
        'value_helper' => 'Enter the value of the variant option.',

        'pricing' => [
            'name_label' => 'Name',
            'name_helper' => 'Enter the name for this pricing option.',
            'type_label' => 'Type',
            'type_helper' => 'Select the type of pricing for this variant option.',
            'time_interval_label' => 'Time Interval',
            'time_interval_helper' => 'Choose the billing interval for this pricing option.',
            'billing_period_label' => 'Billing Period',
            'billing_period_helper' => 'Select how often the customer will be billed for this variant option.',
            'currency_code_label' => 'Currency Code',
            'currency_code_helper' => 'Displays the currency used for this price. This value cannot be changed here.',
            'price_label' => 'Price',
            'price_helper' => 'Set the price for this variant option in the selected currency.',
            'setup_fee_label' => 'Setup Fee',
            'setup_fee_helper' => 'Enter any one-time setup fee for this pricing option.',
            'enabled_label' => 'Enabled?',
            'enabled_helper' => 'Enable or disable this pricing option without deleting it.',
        ]
    ]
];