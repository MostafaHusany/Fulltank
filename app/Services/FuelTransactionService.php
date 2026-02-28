<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Station;
use App\Models\Vehicle;
use App\Models\FuelType;
use App\Models\Transaction;
use App\Models\VehicleQuota;
use App\Models\FuelTransaction;

use App\Services\NotificationService;

class FuelTransactionService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function generateQrToken(): string
    {
        return Str::uuid()->toString();
    }

    public function generateReferenceNo(): string
    {
        $prefix = 'FT';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));
        return "{$prefix}{$date}{$random}";
    }

    public function initiatePendingTransaction(array $data): FuelTransaction
    {
        return DB::transaction(function () use ($data) {
            $vehicle = Vehicle::with('activeQuota')->findOrFail($data['vehicle_id']);
            $fuelType = FuelType::findOrFail($data['fuel_type_id']);
            $station = Station::findOrFail($data['station_id']);

            $clientWallet = Wallet::where('user_id', $vehicle->client_id)->first();
            if (!$clientWallet || !$clientWallet->is_active) {
                throw new Exception(__('fuel_transactions.client_wallet_inactive'));
            }

            $quota = $vehicle->activeQuota;
            $quotaRemaining = $quota ? $quota->remaining_amount : 0;
            $walletBalance = (float) $clientWallet->valide_balance;

            $maxByQuota = $quotaRemaining;
            $maxByWallet = $walletBalance;
            $maxAllowed = min($maxByQuota, $maxByWallet);

            if ($maxAllowed <= 0) {
                $client = $vehicle->client;
                $driver = isset($data['driver_id']) ? User::find($data['driver_id']) : null;
                
                $reason = $walletBalance <= 0 
                    ? __('fuel_transactions.insufficient_balance')
                    : __('fuel_transactions.quota_exhausted');

                if ($client) {
                    $this->notificationService->notifyDeniedFueling(
                        $client,
                        $reason,
                        $vehicle->plate_number,
                        $driver?->name,
                        $station->name
                    );
                }

                throw new Exception(__('fuel_transactions.insufficient_balance_or_quota'));
            }

            $transaction = FuelTransaction::create([
                'reference_no'       => $this->generateReferenceNo(),
                'qr_token'           => $this->generateQrToken(),
                'client_id'          => $vehicle->client_id,
                'driver_id'          => $data['driver_id'] ?? null,
                'vehicle_id'         => $vehicle->id,
                'station_id'         => $station->id,
                'fuel_type_id'       => $fuelType->id,
                'price_per_liter'    => $fuelType->price_per_liter,
                'max_allowed_amount' => $maxAllowed,
                'status'             => 'pending',
                'type'               => 'qr_based',
            ]);

            return $transaction->load(['client', 'vehicle', 'station', 'fuelType']);
        });
    }

    public function completeTransaction(
        FuelTransaction $transaction,
        float $actualAmount,
        ?UploadedFile $meterImage = null,
        int $workerId
    ): FuelTransaction {
        if (!$transaction->isPending()) {
            throw new Exception(__('fuel_transactions.transaction_not_pending'));
        }

        if ($actualAmount <= 0) {
            throw new Exception(__('fuel_transactions.invalid_amount'));
        }

        if ($actualAmount > (float) $transaction->max_allowed_amount) {
            throw new Exception(__('fuel_transactions.exceeds_max_amount'));
        }

        return DB::transaction(function () use ($transaction, $actualAmount, $meterImage, $workerId) {
            $imagePath = null;
            if ($meterImage) {
                $imagePath = $this->storeMeterImage($meterImage);
            }

            $pricePerLiter = (float) $transaction->price_per_liter;
            $actualLiters = $pricePerLiter > 0 ? $actualAmount / $pricePerLiter : 0;

            $clientWallet = Wallet::where('user_id', $transaction->client_id)->lockForUpdate()->first();
            if (!$clientWallet) {
                throw new Exception(__('fuel_transactions.client_wallet_not_found'));
            }

            $stationWallet = Wallet::whereHas('station', function ($q) use ($transaction) {
                $q->where('id', $transaction->station_id);
            })->lockForUpdate()->first();

            if (!$stationWallet) {
                throw new Exception(__('fuel_transactions.station_wallet_not_found'));
            }

            if (!$stationWallet->is_active) {
                throw new Exception(__('fuel_transactions.station_wallet_inactive'));
            }

            if ((float) $clientWallet->valide_balance < $actualAmount) {
                throw new Exception(__('fuel_transactions.insufficient_client_balance'));
            }

            $clientBeforeBalance = (float) $clientWallet->valide_balance;
            $clientWallet->valide_balance = $clientBeforeBalance - $actualAmount;
            $clientWallet->save();

            Transaction::create([
                'wallet_id'      => $clientWallet->id,
                'amount'         => -$actualAmount,
                'type'           => 'fuel_debit',
                'created_by'     => $workerId,
                'notes'          => "Fuel transaction: {$transaction->reference_no}",
                'before_balance' => $clientBeforeBalance,
                'after_balance'  => $clientWallet->valide_balance,
            ]);

            $stationBeforeBalance = (float) $stationWallet->valide_balance;
            $stationWallet->valide_balance = $stationBeforeBalance + $actualAmount;
            $stationWallet->save();

            Transaction::create([
                'wallet_id'      => $stationWallet->id,
                'amount'         => $actualAmount,
                'type'           => 'fuel_credit',
                'created_by'     => $workerId,
                'notes'          => "Fuel transaction: {$transaction->reference_no}",
                'before_balance' => $stationBeforeBalance,
                'after_balance'  => $stationWallet->valide_balance,
            ]);

            $quota = VehicleQuota::where('vehicle_id', $transaction->vehicle_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($quota) {
                $quota->consumed_amount = (float) $quota->consumed_amount + $actualAmount;
                $quota->save();
            }

            $transaction->update([
                'actual_liters' => $actualLiters,
                'total_amount'  => $actualAmount,
                'meter_image'   => $imagePath,
                'worker_id'     => $workerId,
                'status'        => 'completed',
                'completed_at'  => now(),
            ]);

            $freshTransaction = $transaction->fresh(['client', 'vehicle', 'station', 'worker', 'fuelType']);

            $this->sendPostTransactionNotifications(
                $freshTransaction->client,
                $freshTransaction->vehicle,
                $clientWallet->valide_balance
            );

            return $freshTransaction;
        });
    }

    protected function sendPostTransactionNotifications(
        User $client,
        Vehicle $vehicle,
        float $newBalance
    ): void {
        $this->notificationService->checkAndNotifyLowBalance($client, $newBalance);

        $vehicle->load('activeQuota');
        $this->notificationService->checkAndNotifyQuotaWarning($vehicle);
    }

    public function refundTransaction(FuelTransaction $transaction, string $reason, int $adminId): FuelTransaction
    {
        if (!$transaction->isCompleted()) {
            throw new Exception(__('fuel_transactions.can_only_refund_completed'));
        }

        return DB::transaction(function () use ($transaction, $reason, $adminId) {
            $refundAmount = (float) $transaction->total_amount;

            $clientWallet = Wallet::where('user_id', $transaction->client_id)->lockForUpdate()->first();
            if ($clientWallet) {
                $beforeBalance = (float) $clientWallet->valide_balance;
                $clientWallet->valide_balance = $beforeBalance + $refundAmount;
                $clientWallet->save();

                Transaction::create([
                    'wallet_id'      => $clientWallet->id,
                    'amount'         => $refundAmount,
                    'type'           => 'fuel_refund',
                    'created_by'     => $adminId,
                    'notes'          => "Refund for: {$transaction->reference_no}. Reason: {$reason}",
                    'before_balance' => $beforeBalance,
                    'after_balance'  => $clientWallet->valide_balance,
                ]);
            }

            $stationWallet = Wallet::whereHas('station', function ($q) use ($transaction) {
                $q->where('id', $transaction->station_id);
            })->lockForUpdate()->first();

            if ($stationWallet) {
                $stationBefore = (float) $stationWallet->valide_balance;
                $stationWallet->valide_balance = max(0, $stationBefore - $refundAmount);
                $stationWallet->save();

                Transaction::create([
                    'wallet_id'      => $stationWallet->id,
                    'amount'         => -$refundAmount,
                    'type'           => 'fuel_refund_debit',
                    'created_by'     => $adminId,
                    'notes'          => "Refund debit for: {$transaction->reference_no}",
                    'before_balance' => $stationBefore,
                    'after_balance'  => $stationWallet->valide_balance,
                ]);
            }

            $quota = VehicleQuota::where('vehicle_id', $transaction->vehicle_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($quota) {
                $quota->consumed_amount = max(0, (float) $quota->consumed_amount - $refundAmount);
                $quota->save();
            }

            $transaction->update([
                'status'        => 'refunded',
                'refund_reason' => $reason,
                'refunded_at'   => now(),
                'admin_id'      => $adminId,
            ]);

            return $transaction->fresh(['client', 'vehicle', 'station', 'admin', 'fuelType']);
        });
    }

    public function createManualTransaction(array $data, ?UploadedFile $meterImage, int $adminId): FuelTransaction
    {
        return DB::transaction(function () use ($data, $meterImage, $adminId) {
            $vehicle = Vehicle::with('activeQuota')->findOrFail($data['vehicle_id']);
            $fuelType = FuelType::findOrFail($data['fuel_type_id']);
            $station = Station::findOrFail($data['station_id']);
            $actualAmount = (float) $data['total_amount'];

            $clientWallet = Wallet::where('user_id', $vehicle->client_id)->lockForUpdate()->first();
            if (!$clientWallet) {
                throw new Exception(__('fuel_transactions.client_wallet_not_found'));
            }

            $stationWallet = Wallet::whereHas('station', function ($q) use ($station) {
                $q->where('id', $station->id);
            })->lockForUpdate()->first();

            if (!$stationWallet) {
                throw new Exception(__('fuel_transactions.station_wallet_not_found'));
            }

            if ((float) $clientWallet->valide_balance < $actualAmount) {
                throw new Exception(__('fuel_transactions.insufficient_client_balance'));
            }

            $imagePath = null;
            if ($meterImage) {
                $imagePath = $this->storeMeterImage($meterImage);
            }

            $pricePerLiter = (float) $fuelType->price_per_liter;
            $actualLiters = $pricePerLiter > 0 ? $actualAmount / $pricePerLiter : 0;

            $clientBeforeBalance = (float) $clientWallet->valide_balance;
            $clientWallet->valide_balance = $clientBeforeBalance - $actualAmount;
            $clientWallet->save();

            $referenceNo = $this->generateReferenceNo();

            Transaction::create([
                'wallet_id'      => $clientWallet->id,
                'amount'         => -$actualAmount,
                'type'           => 'fuel_debit',
                'created_by'     => $adminId,
                'notes'          => "Manual fuel transaction: {$referenceNo}",
                'before_balance' => $clientBeforeBalance,
                'after_balance'  => $clientWallet->valide_balance,
            ]);

            $stationBeforeBalance = (float) $stationWallet->valide_balance;
            $stationWallet->valide_balance = $stationBeforeBalance + $actualAmount;
            $stationWallet->save();

            Transaction::create([
                'wallet_id'      => $stationWallet->id,
                'amount'         => $actualAmount,
                'type'           => 'fuel_credit',
                'created_by'     => $adminId,
                'notes'          => "Manual fuel transaction: {$referenceNo}",
                'before_balance' => $stationBeforeBalance,
                'after_balance'  => $stationWallet->valide_balance,
            ]);

            $quota = VehicleQuota::where('vehicle_id', $vehicle->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($quota) {
                $quota->consumed_amount = (float) $quota->consumed_amount + $actualAmount;
                $quota->save();
            }

            $transaction = FuelTransaction::create([
                'reference_no'       => $referenceNo,
                'qr_token'           => null,
                'client_id'          => $vehicle->client_id,
                'driver_id'          => $data['driver_id'] ?? null,
                'vehicle_id'         => $vehicle->id,
                'station_id'         => $station->id,
                'fuel_type_id'       => $fuelType->id,
                'price_per_liter'    => $pricePerLiter,
                'actual_liters'      => $actualLiters,
                'total_amount'       => $actualAmount,
                'max_allowed_amount' => $actualAmount,
                'meter_image'        => $imagePath,
                'status'             => 'completed',
                'type'               => 'manual_admin',
                'admin_id'           => $adminId,
                'completed_at'       => now(),
            ]);

            $freshTransaction = $transaction->load(['client', 'vehicle', 'station', 'admin', 'fuelType']);

            $this->sendPostTransactionNotifications(
                $freshTransaction->client,
                $freshTransaction->vehicle,
                $clientWallet->valide_balance
            );

            return $freshTransaction;
        });
    }

    public function cancelTransaction(FuelTransaction $transaction, int $adminId): FuelTransaction
    {
        if (!$transaction->isPending()) {
            throw new Exception(__('fuel_transactions.can_only_cancel_pending'));
        }

        $transaction->update([
            'status'   => 'cancelled',
            'admin_id' => $adminId,
        ]);

        return $transaction->fresh();
    }

    private function storeMeterImage(UploadedFile $file): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $directory = "transactions/{$year}/{$month}";

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    public function getMeterImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
