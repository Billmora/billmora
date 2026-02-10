<?php

return [
    'view_package' => 'View Package',
    
    'order_now' => 'Order Now',
    'order_unavailable' => 'Order Unavailable',
    'order_out_of_stock' => 'Out of Stock',

    'stock_unavailable' => ':item Out of Stock',
    'stock_available' => ':item Stock Available',

    'package' => [
        'billing_cycle' => 'Billing cycle',
        'order_summary' => 'Order Summary',
        'subtotal' => 'Subtotal',
        'setup_fee' => 'Setup Fee',
        'due_today' => 'Total Due Today',
        'next_billing' => 'Price that will be charged on the next billing cycle',
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
];