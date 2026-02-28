<?php

namespace App\Services\Client;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverService
{
    public function create(array $data, int $clientId): User
    {
        return DB::transaction(function () use ($data, $clientId) {
            $driver = User::create([
                'name'        => $data['name'],
                'email'       => $this->generateDummyEmail($data['phone']),
                'phone'       => $data['phone'],
                'national_id' => $data['national_id'] ?? null,
                'password'    => Hash::make($data['password']),
                'category'    => 'driver',
                'client_id'   => $clientId,
                'vehicle_id'  => $data['vehicle_id'] ?? null,
                'is_active'   => true,
            ]);

            Wallet::create([
                'user_id'   => $driver->id,
                'balance'   => 0,
                'category'  => 'driver',
                'is_active' => true,
            ]);

            return $driver;
        });
    }

    public function update(int $driverId, array $data, int $clientId): User
    {
        return DB::transaction(function () use ($driverId, $data, $clientId) {
            $driver = User::where('category', 'driver')
                ->where('client_id', $clientId)
                ->findOrFail($driverId);

            $updateData = [
                'name'        => $data['name'],
                'phone'       => $data['phone'],
                'national_id' => $data['national_id'] ?? null,
                'vehicle_id'  => $data['vehicle_id'] ?? null,
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $driver->update($updateData);

            return $driver->fresh();
        });
    }

    public function delete(int $driverId, int $clientId): bool
    {
        return DB::transaction(function () use ($driverId, $clientId) {
            $driver = User::where('category', 'driver')
                ->where('client_id', $clientId)
                ->findOrFail($driverId);

            if ($driver->wallet) {
                $driver->wallet->delete();
            }
            return $driver->delete();
        });
    }

    public function toggleStatus(int $driverId, int $clientId): User
    {
        $driver = User::where('category', 'driver')
            ->where('client_id', $clientId)
            ->findOrFail($driverId);

        $driver->update(['is_active' => !$driver->is_active]);
        return $driver;
    }

    private function generateDummyEmail(string $phone): string
    {
        return 'driver_' . $phone . '_' . time() . '@system.local';
    }
}
