<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core Data
            GovernorateSeeder::class,
            DistrictSeeder::class,
            FuelTypeSeeder::class,

            // Users & Roles
            UserRolleSeeder::class,
            UserSeeder::class,

            // Stations & Workers
            StationSeeder::class,

            // Vehicles & Drivers
            VehicleSeeder::class,

            // Transactions (Demo Data)
            FuelTransactionSeeder::class,
        ]);
    }
}
