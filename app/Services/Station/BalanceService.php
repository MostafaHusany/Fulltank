<?php

namespace App\Services\Station;

use App\Models\FuelTransaction;
use App\Models\Settlement;
use App\Models\Station;

class BalanceService
{
    public function getStationBalance(?int $stationId): array
    {
        if (!$stationId) {
            return [
                'total_revenue'       => 0,
                'settled_amount'      => 0,
                'outstanding_balance' => 0,
                'formatted_balance'   => '0.00',
            ];
        }

        $totalRevenue = (float) FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->sum('total_amount');

        $settledAmount = (float) Settlement::where('station_id', $stationId)
            ->sum('amount');

        $outstandingBalance = $totalRevenue - $settledAmount;

        return [
            'total_revenue'       => $totalRevenue,
            'settled_amount'      => $settledAmount,
            'outstanding_balance' => $outstandingBalance,
            'formatted_balance'   => number_format($outstandingBalance, 2),
        ];
    }

    public function getStationIdForUser($user): ?int
    {
        if (!$user) {
            return null;
        }

        $station = $user->managedStation;
        return $station ? $station->id : null;
    }
}
