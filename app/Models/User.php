<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

// use Spatie\Image\Manipulations;
// use Spatie\MediaLibrary\HasMedia;
// use Spatie\MediaLibrary\InteractsWithMedia;
// use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements LaratrustUser
{
    use HasRolesAndPermissions;

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'company_name', 'client_category_id', 'phone', 'email', 'password', 'category',
        'phone_verified_at', 'email_verified_at', 'is_active',
        'group_id', 'picture', 'partner_category_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    //     'password' => 'hashed',
    // ];

    // public function OTP() {
    //     return $this->hasOne(OTP::class, 'user_id');
    // }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function clientCategory()
    {
        return $this->belongsTo(ClientCategory::class, 'client_category_id');
    }

    public function employee () {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function student () {
        return $this->hasOne(AdmissionStudent::class, 'user_id');
    }

    // START FILTRATION; Keep in the end of the model !
    public function scopeAdminFilter(Builder $query) {
        if (request()->filled('name')) {
            $query->where('name', 'like', '%' . request()->query('name') . '%');
        }

        if (request()->filled('company_name')) {
            $query->where('company_name', 'like', '%' . request()->query('company_name') . '%');
        }

        if (request()->filled('client_category_id')) {
            $query->where('client_category_id', request()->query('client_category_id'));
        }

        if (request()->filled('email')) {
            $query->where('email', 'like', '%' . request()->query('email') . '%');
        }

        if (request()->filled('phone')) {
            $query->where('phone', 'like', '%' . request()->query('phone') . '%');
        }

        if (request()->filled('category')) {
            $query->where('category', request()->query('category'));
        }

        if (request()->filled('is_active')) {
            $query->where('is_active', request()->query('is_active'));
        }

        if (request()->filled('roles')) {
            $roles = request()->query('roles');
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('roles.id', $roles);
            });
        } 

        if (request()->filled('groups')) {
            $query->whereIn('group_id', request()->query('groups'));
        }

    }

    public function scopeStudentFilter (Builder $query) {
        if (request()->filled('grades')) {
            $term = request()->query('grades');

            $query->whereHas('student', function ($q) use ($term) {
                $q->whereIn('admission_students.grade_id', is_array($term) ? $term : explode(',', $term));
            });
        } 

        if (request()->filled('levels')) {
            $term = request()->query('levels');

            $query->whereHas('student', function ($q) use ($term) {
                $q->whereIn('admission_students.level_id', is_array($term) ? $term : explode(',', $term));
            });
        } 
    }

}
