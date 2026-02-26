<?php

namespace App\Services;

use App\Models\FuelType;
use App\Models\FuelPriceLog;

class FuelService
{
    public function create(array $data): FuelType
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['price_per_liter'] = (float) ($data['price_per_liter'] ?? 0);
        return FuelType::create($data);
    }

    public function update(FuelType $fuelType, array $data): FuelType
    {
        $oldPrice = (float) $fuelType->price_per_liter;
        $newPrice = isset($data['price_per_liter']) ? (float) $data['price_per_liter'] : $oldPrice;

        $fuelType->name = $data['name'] ?? $fuelType->name;
        $fuelType->price_per_liter = $newPrice;
        $fuelType->description = $data['description'] ?? $fuelType->description;
        if (array_key_exists('is_active', $data)) {
            $fuelType->is_active = (bool) $data['is_active'];
        }
        $fuelType->save();

        if ($oldPrice != $newPrice) {
            $this->logPriceChange($fuelType, $oldPrice, $newPrice);
        }

        return $fuelType->fresh();
    }

    public function logPriceChange(FuelType $fuelType, float $oldPrice, float $newPrice): FuelPriceLog
    {
        return FuelPriceLog::create([
            'fuel_type_id' => $fuelType->id,
            'old_price'    => $oldPrice,
            'new_price'    => $newPrice,
            'changed_by'   => auth()->id(),
            'created_at'   => now(),
        ]);
    }

    public function toggleActive(FuelType $fuelType): FuelType
    {
        $fuelType->is_active = !$fuelType->is_active;
        $fuelType->save();
        return $fuelType->fresh();
    }
}
