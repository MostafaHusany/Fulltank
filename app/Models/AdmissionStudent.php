<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmissionStudent extends SchoolScopedModel
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name', 'first_name', 'second_name', 'third_name', 'birth_date', 'status', 'gender',
        
        'admission_id', 'grade_id', 'level_id', 'bus', 'bus_address', 'bus_lat', 'bus_lng',
        
        'bus_id', 'semester_id', 'is_shifted', 'class_id', 'user_id'
    ];
    
    public function user () {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grade () {
        return $this->belongsTo(SchoolGrade::class, 'grade_id');
    }
    
    public function level () {
        return $this->belongsTo(SchoolGrade::class, 'level_id');
    }

    public function admission () {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function payments () {
        return $this->hasMany(StudentPayment::class, 'admission_student_id');
    }

    public function class () {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function studentClasses () {
        // for all prev semesters 
        return $this->hasMany(StudentClass::class, 'student_id');
    }

    public function bus () {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function semester () {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    // START FILTRATION; Keep in the end of the model !
    public function scopeAdminFilter(Builder $query) {

        if (request()->filled('name')) {
            $query->where('name', 'like', '%' . request()->query('name') . '%');
        }

        if (request()->filled('status')) {
            $query->where('status', request()->query('status'));
        }
        
        if (request()->filled('reference_number')) {
            $query->whereHas('admission', function ($q) {
                $q->where('reference_number', request()->query('reference_number'));
            });
        }

        if (request()->filled('semesters')) {
            $query->whereHas('admission.semester', function ($q) {
                $term = request()->query('semesters');

                $q->whereIn('semesters.id', is_array($term) ? $term : explode(',', $term));
            });
        }
        
        if (request()->filled('grades')) {
            $term = request()->query('grades');
            
            $query->whereIn('grade_id', is_array($term) ? $term : explode(',', $term));
        }

        if (request()->filled('level_id')) {
            $term = request()->query('level_id');
            
            $query->where('level_id', $term);
        }

        if (request()->filled('levels')) {
            $term = request()->query('levels');
            
            $query->whereIn('level_id', is_array($term) ? $term : explode(',', $term));
        }

        if (request()->filled('classes')) {
            $term = request()->query('classes');
            
            $query->whereIn('class_id', is_array($term) ? $term : explode(',', $term));
        }

        if ((request()->filled('bus'))) {
            $query->where('bus', request()->query('bus'));
        }

        if (request()->filled('bus_id')) {
            $query->where('bus_id', request()->query('bus_id'));
        }

        if (request()->filled('gender')) {
            $query->where('gender', request()->query('gender'));
        }


    }
    
}
