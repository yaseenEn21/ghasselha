<?php

return [
    'title' => 'Zones',
    'create' => 'Create Zone',
    'edit' => 'Edit Zone',

    'create_new' => 'Add New Zone',
    'back_to_list' => 'Back to list',

    'basic_data' => 'Basic Data',
    'basic_data_hint' => 'Set zone name, ordering and status. You may paste Polygon JSON.',

    'fields' => [
        'name' => 'Zone Name',
        'polygon' => 'Polygon',
        'bbox' => 'Bounding Box',
        'center' => 'Center',
        'sort_order' => 'Sort Order',
        'status' => 'Status',
        'prices_count' => 'Service Prices Count',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'id_label' => 'ID',
    'show' => 'View Zone',

    'map_title' => 'Map',
    'fit_bounds' => 'Fit bounds',

    'location_details' => 'Location Details',
    'general_hint' => 'Use the tabs to view zone details and related service prices.',

    'no_polygon_notice' => 'No polygon has been drawn for this zone yet.',
    'no_prices_notice' => 'No service prices have been set for this zone yet.',


    'view' => 'View Zone',
    'edit_zone' => 'Edit Zone',

    'map' => 'Map',
    'map_hint_show' => 'Showing the zone polygon (read-only).',

    'tabs' => [
        'general' => 'General',
        'service_prices' => 'Service Prices',
    ],

    'time_periods' => [
        'all' => 'All day',
        'morning' => 'Morning',
        'evening' => 'Evening',
    ],

    'service_prices' => [
        'hint' => 'Set zone-specific prices for services (by time period).',
        'count' => 'Prices count',
        'add' => 'Add service price',
        'edit' => 'Edit service price',
        'empty' => 'No service prices yet.',
        'service' => 'Service',
        'time_period' => 'Time period',
        'price' => 'Price',
        'discounted_price' => 'Discounted price',
        'status' => 'Status',
        'base_price' => 'Base price',
        'base_discounted' => 'Base discounted',
        'unique_notice' => 'Only one record allowed per (service + zone + time period).',
        'delete_confirm' => 'This will remove the service price from the zone.',
        'created_successfully' => 'Service price added successfully.',
        'updated_successfully' => 'Service price updated successfully.',
        'deleted_successfully' => 'Service price deleted successfully.',
    ],

    'auto_bbox_notice' => 'Bounding Box is calculated automatically on save to speed up geo filtering.',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'created_at' => 'Created at',
    'actions' => 'Actions',
    'delete' => 'Delete',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'done' => 'Done',
    'are_you_sure' => 'Are you sure?',

    'prices' => [
        'service' => 'Service',
        'service_id' => 'Service ID',
        'time_period' => 'Time period',
        'price' => 'Price',
        'discounted_price' => 'Discounted price',
        'status' => 'Status',
        'created_at' => 'Created at',
    ],

    'time_period' => [
        'all' => 'All day',
        'morning' => 'Morning',
        'evening' => 'Evening',
    ],

    'placeholders' => [
        'name' => 'e.g. Al Rimal District',
        'polygon' => 'Example:
[
  {"lat":26.1234567,"lng":50.1234567},
  {"lat":26.2234567,"lng":50.2234567},
  {"lat":26.3234567,"lng":50.3234567}
]',
    ],

    'polygon_hint' => 'You can paste array of points [{lat,lng},...] or GeoJSON Polygon. BBox and center are computed automatically on save.',
    'auto_bbox_notice' => 'On saving polygon, Bounding Box and center are computed automatically to speed up lookups.',

    'filters' => [
        'search_placeholder' => 'Search by name...',
        'status_placeholder' => 'Status',
        'reset' => 'Reset',
    ],

    'has_polygon' => 'Has Polygon',
    'no_polygon' => 'No Polygon',

    'active' => 'Active',
    'inactive' => 'Inactive',

    'actions_title' => 'Actions',
    'save' => 'Save',
    'save_changes' => 'Save Changes',
    'delete' => 'Delete',
    'cancel' => 'Cancel',
    'done' => 'Done',

    'created_successfully' => 'Zone created successfully.',
    'updated_successfully' => 'Zone updated successfully.',
    'deleted_successfully' => 'Zone deleted successfully.',

    'delete_confirm_title' => 'Delete confirmation',
    'delete_confirm_text' => 'Are you sure you want to delete this zone?',
];