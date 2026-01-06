<?php

return [
    'title' => 'Payments',
    'list' => 'Payments List',
    'payment' => 'Payment',
    'view' => 'View',
    'print' => 'Print',
    'back_to_list' => 'Back to list',
    'actions_title' => 'Actions',

    'filters' => [
        'search_placeholder' => 'Search: payment id / customer / invoice / gateway...',
        'status_placeholder' => 'Status',
        'method_placeholder' => 'Method',
        'has_invoice_placeholder' => 'Has invoice?',
        'has_invoice_yes' => 'Yes',
        'has_invoice_no' => 'No',
        'gateway_placeholder' => 'Gateway (e.g. moyasar)',
        'payable_type_placeholder' => 'payable_type (e.g. booking_payment)',
        'from' => 'From',
        'to' => 'To',
        'reset' => 'Reset',
    ],

    'status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'method' => [
        'wallet' => 'Wallet',
        'credit_card' => 'Credit Card',
        'apple_pay' => 'Apple Pay',
        'google_pay' => 'Google Pay',
        'cash' => 'Cash',
        'visa' => 'Visa',
        'stc' => 'STC Pay',
    ],

    'fields' => [
        'id' => 'ID',
        'user' => 'Customer',
        'invoice' => 'Invoice',
        'payable' => 'Payable',
        'method' => 'Method',
        'status' => 'Status',
        'gateway' => 'Gateway',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'paid_at' => 'Paid at',
        'created_at' => 'Created at',

        'gateway_status' => 'Gateway status',
        'gateway_payment_id' => 'Gateway payment ID',
        'gateway_invoice_id' => 'Gateway invoice ID',
        'gateway_transaction_url' => 'Transaction URL',
    ],

    'summary_title' => 'Payment summary',
    'links_title' => 'Links & references',

    'gateway_title' => 'Gateway details',
    'gateway_raw_title' => 'Gateway raw payload',
    'meta_title' => 'Meta',

    'open_transaction' => 'Open transaction link',
    'open_invoice' => 'Open invoice',
    'invoice_notice_title' => 'This payment is linked to an invoice',
    'invoice_total' => 'Invoice total',

    'notice_failed_title' => 'Payment failed',
    'notice_failed_text' => 'Check gateway_status and gateway_raw for details.',
];