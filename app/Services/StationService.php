<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Station;
use App\Models\Wallet;

class StationService
{
    /**
     * Atomically create User (station_manager), Station, Wallet, and station_fuel_types pivot.
     */
    public function create(array $data): Station
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'      => $data['manager_name'] ?? $data['name'],
                'email'     => $data['email'],
                'password'  => Hash::make($data['password']),
                'category'  => 'station_manager',
                'is_active' => true,
            ]);

            $station = Station::create([
                'name'                  => $data['name'],
                'governorate_id'        => $data['governorate_id'],
                'district_id'           => $data['district_id'],
                'address'               => $data['address'] ?? null,
                'lat'                   => $data['lat'] ?? null,
                'lng'                   => $data['lng'] ?? null,
                'nearby_landmarks'      => $data['nearby_landmarks'] ?? null,
                'manager_name'          => $data['manager_name'] ?? null,
                'phone_1'               => $data['phone_1'],
                'phone_2'               => $data['phone_2'] ?? null,
                'bank_account_details'  => $data['bank_account_details'] ?? null,
                'user_id'               => $user->id,
            ]);

            Wallet::create([
                'user_id'         => $user->id,
                'valide_balance'  => 0,
                'pendding_balance' => 0,
                'is_active'       => true,
            ]);

            $fuelTypeIds = is_array($data['fuel_type_ids'] ?? null)
                ? $data['fuel_type_ids']
                : (isset($data['fuel_type_ids']) ? explode(',', $data['fuel_type_ids']) : []);
            if (!empty($fuelTypeIds)) {
                $station->fuelTypes()->attach($fuelTypeIds);
            }

            return $station->load(['governorate', 'district', 'user', 'fuelTypes']);
        });
    }

    /**
     * Update station and optionally user email/password.
     */
    public function update(Station $station, array $data): Station
    {
        return DB::transaction(function () use ($station, $data) {
            $station->fill([
                'name'                  => $data['name'] ?? $station->name,
                'governorate_id'        => $data['governorate_id'] ?? $station->governorate_id,
                'district_id'           => $data['district_id'] ?? $station->district_id,
                'address'               => $data['address'] ?? $station->address,
                'lat'                   => $data['lat'] ?? $station->lat,
                'lng'                   => $data['lng'] ?? $station->lng,
                'nearby_landmarks'      => $data['nearby_landmarks'] ?? $station->nearby_landmarks,
                'manager_name'          => $data['manager_name'] ?? $station->manager_name,
                'phone_1'               => $data['phone_1'] ?? $station->phone_1,
                'phone_2'               => $data['phone_2'] ?? $station->phone_2,
                'bank_account_details'  => $data['bank_account_details'] ?? $station->bank_account_details,
            ]);
            $station->save();

            if (isset($data['fuel_type_ids'])) {
                $ids = is_array($data['fuel_type_ids']) ? $data['fuel_type_ids'] : explode(',', $data['fuel_type_ids']);
                $station->fuelTypes()->sync($ids);
            }

            if ($station->user_id && (isset($data['email']) || isset($data['password']))) {
                $user = $station->user;
                if (isset($data['email'])) {
                    $user->email = $data['email'];
                }
                if (!empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                }
                $user->save();
            }

            return $station->fresh(['governorate', 'district', 'user', 'fuelTypes']);
        });
    }
}
