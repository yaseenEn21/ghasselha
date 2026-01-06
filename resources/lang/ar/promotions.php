<?php

return [
    'title' => 'العروض والكوبونات',
    'list' => 'قائمة العروض',
    'singular_title' => 'عرض ترويجي',

    'create_new' => 'إضافة عرض جديد',
    'create' => 'إنشاء',
    'edit' => 'تعديل',
    'update' => 'تحديث',
    'view' => 'عرض',
    'delete' => 'حذف',

    'save' => 'حفظ',
    'done' => 'تم',
    'cancel' => 'إلغاء',

    'back_to_list' => 'العودة للقائمة',
    'promotion_details' => 'تفاصيل العرض',
    'quick_actions' => 'إجراءات سريعة',

    'created_successfully' => 'تم إنشاء العرض بنجاح.',
    'updated_successfully' => 'تم تحديث العرض بنجاح.',
    'deleted_successfully' => 'تم حذف العرض بنجاح.',

    // blocks
    'basic_data' => 'البيانات الأساسية',
    'basic_data_hint' => 'اسم ووصف العرض بلغتين.',
    'discount_block' => 'إعدادات الخصم',
    'period_block' => 'فترة العرض',
    'scope' => 'نطاق التطبيق',

    // fields
    'fields' => [
        'name' => 'الاسم',
        'applies_to' => 'يطبق على',
        'discount' => 'قيمة الخصم',
        'period' => 'الفترة',
        'status' => 'الحالة',
        'coupons_count' => 'عدد الكوبونات',
    ],

    'name_ar' => 'الاسم بالعربية',
    'name_en' => 'الاسم بالإنجليزية',
    'description_ar' => 'الوصف بالعربية',
    'description_en' => 'الوصف بالإنجليزية',

    'applies_to' => 'يطبق على',
    'applies_to_service' => 'الخدمات',
    'applies_to_package' => 'الباقات',
    'applies_to_both' => 'الخدمات والباقات',

    'apply_all_services' => 'تطبيق على كل الخدمات',
    'apply_all_packages' => 'تطبيق على كل الباقات',

    'select_services' => 'اختيار خدمات محددة',
    'select_packages' => 'اختيار باقات محددة',
    'select2_hint' => 'افتح القائمة لعرض أول 10، أو اكتب للبحث.',

    'discount_type' => 'نوع الخصم',
    'discount_type_percent' => 'نسبة مئوية (%)',
    'discount_type_fixed' => 'مبلغ ثابت (SAR)',
    'discount_value' => 'قيمة الخصم',
    'max_discount' => 'حد أقصى للخصم',
    'starts_at' => 'تاريخ البداية',
    'ends_at' => 'تاريخ النهاية',

    'status' => 'الحالة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'created_at' => 'تاريخ الانشاء',

    'actions_title' => 'إجراءات',

    'delete_confirm_title' => 'تأكيد الحذف',
    'delete_confirm_text' => 'هل أنت متأكد من حذف هذا العنصر؟',

    // coupons
    'coupons_manage' => 'إدارة الكوبونات',
    'add_coupon' => 'إضافة كوبون',
    'back_to_promotion' => 'العودة للعرض',

    'coupon_code' => 'الكود',
    'coupon_period' => 'صلاحية الكوبون',
    'usage_limit_total' => 'حد الاستخدام (إجمالي)',
    'usage_limit_per_user' => 'حد الاستخدام (لكل مستخدم)',
    'used_count' => 'مرات الاستخدام',

    'coupon_created_successfully' => 'تم إنشاء الكوبون بنجاح.',
    'coupon_updated_successfully' => 'تم تحديث الكوبون بنجاح.',
    'coupon_deleted_successfully' => 'تم حذف الكوبون بنجاح.',

    // redemptions
    'redemptions' => 'سجل الاستخدام',
    'discount_amount' => 'قيمة الخصم',
    'invoice_id' => 'رقم الفاتورة',
    'applied_at' => 'تاريخ التطبيق',
    'redemption_applied' => 'مطبق',
    'redemption_voided' => 'ملغي',
    'save_changes' => 'حفظ التغييرات',

    'filters' => [
        'search_placeholder' => 'بحث بالاسم (AR/EN)...',
        'search_coupon_placeholder' => 'بحث بالكود...',
        'status_placeholder' => 'كل الحالات',
        'applies_to_placeholder' => 'كل النطاقات',
        'discount_type_placeholder' => 'كل الأنواع',
    ],

    'coupons' => [
        'title' => 'كوبونات الحملة',
        'create' => 'إضافة كوبون',
        'edit' => 'تعديل الكوبون',
        'back_to_list' => 'العودة لقائمة الكوبونات',

        'status' => 'الحالة',
        'basic_data' => 'بيانات الكوبون',
        'basic_data_hint' => 'أنشئ كوبون وحدد صلاحيته وحدود استخدامه.',
        'rules' => 'الشروط والحدود',
        'rules_hint' => 'حدود استخدام وشروط أقل إجمالي.',
        'stats' => 'إحصائيات',
        'promotion_snapshot' => 'ملخص الحملة',
        'promotion_hint' => 'الكوبون يرث إعدادات الخصم من الحملة، ويمكنه override لبعض الشروط.',

        'fields' => [
            'code' => 'الكود',
            'status' => 'الحالة',
            'starts_at' => 'بداية الكوبون',
            'ends_at' => 'نهاية الكوبون',
            'usage_limit_total' => 'حد الاستخدام الإجمالي',
            'usage_limit_per_user' => 'حد الاستخدام لكل مستخدم',
            'used_count' => 'مرات الاستخدام',
            'min_invoice_total' => 'أقل إجمالي فاتورة',
            'max_discount' => 'سقف خصم للكوبون',
            'meta' => 'بيانات إضافية (JSON)',
            'period' => 'الفترة',
            'limits' => 'الحدود',
        ],

        'limits_total' => 'إجمالي',
        'limits_per_user' => 'لكل مستخدم',

        'filters' => [
            'search_placeholder' => 'بحث بالكود...',
            'status_placeholder' => 'كل الحالات',
        ],

        'code_hint' => 'سيتم حفظه كـ Uppercase.',
        'meta_hint' => 'اختياري، اكتب JSON صحيح.',

        'redemptions_title' => 'سجل استخدامات الكوبون',
        'redemptions_search_placeholder' => 'بحث بالمستخدم (الاسم/الجوال)...',
        'user' => 'المستخدم',
        'invoice' => 'الفاتورة',
        'discount_amount' => 'قيمة الخصم',
        'applied_at' => 'وقت التطبيق',

        'status_applied' => 'مطبق',
        'status_voided' => 'ملغي',

        'created_successfully' => 'تم إنشاء الكوبون بنجاح.',
        'updated_successfully' => 'تم تحديث الكوبون بنجاح.',
        'deleted_successfully' => 'تم حذف الكوبون بنجاح.',

        'delete_confirm_title' => 'حذف الكوبون',
        'delete_confirm_text' => 'هل أنت متأكد من حذف هذا الكوبون؟',
    ]
];