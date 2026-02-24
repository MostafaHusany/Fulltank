<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\District;

use App\Http\Traits\ResponseTemplate;

class DistrictController extends Controller
{
    use ResponseTemplate;

    private $targetModel;

    public function __construct () {
        $this->targetModel = new District;
    }

    public function index (Request $request) {
        
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';
        
        $permissions = auth()->user()->category == 'admin' 
            ? 'admin' 
            : $this->getPermissions(['districts_add', 'districts_edit', 'districts_delete', 'districts_show']);

        if ($request->ajax()) {
            $model = $this->targetModel->query()
            ->where('category', 'gove')
            ->orderBy('id', 'desc')
            ->adminFilter();
            
            $datatableModel = Datatables::of($model)
            ->addColumn('name', function ($row_object) use ($permissions, $is_ar) {
                return $row_object->{$is_ar ? 'ar_name' : 'en_name'};
            })
            ->addColumn('centers_btn', function ($row_object) use ($permissions) {
                return view('admin.districts.incs._centers_btn', compact('row_object'));
            })
            ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
            })
            ->addColumn('activation', function ($row_object) use ($permissions) {
                return view('admin.districts.incs._active', compact('row_object', 'permissions'));
            })
            ->addColumn('actions', function ($row_object) use ($permissions) {
                return view('admin.districts.incs._actions', compact('row_object', 'permissions'));
            });

            return $datatableModel->make(true);
        }
        
        return view('admin.districts.index', compact('permissions'));
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = $request->only($this->targetModel->getFillable());

        try {
            DB::beginTransaction();
        
            $district = $this->targetModel->create($data);
            
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            Log::error('DistrictController@store Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('districts.object_error')]);
        }

        return $this->responseTemplate($district, true, __('districts.object_created'));
    }

    public function show ($id) {
        $gove = $this->targetModel->with(['children'])->find($id);

        if (!isset($gove)) {
            return $this->responseTemplate(null, false, __('districts.object_not_found'));
        }

        return $this->responseTemplate($gove, true, null);
    }

    public function update (Request $request, $id) {
        $district = $this->targetModel->query()->find($id);

        if (!isset($district)) {
            return $this->responseTemplate(null, false, __('districts.object_not_found'));
        }

        return isset($request->activate_object)
            ? $this->activateDistrict($district)
            : $this->updateDistrict($request, $district);
    }

    public function destroy (Request $request, $id) {
        return $id == 0 && isset($request->selected_ids)
        ? $this->bulkDelete($request, $id)
        : $this->delete($id);
    }

    public function dataAjax(Request $request) {
    	
        $search = $request->q;

        $model = $this->targetModel->query();

        if (isset($request->is_main)) {
            $model->where('category', 'gove')
            ->where('is_active', 1);
        } else if (isset($request->is_sub)) {
            $model->where('category', 'cent');
            isset($request->gove_list) 
                ? $model->whereIn('district_id', $request->gove_list)
                : $model->where('district_id', $request->gove_id);
        }

        $data = $model->where(function ($q) use ($request) {
            $q->orWhere('ar_name', 'like', "%$request->q%");
            $q->orWhere('en_name', 'like', "%$request->q%");
        })->get();

        return response()->json($data);
    }

    // START HELPERS
    private function getValidationRules($id = null): array {
        return [
            'ar_name'       => 'required|max:255',
            'en_name'       => 'required|max:255',
            'geo_lat'       => 'nullable|numeric',
            'geo_lng'       => 'nullable|numeric'
        ];
    }

    private function updateDistrict (Request $request, District $gove) {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data = $request->only($this->targetModel->getFillable());

        try {
            DB::beginTransaction();
            
            $gove->update($data);

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            Log::error('DistrictController@update Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('districts.object_error')]);
        }

        return $this->responseTemplate($gove, true, __('districts.object_updated'));
    }

    private function activateDistrict (District $target_obj) {
        $target_obj->is_active = !$target_obj->is_active;
        $target_obj->save();

        return $this->responseTemplate($target_obj, true, __('districts.object_updated'));
    }

    private function bulkDelete (Request $request, $id) {
        $this->targetModel->query()
        ->whereIn('id', is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids))
        ->delete();
        
        return $this->responseTemplate(null, true, __('districts.object_deleted'));
    }

    private function delete ($id) {
        $district = $this->targetModel->query()->find($id);

        if (!isset($district))
        return $this->responseTemplate(null, false, __('districts.object_not_found'));
        
        $district->delete();

        return $this->responseTemplate($district, true, __('districts.object_deleted'));
    }   

}
