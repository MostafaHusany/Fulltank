<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

use App\Models\Transaction;
use App\Models\FuelTransaction;
use App\Models\FuelType;
use App\Models\Vehicle;
use App\Models\User;
use App\Http\Traits\ResponseTemplate;

class WalletController extends Controller
{
    use ResponseTemplate;

    public function index(Request $request)
    {
        $user     = auth()->user();
        $clientId = $user->id;
        $wallet   = $user->wallet;

        if (!$wallet) {
            return view('clients.wallet.index', [
                'wallet'            => null,
                'balance'           => 0,
                'monthlySpent'      => 0,
                'totalTransactions' => 0,
                'avgFillUp'         => 0,
                'lastTopUp'         => null,
                'vehicles'          => collect([]),
                'drivers'           => collect([]),
                'fuelTypes'         => collect([]),
            ]);
        }

        $balance = $wallet->balance;

        $monthlySpent = FuelTransaction::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $totalTransactions = FuelTransaction::where('client_id', $clientId)
            ->where('status', 'completed')
            ->count();

        $avgFillUp = FuelTransaction::where('client_id', $clientId)
            ->where('status', 'completed')
            ->avg('total_amount');

        $lastTopUp = Transaction::where('wallet_id', $wallet->id)
            ->where('type', 'deposit')
            ->where('amount', '>', 0)
            ->orderByDesc('created_at')
            ->first();

        $vehicles = Vehicle::where('client_id', $clientId)
            ->where('status', 'active')
            ->orderBy('plate_number')
            ->get(['id', 'plate_number']);

        $drivers = User::where('client_id', $clientId)
            ->where('category', 'driver')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $fuelTypes = FuelType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('clients.wallet.index', compact(
            'wallet',
            'balance',
            'monthlySpent',
            'totalTransactions',
            'avgFillUp',
            'lastTopUp',
            'vehicles',
            'drivers',
            'fuelTypes'
        ));
    }

    public function chartData(Request $request)
    {
        $clientId = auth()->id();

        $query = FuelTransaction::where('client_id', $clientId)
            ->where('status', 'completed');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('fuel_type_id')) {
            $query->where('fuel_type_id', $request->fuel_type_id);
        }

        $dailyData = (clone $query)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(actual_liters) as liters')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $topVehicles = (clone $query)
            ->select(
                'vehicle_id',
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(actual_liters) as liters')
            )
            ->with('vehicle:id,plate_number')
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'vehicle'  => $item->vehicle ? $item->vehicle->plate_number : __('client.wallet.unknown'),
                    'total'    => (float) $item->total,
                    'liters'   => (float) $item->liters,
                ];
            });

        $fuelDistribution = (clone $query)
            ->select(
                'fuel_type_id',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('SUM(actual_liters) as liters')
            )
            ->with('fuelType:id,name')
            ->groupBy('fuel_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'fuel_type' => $item->fuelType ? $item->fuelType->name : __('client.wallet.unknown'),
                    'count'     => (int) $item->count,
                    'total'     => (float) $item->total,
                    'liters'    => (float) $item->liters,
                ];
            });

        $transactionsCount = (int) (clone $query)->count();
        $totalAmount = (float) ((clone $query)->sum('total_amount') ?? 0);

        $summary = [
            'total_amount'       => $totalAmount,
            'total_liters'       => (float) ((clone $query)->sum('actual_liters') ?? 0),
            'transactions_count' => $transactionsCount,
            'avg_fillup'         => $transactionsCount > 0 ? round($totalAmount / $transactionsCount, 2) : 0,
        ];

        return response()->json([
            'daily'        => $dailyData,
            'top_vehicles' => $topVehicles,
            'fuel_dist'    => $fuelDistribution,
            'summary'      => $summary,
        ]);
    }

    public function transactions(Request $request)
    {
        $user   = auth()->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return DataTables::of(collect([]))->make(true);
        }

        $query = Transaction::where('wallet_id', $wallet->id)
            ->with(['creator:id,name'])
            ->select('transactions.*');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('type_badge', function ($row) {
                $colors = [
                    'deposit'    => 'success',
                    'withdrawal' => 'danger',
                    'transfer'   => 'info',
                ];
                $color = $colors[$row->type] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . __('client.wallet.' . $row->type) . '</span>';
            })
            ->addColumn('amount_display', function ($row) {
                $isPositive = $row->type === 'deposit';
                $sign = $isPositive ? '+' : '-';
                $color = $isPositive ? 'text-success' : 'text-danger';
                return '<span class="' . $color . ' fw-bold">' . $sign . number_format(abs($row->amount), 2) . '</span>';
            })
            ->addColumn('creator_name', function ($row) {
                return $row->creator->name ?? '-';
            })
            ->addColumn('formatted_date', function ($row) {
                return $row->created_at->format('Y-m-d H:i');
            })
            ->rawColumns(['type_badge', 'amount_display'])
            ->make(true);
    }

    public function fuelTransactions(Request $request)
    {
        $clientId = auth()->id();

        $query = FuelTransaction::where('client_id', $clientId)
            ->with([
                'vehicle:id,plate_number',
                'driver:id,name',
                'station:id,name',
                'fuelType:id,name'
            ])
            ->select('fuel_transactions.*');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('fuel_type_id')) {
            $query->where('fuel_type_id', $request->fuel_type_id);
        }

        $query->orderByDesc('created_at');

        return DataTables::of($query)
            ->addColumn('formatted_date', function ($row) {
                return $row->created_at->format('Y-m-d H:i');
            })
            ->addColumn('vehicle_plate', function ($row) {
                return $row->vehicle ? $row->vehicle->plate_number : '-';
            })
            ->addColumn('driver_name', function ($row) {
                return $row->driver ? $row->driver->name : '-';
            })
            ->addColumn('station_name', function ($row) {
                return $row->station ? $row->station->name : '-';
            })
            ->addColumn('fuel_type_name', function ($row) {
                return $row->fuelType ? $row->fuelType->name : '-';
            })
            ->addColumn('liters_display', function ($row) {
                return number_format($row->actual_liters ?? 0, 2);
            })
            ->addColumn('price_display', function ($row) {
                return number_format($row->price_per_liter ?? 0, 2);
            })
            ->addColumn('total_display', function ($row) {
                return '<span class="text-danger fw-bold">-' . number_format($row->total_amount ?? 0, 2) . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $colors = [
                    'pending'   => 'warning',
                    'completed' => 'success',
                    'refunded'  => 'info',
                    'cancelled' => 'secondary',
                ];
                $color = $colors[$row->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . __('client.wallet.status_' . $row->status) . '</span>';
            })
            ->rawColumns(['total_display', 'status_badge'])
            ->make(true);
    }

    public function exportFuelTransactions(Request $request)
    {
        $clientId = auth()->id();

        $query = FuelTransaction::where('client_id', $clientId)
            ->with([
                'vehicle:id,plate_number',
                'driver:id,name',
                'station:id,name',
                'fuelType:id,name'
            ]);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->date_from));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->date_to));
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('fuel_type_id')) {
            $query->where('fuel_type_id', $request->fuel_type_id);
        }

        $transactions = $query->orderByDesc('created_at')->get();

        $filename = 'fuel_transactions_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                __('client.wallet.date'),
                __('client.wallet.reference'),
                __('client.wallet.vehicle'),
                __('client.wallet.driver'),
                __('client.wallet.station'),
                __('client.wallet.fuel_type'),
                __('client.wallet.liters'),
                __('client.wallet.unit_price'),
                __('client.wallet.total_amount'),
                __('client.wallet.status'),
            ]);

            foreach ($transactions as $row) {
                fputcsv($file, [
                    $row->created_at->format('Y-m-d H:i'),
                    $row->reference_no,
                    $row->vehicle ? $row->vehicle->plate_number : '-',
                    $row->driver ? $row->driver->name : '-',
                    $row->station ? $row->station->name : '-',
                    $row->fuelType ? $row->fuelType->name : '-',
                    number_format($row->actual_liters ?? 0, 2),
                    number_format($row->price_per_liter ?? 0, 2),
                    number_format($row->total_amount ?? 0, 2),
                    __('client.wallet.status_' . $row->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
