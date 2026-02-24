<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'ar_title',
        'ar_body',
        'type',
        'target_id',
        'is_read',
        'is_action',
        'is_hidden',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
