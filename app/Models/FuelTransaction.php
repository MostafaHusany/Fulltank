<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\LogsActivity;

class FuelTransaction extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static string $logName = 'fuel_transactions';

    protected $fillable = [
        'reference_no',
        'qr_token',
        'client_id',
        'driver_id',
        'vehicle_id',
        'station_id',
        'worker_id',
        'admin_id',
        'fuel_type_id',
        'price_per_liter',
        'actual_liters',
        'total_amount',
        'max_allowed_amount',
        'meter_image',
        'status',
        'type',
        'refund_reason',
        'refunded_at',
        'completed_at',
    ];

    protected $casts = [
        'price_per_liter'    => 'decimal:2',
        'actual_liters'      => 'decimal:3',
        'total_amount'       => 'decimal:2',
        'max_allowed_amount' => 'decimal:2',
        'refunded_at'        => 'datetime',
        'completed_at'       => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(StationWorker::class, 'worker_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function scopeAdminFilter(Builder $query): void
    {
        if (request()->filled('reference_no')) {
            $query->where('reference_no', 'like', '%' . request()->query('reference_no') . '%');
        }

        if (request()->filled('client_id')) {
            $query->where('client_id', request()->query('client_id'));
        }

        if (request()->filled('station_id')) {
            $query->where('station_id', request()->query('station_id'));
        }

        if (request()->filled('status')) {
            $query->where('status', request()->query('status'));
        }

        if (request()->filled('type')) {
            $query->where('type', request()->query('type'));
        }

        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request()->query('date_from'));
        }

        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request()->query('date_to'));
        }
    }
}
