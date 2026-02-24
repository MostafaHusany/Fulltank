<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'path', 
        'scorm_status', 'is_active', 'extension', 
        'size_in_kb', 'type', 'user_id'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function grades () {
        return $this->belongsToMany(SchoolGrade::class, 'media_files_levels', 'media_file_id', 'grade_id');
    }

    public function levels () {
        return $this->belongsToMany(SchoolGrade::class, 'media_files_levels', 'media_file_id', 'level_id');
    }

    public function subjects () {
        return $this->belongsToMany(Subject::class, 'media_files_subjects', 'media_file_id', 'subject_id');
    }

    public function learningLists () {
        return $this->belongsToMany(LearningList::class, 'learning_lists_media_files', 'media_file_id', 'learning_list_id');
    }

    public function progress () {
        return $this->hasMany(StudentProgress::class, 'media_file_id');
    }

    // START FILTRATION; Keep in the end of the model !
    public function scopeAdminFilter(Builder $query) {

        if (request()->filled('title')) {
            $term = request()->query('title');

            $query->where('title', 'like', '%' . $term . '%');
        }

        if (request()->filled('type')) {
            $term = request()->query('type');

            $query->where('type', $term);
        }

        if (request()->filled('grades')) {
            $term = request()->query('grades');

            $query->whereHas('grades', function ($q) use ($term) {
                $q->whereIn('school_grades.id', is_array($term) ? $term : explode(',', $term));
            });
        }

        if (request()->filled('levels')) {
            $term = request()->query('levels');

            $query->whereHas('levels', function ($q) use ($term) {
                $q->whereIn('school_grades.id', is_array($term) ? $term : explode(',', $term));
            });
        }

        if (request()->filled('subjects')) {
            $term = request()->query('subjects');

            $query->whereHas('subjects', function ($q) use ($term) {
                $q->whereIn('subjects.id', is_array($term) ? $term : explode(',', $term));
            });
        }

    }

    public function scopeTeacherFilter(Builder $query) {

        $query->where(function ($q) {
            $q->orWhere('is_active', 1);
            $q->orWhere('media_files.user_id', auth()->user()->id);
        });

        if (request()->filled('title')) {
            $term = request()->query('title');

            $query->where('title', 'like', '%' . $term . '%');
        }

        if (request()->filled('type')) {
            $term = request()->query('type');

            $query->whereIn('type', is_array($term) ? $term : explode(',', $term));
        }

        if (request()->filled('levels')) {
            $term = request()->query('levels');

            $query->whereHas('levels', function ($q) use ($term) {
                $q->whereIn('school_grades.id', is_array($term) ? $term : explode(',', $term));
            });
        }

        if (request()->filled('subjects')) {
            $term = request()->query('subjects');

            $query->whereHas('subjects', function ($q) use ($term) {
                $q->whereIn('subjects.id', is_array($term) ? $term : explode(',', $term));
            });
        }

    }

}
