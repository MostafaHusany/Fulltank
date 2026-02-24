<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\Partner;
use App\Http\Traits\ResponseTemplate;

class PartnerController extends Controller
{
    use ResponseTemplate;

    /** @var string Path for logos: storage/app/public/media/partners */
    private const LOGO_DIR = 'media/partners';
    private const LOGO_DISK = 'public';

    private $targetModel;

    public function __construct()
    {
        $this->targetModel = new Partner;
    }

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['partners_add', 'partners_edit', 'partners_delete', 'partners_show']);

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->orderBy('id', 'desc')
                ->adminFilter();

            $datatableModel = Datatables::of($model)
                ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                    return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
                })
                ->addColumn('logo', function ($row_object) {
                    return view('admin.partners.incs._thumbnail', compact('row_object'));
                })
                ->addColumn('name', fn ($row_object) => $row_object->name)
                ->addColumn('activation', function ($row_object) use ($permissions) {
                    return view('admin.partners.incs._active', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.partners.incs._actions', compact('row_object', 'permissions'));
                });

            return $datatableModel->make(true);
        }

        return view('admin.partners.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules('store'));

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = $request->only(['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        try {
            DB::beginTransaction();

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store(self::LOGO_DIR, self::LOGO_DISK);
            }

            $partner = $this->targetModel->create($data);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            Log::error('PartnerController@store Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('partners.object_error')]);
        }

        return $this->responseTemplate($partner, true, __('partners.object_created'));
    }

    public function show($id)
    {
        $partner = $this->targetModel->find($id);

        if (!$partner) {
            return $this->responseTemplate(null, false, __('partners.object_not_found'));
        }

        return $this->responseTemplate($partner, true, null);
    }

    public function update(Request $request, $id)
    {
        $partner = $this->targetModel->find($id);

        if (!$partner) {
            return $this->responseTemplate(null, false, __('partners.object_not_found'));
        }

        if (isset($request->activate_object)) {
            return $this->activateRecord($partner);
        }

        return $this->updateRecord($request, $partner);
    }

    public function destroy(Request $request, $id)
    {
        if ($id == 0 && isset($request->selected_ids)) {
            return $this->bulkDelete($request);
        }

        return $this->delete($id);
    }

    private function getValidationRules($action = 'store'): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
        $rules['logo'] = $action === 'update'
            ? 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048'
            : 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048';
        return $rules;
    }

    private function updateRecord(Request $request, Partner $partner)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules('update'));

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = $request->only(['name']);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        try {
            DB::beginTransaction();

            if ($request->hasFile('logo')) {
                if ($partner->logo && Storage::disk(self::LOGO_DISK)->exists($partner->logo)) {
                    Storage::disk(self::LOGO_DISK)->delete($partner->logo);
                }
                $data['logo'] = $request->file('logo')->store(self::LOGO_DIR, self::LOGO_DISK);
            }

            $partner->update($data);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            Log::error('PartnerController@update Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('partners.object_error')]);
        }

        return $this->responseTemplate($partner, true, __('partners.object_updated'));
    }

    private function activateRecord(Partner $target_obj)
    {
        $target_obj->is_active = !$target_obj->is_active;
        $target_obj->save();

        return $this->responseTemplate($target_obj, true, __('partners.object_updated'));
    }

    private function bulkDelete(Request $request)
    {
        $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids);
        $items = $this->targetModel->whereIn('id', $ids)->get();

        foreach ($items as $item) {
            if ($item->logo && Storage::disk(self::LOGO_DISK)->exists($item->logo)) {
                Storage::disk(self::LOGO_DISK)->delete($item->logo);
            }
        }

        $this->targetModel->whereIn('id', $ids)->delete();

        return $this->responseTemplate(null, true, __('partners.object_deleted'));
    }

    private function delete($id)
    {
        $partner = $this->targetModel->find($id);

        if (!$partner) {
            return $this->responseTemplate(null, false, __('partners.object_not_found'));
        }

        if ($partner->logo && Storage::disk(self::LOGO_DISK)->exists($partner->logo)) {
            Storage::disk(self::LOGO_DISK)->delete($partner->logo);
        }

        $partner->delete();

        return $this->responseTemplate($partner, true, __('partners.object_deleted'));
    }
}
