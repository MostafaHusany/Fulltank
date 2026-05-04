<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Vehicle;
use App\Models\FuelType;
use App\Services\Client\VehicleService;
use App\Services\VehicleTracking\VehicleTrackingResponseService;
use App\Http\Traits\ResponseTemplate;

class VehicleController extends Controller
{
    use ResponseTemplate;

    private $targetModel;
    private $vehicleService;

    public function __construct(
        VehicleService $vehicleService,
        private VehicleTrackingResponseService $vehicleTrackingResponse
    ) {
        $this->targetModel    = new Vehicle;
        $this->vehicleService = $vehicleService;
    }

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';
        $clientId = auth()->id();

        if ($request->ajax()) {
            $query = $this->targetModel
                ->where('client_id', $clientId)
                ->with(['quota:id,vehicle_id,amount_limit,consumed_amount', 'fuelType:id,name'])
                ->select('vehicles.*');

            return DataTables::of($query)
                ->addColumn('checkbox_selector', function ($row) {
                    return view('layouts.admin.incs._checkbox_selector', ['row_object' => $row]);
                })
                ->addColumn('fuel_type_name', function ($row) {
                    return $row->fuelType ? e($row->fuelType->name) : '---';
                })
                ->addColumn('quota_info', function ($row) {
                    if ($row->quota) {
                        $remaining = $row->quota->amount_limit - $row->quota->consumed_amount;
                        return number_format($remaining, 2) . ' / ' . number_format($row->quota->amount_limit, 2);
                    }
                    return __('client.vehicles.no_quota');
                })
                ->addColumn('activation', function ($row) {
                    return view('clients.vehicles.incs._activation', ['row_object' => $row]);
                })
                ->addColumn('actions', function ($row) {
                    return view('clients.vehicles.incs._actions', ['row_object' => $row]);
                })
                ->rawColumns(['checkbox_selector', 'activation', 'actions'])
                ->make(true);
        }

        $fuelTypes = FuelType::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('clients.vehicles.index', compact('is_ar', 'fuelTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $clientId = auth()->id();

        try {
            $data = $request->only(['plate_number', 'model', 'fuel_type_id', 'monthly_quota']);
            $vehicle = $this->vehicleService->create($data, $clientId);
            $vehicle->load(['quota', 'fuelType']);

            return $this->responseTemplate($vehicle, true, __('client.vehicles.created'));
        } catch (Exception $e) {
            Log::error('Client\VehicleController@store Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, __('client.vehicles.error'));
        }
    }

    public function show($id)
    {
        $clientId = auth()->id();

        $vehicle = $this->targetModel
            ->where('client_id', $clientId)
            ->with(['quota', 'fuelType:id,name', 'fuelTransactions' => function ($q) {
                $q->orderByDesc('created_at')->limit(10);
            }])
            ->find($id);

        if (!$vehicle) {
            return $this->responseTemplate(null, false, __('client.vehicles.not_found'));
        }

        $data = $vehicle->toArray();
        $data['formatted_plate_number'] = $vehicle->formatted_plate_number;
        $data['fuel_type_name'] = $vehicle->fuelType?->name;
        if ($vehicle->quota) {
            $remaining = (float) $vehicle->quota->amount_limit - (float) $vehicle->quota->consumed_amount;
            $data['quota_display'] = number_format(max(0, $remaining), 2) . ' / ' . number_format((float) $vehicle->quota->amount_limit, 2);
        } else {
            $data['quota_display'] = __('client.vehicles.no_quota');
        }

        return $this->responseTemplate($data, true);
    }

    /**
     * Live positions for the authenticated client's fleet only.
     */
    public function trackingLive(Request $request)
    {
        $clientId = auth()->id();

        $rows = Vehicle::query()
            ->where('client_id', $clientId)
            ->with(['latestLocation'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return $this->responseTemplate($this->vehicleTrackingResponse->liveMapRows($rows), true, null);
    }

    /**
     * Daily routes + fuel visits for one vehicle owned by the client.
     */
    public function trackingHistory(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->client_id === auth()->id(), 404);

        return $this->responseTemplate($this->vehicleTrackingResponse->historyPayload($vehicle), true, null);
    }

    public function update(Request $request, $id)
    {
        $clientId = auth()->id();

        $vehicle = $this->targetModel
            ->where('client_id', $clientId)
            ->find($id);

        if (!$vehicle) {
            return $this->responseTemplate(null, false, __('client.vehicles.not_found'));
        }

        if ($request->has('toggle_status')) {
            return $this->toggleStatus($vehicle);
        }

        $rules = $this->getValidationRules($id);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $data = $request->only(['plate_number', 'model', 'fuel_type_id', 'monthly_quota']);
            $vehicle = $this->vehicleService->update($id, $data, $clientId);
            $vehicle->load(['quota', 'fuelType']);

            return $this->responseTemplate($vehicle, true, __('client.vehicles.updated'));
        } catch (Exception $e) {
            Log::error('Client\VehicleController@update Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, __('client.vehicles.error'));
        }
    }

    public function destroy($id)
    {
        $clientId = auth()->id();

        try {
            $this->vehicleService->delete($id, $clientId);
            return $this->responseTemplate(null, true, __('client.vehicles.deleted'));
        } catch (Exception $e) {
            Log::error('Client\VehicleController@destroy Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, __('client.vehicles.error'));
        }
    }

    protected function toggleStatus(Vehicle $vehicle)
    {
        try {
            $vehicle->status = $vehicle->status === 'active' ? 'inactive' : 'active';
            $vehicle->save();
            return $this->responseTemplate($vehicle, true, __('client.vehicles.status_updated'));
        } catch (Exception $e) {
            Log::error('Client\VehicleController@toggleStatus Exception', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, __('client.vehicles.error'));
        }
    }

    protected function getValidationRules($id = null): array
    {
        $uniqueRule = $id
            ? 'required|string|max:50|unique:vehicles,plate_number,' . $id
            : 'required|string|max:50|unique:vehicles,plate_number';

        return [
            'plate_number'  => $uniqueRule,
            'fuel_type_id'  => 'required|exists:fuel_types,id',
            'model'         => 'nullable|string|max:100',
            'monthly_quota' => 'required|numeric|min:0',
        ];
    }
}
