<?php

return [
    'view_package' => 'View Package',
    'view_store' => 'View Store',
    
    'order_now' => 'Order Now',
    'order_unavailable' => 'Order Unavailable',
    'order_out_of_stock' => 'Out of Stock',

    'stock_unavailable' => ':item Out of Stock',
    'stock_available' => ':item Stock Available',

    'package' => [
        'billing_cycle' => 'Billing cycle',
        'configuration' => 'Configuration',
        'order_summary' => 'Order Summary',
        'subtotal' => 'Subtotal',
        'setup_fee' => 'Setup Fee',
        'due_today' => 'Total Due Today',
        'next_billing' => 'Price that will be charged on the next billing cycle',
        'add_to_cart' => 'Add to Cart',
        'checkout' => 'Checkout',
    ],

    'order' => [
        'cycle_mismatch' => 'Selected billing cycle does not belong to the selected package.',
        'cycle_currency_unavailable' => 'The selected billing cycle is not available for your selected currency.',
        'variant_mismatch' => 'One or more selected variants do not belong to this package.',
        'variant_price_missing' => 'One or more selected variants do not have prices for the selected billing cycle.',
        'option_invalid' => 'One or more selected options are invalid or do not belong to this package.',
        'option_missing' => 'One or more selected options were not found.',
        'option_unavailable' => 'The option ":attribute" is not available for the selected billing cycle and currency.',
    ],

    'unavailable_currency' => 'Unavailable in this currency.',

    'domain_search_label' => 'Search Domain',
    'domain_search_placeholder' => 'Find your perfect domain name...',
    'domain_register_tab' => 'Register',
    'domain_transfer_tab' => 'Transfer',
    'domain_available' => 'Available!',
    'domain_unavailable' => 'Taken or Unavailable',
    'domain_add_to_cart' => 'Add to Cart',
    'domain_epp_code_label' => 'EPP / Authorization Code',
    'domain_epp_code_helper' => 'Required for domain transfer. Get it from your current registrar.',
    'domain_years_label' => 'Registration Period',
    'domain_disabled' => 'Domain registration and transfer are currently unavailable.',
    'domain_configure_helper' => 'Please configure your domain below. You can specify custom nameservers or use our default ones.',
];