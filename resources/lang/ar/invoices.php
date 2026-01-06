<?php

return [
    'title' => 'الفواتير',
    'list' => 'قائمة الفواتير',
    'invoice' => 'فاتورة',
    'view' => 'عرض',
    'print' => 'طباعة',
    'back_to_list' => 'رجوع للقائمة',
    'actions_title' => 'إجراءات',

    'locked' => 'مقفلة',
    'copied' => 'تم النسخ',
    'copy_number' => 'نسخ رقم الفاتورة',

    'filters' => [
        'search_placeholder' => 'بحث بالرقم / رقم الفاتورة / العميل...',
        'status_placeholder' => 'الحالة',
        'type_placeholder' => 'النوع',
        'locked_placeholder' => 'القفل',
        'locked_yes' => 'مقفلة',
        'locked_no' => 'غير مقفلة',
        'from' => 'من',
        'to' => 'إلى',
        'reset' => 'إعادة ضبط',
    ],

    'type' => [
        'invoice' => 'فاتورة',
        'adjustment' => 'تعديل',
        'credit_note' => 'إشعار دائن',
    ],

    'status' => [
        'unpaid' => 'غير مدفوعة',
        'paid' => 'مدفوعة',
        'cancelled' => 'ملغاة',
        'refunded' => 'مسترجعة',
    ],

    'fields' => [
        'id' => 'المعرف',
        'number' => 'رقم الفاتورة',
        'user' => 'العميل',
        'invoiceable' => 'المرجع',
        'type' => 'نوع الفاتورة',
        'status' => 'الحالة',
        'locked' => 'القفل',
        'subtotal' => 'الإجمالي قبل الخصم',
        'discount' => 'خصم الفاتورة',
        'tax' => 'الضريبة',
        'gross_total' => 'الإجمالي قبل الخصم (مع الضريبة)',
        'total' => 'الإجمالي النهائي',
        'currency' => 'العملة',
        'issued_at' => 'تاريخ الإصدار',
        'paid_at' => 'تاريخ الدفع',
        'version' => 'الإصدار',
        'parent_invoice' => 'الفاتورة الأصل',
        'child_invoices' => 'فواتير مرتبطة',
    ],

    'items_title' => 'بنود الفاتورة',
    'items_count' => 'عدد البنود',

    'item' => [
        'fields' => [
            'type' => 'نوع البند',
            'title' => 'البند',
            'qty' => 'الكمية',
            'unit_price' => 'سعر الوحدة',
            'line_tax' => 'ضريبة السطر',
            'line_total' => 'إجمالي السطر',
        ],
        'type' => [
            'service' => 'خدمة',
            'product' => 'منتج',
            'fee' => 'رسوم',
            'custom' => 'مخصص',
        ],
    ],

    'payments_title' => 'المدفوعات',
    'payment' => [
        'fields' => [
            'amount' => 'المبلغ',
            'method' => 'الطريقة',
            'status' => 'الحالة',
            'paid_at' => 'تاريخ الدفع',
        ],
    ],

    'totals_title' => 'ملخص المبالغ',

    'notice_unpaid_title' => 'الفاتورة غير مدفوعة',
    'notice_unpaid_text' => 'يمكن تطبيق كوبون/تعديل قبل الدفع حسب السياسات.',

    'coupon_title' => 'تفاصيل الكوبون',
    'coupon' => [
        'code' => 'الكود',
        'discount' => 'قيمة الخصم',
        'eligible_base' => 'الأساس المؤهل',
        'applied_at' => 'تاريخ التطبيق',
    ],

    'relations_title' => 'العلاقات',
    'meta_title' => 'بيانات إضافية (Meta)',
];