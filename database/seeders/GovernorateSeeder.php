<?php

namespace Database\Seeders;

use App\Models\Governorate;
use Illuminate\Database\Seeder;

class GovernorateSeeder extends Seeder
{
    public function run(): void
    {
        $governorates = [
            'القاهرة',
            'الجيزة',
            'الإسكندرية',
            'الدقهلية',
            'الشرقية',
            'المنوفية',
            'القليوبية',
            'البحيرة',
            'الغربية',
            'كفر الشيخ',
            'دمياط',
            'بورسعيد',
            'الإسماعيلية',
            'السويس',
            'شمال سيناء',
            'جنوب سيناء',
            'البحر الأحمر',
            'الفيوم',
            'بني سويف',
            'المنيا',
            'أسيوط',
            'سوهاج',
            'قنا',
            'الأقصر',
            'أسوان',
            'الوادي الجديد',
            'مطروح',
        ];

        foreach ($governorates as $name) {
            Governorate::firstOrCreate(['name' => $name]);
        }
    }
}
