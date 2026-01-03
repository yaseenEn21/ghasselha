<?php

return [

    'title' => 'الموظفون',
    'list' => 'قائمة الموظفين',
    'singular_title' => 'الموظف',

    'create_new' => 'إضافة موظف جديد',
    'create' => 'إنشاء',
    'edit' => 'تعديل الموظف',
    'update' => 'تحديث',
    'view' => 'عرض الموظف',
    'delete' => 'حذف الموظف',

    'biker_type_label' => 'حساب بايكر (موظف ميداني)',

    'fields' => [
        'name' => 'الاسم',
        'mobile' => 'رقم الجوال',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'birth_date' => 'تاريخ الميلاد',
        'gender' => 'الجنس',
        'gender_male' => 'ذكر',
        'gender_female' => 'أنثى',
        'is_active' => 'نشط',
        'notification' => 'استقبال الإشعارات',
        'user_type' => 'نوع الحساب',
    ],

    'status_active' => 'نشط',
    'status_inactive' => 'موقوف',

    'created_at' => 'تاريخ الإنشاء',
    'updated_at' => 'آخر تحديث',

    'filters' => [
        'search_placeholder' => 'بحث بالاسم أو رقم الجوال...',
        'status' => 'حالة الحساب',
        'status_placeholder' => 'كل الحالات',
        'gender' => 'الجنس',
        'gender_placeholder' => 'الجميع',
        'all' => 'الكل',
        'reset' => 'إعادة تعيين الفلاتر',
    ],

    'updated_successfully' => 'تم تحديث بيانات الموظف بنجاح.',
    'created_successfully' => 'تم إنشاء الموظف بنجاح.',
    'deleted_successfully' => 'تم حذف الموظف بنجاح.',

    'singular' => 'الموظف',
    'show_title' => 'عرض الموظف :name',


    // التنبيهات
    'notification_on' => 'التنبيهات مفعّلة',
    'notification_off' => 'التنبيهات معطّلة',

    // شارة خاصة للبايكر
    'biker_badge' => 'سائق دراجة (Biker)',

    // التواريخ
    'last_update' => 'آخر تحديث',

    // التابات
    'tab_overview' => 'نظرة عامة',
    'tab_bookings' => 'الحجوزات',

    // تبويب الحجوزات (جاهز للمستقبل)
    'bookings_tab_title' => 'حجوزات الموظف',
    'bookings_tab_hint' => 'سيتم عرض حجوزات هذا الموظف هنا لاحقاً.',

    // البطاقات
    'basic_info' => 'المعلومات الأساسية',
    'basic_info_hint' => 'بيانات الحساب الأساسية لهذا الموظف.',

    'working_hours_card_title' => 'ساعات العمل الأسبوعية',
    'working_hours_card_hint' => 'أوقات الدوام والاستراحة لكل يوم في الأسبوع.',

    'services_card_title' => 'الخدمات المرتبطة بالموظف',
    'services_card_hint' => 'الخدمات التي يستطيع الموظف تنفيذها.',

    'work_area_card_title' => 'منطقة تغطية العمل',
    'work_area_card_hint' => 'خريطة توضّح منطقة عمل الموظف.',

    'working_hours_title' => 'ساعات العمل الأسبوعية',
    'working_hours_work' => 'فترة الدوام',
    'working_hours_break' => 'فترة الاستراحة',

    'off_day' => 'يوم عطلة',
    'no_break' => 'لا يوجد استراحة',

    'no_services_assigned' => 'لم يتم ربط أي خدمات بهذا الموظف.',

    'no_work_area_defined' => 'لم يتم تحديد منطقة العمل بعد.',

];
