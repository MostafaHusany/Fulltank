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

use App\Models\User;
use App\Models\StationWorker;

use App\Services\StationWorkerService;
use App\Http\Traits\ResponseTemplate;

class StationWorkerController extends Controller
{
    use ResponseTemplate;

    private $userModel;
    private $targetModel;
    private $stationWorkerService;

    public function __construct(StationWorkerService $stationWorkerService)
    {
        $this->userModel = new User;
        $this->targetModel = new StationWorker;
        $this->stationWorkerService = $stationWorkerService;
    }

    public function index(Request $request)
    {
        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar';

        $permissions = auth()->user()->category == 'admin'
            ? 'admin'
            : $this->getPermissions(['stationWorkers_add', 'stationWorkers_edit', 'stationWorkers_delete', 'stationWorkers_show']);

        if ($request->ajax()) {
            $model = $this->targetModel->query()
                ->with(['user', 'station.governorate', 'station.district'])
                ->adminFilter()
                ->orderBy('id', 'desc');

            $datatable_model = Datatables::of($model)
                ->addColumn('username', function ($row_object) {
                    return $row_object->user ? e($row_object->user->username) : '---';
                })
                ->addColumn('station_name', function ($row_object) {
                    return $row_object->station ? e($row_object->station->name) : '---';
                })
                ->addColumn('status', function ($row_object) use ($permissions) {
                    return view('admin.station_workers.incs._status', compact('row_object', 'permissions'));
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.station_workers.incs._actions', compact('row_object', 'permissions'));
                })
                ->rawColumns(['status', 'actions']);

            return $datatable_model->make(true);
        }

        return view('admin.station_workers.index', compact('permissions', 'is_ar'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'  => 'required|string|max:255',
            'station_id' => 'required|exists:stations,id',
            'phone'      => 'nullable|string|max:50',
            'username'   => 'required|string|max:100|unique:users,username',
            'password'   => 'required|string|min:6|max:50',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $worker = $this->stationWorkerService->create($request->only([
                'full_name', 'station_id', 'phone', 'username', 'password'
            ]));

            return $this->responseTemplate($worker, true, [__('station_workers.object_created')]);
        } catch (Exception $exception) {
            Log::error('StationWorkerController@store Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('station_workers.object_error')]);
        }
    }

    public function show($id)
    {
        $worker = $this->targetModel
            ->with(['user', 'station.governorate', 'station.district'])
            ->find($id);

        if (!$worker) {
            return $this->responseTemplate(null, false, __('station_workers.object_not_found'));
        }

        return $this->responseTemplate($worker, true);
    }

    public function update(Request $request, $id)
    {
        $worker = $this->targetModel->with('user')->find($id);

        if (!$worker) {
            return $this->responseTemplate(null, false, __('station_workers.object_not_found'));
        }

        if (isset($request->update_status)) {
            return $this->updateStatus($worker);
        }

        return $this->updateWorker($request, $worker);
    }

    public function destroy($id)
    {
        $worker = $this->targetModel->find($id);

        if (!$worker) {
            return $this->responseTemplate(null, false, __('station_workers.object_not_found'));
        }

        try {
            $this->stationWorkerService->delete($worker);

            return $this->responseTemplate(null, true, __('station_workers.object_deleted'));
        } catch (Exception $exception) {
            Log::error('StationWorkerController@destroy Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('station_workers.object_error')]);
        }
    }

    private function updateWorker(Request $request, StationWorker $worker)
    {
        $validator = Validator::make($request->all(), [
            'full_name'  => 'required|string|max:255',
            'station_id' => 'required|exists:stations,id',
            'phone'      => 'nullable|string|max:50',
            'username'   => 'required|string|max:100|unique:users,username,' . ($worker->user_id ?? 0),
            'password'   => 'nullable|string|min:6|max:50',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $updatedWorker = $this->stationWorkerService->update($worker, $request->only([
                'full_name', 'station_id', 'phone', 'username', 'password'
            ]));

            return $this->responseTemplate($updatedWorker, true, [__('station_workers.object_updated')]);
        } catch (Exception $exception) {
            Log::error('StationWorkerController@updateWorker Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('station_workers.object_error')]);
        }
    }

    private function updateStatus(StationWorker $worker)
    {
        try {
            $updatedWorker = $this->stationWorkerService->toggleStatus($worker);

            return $this->responseTemplate($updatedWorker, true, __('station_workers.status_updated'));
        } catch (Exception $exception) {
            Log::error('StationWorkerController@updateStatus Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('station_workers.object_error')]);
        }
    }
}
