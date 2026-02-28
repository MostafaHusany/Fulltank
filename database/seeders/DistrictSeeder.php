<?php

namespace Database\Seeders;

use App\Models\Governorate;
use App\Models\GovernorateDistrict;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            'القاهرة' => [
                'مدينة نصر', 'المعادي', 'مصر الجديدة', 'الزمالك', 'وسط البلد',
                'شبرا', 'المقطم', 'حلوان', 'التجمع الخامس', 'الشروق',
                'مدينتي', 'الرحاب', 'عين شمس', 'المرج', 'السلام',
            ],
            'الجيزة' => [
                'الدقي', 'المهندسين', 'العجوزة', '6 أكتوبر', 'الشيخ زايد',
                'الهرم', 'فيصل', 'العمرانية', 'بولاق الدكرور', 'أبو النمرس',
                'الحوامدية', 'البدرشين', 'العياط', 'الصف', 'أطفيح',
            ],
            'الإسكندرية' => [
                'سموحة', 'سيدي جابر', 'محطة الرمل', 'المنشية', 'بحري',
                'العجمي', 'المندرة', 'المعمورة', 'أبو قير', 'الدخيلة',
                'العامرية', 'برج العرب', 'كينج مريوط',
            ],
            'الدقهلية' => [
                'المنصورة', 'طلخا', 'ميت غمر', 'دكرنس', 'بلقاس',
                'شربين', 'المطرية', 'جمصة', 'منية النصر', 'أجا',
            ],
            'الشرقية' => [
                'الزقازيق', 'العاشر من رمضان', 'بلبيس', 'منيا القمح', 'أبو كبير',
                'فاقوس', 'ههيا', 'أبو حماد', 'القرين', 'ديرب نجم',
            ],
        ];

        foreach ($districts as $govName => $districtList) {
            $governorate = Governorate::where('name', $govName)->first();

            if ($governorate) {
                foreach ($districtList as $districtName) {
                    GovernorateDistrict::firstOrCreate([
                        'name'           => $districtName,
                        'governorate_id' => $governorate->id,
                    ]);
                }
            }
        }
    }
}
