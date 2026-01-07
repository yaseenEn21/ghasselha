<?php

return [
    'title' => 'المناطق',
    'create' => 'إنشاء منطقة',
    'edit' => 'تعديل المنطقة',

    'create_new' => 'إضافة منطقة جديدة',
    'back_to_list' => 'العودة للقائمة',

    'basic_data' => 'البيانات الأساسية',
    'basic_data_hint' => 'حدّد اسم المنطقة وترتيبها وحالتها، ويمكنك إدخال Polygon بصيغة JSON.',

    'id_label' => 'المعرف',
    'show' => 'عرض المنطقة',

    'map_title' => 'الخريطة',
    'fit_bounds' => 'توسيط المنطقة',

    'location_details' => 'تفاصيل الموقع',
    'general_hint' => 'تستطيع من التبويبات استعراض تفاصيل المنطقة وأسعار الخدمات المرتبطة بها.',

    'no_polygon_notice' => 'لا يوجد مضلع مرسوم لهذه المنطقة حتى الآن.',
    'no_prices_notice' => 'لا توجد أسعار خدمات مخصصة لهذه المنطقة حتى الآن.',

    'tabs' => [
        'general' => 'عام',
        'service_prices' => 'أسعار الخدمات',
    ],

    'prices' => [
        'service' => 'الخدمة',
        'service_id' => 'رقم الخدمة',
        'time_period' => 'الفترة',
        'price' => 'السعر',
        'discounted_price' => 'السعر بعد الخصم',
        'status' => 'الحالة',
        'created_at' => 'تاريخ الإضافة',
    ],

    'time_period' => [
        'all' => 'طوال اليوم',
        'morning' => 'صباحي',
        'evening' => 'مسائي',
    ],

    'fields' => [
        'name' => 'اسم المنطقة',
        'polygon' => 'المخطط (Polygon)',
        'bbox' => 'Bounding Box',
        'center' => 'المركز',
        'sort_order' => 'الترتيب',
        'status' => 'الحالة',
        'prices_count' => 'عدد أسعار الخدمات',
        'created_at' => 'تاريخ الإنشاء',
        'updated_at' => 'تاريخ التحديث',
    ],

    'view' => 'عرض المنطقة',
    'edit_zone' => 'تعديل المنطقة',

    'map' => 'الخريطة',
    'map_hint_show' => 'عرض المضلع الخاص بالمنطقة (للقراءة فقط).',

    'time_periods' => [
        'all' => 'طوال اليوم',
        'morning' => 'صباحي',
        'evening' => 'مسائي',
    ],

    'service_prices' => [
        'hint' => 'تحديد أسعار خاصة لخدمات ضمن هذه المنطقة (حسب الفترة الزمنية).',
        'count' => 'عدد الأسعار',
        'add' => 'إضافة سعر خدمة',
        'edit' => 'تعديل سعر خدمة',
        'empty' => 'لا توجد أسعار خدمات بعد.',
        'service' => 'الخدمة',
        'time_period' => 'الفترة',
        'price' => 'السعر',
        'discounted_price' => 'السعر بعد الخصم',
        'status' => 'الحالة',
        'base_price' => 'السعر الأساسي',
        'base_discounted' => 'الأساسي بعد الخصم',
        'unique_notice' => 'مسموح سعر واحد فقط لكل (خدمة + منطقة + فترة).',
        'delete_confirm' => 'سيتم حذف سعر الخدمة من هذه المنطقة.',
        'created_successfully' => 'تمت إضافة سعر الخدمة بنجاح.',
        'updated_successfully' => 'تم تحديث سعر الخدمة بنجاح.',
        'deleted_successfully' => 'تم حذف سعر الخدمة بنجاح.',
    ],

    'auto_bbox_notice' => 'يتم احتساب الـ Bounding Box تلقائيًا عند الحفظ لتسريع البحث داخل المناطق.',
    'active' => 'مفعلة',
    'inactive' => 'مقفلة',
    'created_at' => 'تاريخ الإضافة',
    'actions' => 'إجراءات',
    'delete' => 'حذف',
    'save' => 'حفظ',
    'cancel' => 'إلغاء',
    'done' => 'تم',
    'are_you_sure' => 'هل أنت متأكد؟',


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