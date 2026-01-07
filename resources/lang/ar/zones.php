<?php

return [
    'title' => 'المناطق',
    'create' => 'إنشاء منطقة',
    'edit' => 'تعديل المنطقة',

    'create_new' => 'إضافة منطقة جديدة',
    'back_to_list' => 'العودة للقائمة',

    'basic_data' => 'البيانات الأساسية',
    'basic_data_hint' => 'حدّد اسم المنطقة وترتيبها وحالتها، ويمكنك إدخال Polygon بصيغة JSON.',

    'fields' => [
        'name' => 'اسم المنطقة',
        'polygon' => 'المخطط (Polygon)',
        'bbox' => 'Bounding Box',
        'center' => 'المركز',
        'sort_order' => 'الترتيب',
        'status' => 'الحالة',
        'prices_count' => 'عدد أسعار الخدمات',
        'created_at' => 'تاريخ الإنشاء',
    ],

    'placeholders' => [
        'name' => 'مثال: حي الرمال',
        'polygon' => 'مثال:
[
  {"lat":26.1234567,"lng":50.1234567},
  {"lat":26.2234567,"lng":50.2234567},
  {"lat":26.3234567,"lng":50.3234567}
]',
    ],

    'polygon_hint' => 'يمكنك إدخال Array نقاط [{lat,lng},...] أو GeoJSON Polygon. سيتم حساب Bounding Box والمركز تلقائياً عند الحفظ.',
    'auto_bbox_notice' => 'عند حفظ الـ Polygon سيتم حساب حدود المنطقة (BBox) والمركز تلقائياً لتسريع البحث.',

    'filters' => [
        'search_placeholder' => 'بحث بالاسم...',
        'status_placeholder' => 'الحالة',
        'reset' => 'إعادة ضبط',
    ],

    'has_polygon' => 'Polygon موجود',
    'no_polygon' => 'بدون Polygon',

    'active' => 'مفعّلة',
    'inactive' => 'غير مفعّلة',

    'actions_title' => 'الإجراءات',
    'save' => 'حفظ',
    'save_changes' => 'حفظ التغييرات',
    'delete' => 'حذف',
    'cancel' => 'إلغاء',
    'done' => 'تم',

    'created_successfully' => 'تم إنشاء المنطقة بنجاح.',
    'updated_successfully' => 'تم تحديث المنطقة بنجاح.',
    'deleted_successfully' => 'تم حذف المنطقة بنجاح.',

    'delete_confirm_title' => 'تأكيد الحذف',
    'delete_confirm_text' => 'هل أنت متأكد من حذف هذه المنطقة؟',
];