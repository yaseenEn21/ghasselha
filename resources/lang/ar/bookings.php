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
    ],

    'columns' => [
        'customer' => 'العميل',
        'service' => 'الخدمة',
        'schedule' => 'الموعد',
        'employee' => 'الموظف',
        'total' => 'الإجمالي',
        'status' => 'الحالة',
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

    'customer' => 'العميل',
    'user_id' => 'رقم العميل',
    'car' => 'السيارة',
    'address' => 'العنوان',

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

    'meta' => 'Meta',
    'meta_empty' => 'لا يوجد بيانات إضافية.',
    'raw' => 'Raw Payload',
];