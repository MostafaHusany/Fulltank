<?php

namespace App\Http\Controllers\Station;

use App\Http\Controllers\Controller;
use App\Services\Station\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\StationWorker;

class WorkerController extends Controller
{
    public function __construct(
        protected WorkerService $workerService
    ) {}

    protected function getStationId(): ?int
    {
        $user = auth()->user();
        $station = $user->managedStation;
        return $station ? $station->id : null;
    }

    public function index(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return redirect()->route('station.dashboard')
                ->with('error', __('station.workers.no_station'));
        }

        if ($request->ajax()) {
            $search = $request->input('search');
            $workers = $this->workerService->getWorkersWithStats($stationId, $search);
            return response()->json(['data' => $workers]);
        }

        return view('station.workers.index');
    }

    public function store(Request $request)
    {
        $stationId = $this->getStationId();

        if (!$stationId) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.no_station'),
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:50',
            'username'  => 'required|string|max:100|unique:users,username',
            'password'  => 'required|string|min:6',
        ], [
            'name.required'     => __('station.workers.validation.name_required'),
            'phone.required'    => __('station.workers.validation.phone_required'),
            'username.required' => __('station.workers.validation.username_required'),
            'username.unique'   => __('station.workers.validation.username_unique'),
            'password.required' => __('station.workers.validation.password_required'),
            'password.min'      => __('station.workers.validation.password_min'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $worker = $this->workerService->create($request->all(), $stationId);

            return response()->json([
                'success' => true,
                'message' => __('station.workers.created'),
                'data'    => $worker,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.create_error') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $stationId = $this->getStationId();

        $worker = StationWorker::where('id', $id)
            ->where('station_id', $stationId)
            ->with('user')
            ->first();

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.not_found'),
            ], 404);
        }

        $stats = $this->workerService->getWorkerStats($worker->user_id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'        => $worker->id,
                'user_id'   => $worker->user_id,
                'name'      => $worker->full_name,
                'phone'     => $worker->phone,
                'username'  => $worker->user ? $worker->user->username : null,
                'is_active' => $worker->is_active,
                'stats'     => $stats,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $stationId = $this->getStationId();

        $worker = StationWorker::where('id', $id)
            ->where('station_id', $stationId)
            ->with('user')
            ->first();

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.not_found'),
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:50',
            'username'  => 'required|string|max:100|unique:users,username,' . ($worker->user_id ?? 0),
            'password'  => 'nullable|string|min:6',
        ], [
            'name.required'     => __('station.workers.validation.name_required'),
            'phone.required'    => __('station.workers.validation.phone_required'),
            'username.required' => __('station.workers.validation.username_required'),
            'username.unique'   => __('station.workers.validation.username_unique'),
            'password.min'      => __('station.workers.validation.password_min'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $worker = $this->workerService->update($worker, $request->all());

            return response()->json([
                'success' => true,
                'message' => __('station.workers.updated'),
                'data'    => $worker,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.update_error'),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $stationId = $this->getStationId();

        $worker = StationWorker::where('id', $id)
            ->where('station_id', $stationId)
            ->first();

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.not_found'),
            ], 404);
        }

        try {
            $this->workerService->delete($worker);

            return response()->json([
                'success' => true,
                'message' => __('station.workers.deleted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.delete_error'),
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        $stationId = $this->getStationId();

        $worker = StationWorker::where('id', $id)
            ->where('station_id', $stationId)
            ->first();

        if (!$worker) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.not_found'),
            ], 404);
        }

        try {
            $worker = $this->workerService->toggleStatus($worker);

            return response()->json([
                'success' => true,
                'message' => $worker->is_active
                    ? __('station.workers.activated')
                    : __('station.workers.deactivated'),
                'is_active' => $worker->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('station.workers.toggle_error'),
            ], 500);
        }
    }
}
