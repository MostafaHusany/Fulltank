<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\FuelType;
use App\Models\FuelingRequest;
use App\Models\Vehicle;
use App\Models\VehicleQuota;
use App\Models\Wallet;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FuelRequestController extends Controller
{
    use ApiResponse;

    protected const OTP_EXPIRY_MINUTES = 15;

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount'       => 'required|numeric|min:1|max:500',
            'fuel_type_id' => 'required|exists:fuel_types,id',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ], [
            'amount.required'       => __('api.fuel_request.amount_required'),
            'amount.min'            => __('api.fuel_request.amount_min'),
            'amount.max'            => __('api.fuel_request.amount_max'),
            'fuel_type_id.required' => __('api.fuel_request.fuel_type_required'),
            'fuel_type_id.exists'   => __('api.fuel_request.fuel_type_invalid'),
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $driver = $request->user();

        $vehicle = $driver->vehicle;
        if (!$vehicle) {
            return $this->error(__('api.fuel_request.no_vehicle'), 400);
        }

        if ($vehicle->status !== 'active') {
            return $this->error(__('api.fuel_request.vehicle_inactive'), 400);
        }

        $clientId = $driver->client_id;
        if (!$clientId) {
            return $this->error(__('api.fuel_request.no_client'), 400);
        }

        $fuelType = FuelType::where('id', $request->fuel_type_id)
            ->where('is_active', true)
            ->first();

        if (!$fuelType) {
            return $this->error(__('api.fuel_request.fuel_type_inactive'), 400);
        }

        $hasPendingRequest = FuelingRequest::where('driver_id', $driver->id)
            ->pending()
            ->exists();

        if ($hasPendingRequest) {
            return $this->error(__('api.fuel_request.pending_exists'), 400);
        }

        $requestedLiters = (float) $request->amount;
        $pricePerLiter = (float) $fuelType->price_per_liter;
        $estimatedCost = $requestedLiters * $pricePerLiter;

        $quotaCheck = $this->checkQuota($vehicle, $requestedLiters);
        if (!$quotaCheck['passed']) {
            return $this->error($quotaCheck['message'], 400, [
                'remaining_quota' => $quotaCheck['remaining'],
            ]);
        }

        $walletCheck = $this->checkWallet($clientId, $estimatedCost);
        if (!$walletCheck['passed']) {
            return $this->error($walletCheck['message'], 400, [
                'available_balance' => $walletCheck['balance'],
                'required_amount'   => $estimatedCost,
            ]);
        }

        try {
            $fuelingRequest = DB::transaction(function () use (
                $driver, $vehicle, $clientId, $fuelType,
                $requestedLiters, $estimatedCost, $pricePerLiter, $request
            ) {
                $otpCode = $this->generateOtp();

                return FuelingRequest::create([
                    'driver_id'             => $driver->id,
                    'vehicle_id'            => $vehicle->id,
                    'client_id'             => $clientId,
                    'fuel_type_id'          => $fuelType->id,
                    'requested_liters'      => $requestedLiters,
                    'estimated_cost'        => $estimatedCost,
                    'fuel_price_at_request' => $pricePerLiter,
                    'otp_code'              => $otpCode,
                    'latitude'              => $request->latitude,
                    'longitude'             => $request->longitude,
                    'status'                => 'pending',
                    'expires_at'            => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
                ]);
            });

            return $this->success([
                'request_id'         => $fuelingRequest->id,
                'otp_code'           => $fuelingRequest->otp_code,
                'requested_liters'   => (float) $fuelingRequest->requested_liters,
                'estimated_cost'     => (float) $fuelingRequest->estimated_cost,
                'fuel_type'          => $fuelType->name,
                'expires_at'         => $fuelingRequest->expires_at->toIso8601String(),
                'expires_in_seconds' => $fuelingRequest->remaining_seconds,
            ], __('api.fuel_request.created'));

        } catch (\Exception $e) {
            report($e);
            return $this->error(__('api.fuel_request.create_failed'), 500);
        }
    }

    public function active(Request $request): JsonResponse
    {
        $driver = $request->user();

        $activeRequest = FuelingRequest::where('driver_id', $driver->id)
            ->pending()
            ->with('fuelType:id,name')
            ->first();

        if (!$activeRequest) {
            return $this->success([
                'has_active_request' => false,
            ]);
        }

        return $this->success([
            'has_active_request' => true,
            'request'            => [
                'id'                 => $activeRequest->id,
                'otp_code'           => $activeRequest->otp_code,
                'requested_liters'   => (float) $activeRequest->requested_liters,
                'estimated_cost'     => (float) $activeRequest->estimated_cost,
                'fuel_type'          => $activeRequest->fuelType?->name,
                'expires_at'         => $activeRequest->expires_at->toIso8601String(),
                'expires_in_seconds' => $activeRequest->remaining_seconds,
                'created_at'         => $activeRequest->created_at->toIso8601String(),
            ],
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $driver = $request->user();

        $fuelingRequest = FuelingRequest::where('id', $id)
            ->where('driver_id', $driver->id)
            ->pending()
            ->first();

        if (!$fuelingRequest) {
            return $this->notFound(__('api.fuel_request.not_found'));
        }

        $fuelingRequest->markAsCancelled();

        return $this->success(null, __('api.fuel_request.cancelled'));
    }

    public function history(Request $request): JsonResponse
    {
        $driver = $request->user();

        $requests = FuelingRequest::where('driver_id', $driver->id)
            ->with(['fuelType:id,name', 'completedAtStation:id,name'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($req) {
                return [
                    'id'               => $req->id,
                    'requested_liters' => (float) $req->requested_liters,
                    'estimated_cost'   => (float) $req->estimated_cost,
                    'fuel_type'        => $req->fuelType?->name,
                    'status'           => $req->status,
                    'station'          => $req->completedAtStation?->name,
                    'created_at'       => $req->created_at->toIso8601String(),
                    'completed_at'     => $req->completed_at?->toIso8601String(),
                ];
            });

        return $this->success([
            'requests' => $requests,
        ]);
    }

    protected function checkQuota(Vehicle $vehicle, float $requestedLiters): array
    {
        $quota = VehicleQuota::where('vehicle_id', $vehicle->id)
            ->where('is_active', true)
            ->first();

        if (!$quota) {
            return [
                'passed'    => true,
                'remaining' => null,
                'message'   => null,
            ];
        }

        $remaining = (float) $quota->amount_limit - (float) $quota->consumed_amount;

        if ($requestedLiters > $remaining) {
            return [
                'passed'    => false,
                'remaining' => $remaining,
                'message'   => __('api.fuel_request.quota_exceeded', [
                    'remaining' => number_format($remaining, 2),
                ]),
            ];
        }

        return [
            'passed'    => true,
            'remaining' => $remaining,
            'message'   => null,
        ];
    }

    protected function checkWallet(int $clientId, float $estimatedCost): array
    {
        $wallet = Wallet::where('user_id', $clientId)->first();

        if (!$wallet) {
            return [
                'passed'  => false,
                'balance' => 0,
                'message' => __('api.fuel_request.no_wallet'),
            ];
        }

        if (!$wallet->is_active) {
            return [
                'passed'  => false,
                'balance' => 0,
                'message' => __('api.fuel_request.wallet_inactive'),
            ];
        }

        $availableBalance = (float) $wallet->valide_balance;

        if ($estimatedCost > $availableBalance) {
            return [
                'passed'  => false,
                'balance' => $availableBalance,
                'message' => __('api.fuel_request.insufficient_balance', [
                    'balance' => number_format($availableBalance, 2),
                    'required' => number_format($estimatedCost, 2),
                ]),
            ];
        }

        return [
            'passed'  => true,
            'balance' => $availableBalance,
            'message' => null,
        ];
    }

    protected function generateOtp(): string
    {
        do {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $exists = FuelingRequest::where('otp_code', $otp)
                ->pending()
                ->exists();
        } while ($exists);

        return $otp;
    }
}
