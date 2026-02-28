<?php

return [

    'auth' => [
        'username_required'    => 'اسم المستخدم أو رقم الهاتف مطلوب.',
        'password_required'    => 'كلمة المرور مطلوبة.',
        'password_min'         => 'كلمة المرور يجب أن تكون 4 أحرف على الأقل.',
        'invalid_credentials'  => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
        'category_not_allowed' => 'نوع حسابك غير مسموح بالوصول لهذا التطبيق.',
        'account_inactive'     => 'حسابك غير نشط. يرجى التواصل مع الدعم.',
        'login_success'        => 'تم تسجيل الدخول بنجاح.',
        'logout_success'       => 'تم تسجيل الخروج بنجاح.',
        'unauthenticated'      => 'يرجى تسجيل الدخول للمتابعة.',
        'profile_incomplete'   => 'ملفك الشخصي غير مكتمل. يرجى التواصل مع الدعم.',
        'profile_refreshed'    => 'تم تحديث الملف الشخصي بنجاح.',
    ],

    'driver' => [
        'no_vehicle_assigned' => 'لا توجد مركبة مسندة لحسابك.',
    ],

    'worker' => [
        'not_assigned' => 'أنت غير مسند لأي محطة.',

        'otp_required'          => 'رمز التحقق مطلوب.',
        'otp_invalid_format'    => 'رمز التحقق يجب أن يكون 6 أرقام بالضبط.',
        'request_not_found'     => 'طلب التزويد غير موجود أو تمت معالجته بالفعل.',
        'request_expired'       => 'انتهت صلاحية طلب التزويد هذا.',
        'ready_to_fuel'         => 'تم التحقق من الطلب. جاهز للتزويد.',

        'request_id_required'   => 'معرف الطلب مطلوب.',
        'actual_liters_required' => 'كمية اللترات الفعلية مطلوبة.',
        'actual_liters_min'     => 'اللترات الفعلية يجب أن تكون 0.1 على الأقل.',

        'client_wallet_not_found' => 'محفظة العميل غير موجودة.',
        'client_wallet_inactive'  => 'محفظة العميل غير نشطة.',
        'insufficient_balance'    => 'رصيد العميل غير كافٍ. المتاح: :available، المطلوب: :required.',
        'quota_exceeded'          => 'تم تجاوز حصة المركبة. المتبقي: :remaining لتر.',

        'fueling_completed'       => 'تم التزويد بنجاح.',

        'transaction_id_required' => 'معرف المعاملة مطلوب.',
        'transaction_not_found'   => 'المعاملة غير موجودة.',
        'transaction_not_yours'   => 'لا يمكنك رفع إثبات لهذه المعاملة.',
        'image_required'          => 'صورة عداد المضخة مطلوبة.',
        'image_invalid'           => 'يرجى رفع ملف صورة صالح.',
        'image_too_large'         => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
        'proof_uploaded'          => 'تم رفع صورة الإثبات بنجاح.',
        'upload_failed'           => 'فشل رفع الصورة. يرجى المحاولة مرة أخرى.',
    ],

    'common' => [
        'success'       => 'تمت العملية بنجاح.',
        'error'         => 'حدث خطأ.',
        'not_found'     => 'المورد غير موجود.',
        'unauthorized'  => 'وصول غير مصرح به.',
        'forbidden'     => 'تم رفض الوصول.',
    ],

    'fuel_request' => [
        'amount_required'      => 'كمية الوقود مطلوبة.',
        'amount_min'           => 'الحد الأدنى لكمية الوقود هو 1 لتر.',
        'amount_max'           => 'الحد الأقصى لكمية الوقود هو 500 لتر.',
        'fuel_type_required'   => 'نوع الوقود مطلوب.',
        'fuel_type_invalid'    => 'نوع الوقود المحدد غير صالح.',
        'fuel_type_inactive'   => 'نوع الوقود المحدد غير متاح.',

        'no_vehicle'           => 'لا توجد مركبة مسندة لحسابك.',
        'vehicle_inactive'     => 'مركبتك غير نشطة حالياً.',
        'no_client'            => 'حسابك غير مرتبط بعميل.',
        'pending_exists'       => 'لديك طلب تزويد نشط بالفعل. يرجى إكماله أو إلغاؤه أولاً.',

        'quota_exceeded'       => 'الكمية المطلوبة تتجاوز حصتك المتبقية. المتبقي: :remaining لتر.',
        'no_wallet'            => 'محفظة العميل غير موجودة.',
        'wallet_inactive'      => 'محفظة العميل غير نشطة.',
        'insufficient_balance' => 'رصيد غير كافٍ. المتاح: :balance، المطلوب: :required.',

        'created'              => 'تم إنشاء طلب التزويد بنجاح.',
        'create_failed'        => 'فشل في إنشاء طلب التزويد. يرجى المحاولة مرة أخرى.',
        'not_found'            => 'طلب التزويد غير موجود أو تمت معالجته بالفعل.',
        'cancelled'            => 'تم إلغاء طلب التزويد بنجاح.',
        'expired'              => 'انتهت صلاحية طلب التزويد.',
    ],

    'stations' => [
        'lat_required'  => 'خط العرض مطلوب.',
        'lat_invalid'   => 'خط العرض يجب أن يكون بين -90 و 90.',
        'lng_required'  => 'خط الطول مطلوب.',
        'lng_invalid'   => 'خط الطول يجب أن يكون بين -180 و 180.',
        'not_found'     => 'المحطة غير موجودة.',
        'no_nearby'     => 'لا توجد محطات ضمن النطاق المحدد.',
    ],

];
