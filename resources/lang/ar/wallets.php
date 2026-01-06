<?php

return [
    'title' => 'المحافظ المالية',
    'list' => 'سجل حركات المحفظة',
    'manage_wallet' => 'إدارة محفظة العميل',
    'manage_hint' => 'اختر المستخدم وحدد إضافة/خصم ومبلغ العملية ونوعها.',
    'create_new' => 'إدارة المحفظة',
    'submit' => 'حفظ العملية',

    'created_successfully' => 'تم تنفيذ العملية بنجاح.',

    'created_at' => 'تاريخ العملية',

    'fields' => [
        'user' => 'المستخدم',
        'direction' => 'اتجاه العملية',
        'type' => 'نوع العملية',
        'amount' => 'المبلغ (SAR)',
        'balance_before' => 'الرصيد قبل',
        'balance_after' => 'الرصيد بعد',
        'description' => 'الوصف',
        'description_ar' => 'وصف (عربي)',
        'description_en' => 'وصف (إنجليزي)',
        'reference' => 'المرجع',
    ],

    'directions' => [
        'credit' => 'إضافة (Credit)',
        'debit'  => 'خصم (Debit)',
    ],

    'types' => [
        'topup' => 'شحن',
        'refund' => 'استرجاع',
        'adjustment' => 'تعديل إداري',
        'booking_charge' => 'خصم لحجز',
        'package_purchase' => 'خصم لشراء باقة',
    ],

    'wallet_snapshot' => 'ملخص المحفظة',
    'wallet_snapshot_hint' => 'يتم تحديث الملخص عند اختيار مستخدم.',
    'wallet' => [
        'balance' => 'الرصيد الحالي',
        'total_credit' => 'إجمالي الإضافات',
        'total_debit' => 'إجمالي الخصومات',
        'currency' => 'العملة',
    ],
    'wallet_note' => 'تنبيه: الخصم يتطلب توفر رصيد كافٍ.',

    'placeholders' => [
        'description_ar' => 'مثال: شحن إداري للعميل...',
        'description_en' => 'Example: Admin topup for customer...',
    ],

    'filters' => [
        'all' => 'الكل',
        'reset' => 'إعادة تعيين الفلاتر',
        'user' => 'المستخدم',
        'user_placeholder' => 'اختر مستخدم...',
        'direction' => 'الاتجاه',
        'direction_placeholder' => 'كل الاتجاهات',
        'type' => 'النوع',
        'type_placeholder' => 'كل الأنواع',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
    ],

    'validation' => [
        'invalid_type_for_direction' => 'نوع العملية غير متوافق مع اتجاه العملية.',
    ],
];