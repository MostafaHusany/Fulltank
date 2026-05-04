<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleDailyRoute extends Model
{
    protected $fillable = [
        'vehicle_id',
        'route_date',
        'started_at',
        'ended_at',
        'point_count',
        'distance_km',
    ];

    protected $casts = [
        'route_date'   => 'date',
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime',
        'point_count'  => 'integer',
        'distance_km'  => 'decimal:3',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(VehicleLocation::class, 'vehicle_daily_route_id')->orderBy('recorded_at');
    }

    public static function recalculateStats(int $id): void
    {
        $route = self::query()->find($id);
        if (!$route) {
            return;
        }

        $pts = VehicleLocation::query()
            ->where('vehicle_daily_route_id', $id)
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get(['lat', 'lng', 'recorded_at']);

        if ($pts->isEmpty()) {
            $route->update([
                'started_at'  => null,
                'ended_at'    => null,
                'point_count' => 0,
                'distance_km' => null,
            ]);

            return;
        }

        $sumKm = 0.0;
        for ($i = 1; $i < $pts->count(); $i++) {
            $sumKm += self::haversineKm(
                (float) $pts[$i - 1]->lat,
                (float) $pts[$i - 1]->lng,
                (float) $pts[$i]->lat,
                (float) $pts[$i]->lng
            );
        }

        $route->update([
            'started_at'  => $pts->first()->recorded_at,
            'ended_at'    => $pts->last()->recorded_at,
            'point_count' => $pts->count(),
            'distance_km' => round($sumKm, 3),
        ]);
    }

    private static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);

        return $earth * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
