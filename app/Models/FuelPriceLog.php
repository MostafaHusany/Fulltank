<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelPriceLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['fuel_type_id', 'old_price', 'new_price', 'changed_by'];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
