<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'station_id',
        'amount',
        'payment_method',
        'transaction_details',
        'receipt_image',
        'admin_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeAdminFilter(Builder $query): void
    {
        if (request()->filled('station_id')) {
            $query->where('station_id', request()->query('station_id'));
        }

        if (request()->filled('payment_method')) {
            $query->where('payment_method', request()->query('payment_method'));
        }

        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request()->query('date_from'));
        }

        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request()->query('date_to'));
        }

        if (request()->filled('reference_no')) {
            $query->where('reference_no', 'like', '%' . request()->query('reference_no') . '%');
        }
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash'          => __('settlements.method_cash'),
            'bank_transfer' => __('settlements.method_bank_transfer'),
            'check'         => __('settlements.method_check'),
            default         => $this->payment_method,
        };
    }
}
