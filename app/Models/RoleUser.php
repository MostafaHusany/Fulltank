<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    protected $table    = 'role_user'; 
    protected $fillable = ['role_id', 'user_id', 'user_type'];
    protected $guarded  = ['created_at', 'updated_at'];

    protected $attributes = [
        'user_type' => 'App\User',
    ];
}
