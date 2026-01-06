<?php

return [
    'title' => 'Promotions & Coupons',
    'list' => 'Promotions List',
    'singular_title' => 'Promotion',

    'create_new' => 'Create Promotion',
    'create' => 'Create',
    'edit' => 'Edit',
    'update' => 'Update',
    'view' => 'View',
    'delete' => 'Delete',

    'save' => 'Save',
    'done' => 'Done',
    'cancel' => 'Cancel',

    'back_to_list' => 'Back to list',
    'promotion_details' => 'Promotion Details',
    'quick_actions' => 'Quick Actions',

    'created_successfully' => 'Promotion created successfully.',
    'updated_successfully' => 'Promotion updated successfully.',
    'deleted_successfully' => 'Promotion deleted successfully.',

    'basic_data' => 'Basic Data',
    'basic_data_hint' => 'Promotion name/description in both languages.',
    'discount_block' => 'Discount Settings',
    'period_block' => 'Promotion Period',
    'scope' => 'Scope',

    'fields' => [
        'name' => 'Name',
        'applies_to' => 'Applies To',
        'discount' => 'Discount',
        'period' => 'Period',
        'status' => 'Status',
        'coupons_count' => 'Coupons',
    ],

    'name_ar' => 'Arabic Name',
    'name_en' => 'English Name',
    'description_ar' => 'Arabic Description',
    'description_en' => 'English Description',

    'applies_to' => 'Applies to',
    'applies_to_service' => 'Services',
    'applies_to_package' => 'Packages',
    'applies_to_both' => 'Both',

    'apply_all_services' => 'Apply to all services',
    'apply_all_packages' => 'Apply to all packages',

    'select_services' => 'Select services',
    'select_packages' => 'Select packages',
    'select2_hint' => 'Open to load first 10, type to search.',

    'discount_type' => 'Discount Type',
    'discount_type_percent' => 'Percent (%)',
    'discount_type_fixed' => 'Fixed amount (SAR)',
    'discount_value' => 'Discount value',
    'max_discount' => 'Max discount',
    'starts_at' => 'Starts at',
    'ends_at' => 'Ends at',
    'created_at' => 'Created at',

    'status' => 'Status',
    'active' => 'Active',
    'inactive' => 'Inactive',

    'actions_title' => 'Actions',

    'delete_confirm_title' => 'Confirm delete',
    'delete_confirm_text' => 'Are you sure you want to delete this item?',

    'coupons_manage' => 'Manage coupons',
    'add_coupon' => 'Add coupon',
    'back_to_promotion' => 'Back to promotion',

    'coupon_code' => 'Code',
    'coupon_period' => 'Coupon validity',
    'usage_limit_total' => 'Total limit',
    'usage_limit_per_user' => 'Per-user limit',
    'used_count' => 'Used',

    'coupon_created_successfully' => 'Coupon created successfully.',
    'coupon_updated_successfully' => 'Coupon updated successfully.',
    'coupon_deleted_successfully' => 'Coupon deleted successfully.',

    'redemptions' => 'Redemptions',
    'discount_amount' => 'Discount',
    'invoice_id' => 'Invoice',
    'applied_at' => 'Applied at',
    'redemption_applied' => 'Applied',
    'redemption_voided' => 'Voided',

    'filters' => [
        'search_placeholder' => 'Search by name (AR/EN)...',
        'search_coupon_placeholder' => 'Search by code...',
        'status_placeholder' => 'All statuses',
        'applies_to_placeholder' => 'All scopes',
        'discount_type_placeholder' => 'All types',
    ],

    'save_changes' => 'Save changes',

    'coupons' => [
        'title' => 'Promotion Coupons',
        'create' => 'Add Coupon',
        'edit' => 'Edit Coupon',
        'back_to_list' => 'Back to coupons',
        'status' => 'Status',

        'basic_data' => 'Coupon Data',
        'basic_data_hint' => 'Create a coupon and configure validity/limits.',
        'rules' => 'Rules & Limits',
        'rules_hint' => 'Usage limits and invoice/discount constraints.',
        'stats' => 'Stats',
        'promotion_snapshot' => 'Promotion Snapshot',
        'promotion_hint' => 'Coupon inherits promotion discount settings; can override some constraints.',

        'fields' => [
            'code' => 'Code',
            'status' => 'Status',
            'starts_at' => 'Starts at',
            'ends_at' => 'Ends at',
            'usage_limit_total' => 'Total usage limit',
            'usage_limit_per_user' => 'Per-user limit',
            'used_count' => 'Used count',
            'min_invoice_total' => 'Min invoice total',
            'max_discount' => 'Coupon max discount',
            'meta' => 'Meta (JSON)',
            'period' => 'Period',
            'limits' => 'Limits',
        ],

        'limits_total' => 'Total',
        'limits_per_user' => 'Per user',

        'filters' => [
            'search_placeholder' => 'Search by code...',
            'status_placeholder' => 'All statuses',
        ],

        'code_hint' => 'Saved as uppercase.',
        'meta_hint' => 'Optional, must be valid JSON.',

        'redemptions_title' => 'Coupon Redemptions',
        'redemptions_search_placeholder' => 'Search user (name/mobile)...',
        'user' => 'User',
        'invoice' => 'Invoice',
        'discount_amount' => 'Discount amount',
        'applied_at' => 'Applied at',

        'status_applied' => 'Applied',
        'status_voided' => 'Voided',

        'created_successfully' => 'Coupon created successfully.',
        'updated_successfully' => 'Coupon updated successfully.',
        'deleted_successfully' => 'Coupon deleted successfully.',

        'delete_confirm_title' => 'Delete coupon',
        'delete_confirm_text' => 'Are you sure you want to delete this coupon?',
    ],

];