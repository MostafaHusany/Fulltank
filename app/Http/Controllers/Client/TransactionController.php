<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\DataTables\DataTables;

use App\Models\FuelTransaction;
use App\Http\Traits\ResponseTemplate;

class TransactionController extends Controller
{
    use ResponseTemplate;

    public function index(Request $request)
    {
        $clientId = auth()->id();

        if ($request->ajax()) {
            $query = FuelTransaction::where('client_id', $clientId)
                ->with([
                    'vehicle:id,plate_number,model',
                    'station:id,name',
                    'fuelType:id,name',
                    'driver:id,name',
                ])
                ->orderByDesc('created_at')
                ->select('fuel_transactions.*');

            if ($request->filled('vehicle_id')) {
                $query->where('vehicle_id', $request->vehicle_id);
            }

            if ($request->filled('station_id')) {
                $query->where('station_id', $request->station_id);
            }

            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->addColumn('vehicle_plate', function ($row) {
                    return $row->vehicle->plate_number ?? '-';
                })
                ->addColumn('station_name', function ($row) {
                    return $row->station->name ?? '-';
                })
                ->addColumn('fuel_type_name', function ($row) {
                    return $row->fuelType->name ?? '-';
                })
                ->addColumn('driver_name', function ($row) {
                    return $row->driver->name ?? '-';
                })
                ->addColumn('status_badge', function ($row) {
                    $colors = [
                        'pending'   => 'warning',
                        'completed' => 'success',
                        'refunded'  => 'info',
                        'cancelled' => 'secondary',
                    ];
                    $color = $colors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . __('client.transactions.' . $row->status) . '</span>';
                })
                ->addColumn('formatted_date', function ($row) {
                    return $row->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['status_badge'])
                ->make(true);
        }

        return view('clients.transactions.index');
    }

    public function show($id)
    {
        $clientId = auth()->id();

        $transaction = FuelTransaction::where('client_id', $clientId)
            ->with(['vehicle', 'station', 'fuelType', 'driver', 'worker'])
            ->findOrFail($id);

        return $this->responseTemplate($transaction, true, null);
    }
}
