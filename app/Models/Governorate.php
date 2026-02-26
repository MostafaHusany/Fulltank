<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    protected $fillable = ['name'];

    public function districts(): HasMany
    {
        return $this->hasMany(GovernorateDistrict::class, 'governorate_id');
    }
}
