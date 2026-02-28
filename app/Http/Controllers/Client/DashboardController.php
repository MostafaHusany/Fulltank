<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\FuelTransaction;
use App\Http\Traits\ResponseTemplate;

class DashboardController extends Controller
{
    use ResponseTemplate;

    public function index(Request $request)
    {
        $user = auth()->user();
        $clientId = $user->id;

        $stats = $this->getClientStats($clientId);

        if ($request->ajax()) {
            if ($request->has('refresh_stats')) {
                return $this->responseTemplate($stats, true, null);
            }
        }

        return view('clients.dashboard.index', compact('stats'));
    }

    public function getStats(Request $request)
    {
        $user = auth()->user();
        $clientId = $user->id;

        $stats = $this->getClientStats($clientId);

        return $this->responseTemplate($stats, true, null);
    }

    protected function getClientStats(int $clientId): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $wallet = auth()->user()->wallet;
        $walletBalance = $wallet ? $wallet->balance : 0;

        $vehicleCount = Vehicle::where('client_id', $clientId)->count();
        $activeVehicles = Vehicle::where('client_id', $clientId)
            ->where('status', 'active')
            ->count();

        $driverCount = User::where('category', 'driver')
            ->where('client_id', $clientId)
            ->count();
        $activeDrivers = User::where('category', 'driver')
            ->where('client_id', $clientId)
            ->where('is_active', true)
            ->count();

        $todayTransactions = FuelTransaction::where('client_id', $clientId)
            ->whereDate('created_at', $today)
            ->count();

        $todayLiters = FuelTransaction::where('client_id', $clientId)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('actual_liters');

        $todayAmount = FuelTransaction::where('client_id', $clientId)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_amount');

        $monthlyLiters = FuelTransaction::where('client_id', $clientId)
            ->where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('actual_liters');

        $monthlyAmount = FuelTransaction::where('client_id', $clientId)
            ->where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('total_amount');

        $recentTransactions = FuelTransaction::where('client_id', $clientId)
            ->with(['vehicle:id,plate_number', 'station:id,name', 'fuelType:id,name'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'wallet_balance'       => number_format($walletBalance, 2),
            'vehicle_count'        => $vehicleCount,
            'active_vehicles'      => $activeVehicles,
            'driver_count'         => $driverCount,
            'active_drivers'       => $activeDrivers,
            'today_transactions'   => $todayTransactions,
            'today_liters'         => number_format($todayLiters, 2),
            'today_amount'         => number_format($todayAmount, 2),
            'monthly_liters'       => number_format($monthlyLiters, 2),
            'monthly_amount'       => number_format($monthlyAmount, 2),
            'recent_transactions'  => $recentTransactions,
        ];
    }
}
