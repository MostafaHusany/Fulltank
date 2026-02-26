<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use App\Models\VehicleQuota;
use App\Models\User;

class QuotaService
{
    protected const CYCLES = ['daily', 'weekly', 'monthly', 'one_time'];

    /**
     * Get or create quota for a vehicle. Returns the active quota.
     */
    public function getOrCreateQuota(int $vehicleId, int $clientId): VehicleQuota
    {
        $quota = VehicleQuota::where('vehicle_id', $vehicleId)
            ->where('is_active', true)
            ->first();

        if ($quota) {
            return $quota;
        }

        return VehicleQuota::create([
            'vehicle_id'      => $vehicleId,
            'client_id'       => $clientId,
            'amount_limit'    => 0,
            'consumed_amount' => 0,
            'reset_cycle'     => 'one_time',
            'last_reset_date' => null,
            'is_active'       => true,
        ]);
    }

    /**
     * Create or update a single vehicle quota.
     */
    public function upsertQuota(int $vehicleId, int $clientId, float $amountLimit, string $resetCycle): VehicleQuota
    {
        $this->validateCycle($resetCycle);

        $quota = VehicleQuota::where('vehicle_id', $vehicleId)->where('is_active', true)->first();

        if ($quota) {
            $quota->amount_limit = $amountLimit;
            $quota->reset_cycle = $resetCycle;
            $quota->last_reset_date = $resetCycle === 'one_time' ? null : now();
            $quota->consumed_amount = 0;
            $quota->save();
            return $quota;
        }

        return VehicleQuota::create([
            'vehicle_id'      => $vehicleId,
            'client_id'       => $clientId,
            'amount_limit'    => $amountLimit,
            'consumed_amount' => 0,
            'reset_cycle'     => $resetCycle,
            'last_reset_date' => $resetCycle === 'one_time' ? null : now(),
            'is_active'       => true,
        ]);
    }

    /**
     * Bulk allocate quotas to multiple vehicles.
     */
    public function bulkAllocate(int $clientId, array $vehicleIds, float $amountLimit, string $resetCycle): int
    {
        $this->validateCycle($resetCycle);

        $client = User::where('category', 'client')->findOrFail($clientId);
        $vehicles = Vehicle::where('client_id', $clientId)->whereIn('id', $vehicleIds)->pluck('id');

        $count = 0;
        foreach ($vehicles as $vid) {
            $this->upsertQuota((int) $vid, $clientId, $amountLimit, $resetCycle);
            $count++;
        }

        return $count;
    }

    protected function validateCycle(string $cycle): void
    {
        if (!in_array($cycle, self::CYCLES)) {
            throw new Exception(__('vehicle_quotas.invalid_cycle'));
        }
    }
}
