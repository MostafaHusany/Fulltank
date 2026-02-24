<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\District;

class GoveDistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحكومات
        $governments = [
            ['ar_name' => 'الرياض',            'geo_lat' => '30.115541', 'geo_lng' => '31.394030',  'en_name' => 'Riyadh'],
            ['ar_name' => 'مكة المكرمة',       'geo_lat' => '29.944333', 'geo_lng' => '31.004016',  'en_name' => 'Makkah'],
            ['ar_name' => 'المدينة المنورة',   'geo_lat' => '31.003751', 'geo_lng' => '29.847379',  'en_name' => 'Madinah'],
            ['ar_name' => 'المنطقة الشرقية',   'geo_lat' => '30.115541', 'geo_lng' => '31.394030',  'en_name' => 'Eastern Province'],
            ['ar_name' => 'عسير',              'geo_lat' => '29.944333', 'geo_lng' => '31.004016',   'en_name' => 'Asir'],
            ['ar_name' => 'تبوك',              'geo_lat' => '31.003751', 'geo_lng' => '29.847379',   'en_name' => 'Tabuk'],
            ['ar_name' => 'القصيم',            'geo_lat' => '30.115541', 'geo_lng' => '31.394030',   'en_name' => 'Qassim'],
            ['ar_name' => 'حائل',              'geo_lat' => '29.944333', 'geo_lng' => '31.004016',   'en_name' => 'Hail'],
            ['ar_name' => 'جازان',             'geo_lat' => '31.003751', 'geo_lng' => '29.847379',   'en_name' => 'Jazan'],
            ['ar_name' => 'نجران',             'geo_lat' => '30.115541', 'geo_lng' => '31.394030',   'en_name' => 'Najran'],
            ['ar_name' => 'الباحة',            'geo_lat' => '29.944333', 'geo_lng' => '31.004016',   'en_name' => 'Al-Baha'],
            ['ar_name' => 'الجوف',             'geo_lat' => '31.003751', 'geo_lng' => '29.847379',  'en_name' => 'Al-Jawf'],
            ['ar_name' => 'حدود الشمال',       'geo_lat' => '30.115541', 'geo_lng' => '31.394030',   'en_name' => 'Northern Borders'],
            ['ar_name' => 'المدينة المنورة',   'geo_lat' => '31.003751', 'geo_lng' => '29.847379',   'en_name' => 'Madinah'],
        ];

        District::insert($governments);

        // المناطق
        $districts = [
            [
                'ar_name' => 'الملز',
                'en_name' => 'Al Malaz',
                'category' => 'cent',
                'district_id' => 1
            ],
            [
                'ar_name' => 'العليا',
                'en_name' => 'Al Olaya',
                'category' => 'cent',
                'district_id' => 1
            ],
            [
                'ar_name' => 'البطحاء',
                'en_name' => 'Al Batha',
                'category' => 'cent',
                'district_id' => 1
            ],
            [
                'ar_name' => 'مكة',
                'en_name' => 'Makkah',
                'category' => 'cent',
                'district_id' => 2
            ],
            [
                'ar_name' => 'جدة',
                'en_name' => 'Jeddah',
                'category' => 'cent',
                'district_id' => 2
            ],
            [
                'ar_name' => 'المدينة',
                'en_name' => 'Al Madinah',
                'category' => 'cent',
                'district_id' => 2
            ],
            [
                'ar_name' => 'المدينة المنورة الوسطى',
                'en_name' => 'Central Al Madinah',
                'category' => 'cent',
                'district_id' => 3
            ],
            [
                'ar_name' => 'المدينة المنورة الغربية',
                'en_name' => 'Western Al Madinah',
                'category' => 'cent',
                'district_id' => 3
            ],
            [
                'ar_name' => 'المدينة المنورة الشرقية',
                'en_name' => 'Eastern Al Madinah',
                'category' => 'cent',
                'district_id' => 3
            ],
            [
                'ar_name' => 'الدمام',
                'en_name' => 'Dammam',
                'category' => 'cent',
                'district_id' => 4
            ],
            [
                'ar_name' => 'الخبر',
                'en_name' => 'Khobar',
                'category' => 'cent',
                'district_id' => 4
            ],
            [
                'ar_name' => 'الظهران',
                'en_name' => 'Dhahran',
                'category' => 'cent',
                'district_id' => 4
            ],
            [
                'ar_name' => 'أبها',
                'en_name' => 'Abha',
                'category' => 'cent',
                'district_id' => 5
            ],
            [
                'ar_name' => 'خميس مشيط',
                'en_name' => 'Khamis Mushait',
                'category' => 'cent',
                'district_id' => 5
            ],
            [
                'ar_name' => 'نجران',
                'en_name' => 'Najran',
                'category' => 'cent',
                'district_id' => 5
            ],
            [
                'ar_name' => 'تبوك',
                'en_name' => 'Tabuk',
                'category' => 'cent',
                'district_id' => 6
            ],
            [
                'ar_name' => 'حقل',
                'en_name' => 'Haql',
                'category' => 'cent',
                'district_id' => 6
            ],
            [
                'ar_name' => 'ضباء',
                'en_name' => 'Duba',
                'category' => 'cent',
                'district_id' => 6
            ],
            [
                'ar_name' => 'بريدة',
                'en_name' => 'Buraydah',
                'category' => 'cent',
                'district_id' => 7
            ],
            [
                'ar_name' => 'عنيزة',
                'en_name' => 'Onaizah',
                'category' => 'cent',
                'district_id' => 7
            ],
            [
                'ar_name' => 'الرس',
                'en_name' => 'Ar Rass',
                'category' => 'cent',
                'district_id' => 7
            ],
            [
                'ar_name' => 'حائل',
                'en_name' => 'Hail',
                'category' => 'cent',
                'district_id' => 8
            ],
            [
                'ar_name' => 'جازان',
                'en_name' => 'Jazan',
                'category' => 'cent',
                'district_id' => 9
            ],
            [
                'ar_name' => 'صبيا',
                'en_name' => 'Sabya',
                'category' => 'cent',
                'district_id' => 9
            ],
            [
                'ar_name' => 'أبو عريش',
                'en_name' => 'Abu Arish',
                'category' => 'cent',
                'district_id' => 9
            ],
            [
                'ar_name' => 'نجران',
                'en_name' => 'Najran',
                'category' => 'cent',
                'district_id' => 10
            ],
            [
                'ar_name' => 'شرورة',
                'en_name' => 'Sharurah',
                'category' => 'cent',
                'district_id' => 10
            ],
            [
                'ar_name' => 'حدود الوطنية',
                'en_name' => 'Hudud Al-Wataniyah',
                'category' => 'cent',
                'district_id' => 10
            ],
            [
                'ar_name' => 'بيشة',
                'en_name' => 'Bisha',
                'category' => 'cent',
                'district_id' => 11
            ],
            [
                'ar_name' => 'محايل',
                'en_name' => 'Mahayel',
                'category' => 'cent',
                'district_id' => 11
            ],
            [
                'ar_name' => 'القنفذة',
                'en_name' => 'Qunfudhah',
                'category' => 'cent',
                'district_id' => 11
            ],
            [
                'ar_name' => 'سكاكا',
                'en_name' => 'Sakaka',
                'category' => 'cent',
                'district_id' => 12
            ],
            [
                'ar_name' => 'دومة الجندل',
                'en_name' => 'Dawmat Al-Jandal',
                'category' => 'cent',
                'district_id' => 12
            ],
            [
                'ar_name' => 'طريف',
                'en_name' => 'Turaif',
                'category' => 'cent',
                'district_id' => 12
            ],
            [
                'ar_name' => 'عرعر',
                'en_name' => 'Arar',
                'category' => 'cent',
                'district_id' => 13
            ],
            [
                'ar_name' => 'رفحاء',
                'en_name' => 'Rafha',
                'category' => 'cent',
                'district_id' => 13
            ],
            [
                'ar_name' => 'العويقيلة',
                'en_name' => 'Al-Uyaynah',
                'category' => 'cent',
                'district_id' => 13
            ],
            [
                'ar_name' => 'ينبع',
                'en_name' => 'Yanbu',
                'category' => 'cent',
                'district_id' => 14
            ],
            [
                'ar_name' => 'العقير',
                'en_name' => 'Al Oqair',
                'category' => 'cent',
                'district_id' => 14
            ],
            [
                'ar_name' => 'المدينة الصناعية',
                'en_name' => 'Industrial City',
                'category' => 'cent',
                'district_id' => 14
            ]
        ];

        District::insert($districts);
    }
}