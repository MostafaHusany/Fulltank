<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price_per_liter', 'is_active', 'description'];

    protected $casts = [
        'is_active'      => 'boolean',
        'price_per_liter' => 'decimal:2',
    ];

    public function priceLogs()
    {
        return $this->hasMany(FuelPriceLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function stations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'station_fuel_types')->withTimestamps();
    }

    public function vehicles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vehicle::class, 'fuel_type_id');
    }
}
