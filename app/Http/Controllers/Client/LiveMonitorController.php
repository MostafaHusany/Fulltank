<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\FuelTransaction;
use App\Http\Traits\ResponseTemplate;

class LiveMonitorController extends Controller
{
    use ResponseTemplate;

    public function index()
    {
        $clientId = auth()->id();
        $stats = $this->getTodayStats($clientId);

        return view('clients.live_monitor.index', [
            'litersToday'    => $stats['total_liters'],
            'activeDrivers'  => $stats['active_drivers'],
            'spentToday'     => $stats['spent_today'],
            'transactionsCount' => $stats['transactions_count'],
        ]);
    }

    public function transactions(Request $request)
    {
        $clientId = auth()->id();

        $transactions = FuelTransaction::where('client_id', $clientId)
            ->whereDate('created_at', today())
            ->with([
                'vehicle:id,plate_number',
                'driver:id,name',
                'station:id,name,lat,lng',
                'fuelType:id,name'
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($tx) {
                return [
                    'id'           => $tx->id,
                    'time'         => $tx->created_at->format('h:i A'),
                    'reference'    => $tx->reference_no,
                    'driver'       => $tx->driver ? $tx->driver->name : '-',
                    'vehicle'      => $tx->vehicle ? $tx->vehicle->plate_number : '-',
                    'station'      => $tx->station ? $tx->station->name : '-',
                    'station_lat'  => $tx->station ? $tx->station->lat : null,
                    'station_lng'  => $tx->station ? $tx->station->lng : null,
                    'fuel_type'    => $tx->fuelType ? $tx->fuelType->name : '-',
                    'liters'       => number_format($tx->actual_liters ?? 0, 2),
                    'amount'       => number_format($tx->total_amount ?? 0, 2),
                    'status'       => $tx->status,
                    'has_image'    => !empty($tx->meter_image),
                ];
            });

        $stats = $this->getTodayStats($clientId);

        return response()->json([
            'transactions'  => $transactions,
            'stats'         => $stats,
            'last_updated'  => now()->format('H:i:s'),
        ]);
    }

    public function viewProof($id)
    {
        $clientId = auth()->id();

        $transaction = FuelTransaction::where('client_id', $clientId)
            ->where('id', $id)
            ->with(['vehicle:id,plate_number', 'driver:id,name', 'station:id,name,lat,lng', 'fuelType:id,name'])
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => __('client.live_monitor.transaction_not_found'),
            ], 404);
        }

        $hasImage = !empty($transaction->meter_image);
        $imageUrl = null;

        if ($hasImage) {
            $imageUrl = route('client.live_monitor.image', $transaction->id);
        }

        return response()->json([
            'success'     => true,
            'has_image'   => $hasImage,
            'image_url'   => $imageUrl,
            'transaction' => [
                'id'         => $transaction->id,
                'reference'  => $transaction->reference_no,
                'date'       => $transaction->created_at->format('Y-m-d'),
                'time'       => $transaction->created_at->format('h:i A'),
                'vehicle'    => $transaction->vehicle ? $transaction->vehicle->plate_number : '-',
                'driver'     => $transaction->driver ? $transaction->driver->name : '-',
                'station'    => $transaction->station ? $transaction->station->name : '-',
                'fuel_type'  => $transaction->fuelType ? $transaction->fuelType->name : '-',
                'liters'     => number_format($transaction->actual_liters ?? 0, 2),
                'amount'     => number_format($transaction->total_amount ?? 0, 2),
                'status'     => $transaction->status,
                'map_url'    => $transaction->station && $transaction->station->lat
                    ? "https://www.google.com/maps?q={$transaction->station->lat},{$transaction->station->lng}"
                    : null,
            ],
        ]);
    }

    public function meterImage($id)
    {
        $clientId = auth()->id();

        $transaction = FuelTransaction::where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (!$transaction || empty($transaction->meter_image)) {
            abort(404);
        }

        $path = storage_path('app/' . $transaction->meter_image);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    private function getTodayStats($clientId): array
    {
        $todayQuery = FuelTransaction::where('client_id', $clientId)
            ->whereDate('created_at', today());

        $completedQuery = (clone $todayQuery)->where('status', 'completed');

        return [
            'total_liters'       => round((float) ($completedQuery->sum('actual_liters') ?? 0), 2),
            'active_drivers'     => (clone $todayQuery)->distinct('driver_id')->count('driver_id'),
            'spent_today'        => round((float) ((clone $completedQuery)->sum('total_amount') ?? 0), 2),
            'transactions_count' => (clone $todayQuery)->count(),
        ];
    }
}
