<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Governorate;
use App\Models\GovernorateDistrict;
use App\Http\Traits\ResponseTemplate;
use Yajra\Datatables\Datatables;

class GovernorateController extends Controller
{
    use ResponseTemplate;

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['governorates_add', 'governorates_edit', 'governorates_delete', 'governorates_show']);

        if ($request->ajax()) {
            $model = Governorate::query()
                ->withCount('districts')
                ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->orderBy('name');

            $datatable = Datatables::of($model)
                ->addColumn('districts_btn', function ($row_object) use ($permissions) {
                    return view('admin.governorates.incs._districts_btn', compact('row_object', 'permissions'))->render();
                })
                ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                    return view('layouts.admin.incs._checkbox_selector', compact('row_object'))->render();
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.governorates.incs._actions', compact('row_object', 'permissions'))->render();
                })
                ->rawColumns(['districts_btn', 'checkbox_selector', 'actions']);

            return $datatable->make(true);
        }

        return view('admin.governorates.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $gov = Governorate::create(['name' => $request->name]);
            return $this->responseTemplate($gov, true, __('governorates.object_created'));
        } catch (Exception $e) {
            Log::error('GovernorateController@store', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('governorates.object_error')]);
        }
    }

    public function show($id)
    {
        $gov = Governorate::with('districts')->find($id);
        if (!$gov) {
            return $this->responseTemplate(null, false, [__('governorates.object_not_found')]);
        }
        return $this->responseTemplate($gov, true, null);
    }

    public function edit($id)
    {
        return redirect()->route('admin.governorates.index');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'   => 'required|exists:governorates,id',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $gov = Governorate::findOrFail($id);
            $gov->update(['name' => $request->name]);
            return $this->responseTemplate($gov, true, __('governorates.object_updated'));
        } catch (Exception $e) {
            Log::error('GovernorateController@update', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('governorates.object_error')]);
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($id == 0 && $request->has('selected_ids')) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
            Governorate::whereIn('id', $ids)->delete();
            return $this->responseTemplate(null, true, __('governorates.object_deleted'));
        }

        $gov = Governorate::find($id);
        if (!$gov) {
            return $this->responseTemplate(null, false, [__('governorates.object_not_found')]);
        }
        $gov->delete();
        return $this->responseTemplate(null, true, __('governorates.object_deleted'));
    }

    public function storeDistrict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'governorate_id' => 'required|exists:governorates,id',
            'name'          => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $district = GovernorateDistrict::create([
                'governorate_id' => (int) $request->governorate_id,
                'name'          => $request->name,
            ]);
            return $this->responseTemplate($district, true, __('governorates.district_created'));
        } catch (Exception $e) {
            Log::error('GovernorateController@storeDistrict', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('governorates.object_error')]);
        }
    }

    public function updateDistrict(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'    => 'required|exists:governorate_districts,id',
            'name'  => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $district = GovernorateDistrict::findOrFail($id);
            $district->update(['name' => $request->name]);
            return $this->responseTemplate($district, true, __('governorates.district_updated'));
        } catch (Exception $e) {
            Log::error('GovernorateController@updateDistrict', ['error' => $e->getMessage()]);
            return $this->responseTemplate(null, false, [__('governorates.object_error')]);
        }
    }

    public function destroyDistrict($id)
    {
        $district = GovernorateDistrict::find($id);
        if (!$district) {
            return $this->responseTemplate(null, false, [__('governorates.district_not_found')]);
        }
        $district->delete();
        return $this->responseTemplate(null, true, __('governorates.district_deleted'));
    }

    public function dataAjax(Request $request)
    {
        $query = Governorate::query()->orderBy('name');
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        $items = $query->get(['id', 'name']);
        return response()->json($items->map(fn($g) => ['id' => $g->id, 'text' => $g->name]));
    }

    public function districtsAjax(Request $request)
    {
        $query = GovernorateDistrict::query()->orderBy('name');
        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        $items = $query->get(['id', 'name']);
        return response()->json($items->map(fn($d) => ['id' => $d->id, 'text' => $d->name]));
    }
}
