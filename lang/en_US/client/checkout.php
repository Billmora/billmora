<?php

return [
    'coupon_label' => 'Coupon',
    'notes_label' => 'Notes',
    'payment_label' => 'Payment Method',
    'agree_terms' => 'I agree with the :attribute',

    'order_summary' => 'Order Summary',
    'subtotal' => 'Subtotal',
    'setup_fee' => 'Setup Fee',
    'discount' => 'Discount',
    'total_due' => 'Total Due',
    'next_billing' => 'Price that will be charged on the next billing cycle',
    'complete_order' => 'Complete Order',

    'coupon' => [
        'invalid' => 'Invalid or expired coupon code.',
        'package_mismatch' => 'This coupon is not applicable to the selected package.',
        'billing_mismatch' => 'This coupon is not applicable to the selected billing cycle.',
        'limit_reached' => 'This coupon has reached its usage limit.',
        'user_limit_reached' => 'You have reached the usage limit for this coupon.',
        'applied' => 'Coupon applied successfully!',
        'removed' => 'Coupon removed successfully.',
    ],

    'complete' => [
        'heading' => 'Thank you for your order!',
        'message' => 'If you have any questions about your order, please open a support ticket from your client area.',
        'information' => 'Your order number is #:order_number.',
        'unpaid_note' => 'Your order is currently unpaid. Please complete the payment to activate your services.',
        'view_invoice' => 'View Invoice',
        'back_to_client' => 'Back to Client Area',
    ],

    'session' => [
        'expired' => 'Session expired. Please select a package again.',
        'missing_data' => 'No checkout data found. Please select a package first.',
        'currency_mismatch' => 'The selected configuration is not available for the current currency. Please select again.',
    ],
];