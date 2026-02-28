<?php

namespace Database\Seeders;

use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleQuota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $benzine92 = FuelType::where('name', 'بنزين 92')->first();
        $solar = FuelType::where('name', 'سولار')->first();

        $vehicleData = [
            'fast_transport' => [
                [
                    'plate_number'  => 'ق ص ر 1234',
                    'model'         => 'تويوتا هايس 2022',
                    'fuel_type_id'  => $benzine92?->id,
                    'quota_amount'  => 5000,
                    'driver'        => ['name' => 'أحمد سامي', 'username' => 'driver_fast_1', 'phone' => '01300000001'],
                ],
                [
                    'plate_number'  => 'ق ص ر 5678',
                    'model'         => 'ميتسوبيشي كانتر 2021',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 8000,
                    'driver'        => ['name' => 'محمود فتحي', 'username' => 'driver_fast_2', 'phone' => '01300000002'],
                ],
                [
                    'plate_number'  => 'ق ص ر 9012',
                    'model'         => 'هيونداي H100 2023',
                    'fuel_type_id'  => $benzine92?->id,
                    'quota_amount'  => 4000,
                    'driver'        => ['name' => 'عمرو حسين', 'username' => 'driver_fast_3', 'phone' => '01300000003'],
                ],
            ],
            'golden_delivery' => [
                [
                    'plate_number'  => 'ذ هـ ب 1111',
                    'model'         => 'مرسيدس سبرينتر 2022',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 10000,
                    'driver'        => ['name' => 'خالد عبدالله', 'username' => 'driver_golden_1', 'phone' => '01300000004'],
                ],
                [
                    'plate_number'  => 'ذ هـ ب 2222',
                    'model'         => 'فولكس واجن كرافتر 2021',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 9000,
                    'driver'        => ['name' => 'سعيد محمد', 'username' => 'driver_golden_2', 'phone' => '01300000005'],
                ],
            ],
            'nile_transport' => [
                [
                    'plate_number'  => 'ن ي ل 3333',
                    'model'         => 'إيسوزو NPR 2020',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 12000,
                    'driver'        => ['name' => 'حسام الدين علي', 'username' => 'driver_nile_1', 'phone' => '01300000006'],
                ],
                [
                    'plate_number'  => 'ن ي ل 4444',
                    'model'         => 'هينو 300 2022',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 15000,
                    'driver'        => ['name' => 'إبراهيم أحمد', 'username' => 'driver_nile_2', 'phone' => '01300000007'],
                ],
                [
                    'plate_number'  => 'ن ي ل 5555',
                    'model'         => 'فوتون أومارك 2023',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 10000,
                    'driver'        => ['name' => 'ياسر عبدالرؤوف', 'username' => 'driver_nile_3', 'phone' => '01300000008'],
                ],
                [
                    'plate_number'  => 'ن ي ل 6666',
                    'model'         => 'جاك شاحنة نقل 2021',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 11000,
                    'driver'        => ['name' => 'وليد حسن', 'username' => 'driver_nile_4', 'phone' => '01300000009'],
                ],
            ],
            'egypt_shipping' => [
                [
                    'plate_number'  => 'م ص ر 7777',
                    'model'         => 'كيا K2700 2020',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 6000,
                    'driver'        => ['name' => 'أشرف سالم', 'username' => 'driver_egypt_1', 'phone' => '01300000010'],
                ],
                [
                    'plate_number'  => 'م ص ر 8888',
                    'model'         => 'شيفروليه N300 2022',
                    'fuel_type_id'  => $benzine92?->id,
                    'quota_amount'  => 4500,
                    'driver'        => ['name' => 'هشام كمال', 'username' => 'driver_egypt_2', 'phone' => '01300000011'],
                ],
            ],
            'falcon_transport' => [
                [
                    'plate_number'  => 'ص ق ر 1010',
                    'model'         => 'مان TGM 2021',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 20000,
                    'driver'        => ['name' => 'ماجد عبدالحميد', 'username' => 'driver_falcon_1', 'phone' => '01300000012'],
                ],
                [
                    'plate_number'  => 'ص ق ر 2020',
                    'model'         => 'سكانيا P-Series 2022',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 25000,
                    'driver'        => ['name' => 'عصام فؤاد', 'username' => 'driver_falcon_2', 'phone' => '01300000013'],
                ],
                [
                    'plate_number'  => 'ص ق ر 3030',
                    'model'         => 'فولفو FH 2023',
                    'fuel_type_id'  => $solar?->id,
                    'quota_amount'  => 30000,
                    'driver'        => ['name' => 'رضا محمود', 'username' => 'driver_falcon_3', 'phone' => '01300000014'],
                ],
            ],
        ];

        foreach ($vehicleData as $clientUsername => $vehicles) {
            $client = User::where('username', $clientUsername)->first();
            if (!$client) continue;

            foreach ($vehicles as $vData) {
                $driverData = $vData['driver'];
                unset($vData['driver']);
                $quotaAmount = $vData['quota_amount'];
                unset($vData['quota_amount']);

                // Create driver user
                $driver = User::firstOrCreate(
                    ['username' => $driverData['username']],
                    [
                        'name'      => $driverData['name'],
                        'phone'     => $driverData['phone'],
                        'email'     => $driverData['username'] . '@fulltank.com',
                        'password'  => Hash::make('123456'),
                        'category'  => 'driver',
                        'client_id' => $client->id,
                        'is_active' => true,
                    ]
                );

                // Create vehicle
                $vehicle = Vehicle::firstOrCreate(
                    ['plate_number' => $vData['plate_number']],
                    [
                        'client_id'    => $client->id,
                        'plate_number' => $vData['plate_number'],
                        'model'        => $vData['model'],
                        'fuel_type_id' => $vData['fuel_type_id'],
                        'status'       => 'active',
                    ]
                );

                // Assign vehicle to driver
                $driver->update(['vehicle_id' => $vehicle->id]);

                // Create quota
                VehicleQuota::firstOrCreate(
                    ['vehicle_id' => $vehicle->id, 'client_id' => $client->id],
                    [
                        'amount_limit'    => $quotaAmount,
                        'consumed_amount' => rand(0, (int)($quotaAmount * 0.3)),
                        'reset_cycle'     => 'monthly',
                        'last_reset_date' => now()->startOfMonth(),
                        'is_active'       => true,
                    ]
                );
            }
        }
    }
}
