<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialSetting extends Model
{
    use HasFactory;

    protected $fillable = ['fee_type', 'fee_value'];

    protected $casts = [
        'fee_value' => 'decimal:2',
    ];

    public static function getActive(): self
    {
        $setting = self::first();
        if (!$setting) {
            $setting = self::create(['fee_type' => 'fixed', 'fee_value' => 0]);
        }
        return $setting;
    }

    public function calculateFeeAndTotal(float $amount): array
    {
        $feeAmount = 0;
        if ($this->fee_type === 'percentage') {
            $feeAmount = round($amount * ((float) $this->fee_value / 100), 2);
        } else {
            $feeAmount = (float) $this->fee_value;
        }
        $totalToPay = round($amount + $feeAmount, 2);
        return ['fee_amount' => $feeAmount, 'total_to_pay' => $totalToPay];
    }
}
