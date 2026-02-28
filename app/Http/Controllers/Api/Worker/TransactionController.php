<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\FuelingRequest;
use App\Models\StationWorker;
use App\Models\VehicleQuota;
use App\Models\Wallet;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    use ApiResponse;

    protected function getStationWorker(Request $request): ?StationWorker
    {
        return StationWorker::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('station')
            ->first();
    }

    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'otp_code' => 'required|string|size:6',
        ], [
            'otp_code.required' => __('api.worker.otp_required'),
            'otp_code.size'     => __('api.worker.otp_invalid_format'),
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $stationWorker = $this->getStationWorker($request);
        if (!$stationWorker) {
            return $this->error(__('api.worker.not_assigned'), 400);
        }

        $fuelingRequest = FuelingRequest::where('otp_code', $request->otp_code)
            ->where('status', 'pending')
            ->with(['vehicle:id,plate_number,model', 'fuelType:id,name', 'driver:id,name,phone'])
            ->first();

        if (!$fuelingRequest) {
            return $this->error(__('api.worker.request_not_found'), 404);
        }

        if ($fuelingRequest->expires_at->isPast()) {
            $fuelingRequest->markAsExpired();
            return $this->error(__('api.worker.request_expired'), 400);
        }

        return $this->success([
            'request_id' => $fuelingRequest->id,
            'vehicle'    => [
                'plate_number' => $fuelingRequest->vehicle?->plate_number,
                'model'        => $fuelingRequest->vehicle?->model,
            ],
            'driver' => [
                'name'  => $fuelingRequest->driver?->name,
                'phone' => $fuelingRequest->driver?->phone,
            ],
            'fuel_type'        => $fuelingRequest->fuelType?->name,
            'requested_liters' => (float) $fuelingRequest->requested_liters,
            'estimated_cost'   => (float) $fuelingRequest->estimated_cost,
            'expires_in_seconds' => $fuelingRequest->remaining_seconds,
        ], __('api.worker.ready_to_fuel'));
    }

    public function confirm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'request_id'    => 'required|integer|exists:fueling_requests,id',
            'actual_liters' => 'required|numeric|min:0.1|max:500',
        ], [
            'request_id.required'    => __('api.worker.request_id_required'),
            'request_id.exists'      => __('api.worker.request_not_found'),
            'actual_liters.required' => __('api.worker.actual_liters_required'),
            'actual_liters.min'      => __('api.worker.actual_liters_min'),
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $stationWorker = $this->getStationWorker($request);
        if (!$stationWorker) {
            return $this->error(__('api.worker.not_assigned'), 400);
        }

        $fuelingRequest = FuelingRequest::where('id', $request->request_id)
            ->where('status', 'pending')
            ->lockForUpdate()
            ->first();

        if (!$fuelingRequest) {
            return $this->error(__('api.worker.request_not_found'), 404);
        }

        if ($fuelingRequest->expires_at->isPast()) {
            $fuelingRequest->markAsExpired();
            return $this->error(__('api.worker.request_expired'), 400);
        }

        $actualLiters = (float) $request->actual_liters;
        $pricePerLiter = (float) $fuelingRequest->fuel_price_at_request;
        $totalCost = $actualLiters * $pricePerLiter;

        try {
            $transaction = DB::transaction(function () use (
                $fuelingRequest, $stationWorker, $actualLiters, $pricePerLiter, $totalCost
            ) {
                $wallet = Wallet::where('user_id', $fuelingRequest->client_id)
                    ->lockForUpdate()
                    ->first();

                if (!$wallet) {
                    throw new \Exception(__('api.worker.client_wallet_not_found'));
                }

                if (!$wallet->is_active) {
                    throw new \Exception(__('api.worker.client_wallet_inactive'));
                }

                $availableBalance = (float) $wallet->valide_balance;
                if ($totalCost > $availableBalance) {
                    throw new \Exception(__('api.worker.insufficient_balance', [
                        'available' => number_format($availableBalance, 2),
                        'required'  => number_format($totalCost, 2),
                    ]));
                }

                $quota = VehicleQuota::where('vehicle_id', $fuelingRequest->vehicle_id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if ($quota) {
                    $remaining = (float) $quota->amount_limit - (float) $quota->consumed_amount;
                    if ($actualLiters > $remaining) {
                        throw new \Exception(__('api.worker.quota_exceeded', [
                            'remaining' => number_format($remaining, 2),
                        ]));
                    }

                    $quota->increment('consumed_amount', $actualLiters);
                }

                $wallet->decrement('valide_balance', $totalCost);

                $referenceNo = 'FT-' . strtoupper(Str::random(8)) . '-' . time();

                $transaction = FuelTransaction::create([
                    'reference_no'       => $referenceNo,
                    'client_id'          => $fuelingRequest->client_id,
                    'driver_id'          => $fuelingRequest->driver_id,
                    'vehicle_id'         => $fuelingRequest->vehicle_id,
                    'station_id'         => $stationWorker->station_id,
                    'worker_id'          => $stationWorker->id,
                    'fuel_type_id'       => $fuelingRequest->fuel_type_id,
                    'price_per_liter'    => $pricePerLiter,
                    'actual_liters'      => $actualLiters,
                    'total_amount'       => $totalCost,
                    'max_allowed_amount' => $fuelingRequest->estimated_cost,
                    'status'             => 'completed',
                    'type'               => 'qr_based',
                    'completed_at'       => now(),
                ]);

                $fuelingRequest->markAsCompleted($stationWorker->id, $stationWorker->station_id);

                return $transaction;
            });

            return $this->success([
                'transaction_id' => $transaction->id,
                'reference_no'   => $transaction->reference_no,
                'actual_liters'  => (float) $transaction->actual_liters,
                'total_amount'   => (float) $transaction->total_amount,
                'station'        => $stationWorker->station?->name,
            ], __('api.worker.fueling_completed'));

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    public function uploadProof(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_id'   => 'required|integer|exists:fuel_transactions,id',
            'pump_meter_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'transaction_id.required'   => __('api.worker.transaction_id_required'),
            'transaction_id.exists'     => __('api.worker.transaction_not_found'),
            'pump_meter_image.required' => __('api.worker.image_required'),
            'pump_meter_image.image'    => __('api.worker.image_invalid'),
            'pump_meter_image.max'      => __('api.worker.image_too_large'),
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $stationWorker = $this->getStationWorker($request);
        if (!$stationWorker) {
            return $this->error(__('api.worker.not_assigned'), 400);
        }

        $transaction = FuelTransaction::where('id', $request->transaction_id)
            ->where('worker_id', $stationWorker->id)
            ->where('station_id', $stationWorker->station_id)
            ->first();

        if (!$transaction) {
            return $this->error(__('api.worker.transaction_not_yours'), 403);
        }

        try {
            $file = $request->file('pump_meter_image');
            $filename = 'meter_' . $transaction->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('fuel_transactions/meters', $filename, 'public');

            $transaction->update(['meter_image' => $path]);

            return $this->success([
                'transaction_id' => $transaction->id,
                'image_path'     => $path,
            ], __('api.worker.proof_uploaded'));

        } catch (\Exception $e) {
            report($e);
            return $this->error(__('api.worker.upload_failed'), 500);
        }
    }

    public function todayStats(Request $request): JsonResponse
    {
        $stationWorker = $this->getStationWorker($request);
        if (!$stationWorker) {
            return $this->error(__('api.worker.not_assigned'), 400);
        }

        $stats = FuelTransaction::where('worker_id', $stationWorker->id)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->selectRaw('
                COUNT(*) as transactions_count,
                COALESCE(SUM(actual_liters), 0) as total_liters,
                COALESCE(SUM(total_amount), 0) as total_amount
            ')
            ->first();

        return $this->success([
            'today' => [
                'transactions' => (int) $stats->transactions_count,
                'liters'       => (float) $stats->total_liters,
                'amount'       => (float) $stats->total_amount,
            ],
        ]);
    }

    public function recentTransactions(Request $request): JsonResponse
    {
        $stationWorker = $this->getStationWorker($request);
        if (!$stationWorker) {
            return $this->error(__('api.worker.not_assigned'), 400);
        }

        $transactions = FuelTransaction::where('worker_id', $stationWorker->id)
            ->where('status', 'completed')
            ->with(['vehicle:id,plate_number', 'fuelType:id,name'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($tx) {
                return [
                    'id'            => $tx->id,
                    'reference_no'  => $tx->reference_no,
                    'vehicle_plate' => $tx->vehicle?->plate_number,
                    'fuel_type'     => $tx->fuelType?->name,
                    'liters'        => (float) $tx->actual_liters,
                    'amount'        => (float) $tx->total_amount,
                    'time'          => $tx->created_at->format('H:i'),
                    'date'          => $tx->created_at->format('Y-m-d'),
                ];
            });

        return $this->success([
            'transactions' => $transactions,
        ]);
    }
}
