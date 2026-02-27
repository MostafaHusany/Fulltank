<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Station;
use App\Models\Vehicle;
use App\Models\FuelTransaction;
use App\Models\Settlement;
use App\Models\Transaction;

class ReportService
{
    public function getClientStatement(int $clientId, ?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20)
    {
        $client = User::with('wallet')->find($clientId);

        if (!$client) {
            return null;
        }

        $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfDay();

        $wallet = $client->wallet;
        $walletId = $wallet ? $wallet->id : null;

        $openingBalance = 0;
        if ($walletId) {
            $firstTransactionBefore = Transaction::where('wallet_id', $walletId)
                ->where('created_at', '<', $dateFrom)
                ->orderBy('created_at', 'desc')
                ->first();

            $openingBalance = $firstTransactionBefore ? (float) $firstTransactionBefore->after_balance : 0;
        }

        $transactionsQuery = Transaction::where('wallet_id', $walletId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'asc');

        $transactions = $transactionsQuery->paginate($perPage);

        $totals = Transaction::where('wallet_id', $walletId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as total_credits')
            ->selectRaw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as total_debits')
            ->first();

        $closingBalance = $wallet ? (float) $wallet->valide_balance : 0;

        $fuelStats = FuelTransaction::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(actual_liters) as total_liters')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();

        return [
            'client' => [
                'id'           => $client->id,
                'name'         => $client->company_name ?: $client->name,
                'phone'        => $client->phone,
                'email'        => $client->email,
            ],
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to'   => $dateTo->format('Y-m-d'),
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_credits'   => (float) ($totals->total_credits ?? 0),
            'total_debits'    => (float) ($totals->total_debits ?? 0),
            'fuel_stats' => [
                'transaction_count' => (int) ($fuelStats->transaction_count ?? 0),
                'total_liters'      => (float) ($fuelStats->total_liters ?? 0),
                'total_amount'      => (float) ($fuelStats->total_amount ?? 0),
            ],
            'transactions' => $transactions,
        ];
    }

    public function getStationReport(int $stationId, ?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20)
    {
        $station = Station::with(['wallet', 'governorate', 'district'])->find($stationId);

        if (!$station) {
            return null;
        }

        $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfDay();

        $transactionsQuery = FuelTransaction::where('station_id', $stationId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['client:id,name,company_name', 'vehicle:id,plate_number', 'fuelType:id,name', 'worker.user:id,name'])
            ->orderBy('created_at', 'desc');

        $transactions = $transactionsQuery->paginate($perPage);

        $stats = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(actual_liters) as total_liters')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();

        $refundedStats = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'refunded')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_amount) as amount')
            ->first();

        $settlementsTotal = Settlement::where('station_id', $stationId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('amount');

        $currentBalance = $station->wallet ? (float) $station->wallet->valide_balance : 0;

        return [
            'station' => [
                'id'           => $station->id,
                'name'         => $station->name,
                'manager_name' => $station->manager_name,
                'governorate'  => $station->governorate->name ?? '---',
                'district'     => $station->district->name ?? '---',
            ],
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to'   => $dateTo->format('Y-m-d'),
            ],
            'stats' => [
                'transaction_count' => (int) ($stats->transaction_count ?? 0),
                'total_liters'      => (float) ($stats->total_liters ?? 0),
                'total_amount'      => (float) ($stats->total_amount ?? 0),
                'refunded_count'    => (int) ($refundedStats->count ?? 0),
                'refunded_amount'   => (float) ($refundedStats->amount ?? 0),
                'settlements_total' => (float) $settlementsTotal,
                'current_balance'   => $currentBalance,
            ],
            'transactions' => $transactions,
        ];
    }

    public function getVehicleConsumption(int $clientId, ?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20)
    {
        $client = User::find($clientId);

        if (!$client) {
            return null;
        }

        $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfDay();

        $vehiclesQuery = Vehicle::where('client_id', $clientId)
            ->withCount(['fuelTransactions as transaction_count' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->withSum(['fuelTransactions as total_liters' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
            }], 'actual_liters')
            ->withSum(['fuelTransactions as total_amount' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$dateFrom, $dateTo]);
            }], 'total_amount')
            ->orderByDesc('total_amount');

        $vehicles = $vehiclesQuery->paginate($perPage);

        $overallStats = FuelTransaction::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(actual_liters) as total_liters')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();

        return [
            'client' => [
                'id'   => $client->id,
                'name' => $client->company_name ?: $client->name,
            ],
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to'   => $dateTo->format('Y-m-d'),
            ],
            'overall_stats' => [
                'transaction_count' => (int) ($overallStats->transaction_count ?? 0),
                'total_liters'      => (float) ($overallStats->total_liters ?? 0),
                'total_amount'      => (float) ($overallStats->total_amount ?? 0),
            ],
            'vehicles' => $vehicles,
        ];
    }

    public function getVehicleDetailedReport(int $vehicleId, ?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20)
    {
        $vehicle = Vehicle::with(['client:id,name,company_name'])->find($vehicleId);

        if (!$vehicle) {
            return null;
        }

        $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfDay();

        $transactionsQuery = FuelTransaction::where('vehicle_id', $vehicleId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['station:id,name', 'fuelType:id,name', 'worker.user:id,name'])
            ->orderBy('created_at', 'desc');

        $transactions = $transactionsQuery->paginate($perPage);

        $stats = FuelTransaction::where('vehicle_id', $vehicleId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(actual_liters) as total_liters')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();

        return [
            'vehicle' => [
                'id'           => $vehicle->id,
                'plate_number' => $vehicle->plate_number,
                'model'        => $vehicle->model,
                'client_name'  => $vehicle->client->company_name ?? $vehicle->client->name ?? '---',
            ],
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to'   => $dateTo->format('Y-m-d'),
            ],
            'stats' => [
                'transaction_count' => (int) ($stats->transaction_count ?? 0),
                'total_liters'      => (float) ($stats->total_liters ?? 0),
                'total_amount'      => (float) ($stats->total_amount ?? 0),
            ],
            'transactions' => $transactions,
        ];
    }

    public function getOverallSummary(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFrom = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $dateTo = $dateTo ? Carbon::parse($dateTo)->endOfDay() : Carbon::now()->endOfDay();

        $fuelStats = FuelTransaction::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('SUM(actual_liters) as total_liters')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->first();

        $settlementsTotal = Settlement::whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('amount');

        $activeClients = FuelTransaction::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->distinct('client_id')
            ->count('client_id');

        $activeStations = FuelTransaction::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->distinct('station_id')
            ->count('station_id');

        return [
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to'   => $dateTo->format('Y-m-d'),
            ],
            'transactions' => [
                'count'        => (int) ($fuelStats->transaction_count ?? 0),
                'total_liters' => (float) ($fuelStats->total_liters ?? 0),
                'total_amount' => (float) ($fuelStats->total_amount ?? 0),
            ],
            'settlements_total' => (float) $settlementsTotal,
            'active_clients'    => $activeClients,
            'active_stations'   => $activeStations,
        ];
    }
}
