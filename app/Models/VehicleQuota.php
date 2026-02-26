<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleQuota extends Model
{
    protected $fillable = [
        'vehicle_id',
        'client_id',
        'amount_limit',
        'consumed_amount',
        'reset_cycle',
        'last_reset_date',
        'is_active',
    ];

    protected $casts = [
        'amount_limit'    => 'decimal:2',
        'consumed_amount' => 'decimal:2',
        'last_reset_date' => 'datetime',
        'is_active'       => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount_limit - (float) $this->consumed_amount);
    }
}
