<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\Vehicle;
use App\Models\User;

use App\Http\Traits\ResponseTemplate;

class VehicleController extends Controller
{
    use ResponseTemplate;

    private $targetModel;

    public function __construct()
    {
        $this->targetModel = new Vehicle;
    }

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['vehicles_add', 'vehicles_edit', 'vehicles_delete', 'vehicles_show']);

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->with('client:id,name,company_name')
                ->orderBy('id', 'desc')
                ->adminFilter();

            $datatable_model = Datatables::of($model)
                ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                    return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
                })
                ->addColumn('client_name', function ($row_object) {
                    return $row_object->client ? e($row_object->client->company_name ?: $row_object->client->name) : '---';
                })
                ->addColumn('formatted_plate', function ($row_object) {
                    return e($row_object->formatted_plate_number);
                })
                ->addColumn('activation', function ($row_object) use ($permissions) {
                    return view('admin.vehicles.incs._active', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.vehicles.incs._actions', compact('row_object', 'permissions'));
                });

            return $datatable_model->make(true);
        }

        return view('admin.vehicles.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id'    => 'required|exists:users,id',
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number',
            'model'        => 'required|string|max:255',
            'fuel_type'    => 'required|in:petrol,diesel,electric,hybrid,cng',
            'status'       => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = $request->only(['client_id', 'plate_number', 'model', 'fuel_type']);
        $data['status'] = $request->input('status', 'active');

        try {
            DB::beginTransaction();

            $vehicle = $this->targetModel->create($data);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VehicleController@store Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('vehicles.object_error')]);
        }

        return $this->responseTemplate($vehicle, true, __('vehicles.object_created'));
    }

    public function show($id)
    {
        $vehicle = $this->targetModel->query()
            ->with('client:id,name,company_name,email,phone')
            ->find($id);

        if (!isset($vehicle)) {
            return $this->responseTemplate(null, false, __('vehicles.object_not_found'));
        }

        $data = $vehicle->toArray();
        $data['formatted_plate_number'] = $vehicle->formatted_plate_number;
        $data['client_name'] = $vehicle->client ? ($vehicle->client->company_name ?: $vehicle->client->name) : '---';
        return $this->responseTemplate($data, true, null);
    }

    public function update(Request $request, $id)
    {
        $vehicle = $this->targetModel->query()->find($id);

        if (!isset($vehicle)) {
            return $this->responseTemplate(null, false, __('vehicles.object_not_found'));
        }

        return isset($request->activate_object)
            ? $this->activateVehicle($vehicle)
            : $this->updateVehicle($request, $vehicle);
    }

    public function destroy(Request $request, $id)
    {
        return $id == 0 && isset($request->selected_ids)
            ? $this->bulkDelete($request)
            : $this->delete($id);
    }

    private function updateVehicle(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'client_id'    => 'required|exists:users,id',
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number,' . $vehicle->id,
            'model'        => 'required|string|max:255',
            'fuel_type'    => 'required|in:petrol,diesel,electric,hybrid,cng',
            'status'       => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = $request->only(['client_id', 'plate_number', 'model', 'fuel_type']);
        if ($request->has('status')) {
            $data['status'] = $request->input('status');
        }

        try {
            DB::beginTransaction();

            $vehicle->update($data);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('VehicleController@update Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('vehicles.object_error')]);
        }

        return $this->responseTemplate($vehicle->fresh(), true, __('vehicles.object_updated'));
    }

    private function activateVehicle(Vehicle $vehicle)
    {
        $vehicle->status = $vehicle->status === 'active' ? 'inactive' : 'active';
        $vehicle->save();

        return $this->responseTemplate($vehicle, true, __('vehicles.object_updated'));
    }

    private function bulkDelete(Request $request)
    {
        $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
        $this->targetModel->query()->whereIn('id', $ids)->delete();

        return $this->responseTemplate(null, true, __('vehicles.object_deleted'));
    }

    private function delete($id)
    {
        $vehicle = $this->targetModel->query()->find($id);

        if (!isset($vehicle)) {
            return $this->responseTemplate(null, false, __('vehicles.object_not_found'));
        }

        $vehicle->delete();

        return $this->responseTemplate($vehicle, true, __('vehicles.object_deleted'));
    }
}
