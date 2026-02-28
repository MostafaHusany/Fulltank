<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Station extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static string $logName = 'stations';

    protected $fillable = [
        'name', 'governorate_id', 'district_id', 'address', 'lat', 'lng',
        'nearby_landmarks', 'manager_name', 'phone_1', 'phone_2', 'bank_account_details', 'user_id'
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(GovernorateDistrict::class, 'district_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fuelTypes(): BelongsToMany
    {
        return $this->belongsToMany(FuelType::class, 'station_fuel_types')->withTimestamps();
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'user_id');
    }

    public function settlements()
    {
        return $this->hasMany(Settlement::class);
    }

    public function workers()
    {
        return $this->hasMany(StationWorker::class);
    }
}
