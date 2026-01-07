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