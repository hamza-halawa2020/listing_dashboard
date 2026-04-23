<?php

return [
    'navigation_group' => 'الإحالات والمكافآت',
    'model_label' => 'إعداد النقاط',
    'plural_model_label' => 'إعدادات النقاط',
    'units' => [
        'point' => 'نقطة',
        'points' => 'نقاط',
        'egp' => 'جنيه',
    ],
    'defaults' => [
        'reason' => 'تم التحديث من لوحة التحكم',
        'initial_notes' => 'الإعداد الأولي: النقطة الواحدة = 10 قروش',
    ],
    'page' => [
        'title' => 'إعدادات النقاط',
        'subheading' => 'من هنا تقدر تعدّل سعر النقطة، تراجع التحويلات بسرعة، وتفتح سجل التغييرات بسهولة. السعر الحالي: :rate جنيه لكل نقطة.',
    ],
    'sections' => [
        'rate' => [
            'title' => 'سعر تحويل النقاط',
            'description' => 'حدّث قيمة النقطة بالجنيه المصري وشاهد أمثلة التحويل مباشرة قبل الحفظ.',
        ],
        'notes' => [
            'title' => 'ملاحظات التحديث',
            'description' => 'اكتب سبب التعديل والملاحظات عشان يكون السجل واضح لأي حد يراجع بعد كده.',
        ],
    ],
    'fields' => [
        'points_to_egp_rate' => [
            'label' => 'قيمة النقطة بالجنيه',
            'helper' => 'مثال: 0.1000 يعني أن النقطة الواحدة تساوي 10 قروش.',
            'suffix' => 'لكل نقطة',
        ],
        'reason_visible' => [
            'label' => 'سبب التعديل',
            'helper' => 'السبب المختصر ده بيتسجل في سجل التغييرات.',
        ],
        'notes' => [
            'label' => 'ملاحظات إضافية',
            'helper' => 'أي تفاصيل إضافية توضح سبب التعديل أو ملاحظات لفريق العمل.',
        ],
    ],
    'placeholders' => [
        'summary' => [
            'label' => 'ملخص سريع',
            'invalid_rate' => 'أدخل قيمة صحيحة للسعر لعرض المعاينة.',
            'content' => '1 نقطة = :rate جنيه | 100 جنيه = :points نقاط | 1000 نقطة = :egp جنيه',
        ],
        'example_100_egp' => ['label' => '100 جنيه تساوي كام نقطة؟'],
        'example_1000_egp' => ['label' => '1000 جنيه تساوي كام نقطة؟'],
        'example_100_points' => ['label' => '100 نقطة تساوي كام جنيه؟'],
        'example_1000_points' => ['label' => '1000 نقطة تساوي كام جنيه؟'],
    ],
    'table' => [
        'current_rate' => 'السعر الحالي',
        'rate_format_suffix' => 'جنيه / نقطة',
        'current_rate_description' => '100 جنيه = :points نقطة',
        'quick_preview' => 'معاينة سريعة',
        'quick_preview_description' => 'قيمة 1000 نقطة',
        'latest_notes' => 'آخر ملاحظات',
        'no_notes' => 'لا توجد ملاحظات',
        'last_updated' => 'آخر تحديث',
    ],
    'actions' => [
        'edit_rate' => 'تعديل السعر',
        'edit_modal_heading' => 'تحديث سعر تحويل النقاط',
        'edit_modal_description' => 'عدّل قيمة النقطة ثم راجع المعاينات السريعة قبل حفظ التغييرات.',
        'save_changes' => 'حفظ التعديلات',
        'edit_success' => 'تم تحديث سعر النقاط بنجاح',
    ],
    'header_actions' => [
        'history' => 'سجل التعديلات',
        'history_tooltip' => 'عرض كل تغييرات سعر النقاط ومن قام بها.',
        'calculator' => 'حاسبة التحويل',
        'calculator_tooltip' => 'جرّب التحويل بين الجنيه والنقاط قبل الحفظ.',
    ],
    'calculator' => [
        'heading' => 'حاسبة تحويل النقاط',
        'description' => 'أدخل أي قيمة لمعاينة التحويل حسب السعر الحالي.',
        'submit' => 'عرض النتيجة',
        'amount_egp' => 'المبلغ بالجنيه',
        'amount_egp_helper' => 'مثال لمبلغ تريد تحويله إلى نقاط.',
        'amount_points' => 'عدد النقاط',
        'amount_points_helper' => 'مثال لعدد نقاط تريد تحويله إلى جنيه.',
        'result_title' => 'نتيجة التحويل',
        'result_body' => "السعر الحالي: :rate جنيه لكل نقطة\n:egp :egp_word = :egp_points :point_word\n:points :point_word = :points_egp :egp_word",
    ],
    'history' => [
        'title' => 'سجل تغييرات سعر النقاط',
        'type_rate'         => 'سعر النقطة',
        'type_reward'       => 'مكافأة التسجيل',
        'type_subscription' => 'مكافأة الخطة',
        'type_visit'        => 'مكافأة الزيارة',
        'table_col_type'    => 'النوع',
        'subheading' => 'راجع كل تعديل حصل على سعر النقاط، واعرف السبب والشخص الذي قام بالتعديل.',
        'back' => 'الرجوع إلى الإعدادات',
        'hero_eyebrow' => 'إدارة سعر تحويل النقاط',
        'hero_title' => 'تابع حركة التغييرات وراجع كل تحديث بسهولة',
        'hero_description' => 'الصفحة دي بتجمع لك السعر الحالي، عدد مرات التعديل، وآخر تحديث حصل، مع جدول واضح تقدر منه تراجع التاريخ بالكامل بسرعة.',
        'current_rate_card' => 'السعر الحالي',
        'current_rate_suffix' => 'جنيه لكل نقطة',
        'timeline_title' => 'سجل التغييرات',
        'timeline_description' => 'راجع السعر السابق والجديد ونسبة التغيير وسبب التعديل ومن قام به في كل مرة.',
        'cards' => [
            'current_rate' => [
                'title' => 'السعر الحالي',
                'suffix' => 'جنيه / نقطة',
                'description' => '100 جنيه تساوي :points نقطة تقريبًا.',
            ],
            'total_changes' => [
                'title' => 'إجمالي التعديلات',
                'description' => 'كل عملية حفظ للسعر يتم تسجيلها تلقائيًا في السجل للمراجعة والمتابعة.',
            ],
            'last_change' => [
                'title' => 'آخر تعديل',
                'none' => 'لا يوجد',
                'reason' => 'السبب: :reason',
                'empty' => 'لم يتم تسجيل أي تعديلات على السعر حتى الآن.',
            ],
        ],
        'table' => [
            'old_rate'   => 'القيمة السابقة',
            'new_rate'   => 'القيمة الجديدة',
            'rate_suffix' => 'جنيه/نقطة',
            'change'     => 'نسبة التغيير',
            'details'    => 'التفاصيل',
            'reason'     => 'سبب التعديل',
            'undefined'  => 'غير محدد',
            'changed_by' => 'تم بواسطة',
            'system'     => 'النظام',
            'changed_at' => 'وقت التعديل',
        ],
        'pagination' => [
            'showing' => 'عرض :from–:to من أصل :total',
        ],
    ],
];
