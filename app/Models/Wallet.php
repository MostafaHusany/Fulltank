<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Wallet extends Model
{
    use HasFactory;

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
