<?php

return [
    'title' => 'المدفوعات',
    'list' => 'قائمة المدفوعات',
    'payment' => 'دفعة',
    'view' => 'عرض',
    'print' => 'طباعة',
    'back_to_list' => 'رجوع للقائمة',
    'actions_title' => 'إجراءات',

    'filters' => [
        'search_placeholder' => 'بحث: رقم دفعة / عميل / فاتورة / Gateway...',
        'status_placeholder' => 'الحالة',
        'method_placeholder' => 'طريقة الدفع',
        'has_invoice_placeholder' => 'مرتبطة بفاتورة؟',
        'has_invoice_yes' => 'نعم',
        'has_invoice_no' => 'لا',
        'gateway_placeholder' => 'Gateway (مثال: moyasar)',
        'payable_type_placeholder' => 'payable_type (مثال: booking_payment)',
        'from' => 'من',
        'to' => 'إلى',
        'reset' => 'إعادة ضبط',
    ],

    'status' => [
        'pending' => 'قيد الانتظار',
        'paid' => 'مدفوعة',
        'failed' => 'فشلت',
        'cancelled' => 'ملغاة',
        'refunded' => 'مسترجعة',
    ],

    'method' => [
        'wallet' => 'محفظة',
        'credit_card' => 'بطاقة ائتمان',
        'apple_pay' => 'Apple Pay',
        'google_pay' => 'Google Pay',
        'cash' => 'نقدًا',
        'visa' => 'Visa',
        'stc' => 'STC Pay',
    ],

    'fields' => [
        'id' => 'المعرف',
        'user' => 'العميل',
        'invoice' => 'الفاتورة',
        'payable' => 'الغرض/المرجع',
        'method' => 'طريقة الدفع',
        'status' => 'الحالة',
        'gateway' => 'البوابة',
        'amount' => 'المبلغ',
        'currency' => 'العملة',
        'paid_at' => 'تاريخ الدفع',
        'created_at' => 'تاريخ الإنشاء',

        'gateway_status' => 'حالة البوابة',
        'gateway_payment_id' => 'معرّف الدفع (Gateway)',
        'gateway_invoice_id' => 'معرّف الفاتورة (Gateway)',
        'gateway_transaction_url' => 'رابط العملية',
    ],

    'summary_title' => 'ملخص الدفعة',
    'links_title' => 'روابط ومراجع',

    'gateway_title' => 'تفاصيل بوابة الدفع',
    'gateway_raw_title' => 'بيانات البوابة الخام (Gateway Raw)',
    'meta_title' => 'بيانات إضافية (Meta)',

    'open_transaction' => 'فتح رابط العملية',
    'open_invoice' => 'فتح الفاتورة',
    'invoice_notice_title' => 'هذه الدفعة مرتبطة بفاتورة',
    'invoice_total' => 'إجمالي الفاتورة',

    'notice_failed_title' => 'عملية الدفع فشلت',
    'notice_failed_text' => 'راجع بيانات البوابة وسبب الفشل من gateway_status أو gateway_raw.',
];