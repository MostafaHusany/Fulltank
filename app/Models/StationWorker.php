<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StationWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'station_id',
        'full_name',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function scopeAdminFilter(Builder $query): void
    {
        if (request()->filled('station_id')) {
            $query->where('station_id', request()->query('station_id'));
        }

        if (request()->filled('username')) {
            $query->whereHas('user', function ($q) {
                $q->where('username', 'like', '%' . request()->query('username') . '%');
            });
        }

        if (request()->filled('full_name')) {
            $query->where('full_name', 'like', '%' . request()->query('full_name') . '%');
        }

        if (request()->filled('is_active')) {
            $query->where('is_active', request()->query('is_active') == '1');
        }
    }
}
