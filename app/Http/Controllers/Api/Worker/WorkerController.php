<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\FuelTransaction;
use App\Models\StationWorker;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    use ApiResponse;

    protected function getStationWorker(Request $request): ?StationWorker
    {
        return StationWorker::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('station')
            ->first();
    }

    public function dashboard(Request $request): JsonResponse
    {
        $stationWorker = $this->getStationWorker($request);

        if (!$stationWorker) {
            return $this->error(__('api.worker.not_assigned'), 400);
        }

        $today = Carbon::today();

        $todayStats = FuelTransaction::where('worker_id', $stationWorker->id)
            ->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as transactions_count,
                COALESCE(SUM(actual_liters), 0) as total_liters,
                COALESCE(SUM(total_amount), 0) as total_amount
            ')
            ->first();

        return $this->success([
            'station' => [
                'id'      => $stationWorker->station->id,
                'name'    => $stationWorker->station->name,
                'address' => $stationWorker->station->address,
            ],
            'today_stats' => [
                'transactions' => (int) $todayStats->transactions_count,
                'liters'       => (float) $todayStats->total_liters,
                'amount'       => (float) $todayStats->total_amount,
            ],
        ]);
    }
}
