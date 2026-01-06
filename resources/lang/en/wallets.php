<?php

return [
    'title' => 'Wallets',
    'list' => 'Wallet Transactions',
    'manage_wallet' => 'Manage Customer Wallet',
    'manage_hint' => 'Select a user, choose credit/debit, amount and transaction type.',
    'create_new' => 'Manage Wallet',
    'submit' => 'Save',

    'created_successfully' => 'Transaction completed successfully.',

    'created_at' => 'Created At',

    'fields' => [
        'user' => 'User',
        'direction' => 'Direction',
        'type' => 'Type',
        'amount' => 'Amount (SAR)',
        'balance_before' => 'Before',
        'balance_after' => 'After',
        'description' => 'Description',
        'description_ar' => 'Description (AR)',
        'description_en' => 'Description (EN)',
        'reference' => 'Reference',
    ],

    'directions' => [
        'credit' => 'Credit',
        'debit'  => 'Debit',
    ],

    'types' => [
        'topup' => 'Topup',
        'refund' => 'Refund',
        'adjustment' => 'Adjustment',
        'booking_charge' => 'Booking Charge',
        'package_purchase' => 'Package Purchase',
    ],

    'wallet_snapshot' => 'Wallet Snapshot',
    'wallet_snapshot_hint' => 'Snapshot updates when you select a user.',
    'wallet' => [
        'balance' => 'Current Balance',
        'total_credit' => 'Total Credits',
        'total_debit' => 'Total Debits',
        'currency' => 'Currency',
    ],
    'wallet_note' => 'Note: debit requires sufficient balance.',

    'placeholders' => [
        'description_ar' => 'e.g. Admin topup...',
        'description_en' => 'e.g. Admin topup...',
    ],

    'filters' => [
        'all' => 'All',
        'reset' => 'Reset Filters',
        'user' => 'User',
        'user_placeholder' => 'Select user...',
        'direction' => 'Direction',
        'direction_placeholder' => 'All directions',
        'type' => 'Type',
        'type_placeholder' => 'All types',
        'date_from' => 'Date from',
        'date_to' => 'Date to',
    ],

    'validation' => [
        'invalid_type_for_direction' => 'Transaction type is not compatible with the selected direction.',
    ],
];