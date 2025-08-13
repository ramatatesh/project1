<?php

return [

    // الرسائل العامة
    'required' => 'حقل :attribute مطلوب.',
    'email'    => 'يجب أن يكون :attribute بريداً إلكترونياً صالحاً.',
    'string'   => 'حقل :attribute يجب أن يكون نصاً.',
    'min'      => [
        'string' => 'حقل :attribute يجب ألا يقل عن :min أحرف.',
    ],
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'exists'    => ':attribute غير موجود أو غير صحيح.',

    // رسائل مخصصة لحقول resetPassword
    'custom' => [
        'email' => [
            'required' => 'البريد الإلكتروني مطلوب.',
            'email'    => 'الرجاء إدخال بريد إلكتروني صالح.',
        ],
        'code' => [
            'required' => 'الرمز مطلوب.',
            'string'   => 'الرمز يجب أن يكون نصاً.',
            'exists'   => 'الرمز غير صحيح أو غير موجود.',
        ],
        'password' => [
            'required'  => 'كلمة المرور مطلوبة.',
            'string'    => 'كلمة المرور يجب أن تكون نصاً.',
            'min'       => 'كلمة المرور يجب ألا تقل عن :min أحرف.',
            'confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ],
    ],

    // أسماء الحقول الودودة
    'attributes' => [
        'email'                 => 'البريد الإلكتروني',
        'code'                  => 'الرمز',
        'password'              => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
    ],

];
