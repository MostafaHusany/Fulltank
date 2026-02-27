<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use LaravelLocalization;

use Yajra\Datatables\Datatables;

use App\Models\Station;
use App\Models\User;
use App\Services\StationService;
use App\Http\Traits\ResponseTemplate;

class StationController extends Controller
{
    use ResponseTemplate;

    public function __construct(protected StationService $stationService) {}

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';

        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['stations_add', 'stations_edit', 'stations_delete', 'stations_show']);

        if ($request->ajax()) {
            $model = Station::query()
                ->with(['governorate', 'district', 'user', 'fuelTypes'])
                ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('governorate_id'), fn ($q) => $q->where('governorate_id', $request->governorate_id))
                ->when($request->filled('district_id'), fn ($q) => $q->where('district_id', $request->district_id))
                ->when($request->filled('manager_name'), fn ($q) => $q->where('manager_name', 'like', '%' . $request->manager_name . '%'))
                ->when($request->filled('phone_1'), fn ($q) => $q->where('phone_1', 'like', '%' . $request->phone_1 . '%'))
                ->when($request->filled('email'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('email', 'like', '%' . $request->email . '%')))
                ->orderBy('id', 'desc');

            $datatable_model = Datatables::of($model)
                ->addColumn('governorate_name', function ($row_object) {
                    return $row_object->governorate ? $row_object->governorate->name : '---';
                })
                ->addColumn('district_name', function ($row_object) {
                    return $row_object->district ? $row_object->district->name : '---';
                })
                ->addColumn('email', function ($row_object) {
                    return $row_object->user ? $row_object->user->email : '---';
                })
                ->addColumn('account_status', function ($row_object) use ($permissions) {
                    return view('admin.stations.incs._account_status', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.stations.incs._actions', compact('row_object', 'permissions'));
                })
                ->rawColumns(['account_status', 'actions']);

            return $datatable_model->make(true);
        }

        return view('admin.stations.index', compact('permissions', 'is_ar'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                 => 'required|string|max:255',
            'governorate_id'       => 'required|exists:governorates,id',
            'district_id'          => 'required|exists:governorate_districts,id',
            'manager_name'         => 'required|string|max:255',
            'bank_account_details' => 'required|string|max:1000',
            'phone_1'              => 'required|string|max:50',
            'email'                => 'required|email|unique:users,email',
            'password'             => 'required|string|min:6|max:50',
            'fuel_type_ids'        => 'required|array',
            'fuel_type_ids.*'      => 'exists:fuel_types,id',
        ], [
            'fuel_type_ids.required' => __('stations.at_least_one_fuel_type'),
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = $request->only([
            'name', 'governorate_id', 'district_id', 'address', 'lat', 'lng',
            'nearby_landmarks', 'manager_name', 'phone_1', 'phone_2', 'bank_account_details',
            'email', 'password'
        ]);
        $data['fuel_type_ids'] = is_array($request->fuel_type_ids) ? $request->fuel_type_ids : array_filter(array_map('intval', explode(',', $request->fuel_type_ids ?? '')));

        try {
            $station = $this->stationService->create($data);
            return $this->responseTemplate($station, true, [__('stations.object_created')]);
        } catch (Exception $e) {
            Log::error('StationController@store', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('stations.object_error')]);
        }
    }

    public function show($id)
    {
        $station = Station::with(['governorate', 'district', 'user', 'fuelTypes'])->find($id);
        if (!$station) {
            return $this->responseTemplate(null, false, [__('stations.object_not_found')]);
        }
        return $this->responseTemplate($station, true, null);
    }

    public function update(Request $request, $id)
    {
        $station = Station::find($id);
        if (!$station) {
            return $this->responseTemplate(null, false, [__('stations.object_not_found')]);
        }

        $rules = [
            'name'                 => 'required|string|max:255',
            'governorate_id'       => 'required|exists:governorates,id',
            'district_id'          => 'required|exists:governorate_districts,id',
            'manager_name'         => 'required|string|max:255',
            'bank_account_details' => 'required|string|max:1000',
            'phone_1'              => 'required|string|max:50',
            'fuel_type_ids'        => 'required|array',
            'fuel_type_ids.*'      => 'exists:fuel_types,id',
        ];
        if ($request->filled('email')) {
            $rules['email'] = 'required|email|unique:users,email,' . ($station->user_id ?? 0);
        }
        if ($request->filled('password')) {
            $rules['password'] = 'nullable|string|min:6|max:50';
        }

        $validator = Validator::make($request->all(), $rules, [
            'fuel_type_ids.required' => __('stations.at_least_one_fuel_type'),
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = $request->only([
            'name', 'governorate_id', 'district_id', 'address', 'lat', 'lng',
            'nearby_landmarks', 'manager_name', 'phone_1', 'phone_2', 'bank_account_details',
            'email', 'password'
        ]);
        $data['fuel_type_ids'] = is_array($request->fuel_type_ids) ? $request->fuel_type_ids : array_filter(array_map('intval', explode(',', $request->fuel_type_ids ?? '')));

        try {
            $station = $this->stationService->update($station, $data);
            return $this->responseTemplate($station, true, [__('stations.object_updated')]);
        } catch (Exception $e) {
            Log::error('StationController@update', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('stations.object_error')]);
        }
    }

    public function destroy($id)
    {
        $station = Station::find($id);
        if (!$station) {
            return $this->responseTemplate(null, false, [__('stations.object_not_found')]);
        }
        try {
            $station->delete();
            return $this->responseTemplate(null, true, [__('stations.object_deleted')]);
        } catch (Exception $e) {
            Log::error('StationController@destroy', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('stations.object_error')]);
        }
    }

    public function toggleAccountStatus($id)
    {
        $station = Station::with('user')->find($id);
        if (!$station || !$station->user_id) {
            return $this->responseTemplate(null, false, [__('stations.object_not_found')]);
        }
        $user = $station->user;
        $user->is_active = !$user->is_active;
        $user->save();
        return $this->responseTemplate($user->fresh(), true, [__('stations.status_updated')]);
    }

    public function dataAjax(Request $request)
    {
        $query = Station::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }

        return $query->orderBy('name')->limit(30)->get(['id', 'name', 'manager_name']);
    }
}
