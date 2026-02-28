<?php

namespace App\Services\Station;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\StationWorker;
use App\Models\FuelTransaction;
use App\Services\StationWorkerService as AdminStationWorkerService;

class WorkerService
{
    protected AdminStationWorkerService $adminWorkerService;

    public function __construct(AdminStationWorkerService $adminWorkerService)
    {
        $this->adminWorkerService = $adminWorkerService;
    }

    public function getWorkers(int $stationId, ?string $search = null)
    {
        $query = StationWorker::where('station_id', $stationId)
            ->with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%");
                  });
            });
        }

        return $query->latest()->get();
    }

    public function create(array $data, int $stationId): StationWorker
    {
        return $this->adminWorkerService->create([
            'full_name'  => $data['name'],
            'station_id' => $stationId,
            'phone'      => $data['phone'] ?? null,
            'username'   => $data['username'] ?? $data['phone'],
            'password'   => $data['password'],
        ]);
    }

    public function update(StationWorker $worker, array $data): StationWorker
    {
        return $this->adminWorkerService->update($worker, [
            'full_name'  => $data['name'] ?? $worker->full_name,
            'station_id' => $worker->station_id,
            'phone'      => $data['phone'] ?? $worker->phone,
            'username'   => $data['username'] ?? $data['phone'] ?? $worker->user->username,
            'password'   => $data['password'] ?? null,
        ]);
    }

    public function delete(StationWorker $worker): bool
    {
        return $this->adminWorkerService->delete($worker);
    }

    public function toggleStatus(StationWorker $worker): StationWorker
    {
        return $this->adminWorkerService->toggleStatus($worker);
    }

    public function getWorkerStats(int $userId): array
    {
        $today = now()->toDateString();

        $todayStats = FuelTransaction::where('worker_id', $userId)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as transactions_count, COALESCE(SUM(actual_liters), 0) as total_liters, COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        $totalStats = FuelTransaction::where('worker_id', $userId)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as transactions_count, COALESCE(SUM(actual_liters), 0) as total_liters, COALESCE(SUM(total_amount), 0) as total_amount')
            ->first();

        return [
            'today' => [
                'transactions' => (int) $todayStats->transactions_count,
                'liters'       => (float) $todayStats->total_liters,
                'amount'       => (float) $todayStats->total_amount,
            ],
            'total' => [
                'transactions' => (int) $totalStats->transactions_count,
                'liters'       => (float) $totalStats->total_liters,
                'amount'       => (float) $totalStats->total_amount,
            ],
        ];
    }

    public function getWorkersWithStats(int $stationId, ?string $search = null): array
    {
        $workers = $this->getWorkers($stationId, $search);
        $today = now()->toDateString();

        $userIds = $workers->pluck('user_id')->filter()->toArray();
        
        $todayStats = FuelTransaction::whereIn('worker_id', $userIds)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->selectRaw('worker_id, COUNT(*) as transactions_count, COALESCE(SUM(actual_liters), 0) as total_liters')
            ->groupBy('worker_id')
            ->get()
            ->keyBy('worker_id');

        return $workers->map(function ($worker) use ($todayStats) {
            $stats = $todayStats->get($worker->user_id);
            return [
                'id'                   => $worker->id,
                'user_id'              => $worker->user_id,
                'name'                 => $worker->full_name,
                'phone'                => $worker->phone,
                'username'             => $worker->user ? $worker->user->username : null,
                'is_active'            => $worker->is_active,
                'created_at'           => $worker->created_at->format('Y-m-d'),
                'today_transactions'   => $stats ? (int) $stats->transactions_count : 0,
                'today_liters'         => $stats ? (float) $stats->total_liters : 0,
            ];
        })->toArray();
    }
}
