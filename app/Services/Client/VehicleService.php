<?php

namespace App\Services\Client;

use App\Models\Vehicle;
use App\Models\VehicleQuota;
use Illuminate\Support\Facades\DB;

class VehicleService
{
    public function create(array $data, int $clientId): Vehicle
    {
        return DB::transaction(function () use ($data, $clientId) {
            $vehicle = Vehicle::create([
                'client_id'    => $clientId,
                'plate_number' => $data['plate_number'],
                'model'        => $data['model'] ?? null,
                'fuel_type_id' => $data['fuel_type_id'],
                'status'       => 'active',
            ]);

            if (!empty($data['monthly_quota'])) {
                VehicleQuota::create([
                    'vehicle_id'      => $vehicle->id,
                    'client_id'       => $clientId,
                    'amount_limit'    => $data['monthly_quota'],
                    'consumed_amount' => 0,
                    'reset_cycle'     => 'monthly',
                    'is_active'       => true,
                ]);
            }

            return $vehicle;
        });
    }

    public function update(int $vehicleId, array $data, int $clientId): Vehicle
    {
        return DB::transaction(function () use ($vehicleId, $data, $clientId) {
            $vehicle = Vehicle::where('client_id', $clientId)->findOrFail($vehicleId);

            $vehicle->update([
                'plate_number' => $data['plate_number'],
                'model'        => $data['model'] ?? null,
                'fuel_type_id' => $data['fuel_type_id'],
            ]);

            if (isset($data['monthly_quota'])) {
                $quota = $vehicle->quota;
                if ($quota) {
                    $quota->update(['amount_limit' => $data['monthly_quota']]);
                } else {
                    VehicleQuota::create([
                        'vehicle_id'      => $vehicle->id,
                        'client_id'       => $clientId,
                        'amount_limit'    => $data['monthly_quota'],
                        'consumed_amount' => 0,
                        'reset_cycle'     => 'monthly',
                        'is_active'       => true,
                    ]);
                }
            }

            return $vehicle->fresh();
        });
    }

    public function delete(int $vehicleId, int $clientId): bool
    {
        return DB::transaction(function () use ($vehicleId, $clientId) {
            $vehicle = Vehicle::where('client_id', $clientId)->findOrFail($vehicleId);

            if ($vehicle->quota) {
                $vehicle->quota->delete();
            }
            return $vehicle->delete();
        });
    }

    public function toggleStatus(int $vehicleId, int $clientId): Vehicle
    {
        $vehicle = Vehicle::where('client_id', $clientId)->findOrFail($vehicleId);
        $newStatus = $vehicle->status === 'active' ? 'inactive' : 'active';
        $vehicle->update(['status' => $newStatus]);
        return $vehicle;
    }

    public function updateQuota(Vehicle $vehicle, array $data, int $clientId): VehicleQuota
    {
        return DB::transaction(function () use ($vehicle, $data, $clientId) {
            $quota = $vehicle->quota;

            if ($quota) {
                $quota->update([
                    'amount_limit' => $data['amount_limit'],
                    'is_active'    => $data['is_active'],
                ]);
            } else {
                $quota = VehicleQuota::create([
                    'vehicle_id'      => $vehicle->id,
                    'client_id'       => $clientId,
                    'amount_limit'    => $data['amount_limit'],
                    'consumed_amount' => 0,
                    'reset_cycle'     => 'monthly',
                    'is_active'       => $data['is_active'],
                ]);
            }

            return $quota->fresh();
        });
    }

    public function resetQuotaConsumption(int $vehicleId, int $clientId): ?VehicleQuota
    {
        $vehicle = Vehicle::where('client_id', $clientId)->findOrFail($vehicleId);
        $quota = $vehicle->quota;

        if ($quota) {
            $quota->update([
                'consumed_amount' => 0,
                'last_reset_date' => now(),
            ]);
            return $quota->fresh();
        }

        return null;
    }
}
