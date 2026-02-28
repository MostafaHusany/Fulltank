<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Vehicle extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static string $logName = 'vehicles';

    protected $fillable = ['client_id', 'plate_number', 'model', 'fuel_type_id', 'status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function quota(): HasOne
    {
        return $this->hasOne(VehicleQuota::class)->orderByDesc('id');
    }

    public function activeQuota(): HasOne
    {
        return $this->hasOne(VehicleQuota::class)->where('is_active', true)->orderByDesc('id');
    }

    public function fuelTransactions(): HasMany
    {
        return $this->hasMany(FuelTransaction::class);
    }

    /**
     * Drivers assigned to this vehicle.
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(User::class, 'vehicle_id')->where('category', 'driver');
    }

    /**
     * Normalize plate number on save: uppercase, trim, single spaces.
     */
    protected function plateNumber(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtoupper(trim(preg_replace('/\s+/', ' ', $value ?? ''))),
        );
    }

    /**
     * Display formatted plate number (e.g. ABC 1234).
     */
    public function getFormattedPlateNumberAttribute(): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $this->plate_number ?? '')));
    }

    public function scopeAdminFilter(Builder $query): void
    {
        if (request()->filled('client_id')) {
            $query->where('client_id', request()->query('client_id'));
        }
        if (request()->filled('plate_number')) {
            $query->where('plate_number', 'like', '%' . request()->query('plate_number') . '%');
        }
        if (request()->filled('model')) {
            $query->where('model', 'like', '%' . request()->query('model') . '%');
        }
        if (request()->filled('fuel_type_id')) {
            $query->where('fuel_type_id', request()->query('fuel_type_id'));
        }
        if (request()->filled('status')) {
            $query->where('status', request()->query('status'));
        }
    }
}
