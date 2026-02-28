<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

use App\Traits\LogsActivity;

class Wallet extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static string $logName = 'wallets';

    protected $fillable = [
        'user_id',
        'valide_balance',
        'pendding_balance',
        'is_active',
    ];

    protected $casts = [
        'valide_balance'   => 'decimal:2',
        'pendding_balance' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    /**
     * Get the available balance (valid balance).
     */
    public function getBalanceAttribute(): float
    {
        return (float) $this->valide_balance;
    }

    /**
     * Get the total balance (valid + pending).
     */
    public function getTotalBalanceAttribute(): float
    {
        return (float) $this->valide_balance + (float) $this->pendding_balance;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get station associated with this wallet (via user_id).
     */
    public function station()
    {
        return $this->hasOne(Station::class, 'user_id', 'user_id');
    }
}
