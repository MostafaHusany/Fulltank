<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'plate_number', 'model', 'fuel_type', 'status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
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
        if (request()->filled('fuel_type')) {
            $query->where('fuel_type', request()->query('fuel_type'));
        }
        if (request()->filled('status')) {
            $query->where('status', request()->query('status'));
        }
    }
}
