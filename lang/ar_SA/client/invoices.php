<?php

return [
    'title' => 'Invoices',
    'invoice_number_label' => 'Invoice Number',
    'invoice_date_label' => 'Invoice Date',
    'due_date_label' => 'Due Date',
    'total_label' => 'Total',

    'download_label' => 'Download as PDF',
    'invoice_label' => 'Invoice #:number',
    'payment_label' => 'Payment Method',
    'payment_process_label' => 'Proceed to Payment',
    'bill_to_label' => 'Bill To',
    'issued_to_label' => 'Issued To',
    'invoice_items_label' => 'Invoice Items',
    'description_label' => 'Description',
    'quantity_label' => 'Quantity',
    'unit_price_label' => 'Unit Price',
    'amount_label' => 'Amount',
    'subtotal_label' => 'Subtotal',
    'discount_label' => 'Discount',
    'total_due_label' => 'Total Due',
    'currency_label' => 'Currency',
    'status' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'payment' => [
        'already_processed' => 'This invoice has been paid or cancelled.',
        'method_required' => 'Please select a payment method first.',
        'invalid_method' => 'Payment method is invalid.',
    ],

    'credit' => [
        'available' => 'Available Credit:',
        'submit_payment' => 'Settle with Credit Balance',

        'cannot_pay_deposit' => 'Credit balance cannot be used to pay a Credit Deposit invoice.',
        'insufficient_balance' => 'You do not have sufficient credit balance to complete this payment.',
        'fully_settled' => 'Your invoice has been fully settled using your credit balance.',
        'partially_applied' => 'Credit balance applied. Please pay the remaining amount due.',
        'transaction_description' => 'Credit Balance Applied',
    ],
];