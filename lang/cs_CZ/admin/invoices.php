<?php

return [
    'tabs' => [
        'summary' => 'Summary',
        'transaction' => 'Transaction',
        'refund' => 'Refund',
    ],

    'number_label' => 'Invoice Number',
    'user_label' => 'User',
    'user_helper' => 'Select the user this invoice is assigned to.',
    'status_label' => 'Status',
    'status_helper' => 'Select the current status of the invoice',
    'date_label' => 'Invoice Date',
    'date_helper' => 'Set the date when this invoice is issued.',
    'due_date_label' => 'Due Date',
    'due_date_helper' => 'Set the payment due date for this invoice.',
    'currency_label' => 'Currency',
    'currency_helper' => 'Select the currency used for this invoice.',
    'email_label' => 'Send Invoice Email',
    'email_helper' => 'Enable to notify the user by email when this invoice is created.',
    'invoice_items' => [
        'items_label' => 'Items',
        'items_helper' => 'Add invoice items with description, quantity, and price.',
        'description_label' => 'Description',
        'description_helper' => 'Enter a description of the invoice item or service.',
        'quantity_label' => 'Quantity',
        'quantity_helper' => 'Enter the quantity for this invoice item.',
        'unit_price_label' => 'Unit Price',
        'unit_price_helper' => 'Enter the price per unit. Use a negative value to apply a discount.',
    ],
    'total_label' => 'Total',
    'download_label' => 'Download as PDF',
    'add_new_items_label' => 'Add new items',

    'refund' => [
        'type_label' => 'Refund Type',
        'type_helper' => 'Select the refund method. Gateway processes the refund through the payment gateway, Manual records it manually.',
        'amount_label' => 'Refund Amount',
        'amount_helper' => 'Enter the amount to be refunded from this transaction.',
        'reference_label' => 'Reference Number',
        'reference_helper' => 'Enter the reference number or ID for this refund. Only applicable for manual refunds.',

        'already_fully_refunded'      => 'This invoice has already been fully refunded.',
        'no_gateway_associated'       => 'No payment gateway is associated with this invoice.',
        'gateway_not_support_refund'  => 'The selected gateway does not support automated refunds.',
        'gateway_rejected'            => 'The gateway rejected the refund request. Please try again or process it manually.',

        'success' => 'Refund processed successfully.',
        'transaction_description' => 'Refund Processed (:type)',

        'type_gateway' => 'Automatic via Gateway',
        'type_manual'  => 'Manual Process (External)',
    ],
];