<?php

return [
    'title' => 'Invoices',
    'invoice_number' => 'Invoice Number',
    'invoice_date' => 'Invoice Date',
    'due_date' => 'Due Date',
    'total' => 'Total',

    'download_label' => 'Download as PDF',
    'invoice_label' => 'Invoice #:number',
    'payment_label' => 'Payment Method',
    'payment_process_label' => 'Proceed to Payment',
    'bill_to' => 'Bill To',
    'issued_to' => 'Issued To',
    'invoice_items' => 'Invoice Items',
    'description' => 'Description',
    'quantity' => 'Quantity',
    'unit_price' => 'Unit Price',
    'amount' => 'Amount',
    'subtotal' => 'Subtotal',
    'discount' => 'Discount',
    'total_due' => 'Total Due',
    'currency' => 'Currency',
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