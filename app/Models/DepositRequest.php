<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepositRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_number', 'client_id', 'amount', 'fee_amount', 'total_to_pay', 'payment_method_id',
        'proof_image', 'status', 'created_by', 'approved_by', 'reviewed_by', 'wallet_transaction_id', 'processed_by', 'action_date'
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'fee_amount'   => 'decimal:2',
        'total_to_pay' => 'decimal:2',
        'action_date'  => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'wallet_transaction_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
