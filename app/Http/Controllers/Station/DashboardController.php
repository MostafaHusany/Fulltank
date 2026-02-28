<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\FuelType;
use App\Models\Settlement;
use App\Models\StationWorker;
use App\Services\Station\BalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        protected BalanceService $balanceService
    ) {}

    protected function getStationId(): ?int
    {
        $user = auth()->user();
        $station = $user->managedStation;
        return $station ? $station->id : null;
    }

    public function index(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return view('station.dashboard.index', [
                'hasStation' => false,
            ]);
        }

        $period = $request->get('period', 'today');
        $dateRange = $this->getDateRange($period);

        $stats = $this->getStats($stationId, $dateRange);
        $fuelTypes = FuelType::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('station.dashboard.index', [
            'hasStation'    => true,
            'stats'         => $stats,
            'fuelTypes'     => $fuelTypes,
            'currentPeriod' => $period,
        ]);
    }

    public function analyticsData(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return response()->json(['success' => false]);
        }

        $period = $request->get('period', 'today');
        $dateRange = $this->getDateRange($period);

        $fuelConsumption = $this->getFuelConsumptionByType($stationId, $dateRange);
        $salesTrend = $this->getSalesTrend($stationId, 14);
        $topWorkers = $this->getTopWorkers($stationId, $dateRange, 5);
        $latestTransactions = $this->getLatestTransactions($stationId, 10);
        $stats = $this->getPeriodStats($stationId, $dateRange);

        return response()->json([
            'success'           => true,
            'fuel_consumption'  => $fuelConsumption,
            'sales_trend'       => $salesTrend,
            'top_workers'       => $topWorkers,
            'latest_transactions' => $latestTransactions,
            'stats'             => $stats,
        ]);
    }

    protected function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end'   => $now->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end'   => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end'   => $now->copy()->endOfMonth(),
            ],
            default => [
                'start' => $now->copy()->startOfDay(),
                'end'   => $now->copy()->endOfDay(),
            ],
        };
    }

    protected function getStats(int $stationId, array $dateRange): array
    {
        $balance = $this->balanceService->getStationBalance($stationId);

        $periodStats = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                COUNT(*) as transactions_count,
                COALESCE(SUM(actual_liters), 0) as total_liters,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COUNT(DISTINCT worker_id) as active_workers
            ')
            ->first();

        $totalWorkers = StationWorker::where('station_id', $stationId)
            ->where('is_active', true)
            ->count();

        return [
            'outstanding_balance' => $balance['outstanding_balance'],
            'formatted_balance'   => $balance['formatted_balance'],
            'period_liters'       => (float) $periodStats->total_liters,
            'period_transactions' => (int) $periodStats->transactions_count,
            'period_amount'       => (float) $periodStats->total_amount,
            'active_workers'      => (int) $periodStats->active_workers,
            'total_workers'       => $totalWorkers,
        ];
    }

    protected function getPeriodStats(int $stationId, array $dateRange): array
    {
        $stats = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('
                COUNT(*) as transactions_count,
                COALESCE(SUM(actual_liters), 0) as total_liters,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COUNT(DISTINCT worker_id) as active_workers
            ')
            ->first();

        return [
            'transactions_count' => (int) $stats->transactions_count,
            'total_liters'       => (float) $stats->total_liters,
            'total_amount'       => (float) $stats->total_amount,
            'active_workers'     => (int) $stats->active_workers,
        ];
    }

    protected function getFuelConsumptionByType(int $stationId, array $dateRange): array
    {
        $consumption = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->join('fuel_types', 'fuel_transactions.fuel_type_id', '=', 'fuel_types.id')
            ->groupBy('fuel_types.id', 'fuel_types.name')
            ->selectRaw('
                fuel_types.id,
                fuel_types.name,
                COALESCE(SUM(fuel_transactions.actual_liters), 0) as total_liters,
                COALESCE(SUM(fuel_transactions.total_amount), 0) as total_amount,
                COUNT(*) as transactions_count
            ')
            ->orderByDesc('total_amount')
            ->get();

        return $consumption->map(function ($item) {
            return [
                'id'           => $item->id,
                'name'         => $item->name,
                'liters'       => (float) $item->total_liters,
                'amount'       => (float) $item->total_amount,
                'transactions' => (int) $item->transactions_count,
            ];
        })->toArray();
    }

    protected function getSalesTrend(int $stationId, int $days = 14): array
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $dailySales = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as total_amount, COALESCE(SUM(actual_liters), 0) as total_liters')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $trend = [];
        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d');
            $dayData = $dailySales->get($date);

            $trend[] = [
                'date'   => $date,
                'label'  => Carbon::parse($date)->format('M d'),
                'amount' => $dayData ? (float) $dayData->total_amount : 0,
                'liters' => $dayData ? (float) $dayData->total_liters : 0,
            ];
        }

        return $trend;
    }

    protected function getTopWorkers(int $stationId, array $dateRange, int $limit = 5): array
    {
        $workers = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('worker_id')
            ->join('station_workers', 'fuel_transactions.worker_id', '=', 'station_workers.id')
            ->groupBy('station_workers.id', 'station_workers.full_name')
            ->selectRaw('
                station_workers.id,
                station_workers.full_name as name,
                COUNT(*) as transactions_count,
                COALESCE(SUM(fuel_transactions.actual_liters), 0) as total_liters,
                COALESCE(SUM(fuel_transactions.total_amount), 0) as total_amount
            ')
            ->orderByDesc('transactions_count')
            ->limit($limit)
            ->get();

        return $workers->map(function ($worker, $index) {
            return [
                'rank'         => $index + 1,
                'id'           => $worker->id,
                'name'         => $worker->name,
                'transactions' => (int) $worker->transactions_count,
                'liters'       => (float) $worker->total_liters,
                'amount'       => (float) $worker->total_amount,
            ];
        })->toArray();
    }

    protected function getLatestTransactions(int $stationId, int $limit = 10): array
    {
        $transactions = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->with([
                'vehicle:id,plate_number',
                'worker:id,full_name',
                'fuelType:id,name'
            ])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $transactions->map(function ($tx) {
            return [
                'id'        => $tx->id,
                'time'      => $tx->created_at->format('H:i'),
                'date'      => $tx->created_at->format('Y-m-d'),
                'vehicle'   => $tx->vehicle ? $tx->vehicle->plate_number : '-',
                'worker'    => $tx->worker ? $tx->worker->full_name : '-',
                'fuel_type' => $tx->fuelType ? $tx->fuelType->name : '-',
                'liters'    => (float) $tx->actual_liters,
                'amount'    => (float) $tx->total_amount,
            ];
        })->toArray();
    }
}
