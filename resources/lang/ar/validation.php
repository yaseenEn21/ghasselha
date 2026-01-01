<?php

return [

    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other :value.',
    'string'   => 'حقل :attribute يجب أن يكون نصاً.',
    'max'      => [
        'string' => 'حقل :attribute يجب ألا يزيد عن :max حرفاً.',
    ],
    'regex'    => 'صيغة حقل :attribute غير صحيحة.',
    'date'     => 'حقل :attribute ليس تاريخاً صالحاً.',
    'unique'   => 'حقل :attribute مستخدم من قبل.',
    'starts_with'   => 'حقل :attribute يجب ان يبدا بـ 05.',
    'digits'   => 'حقل :attribute يجب ان يكون 10 رقم.',

    // أسماء الحقول بالعربي (تستخدم في كل المشروع)
    'attributes' => [
        'plate_letters' => 'حروف اللوحة',
        'mobile' => 'رقم الجوال',
    ],

];