<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use LaravelLocalization;

use Yajra\Datatables\Datatables;

use App\Models\FuelType;
use App\Services\FuelService;
use App\Http\Traits\ResponseTemplate;

class FuelTypeController extends Controller
{
    use ResponseTemplate;

    public function __construct(protected FuelService $fuelService) {}

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';

        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['fuelTypes_add', 'fuelTypes_edit', 'fuelTypes_delete', 'fuelTypes_show']);

        if ($request->ajax()) {
            $model = FuelType::query()
                ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->orderBy('id', 'desc');
            $datatable_model = Datatables::of($model)
                ->addColumn('price_formatted', function ($row_object) {
                    return number_format((float) $row_object->price_per_liter, 2);
                })
                ->addColumn('status_toggle', function ($row_object) use ($permissions) {
                    return view('admin.fuel_types.incs._status_toggle', compact('row_object', 'permissions'));
                })
                ->addColumn('last_updated', function ($row_object) {
                    return $row_object->updated_at ? $row_object->updated_at->format('Y-m-d H:i') : '—';
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.fuel_types.incs._actions', compact('row_object', 'permissions'));
                })
                ->rawColumns(['status_toggle', 'actions']);
            return $datatable_model->make(true);
        }

        return view('admin.fuel_types.index', compact('permissions', 'is_ar'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255|unique:fuel_types,name',
            'price_per_liter' => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors()->all());
        }
        try {
            $data = $request->only(['name', 'price_per_liter', 'description']);
            $data['is_active'] = true;
            $ft = $this->fuelService->create($data);
            return $this->responseTemplate($ft, true, __('fuel_types.object_created'));
        } catch (Exception $e) {
            Log::error('FuelTypeController@store', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('fuel_types.object_error')]);
        }
    }

    public function show($id)
    {
        $ft = FuelType::find($id);
        if (!$ft) {
            return $this->responseTemplate(null, false, __('fuel_types.object_not_found'));
        }
        return $this->responseTemplate($ft, true, null);
    }

    public function update(Request $request, $id)
    {
        $ft = FuelType::find($id);
        if (!$ft) {
            return $this->responseTemplate(null, false, [__('fuel_types.object_not_found')]);
        }
        $validator = Validator::make($request->all(), [
            'name'            => 'required|string|max:255|unique:fuel_types,name,' . $id,
            'price_per_liter' => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors()->all());
        }
        try {
            $data = $request->only(['name', 'price_per_liter', 'description']);
            $this->fuelService->update($ft, $data);
            return $this->responseTemplate($ft->fresh(), true, __('fuel_types.object_updated'));
        } catch (Exception $e) {
            Log::error('FuelTypeController@update', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('fuel_types.object_error')]);
        }
    }

    public function destroy($id)
    {
        $ft = FuelType::find($id);
        if (!$ft) {
            return $this->responseTemplate(null, false, [__('fuel_types.object_not_found')]);
        }
        try {
            $ft->delete();
            return $this->responseTemplate(null, true, __('fuel_types.object_deleted'));
        } catch (Exception $e) {
            Log::error('FuelTypeController@destroy', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('fuel_types.object_error')]);
        }
    }

    public function toggleStatus($id)
    {
        $ft = FuelType::find($id);
        if (!$ft) {
            return $this->responseTemplate(null, false, __('fuel_types.object_not_found'));
        }
        try {
            $ft = $this->fuelService->toggleActive($ft);
            return $this->responseTemplate($ft, true, __('fuel_types.status_updated'));
        } catch (Exception $e) {
            Log::error('FuelTypeController@toggleStatus', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, __('fuel_types.object_error'));
        }
    }

    /**
     * List active fuel types for dropdowns (Station/Vehicle allocation).
     */
    public function listActive()
    {
        $list = FuelType::active()->orderBy('name')->get(['id', 'name', 'price_per_liter']);
        return $this->responseTemplate($list, true, null);
    }

}
