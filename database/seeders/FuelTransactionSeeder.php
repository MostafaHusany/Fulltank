<?php

namespace Database\Seeders;

use App\Models\FuelTransaction;
use App\Models\FuelType;
use App\Models\Station;
use App\Models\StationWorker;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FuelTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::with(['client', 'fuelType', 'drivers'])
            ->where('status', 'active')
            ->get();

        $stations = Station::with('workers')->get();
        $fuelTypes = FuelType::where('is_active', true)->get()->keyBy('id');

        if ($vehicles->isEmpty() || $stations->isEmpty()) {
            return;
        }

        // Generate transactions for the last 30 days
        foreach (range(1, 100) as $i) {
            $vehicle = $vehicles->random();
            $station = $stations->random();
            $stationWorker = $station->workers->isNotEmpty() ? $station->workers->random() : null;

            if (!$vehicle->client || !$stationWorker) {
                continue;
            }

            // Get driver from vehicle's drivers relationship or from users table
            $driver = $vehicle->drivers->first();
            if (!$driver) {
                continue;
            }

            $fuelType = $vehicle->fuelType ?? $fuelTypes->first();
            if (!$fuelType) {
                continue;
            }

            $liters = rand(20, 80);
            $pricePerLiter = $fuelType->price_per_liter ?? 12.00;
            $totalAmount = $liters * $pricePerLiter;

            $daysAgo = rand(0, 30);
            $transactionDate = now()->subDays($daysAgo)->setTime(rand(6, 22), rand(0, 59));

            FuelTransaction::create([
                'reference_no'       => 'FT-' . strtoupper(Str::random(8)) . '-' . time() . '-' . $i,
                'client_id'          => $vehicle->client_id,
                'driver_id'          => $driver->id,
                'vehicle_id'         => $vehicle->id,
                'station_id'         => $station->id,
                'worker_id'          => $stationWorker->id,
                'fuel_type_id'       => $fuelType->id,
                'price_per_liter'    => $pricePerLiter,
                'actual_liters'      => $liters,
                'total_amount'       => $totalAmount,
                'max_allowed_amount' => $totalAmount,
                'status'             => 'completed',
                'type'               => 'qr_based',
                'completed_at'       => $transactionDate,
                'created_at'         => $transactionDate,
                'updated_at'         => $transactionDate,
            ]);
        }
    }
}
