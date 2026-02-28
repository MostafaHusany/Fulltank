<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VehicleQuota;
use Illuminate\Support\Facades\Log;

class ResetMonthlyQuotas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotas:reset-monthly 
                            {--client= : Reset quotas for a specific client ID only}
                            {--dry-run : Preview what would be reset without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all vehicle quota consumption to zero for the new month';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting monthly quota reset...');

        $query = VehicleQuota::query()
            ->where('reset_cycle', 'monthly')
            ->where('is_active', true);

        if ($this->option('client')) {
            $query->where('client_id', $this->option('client'));
        }

        $quotas = $query->get();

        if ($quotas->isEmpty()) {
            $this->warn('No active quotas found to reset.');
            return Command::SUCCESS;
        }

        $this->info("Found {$quotas->count()} quotas to reset.");

        if ($this->option('dry-run')) {
            $this->table(
                ['Vehicle ID', 'Client ID', 'Current Consumed', 'Limit'],
                $quotas->map(fn ($q) => [
                    $q->vehicle_id,
                    $q->client_id,
                    number_format($q->consumed_amount, 2),
                    number_format($q->amount_limit, 2),
                ])->toArray()
            );
            $this->warn('Dry run - no changes made.');
            return Command::SUCCESS;
        }

        $resetCount = 0;
        $now = now();

        foreach ($quotas as $quota) {
            try {
                $oldConsumed = $quota->consumed_amount;
                
                $quota->update([
                    'consumed_amount'  => 0,
                    'last_reset_date'  => $now,
                ]);

                Log::info('Quota reset', [
                    'vehicle_id'    => $quota->vehicle_id,
                    'client_id'     => $quota->client_id,
                    'old_consumed'  => $oldConsumed,
                    'reset_date'    => $now->toDateTimeString(),
                ]);

                $resetCount++;
            } catch (\Exception $e) {
                $this->error("Failed to reset quota for vehicle ID {$quota->vehicle_id}: {$e->getMessage()}");
                Log::error('Quota reset failed', [
                    'vehicle_id' => $quota->vehicle_id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully reset {$resetCount} quotas.");

        return Command::SUCCESS;
    }
}
