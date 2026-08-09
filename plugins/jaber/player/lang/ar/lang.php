<?php

return [
    'plugin' => [
        'name' => 'المقهى',
        'description' => '',
        'player' => 'العاب',
        'tables' => 'الطاولات',
        'categories' => 'الفئات',
        'products' => 'الالعاب',
        'prices' => 'الأسعار',
        'invoices' => 'الفواتير',
        'invoice_items' => 'عناصر الفاتورة',
        'select' => 'اختر',
        'unknown' => 'غير معروف',
        'create_and_print' => 'إنشاء وطباعة',
        'update_and_print' => 'تحديث وطباعة',
        'print' => 'طباعة',
        'signature' => 'التوقيع',
        'sign_here' => 'وقّع هنا',
        'clear' => 'مسح',
        'your_name' => 'اسمك',
        'name_conversion' => 'تحويل الاسم',
        'date' => 'التاريخ',
        'add' => 'إضافة',
        'remove' => 'إزالة',
        'amount' => 'المبلغ',
        'reports' => 'التقارير',
        'from_date' => 'من تاريخ',
        'to_date' => 'إلى تاريخ',
        'total_price' => 'السعر الإجمالي',
        'message_delete' => 'لا يمكن الحذف بسبب وجود بيانات مرتبطة بالقسم',
        'price_require' => 'السعر مطلوب',
         'error_status_save' => 'لا يمكن الحفظ بسبب وجود قسم اخر مفعل',
        'create_and_new' => 'إنشاء وجديد',



    ],
    'model' => [
        'product' => [
            'id' => 'المعرف',
             'price' => 'السعر',
            'name' => 'الاسم',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
        'price' => [
            'id' => 'المعرف',
             'status' => 'الحالة',
            'product' => 'المنتج',
            'price' => 'السعر',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
        'invoice' => [
            'id' => 'المعرف',
            'product' => 'المنتج',
            'invoice' => 'الفاتورة',
            'category' => 'الفئة',
            'price' => 'السعر',
            'number' => 'العدد',
            'total_price' => 'السعر الإجمالي',
            'created_at' => 'تاريخ الإنشاء',
            'updated_at' => 'تاريخ التحديث',
        ],
    ],
    'controller' => [
       
        'products' => [
            'products' => 'الالعاب',
        ],
        'prices' => [
            'prices' => 'الأسعار',
        ],
        'invoices' => [
            'invoices' => 'الفواتير',
        ],
    ],
];
