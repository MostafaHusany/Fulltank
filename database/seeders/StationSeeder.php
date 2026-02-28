<?php

namespace Database\Seeders;

use App\Models\FuelType;
use App\Models\Governorate;
use App\Models\GovernorateDistrict;
use App\Models\Station;
use App\Models\StationWorker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $fuelTypes = FuelType::where('is_active', true)->pluck('id')->toArray();

        $stations = [
            [
                'name'             => 'محطة مصر للبترول - مدينة نصر',
                'address'          => 'شارع عباس العقاد، مدينة نصر',
                'nearby_landmarks' => 'بجوار سيتي ستارز',
                'lat'              => 30.0626,
                'lng'              => 31.3456,
                'phone_1'          => '01500000001',
                'phone_2'          => '0222600001',
                'governorate'      => 'القاهرة',
                'district'         => 'مدينة نصر',
                'manager_username' => 'station_nasr',
                'workers'          => [
                    ['name' => 'عبدالله محمد', 'username' => 'worker_nasr_1', 'phone' => '01500100001'],
                    ['name' => 'حسين أحمد', 'username' => 'worker_nasr_2', 'phone' => '01500100002'],
                    ['name' => 'علي حسن', 'username' => 'worker_nasr_3', 'phone' => '01500100003'],
                ],
            ],
            [
                'name'             => 'محطة موبيل - الهرم',
                'address'          => 'شارع الهرم الرئيسي، الجيزة',
                'nearby_landmarks' => 'أمام فندق موفنبيك',
                'lat'              => 30.0131,
                'lng'              => 31.2089,
                'phone_1'          => '01500000002',
                'phone_2'          => '0233600002',
                'governorate'      => 'الجيزة',
                'district'         => 'الهرم',
                'manager_username' => 'station_giza',
                'workers'          => [
                    ['name' => 'كريم سعيد', 'username' => 'worker_giza_1', 'phone' => '01500200001'],
                    ['name' => 'أيمن فوزي', 'username' => 'worker_giza_2', 'phone' => '01500200002'],
                ],
            ],
            [
                'name'             => 'محطة شل - سموحة',
                'address'          => 'شارع فوزي معاذ، سموحة',
                'nearby_landmarks' => 'بجوار نادي سموحة',
                'lat'              => 31.2200,
                'lng'              => 29.9400,
                'phone_1'          => '01500000003',
                'phone_2'          => '0355500003',
                'governorate'      => 'الإسكندرية',
                'district'         => 'سموحة',
                'manager_username' => 'station_alex',
                'workers'          => [
                    ['name' => 'وائل إبراهيم', 'username' => 'worker_alex_1', 'phone' => '01500300001'],
                    ['name' => 'هاني محمود', 'username' => 'worker_alex_2', 'phone' => '01500300002'],
                    ['name' => 'رامي خالد', 'username' => 'worker_alex_3', 'phone' => '01500300003'],
                ],
            ],
            [
                'name'             => 'محطة طاقة - 6 أكتوبر',
                'address'          => 'المحور المركزي، 6 أكتوبر',
                'nearby_landmarks' => 'بجوار مول العرب',
                'lat'              => 29.9600,
                'lng'              => 30.9200,
                'phone_1'          => '01500000004',
                'phone_2'          => '0238500004',
                'governorate'      => 'الجيزة',
                'district'         => '6 أكتوبر',
                'manager_username' => 'station_october',
                'workers'          => [
                    ['name' => 'شريف عمر', 'username' => 'worker_oct_1', 'phone' => '01500400001'],
                    ['name' => 'باسم أشرف', 'username' => 'worker_oct_2', 'phone' => '01500400002'],
                ],
            ],
            [
                'name'             => 'محطة كويت بتروليوم - حلوان',
                'address'          => 'شارع المصانع، حلوان',
                'nearby_landmarks' => 'أمام مصانع الحديد والصلب',
                'lat'              => 29.8400,
                'lng'              => 31.3000,
                'phone_1'          => '01500000005',
                'phone_2'          => '0255000005',
                'governorate'      => 'القاهرة',
                'district'         => 'حلوان',
                'manager_username' => 'station_helwan',
                'workers'          => [
                    ['name' => 'تامر سمير', 'username' => 'worker_helwan_1', 'phone' => '01500500001'],
                    ['name' => 'أسامة جمال', 'username' => 'worker_helwan_2', 'phone' => '01500500002'],
                ],
            ],
        ];

        foreach ($stations as $stationData) {
            $manager = User::where('username', $stationData['manager_username'])->first();
            $governorate = Governorate::where('name', $stationData['governorate'])->first();
            $district = GovernorateDistrict::where('name', $stationData['district'])->first();

            if (!$manager) continue;

            $station = Station::firstOrCreate(
                ['name' => $stationData['name']],
                [
                    'address'          => $stationData['address'],
                    'nearby_landmarks' => $stationData['nearby_landmarks'],
                    'lat'              => $stationData['lat'],
                    'lng'              => $stationData['lng'],
                    'phone_1'          => $stationData['phone_1'],
                    'phone_2'          => $stationData['phone_2'],
                    'governorate_id'   => $governorate?->id,
                    'district_id'      => $district?->id,
                    'user_id'          => $manager->id,
                    'manager_name'     => $manager->name,
                ]
            );

            // Attach fuel types to station
            if (!empty($fuelTypes)) {
                $station->fuelTypes()->syncWithoutDetaching($fuelTypes);
            }

            // Create workers for this station
            foreach ($stationData['workers'] as $workerData) {
                $workerUser = User::firstOrCreate(
                    ['username' => $workerData['username']],
                    [
                        'name'      => $workerData['name'],
                        'phone'     => $workerData['phone'],
                        'email'     => $workerData['username'] . '@fulltank.com',
                        'password'  => Hash::make('123456'),
                        'category'  => 'worker',
                        'is_active' => true,
                    ]
                );

                StationWorker::firstOrCreate(
                    ['user_id' => $workerUser->id],
                    [
                        'station_id' => $station->id,
                        'full_name'  => $workerData['name'],
                        'phone'      => $workerData['phone'],
                        'is_active'  => true,
                    ]
                );
            }
        }
    }
}
