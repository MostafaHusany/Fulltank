<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Station;
use App\Models\Vehicle;
use App\Models\FuelType;
use App\Models\FuelTransaction;
use App\Models\Governorate;

class DashboardService
{
    public function getStatCards(): array
    {
        $today = Carbon::today();

        $clientBalances = Wallet::whereHas('user', function ($q) {
            $q->where('category', 'client');
        })->sum('valide_balance');

        $stationUnsettledBalances = Wallet::whereHas('station')->sum('valide_balance');

        $dailyFuelLiters = FuelTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('actual_liters');

        $totalTransactionsToday = FuelTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        $totalTransactionsAmount = FuelTransaction::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $activeStations = Station::whereHas('wallet', function ($q) {
            $q->where('is_active', true);
        })->count();

        $activeVehicles = Vehicle::where('status', 'active')->count();

        return [
            'client_balances'           => (float) $clientBalances,
            'station_unsettled'         => (float) $stationUnsettledBalances,
            'daily_liters'              => (float) $dailyFuelLiters,
            'transactions_today'        => (int) $totalTransactionsToday,
            'transactions_amount_today' => (float) $totalTransactionsAmount,
            'active_stations'           => (int) $activeStations,
            'active_vehicles'           => (int) $activeVehicles,
        ];
    }

    public function getWeeklyConsumptionTrend(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D');

            $liters = FuelTransaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('actual_liters');

            $data[] = round((float) $liters, 2);
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }

    public function getFuelTypeDistribution(): array
    {
        $fuelTypes = FuelType::all();
        $labels = [];
        $data = [];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

        $colorIndex = 0;
        foreach ($fuelTypes as $fuelType) {
            $totalLiters = FuelTransaction::where('fuel_type_id', $fuelType->id)
                ->where('status', 'completed')
                ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
                ->sum('actual_liters');

            if ($totalLiters > 0) {
                $labels[] = $fuelType->name;
                $data[] = round((float) $totalLiters, 2);
            }
            $colorIndex++;
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'colors' => array_slice($colors, 0, count($labels)),
        ];
    }

    public function getStationsForMap(?int $governorateId = null): array
    {
        $today = Carbon::today();

        $query = Station::query()
            ->with(['governorate:id,name', 'district:id,name'])
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        if ($governorateId) {
            $query->where('governorate_id', $governorateId);
        }

        $stations = $query->get();

        return $stations->map(function ($station) use ($today) {
            $transactionsToday = FuelTransaction::where('station_id', $station->id)
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->count();

            $amountToday = FuelTransaction::where('station_id', $station->id)
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('total_amount');

            return [
                'id'                 => $station->id,
                'name'               => $station->name,
                'lat'                => (float) $station->lat,
                'lng'                => (float) $station->lng,
                'manager_name'       => $station->manager_name ?? '---',
                'governorate'        => $station->governorate->name ?? '---',
                'district'           => $station->district->name ?? '---',
                'transactions_today' => $transactionsToday,
                'amount_today'       => number_format((float) $amountToday, 2),
            ];
        })->toArray();
    }

    public function getGovernorates(): array
    {
        return Governorate::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function getGovernorateCenter(int $governorateId): ?array
    {
        $station = Station::where('governorate_id', $governorateId)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->first();

        if ($station) {
            return [
                'lat'  => (float) $station->lat,
                'lng'  => (float) $station->lng,
                'zoom' => 10,
            ];
        }

        return null;
    }

    public function getMonthlyTrend(): array
    {
        $labels = [];
        $amounts = [];
        $liters = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');

            $dayAmount = FuelTransaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');

            $dayLiters = FuelTransaction::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('actual_liters');

            $amounts[] = round((float) $dayAmount, 2);
            $liters[] = round((float) $dayLiters, 2);
        }

        return [
            'labels'  => $labels,
            'amounts' => $amounts,
            'liters'  => $liters,
        ];
    }
}
