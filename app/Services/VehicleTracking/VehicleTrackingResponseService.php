<?php

namespace App\Services\VehicleTracking;

use App\Models\FuelTransaction;
use App\Models\Vehicle;
use App\Models\VehicleDailyRoute;
use App\Models\VehicleLocation;
use Illuminate\Database\Eloquent\Collection;

class VehicleTrackingResponseService
{
    /**
     * @param  Collection<int, Vehicle>  $vehicles  Must load latestLocation; optionally load client for admin map labels.
     * @return list<array<string, mixed>>
     */
    public function liveMapRows(Collection $vehicles): array
    {
        return $vehicles->map(function (Vehicle $v) {
            $loc = $v->latestLocation;

            return [
                'vehicle_id'    => $v->id,
                'plate'         => $v->formatted_plate_number,
                'status'        => $v->status,
                'client_name'   => $v->relationLoaded('client') && $v->client
                    ? ($v->client->company_name ?: $v->client->name)
                    : null,
                'lat'           => $loc ? (float) $loc->lat : null,
                'lng'           => $loc ? (float) $loc->lng : null,
                'recorded_at'   => $loc?->recorded_at?->toIso8601String(),
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function historyPayload(Vehicle $vehicle): array
    {
        $sinceDate = now()->subDays(30)->toDateString();

        $dailyRoutes = VehicleDailyRoute::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('route_date', '>=', $sinceDate)
            ->orderByDesc('route_date')
            ->get(['id', 'route_date', 'point_count', 'distance_km', 'started_at', 'ended_at'])
            ->map(fn (VehicleDailyRoute $r) => [
                'id'           => $r->id,
                'route_date'   => $r->route_date?->toDateString(),
                'point_count'  => (int) $r->point_count,
                'distance_km'  => $r->distance_km !== null ? (float) $r->distance_km : null,
                'started_at'   => $r->started_at?->toIso8601String(),
                'ended_at'     => $r->ended_at?->toIso8601String(),
            ]);

        $locationsByDailyRoute = [];
        foreach ($dailyRoutes as $meta) {
            $rid = $meta['id'];
            $locationsByDailyRoute[$rid] = VehicleLocation::query()
                ->where('vehicle_daily_route_id', $rid)
                ->orderBy('recorded_at')
                ->orderBy('id')
                ->get(['lat', 'lng', 'recorded_at'])
                ->map(fn (VehicleLocation $l) => [
                    'lat'         => (float) $l->lat,
                    'lng'         => (float) $l->lng,
                    'recorded_at' => $l->recorded_at->toIso8601String(),
                ])
                ->values()
                ->all();
        }

        $trips = FuelTransaction::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('status', 'completed')
            ->with(['station:id,name,lat,lng', 'driver:id,name', 'fuelType:id,name'])
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->limit(80)
            ->get()
            ->map(function (FuelTransaction $t) {
                $station = $t->station;
                $when = $t->completed_at ?? $t->created_at;

                return [
                    'id'            => $t->id,
                    'reference_no'  => $t->reference_no,
                    'completed_at'  => $when?->toIso8601String(),
                    'station_name'  => $station?->name,
                    'lat'           => $station && $station->lat !== null ? (float) $station->lat : null,
                    'lng'           => $station && $station->lng !== null ? (float) $station->lng : null,
                    'total_amount'  => (float) $t->total_amount,
                    'actual_liters' => (float) $t->actual_liters,
                    'fuel_type'     => $t->fuelType?->name,
                    'driver_name'   => $t->driver?->name,
                ];
            });

        return [
            'vehicle_id'               => $vehicle->id,
            'plate'                    => $vehicle->formatted_plate_number,
            'daily_routes'             => $dailyRoutes->values()->all(),
            'locations_by_daily_route' => $locationsByDailyRoute,
            'fuel_visits'              => $trips->values()->all(),
        ];
    }
}
