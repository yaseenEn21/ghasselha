<?php

return [
    'title' => 'Bookings',
    'booking' => 'Booking',
    'view' => 'View',
    'back_to_list' => 'Back to list',

    'tabs' => [
        'overview' => 'Overview',
        'products' => 'Products',
        'invoices' => 'Invoices',
        'logs' => 'Status Logs',
        'meta' => 'Meta',
    ],

    'columns' => [
        'customer' => 'Customer',
        'service' => 'Service',
        'schedule' => 'Schedule',
        'employee' => 'Employee',
        'total' => 'Total',
        'status' => 'Status',
        'actions' => 'Actions',
    ],

    'filters' => [
        'search_placeholder' => 'Search: booking id / customer name / mobile / service...',
        'status_placeholder' => 'Status',
        'time_period_placeholder' => 'Time period',
        'service_placeholder' => 'Service',
        'employee_placeholder' => 'Employee ID (optional)',
        'zone_placeholder' => 'Zone ID (optional)',
        'reset' => 'Reset',
    ],

    'status' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'moving' => 'Moving',
        'arrived' => 'Arrived',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'time_period' => [
        'morning' => 'Morning',
        'evening' => 'Evening',
        'all' => 'All day',
    ],

    'customer' => 'Customer',
    'user_id' => 'User ID',
    'car' => 'Car',
    'address' => 'Address',

    'assignment' => 'Assignment & Context',
    'employee' => 'Employee',
    'zone' => 'Zone',
    'time_period_label' => 'Time period',
    'package_subscription' => 'Package subscription',
    'package_cover_hint' => 'This booking is linked to a package subscription and may be partially/fully covered by package rules.',

    'duration' => 'Duration',
    'minutes' => 'min',

    'pricing' => 'Pricing',
    'pricing_source' => 'Pricing source',
    'pricing_source_values' => [
        'base' => 'Base price',
        'zone' => 'Zone price',
        'group' => 'Customer group price',
        'package' => 'Covered by package',
    ],
    'service_unit_price' => 'Service unit price (before cover)',
    'service_charge_amount' => 'Charge amount to customer',
    'pricing_meta' => 'Pricing meta',

    'subtotal' => 'Subtotal',
    'discount' => 'Discount',
    'tax' => 'Tax',
    'total' => 'Total',
    'total_snapshot' => 'Booking total',

    'lifecycle' => 'Lifecycle',
    'created_at' => 'Created at',
    'confirmed_at' => 'Confirmed at',
    'cancelled_at' => 'Cancelled at',
    'cancel_reason' => 'Cancel reason',

    'products_subtotal' => 'Products subtotal',
    'products' => [
        'product' => 'Product',
        'product_id' => 'Product ID',
        'qty' => 'Qty',
        'unit_price' => 'Unit price',
        'line_total' => 'Line total',
        'empty' => 'No products in this booking.',
    ],

    'invoices' => [
        'number' => 'Invoice #',
        'status' => 'Status',
        'type' => 'Type',
        'total' => 'Total',
        'paid_at' => 'Paid at',
        'empty' => 'No invoices linked to this booking.',
    ],
    'latest_unpaid_invoice_hint' => 'There is an unpaid invoice linked to this booking:',

    'logs' => [
        'from' => 'From',
        'to' => 'To',
        'by' => 'By',
        'empty' => 'No status logs for this booking.',
    ],

    'meta' => 'Meta',
    'meta_empty' => 'No meta data.',
    'raw' => 'Raw Payload',
];