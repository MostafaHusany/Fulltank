<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Carbon\Carbon;

use App\Models\Vehicle;
use App\Models\FuelTransaction;
use App\Http\Traits\ResponseTemplate;

class ReportController extends Controller
{
    use ResponseTemplate;

    public function index()
    {
        return view('clients.reports.index');
    }

    public function vehicleConsumption(Request $request)
    {
        $clientId = auth()->id();

        $rules = [
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ];

        $validated = $request->validate($rules);

        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate   = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');

        $query = Vehicle::where('client_id', $clientId)
            ->with(['fuelType:id,name'])
            ->withSum(['fuelTransactions as total_liters' => function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }], 'actual_liters')
            ->withSum(['fuelTransactions as total_amount' => function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }], 'total_amount')
            ->withCount(['fuelTransactions as transaction_count' => function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')
                    ->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            }]);

        if (!empty($validated['vehicle_id'])) {
            $query->where('id', $validated['vehicle_id']);
        }

        $vehicles = $query->get();

        $totals = [
            'total_liters'      => $vehicles->sum('total_liters'),
            'total_amount'      => $vehicles->sum('total_amount'),
            'transaction_count' => $vehicles->sum('transaction_count'),
        ];

        return $this->responseTemplate([
            'vehicles' => $vehicles,
            'totals'   => $totals,
            'period'   => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
        ], true, null);
    }

    public function driverActivity(Request $request)
    {
        $clientId = auth()->id();

        $rules = [
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'driver_id'  => 'nullable|exists:users,id',
        ];

        $validated = $request->validate($rules);

        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate   = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');

        $query = FuelTransaction::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->with(['driver:id,name', 'vehicle:id,plate_number'])
            ->selectRaw('
                driver_id,
                COUNT(*) as transaction_count,
                SUM(actual_liters) as total_liters,
                SUM(total_amount) as total_amount
            ')
            ->groupBy('driver_id');

        if (!empty($validated['driver_id'])) {
            $query->where('driver_id', $validated['driver_id']);
        }

        $drivers = $query->get();

        return $this->responseTemplate([
            'drivers' => $drivers,
            'period'  => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
        ], true, null);
    }

    public function statement(Request $request)
    {
        $clientId = auth()->id();
        $wallet   = auth()->user()->wallet;

        if (!$wallet) {
            return $this->responseTemplate([], false, __('client.reports.no_wallet'));
        }

        $rules = [
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ];

        $validated = $request->validate($rules);

        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate   = $validated['end_date'] ?? Carbon::now()->format('Y-m-d');

        $transactions = $wallet->transactions()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();

        $deposits    = $transactions->where('type', 'deposit')->sum('amount');
        $withdrawals = $transactions->where('type', 'withdrawal')->sum('amount');

        return $this->responseTemplate([
            'transactions' => $transactions,
            'summary'      => [
                'deposits'        => $deposits,
                'withdrawals'     => abs($withdrawals),
                'current_balance' => $wallet->balance,
            ],
            'period' => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
        ], true, null);
    }
}
