<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'causer_type',
        'causer_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function getOldAttribute(): array
    {
        return $this->properties['old'] ?? [];
    }

    public function getNewAttribute(): array
    {
        return $this->properties['attributes'] ?? [];
    }

    public function getChangesAttribute(): array
    {
        $old = $this->old;
        $new = $this->new;
        $changes = [];

        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        return $changes;
    }

    public function getSubjectLabelAttribute(): string
    {
        if (!$this->subject_type) {
            return '---';
        }

        $shortType = class_basename($this->subject_type);
        return "{$shortType} #{$this->subject_id}";
    }

    public function scopeAdminFilter(Builder $query): void
    {
        if (request()->filled('causer_id')) {
            $query->where('causer_id', request()->query('causer_id'));
        }

        if (request()->filled('subject_type')) {
            $query->where('subject_type', request()->query('subject_type'));
        }

        if (request()->filled('event')) {
            $query->where('event', request()->query('event'));
        }

        if (request()->filled('date_from')) {
            $query->whereDate('created_at', '>=', request()->query('date_from'));
        }

        if (request()->filled('date_to')) {
            $query->whereDate('created_at', '<=', request()->query('date_to'));
        }

        if (request()->filled('log_name')) {
            $query->where('log_name', request()->query('log_name'));
        }
    }
}
