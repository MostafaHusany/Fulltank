<?php

namespace App\Observers;

use App\Models\VehicleDailyRoute;
use App\Models\VehicleLocation;
use Carbon\Carbon;

class VehicleLocationObserver
{
    public function creating(VehicleLocation $location): void
    {
        if ($location->vehicle_daily_route_id) {
            return;
        }

        $tz = config('vehicle_tracking.timezone', 'Africa/Cairo');
        $recordedAt = $location->recorded_at ?? now();
        $routeDate = Carbon::parse($recordedAt)->timezone($tz)->toDateString();

        $daily = VehicleDailyRoute::query()->firstOrCreate(
            [
                'vehicle_id' => $location->vehicle_id,
                'route_date' => $routeDate,
            ],
            []
        );

        $location->vehicle_daily_route_id = $daily->id;
    }

    public function created(VehicleLocation $location): void
    {
        if ($location->vehicle_daily_route_id) {
            VehicleDailyRoute::recalculateStats((int) $location->vehicle_daily_route_id);
        }
    }
}
