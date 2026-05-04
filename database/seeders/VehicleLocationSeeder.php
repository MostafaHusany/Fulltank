<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleDailyRoute;
use App\Models\VehicleLocation;
use App\Services\VehicleTracking\RoadRouteSampler;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VehicleLocationSeeder extends Seeder
{
    /**
     * Seeds road-aligned GPS samples (OSRM driving geometry) grouped by calendar day (Africa/Cairo).
     */
    public function run(): void
    {
        VehicleLocation::query()->delete();
        VehicleDailyRoute::query()->delete();

        $vehicles = Vehicle::query()->orderBy('id')->get();
        if ($vehicles->isEmpty()) {
            return;
        }

        $sampler = new RoadRouteSampler();
        $presetKeys = array_keys(RoadRouteSampler::EGYPT_PRESETS);
        $tz = config('vehicle_tracking.timezone', 'Africa/Cairo');

        $cachedBasePaths = [];
        foreach ($presetKeys as $presetName) {
            $cachedBasePaths[$presetName] = $sampler->routeAlongRoads(
                RoadRouteSampler::EGYPT_PRESETS[$presetName],
                52
            );
        }

        foreach ($vehicles as $vehicle) {
            $offsetLat = (($vehicle->id % 7) - 3) * 0.0018;
            $offsetLng = (($vehicle->id % 5) - 2) * 0.0018;

            for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                $dayStart = Carbon::now($tz)->subDays($dayOffset)->startOfDay();

                $presetName = $presetKeys[($vehicle->id + $dayOffset) % count($presetKeys)];
                $basePath = $cachedBasePaths[$presetName] ?? [];
                $pathLatLng = array_map(function (array $p) use ($offsetLat, $offsetLng) {
                    return [$p[0] + $offsetLat, $p[1] + $offsetLng];
                }, $basePath);
                if (count($pathLatLng) < 2) {
                    continue;
                }

                $daily = VehicleDailyRoute::query()->firstOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'route_date' => $dayStart->toDateString(),
                    ],
                    []
                );
                VehicleLocation::query()->where('vehicle_daily_route_id', $daily->id)->delete();

                $rows = [];
                $count = count($pathLatLng);
                $startRun = $dayStart->copy()->setTime(8, 15, 0);
                $endRun = $dayStart->copy()->setTime(16, 45, 0);
                $secondsSpan = max(1, $endRun->diffInSeconds($startRun));

                foreach ($pathLatLng as $i => $coord) {
                    $t = $count > 1 ? $i / ($count - 1) : 0;
                    $recordedAt = $startRun->copy()->addSeconds((int) round($secondsSpan * $t));

                    $rows[] = [
                        'vehicle_id'             => $vehicle->id,
                        'vehicle_daily_route_id' => $daily->id,
                        'lat'                    => round($coord[0], 7),
                        'lng'                    => round($coord[1], 7),
                        'recorded_at'            => $recordedAt->copy()->utc()->format('Y-m-d H:i:s'),
                        'created_at'             => now()->format('Y-m-d H:i:s'),
                        'updated_at'             => now()->format('Y-m-d H:i:s'),
                    ];
                }

                foreach (array_chunk($rows, 80) as $chunk) {
                    VehicleLocation::query()->insert($chunk);
                }

                VehicleDailyRoute::recalculateStats((int) $daily->id);
            }
        }
    }
}
