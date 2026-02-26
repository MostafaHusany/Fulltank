<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Vehicle;

use App\Services\UserService;

use App\Http\Traits\ResponseTemplate;

class DriverController extends Controller
{
    use ResponseTemplate;

    private $targetModel;
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->targetModel = new User;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['drivers_add', 'drivers_edit', 'drivers_delete', 'drivers_show']);

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->where('category', 'driver')
                ->with(['client:id,name,company_name', 'vehicle:id,plate_number,client_id'])
                ->when($request->filled('client_id'), function ($q) use ($request) {
                    $q->where('client_id', $request->client_id);
                })
                ->orderBy('id', 'desc')
                ->adminFilter();

            $datatable_model = Datatables::of($model)
                ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                    return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
                })
                ->addColumn('client_name', function ($row_object) {
                    return $row_object->client ? e($row_object->client->company_name ?: $row_object->client->name) : '---';
                })
                ->addColumn('vehicle_display', function ($row_object) {
                    if (!$row_object->vehicle) {
                        return '---';
                    }
                    return e($row_object->vehicle->formatted_plate_number ?? $row_object->vehicle->plate_number);
                })
                ->addColumn('activation', function ($row_object) use ($permissions) {
                    return view('admin.drivers.incs._active', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.drivers.incs._actions', compact('row_object', 'permissions'));
                });

            return $datatable_model->make(true);
        }

        $filterClientId   = $request->get('client_id');
        $filterClientName = $request->get('client_name');

        return view('admin.drivers.index', compact('permissions', 'filterClientId', 'filterClientName'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'phone'      => 'required|max:255|unique:users,phone',
            'password'   => 'required|min:8',
            'client_id'  => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'picture'    => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $client = $this->targetModel->query()->where('category', 'client')->find($request->client_id);
        if (!$client) {
            return $this->responseTemplate(null, false, ['client_id' => [__('drivers.client_must_be_owner')]]);
        }

        if ($request->filled('vehicle_id')) {
            $vehicle = Vehicle::where('id', $request->vehicle_id)->where('client_id', $request->client_id)->first();
            if (!$vehicle) {
                return $this->responseTemplate(null, false, ['vehicle_id' => [__('drivers.vehicle_must_belong_to_client')]]);
            }
        }

        $data = $request->only(['name', 'email', 'phone', 'password', 'client_id', 'vehicle_id']);

        try {
            DB::beginTransaction();

            $picture_path = $this->userService->handleClientPicture($request);
            if ($picture_path) {
                $data['picture'] = $picture_path;
            }

            $user = $this->userService->createDriver($data);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('DriverController@store Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('drivers.object_error')]);
        }

        return $this->responseTemplate($user, true, __('drivers.object_created'));
    }

    public function show($id)
    {
        $user = $this->targetModel->query()
            ->where('category', 'driver')
            ->with(['client:id,name,company_name', 'vehicle:id,plate_number,client_id'])
            ->find($id);

        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('drivers.object_not_found'));
        }

        $data = $user->toArray();
        $data['client_name'] = $user->client ? ($user->client->company_name ?: $user->client->name) : null;
        $data['vehicle_display'] = $user->vehicle ? ($user->vehicle->formatted_plate_number ?? $user->vehicle->plate_number) : null;
        return $this->responseTemplate($data, true, null);
    }

    public function update(Request $request, $id)
    {
        $user = $this->targetModel->query()->where('category', 'driver')->find($id);

        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('drivers.object_not_found'));
        }

        return isset($request->activate_object)
            ? $this->activateDriver($user)
            : $this->updateDriver($request, $user);
    }

    public function destroy(Request $request, $id)
    {
        return $id == 0 && isset($request->selected_ids)
            ? $this->bulkDelete($request)
            : $this->delete($id);
    }

    /**
     * AJAX: vehicles belonging to a client (for Select2 when creating/editing driver).
     */
    public function vehiclesByClient(Request $request)
    {
        $clientId = $request->get('client_id');
        $q = $request->get('q');

        if (!$clientId) {
            return response()->json([]);
        }

        $query = Vehicle::query()
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->orderBy('plate_number');

        if ($q) {
            $query->where('plate_number', 'like', '%' . $q . '%');
        }

        $vehicles = $query->get(['id', 'plate_number']);
        return response()->json($vehicles->map(function ($v) {
            return ['id' => $v->id, 'text' => $v->formatted_plate_number ?? $v->plate_number];
        }));
    }

    private function updateDriver(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'      => 'required|max:255|unique:users,phone,' . $user->id,
            'password'   => 'nullable|min:8',
            'client_id'  => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'picture'    => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $client = $this->targetModel->query()->where('category', 'client')->find($request->client_id);
        if (!$client) {
            return $this->responseTemplate(null, false, ['client_id' => [__('drivers.client_must_be_owner')]]);
        }

        if ($request->filled('vehicle_id')) {
            $vehicle = Vehicle::where('id', $request->vehicle_id)->where('client_id', $request->client_id)->first();
            if (!$vehicle) {
                return $this->responseTemplate(null, false, ['vehicle_id' => [__('drivers.vehicle_must_belong_to_client')]]);
            }
        }

        $data = $request->only(['name', 'email', 'phone', 'client_id', 'vehicle_id']);
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        try {
            DB::beginTransaction();

            $picture_path = $this->userService->handleClientPicture($request);
            if ($picture_path) {
                $data['picture'] = $picture_path;
            }

            $this->userService->updateDriver($user, $data);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('DriverController@update Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('drivers.object_error')]);
        }

        return $this->responseTemplate($user->fresh(), true, __('drivers.object_updated'));
    }

    private function activateDriver(User $user)
    {
        try {
            DB::beginTransaction();
            $user->is_active = !$user->is_active;
            $user->save();
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->responseTemplate(null, false, [__('drivers.object_error')]);
        }
        return $this->responseTemplate($user, true, __('drivers.object_updated'));
    }

    private function bulkDelete(Request $request)
    {
        $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
        $this->targetModel->query()
            ->where('category', 'driver')
            ->whereIn('id', $ids)
            ->delete();
        return $this->responseTemplate(null, true, __('drivers.object_deleted'));
    }

    private function delete($id)
    {
        $user = $this->targetModel->query()->where('category', 'driver')->find($id);
        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('drivers.object_not_found'));
        }
        $user->delete();
        return $this->responseTemplate($user, true, __('drivers.object_deleted'));
    }
}
