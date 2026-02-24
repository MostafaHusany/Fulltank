<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFiles extends Model
{
    use HasFactory;
    
    public $fillable = ['user_id', 'title', 'path', 'extension', 'size_in_kb'];

    public function user () {
        return $this->belongsTo(User::class, 'user_id');
    }

}
