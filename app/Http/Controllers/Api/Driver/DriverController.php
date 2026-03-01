<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    use ApiResponse;

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $vehicle = $user->vehicle;

        if (!$vehicle) {
            return $this->success([
                'has_vehicle' => false,
                'message'     => __('api.driver.no_vehicle_assigned'),
            ]);
        }

        $vehicle->load(['fuelType:id,name,price_per_liter', 'activeQuota']);

        $quotaData = null;
        if ($vehicle->activeQuota) {
            $quotaData = [
                'amount_limit'     => (float) $vehicle->activeQuota->amount_limit,
                'consumed_amount'  => (float) $vehicle->activeQuota->consumed_amount,
                'remaining_amount' => (float) $vehicle->activeQuota->remaining_amount,
                'reset_cycle'      => $vehicle->activeQuota->reset_cycle,
            ];
        }

        return $this->success([
            'has_vehicle' => true,
            'vehicle'     => [
                'id'                   => $vehicle->id,
                'plate_number'         => $vehicle->plate_number,
                'model'                => $vehicle->model,
                'fuel_type'            => $vehicle->fuelType?->name,
                'fuel_type_id'         => $vehicle->fuelType?->id,
                'fuel_price_per_liter' => $vehicle->fuelType ? (float) $vehicle->fuelType->price_per_liter : null,
                'status'               => $vehicle->status,
            ],
            'quota'       => $quotaData,
        ]);
    }
}
