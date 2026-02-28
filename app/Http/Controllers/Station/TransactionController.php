<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\FuelType;
use App\Models\StationWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StationTransactionsExport;

class TransactionController extends Controller
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
                ->with('error', __('station.transactions.no_station'));
        }

        $workers = StationWorker::where('station_id', $stationId)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $fuelTypes = FuelType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $todayStats = $this->getTodayStats($stationId);

        return view('station.transactions.index', compact('workers', 'fuelTypes', 'todayStats'));
    }

    public function data(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return response()->json(['data' => [], 'stats' => []]);
        }

        $query = FuelTransaction::where('station_id', $stationId)
            ->with([
                'vehicle:id,plate_number',
                'client:id,name,company_name',
                'worker:id,full_name',
                'fuelType:id,name'
            ])
            ->orderBy('created_at', 'desc');

        $this->applyFilters($query, $request);

        $transactions = $query->get();

        $data = $transactions->map(function ($tx) {
            return [
                'id'           => $tx->id,
                'time'         => $tx->created_at->format('Y-m-d H:i'),
                'reference_no' => $tx->reference_no,
                'vehicle'      => $tx->vehicle ? $tx->vehicle->plate_number : '-',
                'client'       => $tx->client ? ($tx->client->company_name ?: $tx->client->name) : '-',
                'worker'       => $tx->worker ? $tx->worker->full_name : '-',
                'fuel_type'    => $tx->fuelType ? $tx->fuelType->name : '-',
                'liters'       => (float) $tx->actual_liters,
                'amount'       => (float) $tx->total_amount,
                'status'       => $tx->status,
                'has_image'    => !empty($tx->meter_image),
            ];
        });

        $stats = $this->calculateStats($transactions);

        return response()->json([
            'data'  => $data,
            'stats' => $stats,
        ]);
    }

    public function viewProof($id)
    {
        $stationId = $this->getStationId();

        $transaction = FuelTransaction::where('id', $id)
            ->where('station_id', $stationId)
            ->with(['vehicle', 'client', 'worker', 'fuelType', 'driver'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => __('station.transactions.not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $transaction->id,
                'reference_no'  => $transaction->reference_no,
                'time'          => $transaction->created_at->format('Y-m-d H:i:s'),
                'vehicle'       => $transaction->vehicle ? $transaction->vehicle->plate_number : '-',
                'client'        => $transaction->client ? ($transaction->client->company_name ?: $transaction->client->name) : '-',
                'driver'        => $transaction->driver ? $transaction->driver->name : '-',
                'worker'        => $transaction->worker ? $transaction->worker->full_name : '-',
                'fuel_type'     => $transaction->fuelType ? $transaction->fuelType->name : '-',
                'price_per_liter' => (float) $transaction->price_per_liter,
                'liters'        => (float) $transaction->actual_liters,
                'amount'        => (float) $transaction->total_amount,
                'status'        => $transaction->status,
                'has_image'     => !empty($transaction->meter_image),
                'image_url'     => $transaction->meter_image
                    ? route('station.transactions.image', $transaction->id)
                    : null,
            ],
        ]);
    }

    public function meterImage($id)
    {
        $stationId = $this->getStationId();

        $transaction = FuelTransaction::where('id', $id)
            ->where('station_id', $stationId)
            ->first();

        if (!$transaction || !$transaction->meter_image) {
            abort(404);
        }

        $path = $transaction->meter_image;

        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        if (file_exists(public_path($path))) {
            return response()->file(public_path($path));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return back()->with('error', __('station.transactions.no_station'));
        }

        $query = FuelTransaction::where('station_id', $stationId)
            ->with(['vehicle', 'client', 'worker', 'fuelType'])
            ->orderBy('created_at', 'desc');

        $this->applyFilters($query, $request);

        $transactions = $query->get();

        $filename = 'station_transactions_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new StationTransactionsExport($transactions), $filename);
    }

    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }

        if ($request->filled('fuel_type_id')) {
            $query->where('fuel_type_id', $request->fuel_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    }

    protected function getTodayStats(int $stationId): array
    {
        $today = now()->toDateString();

        $stats = FuelTransaction::where('station_id', $stationId)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as transactions_count,
                COALESCE(SUM(actual_liters), 0) as total_liters,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COUNT(DISTINCT vehicle_id) as vehicles_count
            ')
            ->first();

        return [
            'transactions_count' => (int) $stats->transactions_count,
            'total_liters'       => (float) $stats->total_liters,
            'total_amount'       => (float) $stats->total_amount,
            'vehicles_count'     => (int) $stats->vehicles_count,
        ];
    }

    protected function calculateStats($transactions): array
    {
        $completed = $transactions->where('status', 'completed');

        return [
            'transactions_count' => $completed->count(),
            'total_liters'       => (float) $completed->sum('liters'),
            'total_amount'       => (float) $completed->sum('amount'),
            'vehicles_count'     => $completed->pluck('vehicle')->unique()->count(),
        ];
    }
}
