<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\Settlement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FinancialController extends Controller
{
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
            return redirect()->route('station.dashboard')
                ->with('error', __('station.financials.no_station'));
        }

        $summary = $this->getFinancialSummary($stationId);

        return view('station.financials.index', compact('summary'));
    }

    public function settlements(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return response()->json(['data' => []]);
        }

        $query = Settlement::where('station_id', $stationId)
            ->with('admin:id,name')
            ->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $settlements = $query->get();

        $data = $settlements->map(function ($settlement) {
            return [
                'id'              => $settlement->id,
                'reference_no'    => $settlement->reference_no,
                'date'            => $settlement->created_at->format('Y-m-d H:i'),
                'amount'          => (float) $settlement->amount,
                'payment_method'  => $settlement->payment_method,
                'payment_label'   => $settlement->payment_method_label,
                'details'         => $settlement->transaction_details,
                'admin'           => $settlement->admin ? $settlement->admin->name : '-',
                'has_receipt'     => !empty($settlement->receipt_image),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function viewReceipt($id)
    {
        $stationId = $this->getStationId();

        $settlement = Settlement::where('id', $id)
            ->where('station_id', $stationId)
            ->first();

        if (!$settlement) {
            return response()->json([
                'success' => false,
                'message' => __('station.financials.settlement_not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $settlement->id,
                'reference_no'   => $settlement->reference_no,
                'date'           => $settlement->created_at->format('Y-m-d H:i:s'),
                'amount'         => (float) $settlement->amount,
                'payment_method' => $settlement->payment_method_label,
                'details'        => $settlement->transaction_details,
                'admin'          => $settlement->admin ? $settlement->admin->name : '-',
                'has_receipt'    => !empty($settlement->receipt_image),
                'receipt_url'    => $settlement->receipt_image
                    ? route('station.financials.receipt_image', $settlement->id)
                    : null,
            ],
        ]);
    }

    public function receiptImage($id)
    {
        $stationId = $this->getStationId();

        $settlement = Settlement::where('id', $id)
            ->where('station_id', $stationId)
            ->first();

        if (!$settlement || !$settlement->receipt_image) {
            abort(404);
        }

        $path = storage_path('app/public/' . $settlement->receipt_image);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function transactions(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return response()->json(['data' => [], 'summary' => []]);
        }

        $query = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->with([
                'vehicle:id,plate_number',
                'client:id,name,company_name',
                'worker:id,full_name',
                'fuelType:id,name'
            ])
            ->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->limit(100)->get();

        $data = $transactions->map(function ($tx) {
            return [
                'id'           => $tx->id,
                'reference_no' => $tx->reference_no,
                'date'         => $tx->created_at->format('Y-m-d H:i'),
                'vehicle'      => $tx->vehicle ? $tx->vehicle->plate_number : '-',
                'client'       => $tx->client ? ($tx->client->company_name ?: $tx->client->name) : '-',
                'worker'       => $tx->worker ? $tx->worker->full_name : '-',
                'fuel_type'    => $tx->fuelType ? $tx->fuelType->name : '-',
                'liters'       => (float) $tx->actual_liters,
                'amount'       => (float) $tx->total_amount,
            ];
        });

        $summary = [
            'count'        => $transactions->count(),
            'total_liters' => (float) $transactions->sum('actual_liters'),
            'total_amount' => (float) $transactions->sum('total_amount'),
        ];

        return response()->json([
            'data'    => $data,
            'summary' => $summary,
        ]);
    }

    protected function getFinancialSummary(int $stationId): array
    {
        $totalRevenue = (float) FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->sum('total_amount');

        $settledAmount = (float) Settlement::where('station_id', $stationId)
            ->sum('amount');

        $outstandingBalance = $totalRevenue - $settledAmount;

        $lastSettlement = Settlement::where('station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->first();

        $thisMonthRevenue = (float) FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $thisMonthSettled = (float) Settlement::where('station_id', $stationId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $totalTransactions = FuelTransaction::where('station_id', $stationId)
            ->where('status', 'completed')
            ->count();

        $totalSettlements = Settlement::where('station_id', $stationId)->count();

        return [
            'total_revenue'       => $totalRevenue,
            'settled_amount'      => $settledAmount,
            'outstanding_balance' => $outstandingBalance,
            'last_settlement'     => $lastSettlement ? [
                'date'   => $lastSettlement->created_at->format('Y-m-d'),
                'amount' => (float) $lastSettlement->amount,
            ] : null,
            'this_month' => [
                'revenue' => $thisMonthRevenue,
                'settled' => $thisMonthSettled,
            ],
            'counts' => [
                'transactions' => $totalTransactions,
                'settlements'  => $totalSettlements,
            ],
        ];
    }
}
