<?php

return [
    'title' => 'الحجوزات',
    'booking' => 'حجز',
    'view' => 'عرض',
    'back_to_list' => 'الرجوع للقائمة',

    'tabs' => [
        'overview' => 'عام',
        'products' => 'المنتجات',
        'invoices' => 'الفواتير',
        'logs' => 'سجل الحالات',
        'meta' => 'بيانات إضافية',
        'customer' => 'العملاء',
        'employees' => 'الموظفين',
        'booking' => 'الحجز',
    ],

    'columns' => [
        'customer' => 'العميل',
        'service' => 'الخدمة',
        'schedule' => 'الموعد',
        'employee' => 'الموظف',
        'total' => 'الإجمالي',
        'status' => 'الحالة',
        'status_control' => 'الحالة',
        'actions' => 'إجراءات',
    ],

    'filters' => [
        'search_placeholder' => 'بحث: رقم الحجز / اسم العميل / رقم الجوال / اسم الخدمة...',
        'status_placeholder' => 'الحالة',
        'time_period_placeholder' => 'الفترة',
        'service_placeholder' => 'الخدمة',
        'employee_placeholder' => 'رقم الموظف (اختياري)',
        'zone_placeholder' => 'رقم المنطقة (اختياري)',
        'reset' => 'إعادة ضبط',
    ],

    'status' => [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'مؤكد',
        'moving' => 'في الطريق',
        'arrived' => 'وصل',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغي',
    ],

    'time_period' => [
        'morning' => 'صباحي',
        'evening' => 'مسائي',
        'all' => 'طوال اليوم',
    ],

    'customer' => [
        'title' => 'العميل',
        'add_new' => 'إضافة عميل جديد',
        'modal_title' => 'إضافة عميل سريع',
        'name' => 'الاسم',
        'phone' => 'رقم الجوال',
        'name_ph' => 'اسم العميل',
        'phone_ph' => '05xxxxxxxx',
        'notice' => 'اختر عميلًا موجودًا أو أضف عميلًا جديدًا بسرعة، ثم أضف السيارة والعنوان.',
    ],
    'user_id' => 'رقم العميل',
    'car' => [
        'title' => 'السيارة',
        'add' => 'إضافة سيارة',
        'modal_title' => 'إضافة سيارة للعميل',
        'make' => 'الماركة',
        'model' => 'الموديل',
        'make_ph' => 'اختر الماركة',
        'model_ph' => 'اختر الموديل',
        'plate_number' => 'أرقام اللوحة',
        'plate_letters' => 'حروف اللوحة',
        'plate_letters_ar' => 'حروف عربية (اختياري)',
        'color' => 'اللون',
        'default' => 'افتراضية؟',
    ],
    'address' => [
        'title' => 'العنوان',
        'add' => 'إضافة عنوان',
        'modal_title' => 'إضافة عنوان للعميل',
        'hint' => 'الـSlots تعتمد على إحداثيات العنوان (Lat/Lng).',
        'type' => 'النوع',
        'types' => ['home' => 'منزل', 'work' => 'عمل', 'other' => 'أخرى'],
        'city' => 'المدينة',
        'area' => 'المنطقة',
        'country' => 'الدولة',
        'address_line' => 'وصف العنوان',
        'address_line_ph' => 'شارع، رقم بناية، تفاصيل...',
        'landmark' => 'معلم قريب',
        'link' => 'رابط خرائط (اختياري)',
        'default' => 'افتراضي؟',
        'map_hint' => 'اضغط على الخريطة لتحديد الموقع أو اسحب المؤشر.',
    ],

    'assignment' => 'التعيين والسياق',
    'employee' => 'الموظف',
    'zone' => 'المنطقة',
    'time_period_label' => 'الفترة',
    'package_subscription' => 'اشتراك الباقة',
    'package_cover_hint' => 'هذا الحجز مرتبط باشتراك باقة وقد يكون “مغطّى” جزئياً أو كلياً حسب منطق الباقة.',

    'duration' => 'المدة',
    'minutes' => 'دقيقة',

    'pricing' => 'التسعير',
    'pricing_source' => 'مصدر السعر',
    'pricing_source_values' => [
        'base' => 'السعر الأساسي',
        'zone' => 'سعر حسب المنطقة',
        'group' => 'سعر حسب مجموعة العملاء',
        'package' => 'مغطّى عبر باقة',
    ],
    'service_unit_price' => 'سعر الخدمة (قبل التغطية)',
    'service_charge_amount' => 'قيمة التحصيل على العميل',
    'pricing_meta' => 'تفاصيل التسعير (Meta)',

    'subtotal' => 'Subtotal',
    'discount' => 'Discount',
    'tax' => 'Tax',
    'total' => 'Total',
    'total_snapshot' => 'إجمالي الحجز',

    'lifecycle' => 'زمنيات الحجز',
    'created_at' => 'تاريخ الإنشاء',
    'confirmed_at' => 'تاريخ التأكيد',
    'cancelled_at' => 'تاريخ الإلغاء',
    'cancel_reason' => 'سبب الإلغاء',

    'products_subtotal' => 'إجمالي المنتجات',
    'products' => [
        'product' => 'المنتج',
        'product_id' => 'رقم المنتج',
        'qty' => 'الكمية',
        'unit_price' => 'سعر الوحدة',
        'line_total' => 'الإجمالي',
        'empty' => 'لا يوجد منتجات ضمن هذا الحجز.',
    ],

    'invoices' => [
        'number' => 'رقم الفاتورة',
        'status' => 'الحالة',
        'type' => 'النوع',
        'total' => 'الإجمالي',
        'paid_at' => 'تاريخ الدفع',
        'empty' => 'لا يوجد فواتير مرتبطة بهذا الحجز.',
    ],
    'latest_unpaid_invoice_hint' => 'يوجد فاتورة غير مدفوعة مرتبطة بهذا الحجز:',

    'logs' => [
        'from' => 'من',
        'to' => 'إلى',
        'by' => 'بواسطة',
        'empty' => 'لا يوجد سجل حالات لهذا الحجز.',
    ],

    'status_updated' => 'تم تحديث حالة الحجز بنجاح.',
    'status_update_failed' => 'تعذر تحديث حالة الحجز.',
    'status_locked' => 'لا يمكن تعديل الحالة بعد الإكمال أو الإلغاء.',
    'status_no_change' => 'لم يتم تغيير الحالة.',

    'service' => 'الخدمة',

    'meta' => 'Meta',
    'meta_empty' => 'لا يوجد بيانات إضافية.',
    'raw' => 'Raw Payload',

    'create' => [
        'title' => 'إضافة حجز',
    ],


    'fields' => [
        'user' => 'العميل',
        'car' => 'السيارة',
        'address' => 'العنوان',
        'service' => 'الخدمة',
        'date' => 'تاريخ الحجز',
        'time' => 'الوقت المتاح',
        'employee' => 'الموظف',
        'package_subscription' => 'اشتراك باقة (اختياري)',
        'note' => 'ملاحظة داخلية',
    ],

    'placeholders' => [
        'user' => 'ابحث بالاسم أو الجوال...',
        'car' => 'اختر سيارة',
        'address' => 'اختر عنوان',
        'service' => 'اختر خدمة',
        'employee_auto' => 'تلقائي (أفضل موظف متاح)',
        'package_optional' => 'بدون باقة',
        'note' => 'مثال: العميل طلب ملاحظة خاصة...',
    ],

    'details' => [
        'title' => 'تفاصيل الحجز',
    ],

    'slots' => [
        'load' => 'تحميل المواعيد',
        'hint' => 'اختر الخدمة + العنوان + التاريخ ثم اضغط تحميل المواعيد.',
        'employees' => 'موظف/موظفين',
    ],

    'summary' => [
        'title' => 'ملخص سريع',
        'duration' => 'مدة الخدمة',
        'start' => 'بداية',
        'end' => 'نهاية',
        'notice' => 'سيتم التحقق من الـSlot عند الحفظ لضمان عدم التعارض.',
    ],

    'package' => [
        'hint' => 'اختياري: عند اختيار باقة قد تصبح تكلفة الخدمة = 0 حسب منطق الباقات.',
    ],

    'optional' => 'اختياري',
    'yes' => 'نعم',
    'close' => 'إغلاق',
    'save' => 'حفظ الحجز',
    'cancel' => 'إلغاء',
    'done' => 'تم',

    'select_user_first' => 'اختر العميل أولاً.',
    'created_successfully' => 'تم إنشاء الحجز بنجاح.',
    'customer_created' => 'تم إنشاء العميل بنجاح.',
    'car_created' => 'تم إنشاء السيارة بنجاح.',
    'address_created' => 'تم إنشاء العنوان بنجاح.',
    'car_duplicate' => 'هذه السيارة موجودة مسبقًا لنفس العميل.',
    'car_model_not_belongs' => 'الموديل لا يتبع الماركة المختارة.',

    'no_slots' => 'لا توجد مواعيد متاحة لهذا اليوم.',
    'out_of_coverage' => 'العنوان خارج نطاق التغطية.',
    'no_working_hours' => 'لا يوجد دوام متاح لهذا اليوم.',
    'time_not_available' => 'الوقت المختار غير متاح.',
    'no_employee_for_slot' => 'لا يوجد موظف متاح لهذا الموعد.',
    'employee_not_available' => 'الموظف المختار غير متاح لهذا الموعد.',

    'colors' => [
        'red'=>'أحمر','silver'=>'فضي','white'=>'أبيض','black'=>'أسود','brown'=>'بني','orange'=>'برتقالي',
        'purple'=>'بنفسجي','gold'=>'ذهبي','green'=>'أخضر','blue'=>'أزرق','yellow'=>'أصفر','beige'=>'بيج',
    ],
];
