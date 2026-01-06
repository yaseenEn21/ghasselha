<?php

return [
    'title' => 'Loyalty System (Points)',
    'list' => 'Point Transactions',
    'manage_wallet' => 'Manage Point Wallet',

    'manage_hint' => 'Select a user, choose add/subtract points and write an optional note.',
    'wallet_snapshot' => 'Wallet Snapshot',
    'wallet_snapshot_hint' => 'Shown after selecting a user.',
    'wallet_note' => 'Balance will be updated immediately after saving.',
    'submit' => 'Save Transaction',

    'created_successfully' => 'Point transaction has been saved successfully.',

    'types' => [
        'earn' => 'Earn (Add)',
        'redeem' => 'Redeem (Subtract)',
        'adjust' => 'Adjust',
        'refund' => 'Refund',
    ],

    'actions' => [
        'add' => 'Add',
        'subtract' => 'Subtract',
    ],

    'fields' => [
        'user' => 'User',
        'mobile' => 'Mobile',
        'type' => 'Type',
        'points' => 'Points',
        'money_amount' => 'Money Amount',
        'reference' => 'Reference',
        'note' => 'Note',

        'action' => 'Action',
        'points_amount' => 'Points Amount',
    ],

    'wallet' => [
        'balance' => 'Current Balance',
        'total_earned' => 'Total Earned',
        'total_spent' => 'Total Spent',
    ],

    'placeholders' => [
        'user' => 'Search by name or mobile...',
        'note' => 'Write a note (optional)...',
    ],

    'filters' => [
        'search_placeholder' => 'Search by name, mobile, or email...',
        'type' => 'Type',
        'type_placeholder' => 'All types',
        'direction' => 'Direction',
        'direction_placeholder' => 'All',
        'plus' => 'Plus (+)',
        'minus' => 'Minus (-)',
        'archived' => 'Archiving',
        'archived_only' => 'Archived only',
        'not_archived' => 'Not archived',
        'date_from' => 'Date from',
        'date_to' => 'Date to',
        'all' => 'All',
        'reset' => 'Reset filters',
    ],

    'validation' => [
        'insufficient_balance' => 'Insufficient balance for subtraction. Available: :balance',
    ],
];