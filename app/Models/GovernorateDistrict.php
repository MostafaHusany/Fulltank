<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernorateDistrict extends Model
{
    protected $table = 'governorate_districts';

    protected $fillable = ['governorate_id', 'name'];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }
}
