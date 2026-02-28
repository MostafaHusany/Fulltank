<?php

namespace Database\Seeders;

use App\Models\FuelType;
use Illuminate\Database\Seeder;

class FuelTypeSeeder extends Seeder
{
    public function run(): void
    {
        $fuelTypes = [
            [
                'name'           => 'بنزين 80',
                'price_per_liter' => 11.00,
                'is_active'      => true,
                'description'    => 'بنزين 80 أوكتان',
            ],
            [
                'name'           => 'بنزين 92',
                'price_per_liter' => 12.50,
                'is_active'      => true,
                'description'    => 'بنزين 92 أوكتان',
            ],
            [
                'name'           => 'بنزين 95',
                'price_per_liter' => 14.00,
                'is_active'      => true,
                'description'    => 'بنزين 95 أوكتان',
            ],
            [
                'name'           => 'سولار',
                'price_per_liter' => 10.00,
                'is_active'      => true,
                'description'    => 'سولار (ديزل)',
            ],
            [
                'name'           => 'غاز طبيعي',
                'price_per_liter' => 5.50,
                'is_active'      => true,
                'description'    => 'غاز طبيعي مضغوط',
            ],
        ];

        foreach ($fuelTypes as $fuelType) {
            FuelType::firstOrCreate(
                ['name' => $fuelType['name']],
                $fuelType
            );
        }
    }
}
