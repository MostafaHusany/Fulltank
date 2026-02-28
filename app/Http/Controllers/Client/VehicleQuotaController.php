<?php

namespace App\Http\Controllers\Client;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use App\Models\Vehicle;
use App\Models\FuelType;
use App\Services\QuotaService;
use App\Http\Traits\ResponseTemplate;

class VehicleQuotaController extends Controller
{
    use ResponseTemplate;

    public function __construct(
        protected QuotaService $quotaService
    ) {}

    public function index()
    {
        $clientId = auth()->id();

        $fuelTypes = FuelType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $vehiclesList = Vehicle::where('client_id', $clientId)
            ->orderBy('plate_number')
            ->get(['id', 'plate_number']);

        return view('clients.quotas.index', compact('fuelTypes', 'vehiclesList'));
    }

    /**
     * AJAX: Load client's vehicles with their quotas.
     */
    public function vehicles(Request $request)
    {
        $clientId = auth()->id();

        $query = Vehicle::where('client_id', $clientId)
            ->with(['activeQuota', 'fuelType:id,name', 'drivers:id,name,vehicle_id']);

        if ($request->filled('vehicle_id')) {
            $query->where('id', $request->vehicle_id);
        }
        if ($request->filled('fuel_type_id')) {
            $query->where('fuel_type_id', $request->fuel_type_id);
        }
        if ($request->filled('plate_number')) {
            $query->where('plate_number', 'like', '%' . $request->plate_number . '%');
        }

        $vehicles = $query->orderBy('plate_number')->get();

        $data = $vehicles->map(function ($v) {
            $q = $v->activeQuota;
            $driverNames = $v->drivers->pluck('name')->toArray();

            return [
                'id'              => $v->id,
                'plate_number'    => $v->formatted_plate_number ?? $v->plate_number,
                'model'           => $v->model ?? '—',
                'fuel_type'       => $v->fuelType ? $v->fuelType->name : '—',
                'drivers'         => $driverNames,
                'drivers_count'   => count($driverNames),
                'amount_limit'    => $q ? (float) $q->amount_limit : 0,
                'consumed_amount' => $q ? (float) $q->consumed_amount : 0,
                'remaining'       => $q ? (float) $q->remaining_amount : 0,
                'reset_cycle'     => $q ? $q->reset_cycle : 'one_time',
                'is_active'       => $q ? $q->is_active : false,
                'quota_id'        => $q ? $q->id : null,
            ];
        });

        return $this->responseTemplate($data->values()->all(), true, null);
    }

    /**
     * Single vehicle quota update.
     */
    public function update(Request $request, int $id)
    {
        $clientId = auth()->id();

        $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'           => 'required|exists:vehicles,id',
            'amount_limit' => 'required|numeric|min:0',
            'reset_cycle'  => 'required|in:daily,weekly,monthly,one_time',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $vehicle = Vehicle::where('client_id', $clientId)->find($id);
        if (!$vehicle) {
            return $this->responseTemplate(null, false, [__('client.quotas.vehicle_not_found')]);
        }

        try {
            $quota = $this->quotaService->upsertQuota(
                $id,
                $clientId,
                (float) $request->amount_limit,
                $request->reset_cycle
            );

            $data = [
                'quota_id'        => $quota->id,
                'amount_limit'    => (float) $quota->amount_limit,
                'consumed_amount' => (float) $quota->consumed_amount,
                'remaining'       => (float) $quota->remaining_amount,
                'reset_cycle'     => $quota->reset_cycle,
            ];

            return $this->responseTemplate($data, true, __('client.quotas.updated'));
        } catch (Exception $e) {
            Log::error('Client\VehicleQuotaController@update', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }

    /**
     * Bulk allocate quotas to multiple vehicles.
     */
    public function bulkAllocate(Request $request)
    {
        $clientId = auth()->id();

        $validator = Validator::make($request->all(), [
            'vehicle_ids'   => 'required|array',
            'vehicle_ids.*' => 'integer|exists:vehicles,id',
            'amount_limit'  => 'required|numeric|min:0',
            'reset_cycle'   => 'required|in:daily,weekly,monthly,one_time',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $vehicleIds = array_map('intval', (array) $request->vehicle_ids);

        try {
            $count = $this->quotaService->bulkAllocate(
                $clientId,
                $vehicleIds,
                (float) $request->amount_limit,
                $request->reset_cycle
            );

            return $this->responseTemplate(['count' => $count], true, __('client.quotas.bulk_updated', ['count' => $count]));
        } catch (Exception $e) {
            Log::error('Client\VehicleQuotaController@bulkAllocate', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [$e->getMessage()]);
        }
    }
}
