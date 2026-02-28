<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\DepositRequest;
use App\Notifications\LowBalanceNotification;
use App\Notifications\QuotaWarningNotification;
use App\Notifications\DeniedFuelingNotification;
use App\Notifications\DepositApprovedNotification;
use App\Notifications\DepositRejectedNotification;

class NotificationService
{
    protected float $lowBalanceThreshold = 100;
    protected float $quotaWarningPercentage = 90;

    public function setLowBalanceThreshold(float $threshold): self
    {
        $this->lowBalanceThreshold = $threshold;
        return $this;
    }

    public function setQuotaWarningPercentage(float $percentage): self
    {
        $this->quotaWarningPercentage = $percentage;
        return $this;
    }

    public function checkAndNotifyLowBalance(User $client, float $currentBalance): bool
    {
        if ($currentBalance < $this->lowBalanceThreshold) {
            $client->notify(new LowBalanceNotification($currentBalance, $this->lowBalanceThreshold));
            return true;
        }
        return false;
    }

    public function checkAndNotifyQuotaWarning(Vehicle $vehicle): bool
    {
        $quota = $vehicle->activeQuota;
        
        if (!$quota || (float) $quota->amount_limit <= 0) {
            return false;
        }

        $usedPercentage = ((float) $quota->consumed_amount / (float) $quota->amount_limit) * 100;

        if ($usedPercentage >= $this->quotaWarningPercentage) {
            $client = $vehicle->client;
            if ($client) {
                $client->notify(new QuotaWarningNotification($vehicle, $usedPercentage));
                return true;
            }
        }

        return false;
    }

    public function notifyDeniedFueling(
        User $client,
        string $reason,
        ?string $vehiclePlate = null,
        ?string $driverName = null,
        ?string $stationName = null
    ): void {
        $client->notify(new DeniedFuelingNotification(
            $reason,
            $vehiclePlate,
            $driverName,
            $stationName
        ));
    }

    public function notifyDepositApproved(DepositRequest $deposit): void
    {
        $client = $deposit->client;
        if ($client) {
            $client->notify(new DepositApprovedNotification($deposit));
        }
    }

    public function notifyDepositRejected(DepositRequest $deposit): void
    {
        $client = $deposit->client;
        if ($client) {
            $client->notify(new DepositRejectedNotification($deposit));
        }
    }

    public function getLowBalanceThreshold(): float
    {
        return $this->lowBalanceThreshold;
    }

    public function getQuotaWarningPercentage(): float
    {
        return $this->quotaWarningPercentage;
    }
}
