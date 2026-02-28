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

use App\Models\User;
use App\Models\Vehicle;
use App\Services\Client\DriverService;
use App\Http\Traits\ResponseTemplate;

class DriverController extends Controller
{
    use ResponseTemplate;

    private $targetModel;
    private $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->targetModel   = new User();
        $this->driverService = $driverService;
    }

    public function index(Request $request)
    {
        $is_ar    = LaravelLocalization::getCurrentLocale() == 'ar';
        $clientId = auth()->id();

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->where('category', 'driver')
                ->where('client_id', $clientId)
                ->with(['vehicle:id,plate_number'])
                ->select('users.*')
                ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('phone'), fn ($q) => $q->where('phone', 'like', '%' . $request->phone . '%'))
                ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->vehicle_id))
                ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->is_active))
                ->orderBy('id', 'desc');

            $datatable_model = DataTables::of($model)
                ->addColumn('checkbox_selector', function ($row_object) {
                    return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
                })
                ->addColumn('vehicle_plate', function ($row_object) {
                    return $row_object->vehicle->plate_number ?? '---';
                })
                ->addColumn('activation', function ($row_object) {
                    return view('clients.drivers.incs._activation', compact('row_object'));
                })
                ->addColumn('actions', function ($row_object) use ($is_ar) {
                    return view('clients.drivers.incs._actions', compact('row_object', 'is_ar'));
                });

            return $datatable_model->make(true);
        }

        $vehicles = Vehicle::where('client_id', $clientId)
            ->where('status', 'active')
            ->orderBy('plate_number')
            ->get(['id', 'plate_number']);

        return view('clients.drivers.index', compact('vehicles', 'is_ar'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $clientId = auth()->id();

        if (!empty($request->vehicle_id)) {
            $vehicleBelongsToClient = Vehicle::where('id', $request->vehicle_id)
                ->where('client_id', $clientId)
                ->exists();

            if (!$vehicleBelongsToClient) {
                return $this->responseTemplate(null, false, [__('client.drivers.vehicle_not_owned')]);
            }
        }

        try {
            DB::beginTransaction();

            $data   = $request->only(['name', 'phone', 'national_id', 'password', 'vehicle_id']);
            $driver = $this->driverService->create($data, $clientId);
            $driver->load(['vehicle']);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('Client\DriverController@store Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('client.drivers.error')]);
        }

        return $this->responseTemplate($driver, true, [__('client.drivers.created')]);
    }

    public function show($id)
    {
        $clientId = auth()->id();

        $driver = $this->targetModel
            ->where('category', 'driver')
            ->where('client_id', $clientId)
            ->with(['vehicle'])
            ->find($id);

        if (!$driver) {
            return $this->responseTemplate(null, false, __('client.drivers.not_found'));
        }

        return $this->responseTemplate($driver, true);
    }

    public function update(Request $request, $id)
    {
        $clientId = auth()->id();

        $driver = $this->targetModel
            ->where('category', 'driver')
            ->where('client_id', $clientId)
            ->find($id);

        if (!$driver) {
            return $this->responseTemplate(null, false, __('client.drivers.not_found'));
        }

        if (isset($request->activate_object)) {
            return $this->activateDriver($driver);
        }

        return $this->updateDriver($request, $driver);
    }

    public function destroy(Request $request, $id)
    {
        return $id == 0 && isset($request->selected_ids)
            ? $this->bulkDelete($request)
            : $this->delete($id);
    }

    private function updateDriver(Request $request, User $driver)
    {
        $rules     = $this->getValidationRules($driver->id);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $clientId = auth()->id();

        if (!empty($request->vehicle_id)) {
            $vehicleBelongsToClient = Vehicle::where('id', $request->vehicle_id)
                ->where('client_id', $clientId)
                ->exists();

            if (!$vehicleBelongsToClient) {
                return $this->responseTemplate(null, false, [__('client.drivers.vehicle_not_owned')]);
            }
        }

        try {
            DB::beginTransaction();

            $data   = $request->only(['name', 'phone', 'national_id', 'password', 'vehicle_id']);
            $driver = $this->driverService->update($driver->id, $data, $clientId);
            $driver->load(['vehicle']);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('Client\DriverController@update Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('client.drivers.error')]);
        }

        return $this->responseTemplate($driver, true, [__('client.drivers.updated')]);
    }

    private function activateDriver(User $driver)
    {
        try {
            DB::beginTransaction();

            $driver->is_active = !$driver->is_active;
            $driver->save();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();

            Log::error('Client\DriverController@activateDriver Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('client.drivers.error')]);
        }

        return $this->responseTemplate($driver, true, __('client.drivers.status_updated'));
    }

    private function bulkDelete(Request $request)
    {
        $clientId = auth()->id();

        $this->targetModel
            ->where('category', 'driver')
            ->where('client_id', $clientId)
            ->whereIn('id', is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids))
            ->delete();

        return $this->responseTemplate(null, true, __('client.drivers.deleted'));
    }

    private function delete($id)
    {
        $clientId = auth()->id();

        $driver = $this->targetModel
            ->where('category', 'driver')
            ->where('client_id', $clientId)
            ->find($id);

        if (!$driver) {
            return $this->responseTemplate(null, false, __('client.drivers.not_found'));
        }

        try {
            $this->driverService->delete($id, $clientId);
        } catch (Exception $exception) {
            Log::error('Client\DriverController@delete Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('client.drivers.error')]);
        }

        return $this->responseTemplate($driver, true, __('client.drivers.deleted'));
    }

    private function getValidationRules($id = null): array
    {
        $phoneRule = $id
            ? 'required|string|max:20|unique:users,phone,' . $id
            : 'required|string|max:20|unique:users,phone';

        $nationalIdRule = $id
            ? 'nullable|string|max:20|unique:users,national_id,' . $id
            : 'nullable|string|max:20|unique:users,national_id';

        $passwordRule = $id
            ? 'nullable|string|min:6'
            : 'required|string|min:6';

        return [
            'name'        => 'required|string|max:100',
            'phone'       => $phoneRule,
            'national_id' => $nationalIdRule,
            'password'    => $passwordRule,
            'vehicle_id'  => 'nullable|exists:vehicles,id',
        ];
    }
}
