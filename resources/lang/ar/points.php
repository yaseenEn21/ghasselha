<?php

return [
    'title' => 'نظام الولاء (النقاط)',
    'list' => 'حركات النقاط',
    'manage_wallet' => 'إدارة محفظة النقاط',

    'manage_hint' => 'اختر المستخدم وحدد إضافة/خصم نقاط مع ملاحظات.',
    'wallet_snapshot' => 'ملخص المحفظة',
    'wallet_snapshot_hint' => 'يظهر بعد اختيار المستخدم.',
    'wallet_note' => 'يتم تحديث الرصيد بعد حفظ الحركة مباشرة.',
    'submit' => 'حفظ الحركة',

    'created_successfully' => 'تم تسجيل حركة النقاط بنجاح.',

    'types' => [
        'earn' => 'إضافة نقاط (Earn)',
        'redeem' => 'خصم نقاط (Redeem)',
        'adjust' => 'تعديل (Adjust)',
        'refund' => 'استرجاع (Refund)',
    ],

    'actions' => [
        'add' => 'إضافة',
        'subtract' => 'خصم',
    ],

    'fields' => [
        'user' => 'المستخدم',
        'mobile' => 'رقم الجوال',
        'type' => 'نوع الحركة',
        'points' => 'النقاط',
        'money_amount' => 'القيمة المالية',
        'reference' => 'المرجع',
        'note' => 'ملاحظات',

        'action' => 'الإجراء',
        'points_amount' => 'عدد النقاط',
    ],

    'wallet' => [
        'balance' => 'الرصيد الحالي',
        'total_earned' => 'إجمالي المكتسب',
        'total_spent' => 'إجمالي المصروف',
    ],

    'placeholders' => [
        'user' => 'ابحث بالاسم أو الجوال...',
        'note' => 'اكتب ملاحظة (اختياري)...',
    ],

    'filters' => [
        'search_placeholder' => 'بحث بالاسم أو رقم الجوال أو الإيميل...',
        'type' => 'نوع الحركة',
        'type_placeholder' => 'كل الأنواع',
        'direction' => 'الاتجاه',
        'direction_placeholder' => 'الكل',
        'plus' => 'إضافة (+)',
        'minus' => 'خصم (-)',
        'archived' => 'الأرشفة',
        'archived_only' => 'المؤرشفة فقط',
        'not_archived' => 'غير مؤرشفة',
        'date_from' => 'من تاريخ',
        'date_to' => 'إلى تاريخ',
        'all' => 'الكل',
        'reset' => 'إعادة تعيين الفلاتر',
    ],

    'validation' => [
        'insufficient_balance' => 'الرصيد الحالي لا يكفي للخصم. الرصيد المتاح: :balance',
    ],
];
