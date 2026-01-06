<?php

return [
    'title' => 'Invoices',
    'list' => 'Invoices List',
    'invoice' => 'Invoice',
    'view' => 'View',
    'print' => 'Print',
    'back_to_list' => 'Back to list',
    'actions_title' => 'Actions',

    'locked' => 'Locked',
    'copied' => 'Copied',
    'copy_number' => 'Copy invoice number',

    'filters' => [
        'search_placeholder' => 'Search by ID / number / customer...',
        'status_placeholder' => 'Status',
        'type_placeholder' => 'Type',
        'locked_placeholder' => 'Lock',
        'locked_yes' => 'Locked',
        'locked_no' => 'Not locked',
        'from' => 'From',
        'to' => 'To',
        'reset' => 'Reset',
    ],

    'type' => [
        'invoice' => 'Invoice',
        'adjustment' => 'Adjustment',
        'credit_note' => 'Credit Note',
    ],

    'status' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'fields' => [
        'id' => 'ID',
        'number' => 'Number',
        'user' => 'Customer',
        'invoiceable' => 'Reference',
        'type' => 'Type',
        'status' => 'Status',
        'locked' => 'Lock',
        'subtotal' => 'Subtotal',
        'discount' => 'Invoice Discount',
        'tax' => 'Tax',
        'gross_total' => 'Gross Total',
        'total' => 'Total',
        'currency' => 'Currency',
        'issued_at' => 'Issued at',
        'paid_at' => 'Paid at',
        'version' => 'Version',
        'parent_invoice' => 'Parent invoice',
        'child_invoices' => 'Related invoices',
    ],

    'items_title' => 'Invoice Items',
    'items_count' => 'Items count',

    'item' => [
        'fields' => [
            'type' => 'Item type',
            'title' => 'Item',
            'qty' => 'Qty',
            'unit_price' => 'Unit price',
            'line_tax' => 'Line tax',
            'line_total' => 'Line total',
        ],
        'type' => [
            'service' => 'Service',
            'product' => 'Product',
            'fee' => 'Fee',
            'custom' => 'Custom',
        ],
    ],

    'payments_title' => 'Payments',
    'payment' => [
        'fields' => [
            'amount' => 'Amount',
            'method' => 'Method',
            'status' => 'Status',
            'paid_at' => 'Paid at',
        ],
    ],

    'totals_title' => 'Totals summary',

    'notice_unpaid_title' => 'Invoice is unpaid',
    'notice_unpaid_text' => 'You may apply coupon/adjustments before payment based on policies.',

    'coupon_title' => 'Coupon details',
    'coupon' => [
        'code' => 'Code',
        'discount' => 'Discount amount',
        'eligible_base' => 'Eligible base',
        'applied_at' => 'Applied at',
    ],

    'relations_title' => 'Relations',
    'meta_title' => 'Meta',
];