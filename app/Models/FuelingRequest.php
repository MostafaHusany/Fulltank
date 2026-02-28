<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'client_id',
        'fuel_type_id',
        'requested_liters',
        'estimated_cost',
        'fuel_price_at_request',
        'otp_code',
        'latitude',
        'longitude',
        'status',
        'expires_at',
        'completed_by_worker_id',
        'completed_at_station_id',
        'completed_at',
    ];

    protected $casts = [
        'requested_liters'      => 'decimal:2',
        'estimated_cost'        => 'decimal:2',
        'fuel_price_at_request' => 'decimal:2',
        'latitude'              => 'decimal:7',
        'longitude'             => 'decimal:7',
        'expires_at'            => 'datetime',
        'completed_at'          => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function completedByWorker(): BelongsTo
    {
        return $this->belongsTo(StationWorker::class, 'completed_by_worker_id');
    }

    public function completedAtStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'completed_at_station_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->isPending() && $this->expires_at->isPast());
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getRemainingSecondsAttribute(): int
    {
        if (!$this->isPending() || $this->expires_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInSeconds($this->expires_at, false);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending')
              ->where('expires_at', '>', now());
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', ['pending'])
              ->where('expires_at', '>', now());
    }

    public function scopeExpiredPending(Builder $query): void
    {
        $query->where('status', 'pending')
              ->where('expires_at', '<=', now());
    }

    public function markAsExpired(): bool
    {
        return $this->update(['status' => 'expired']);
    }

    public function markAsCompleted(int $workerId, int $stationId): bool
    {
        return $this->update([
            'status'                 => 'completed',
            'completed_by_worker_id' => $workerId,
            'completed_at_station_id' => $stationId,
            'completed_at'           => now(),
        ]);
    }

    public function markAsCancelled(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }
}
