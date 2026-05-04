<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLocation extends Model
{
    protected $fillable = [
        'vehicle_id',
        'vehicle_daily_route_id',
        'lat',
        'lng',
        'recorded_at',
    ];

    protected $casts = [
        'lat'          => 'decimal:7',
        'lng'          => 'decimal:7',
        'recorded_at'  => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function dailyRoute(): BelongsTo
    {
        return $this->belongsTo(VehicleDailyRoute::class, 'vehicle_daily_route_id');
    }
}
