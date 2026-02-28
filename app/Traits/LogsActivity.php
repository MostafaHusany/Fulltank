<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected static array $logAttributesToIgnore = [
        'updated_at',
        'created_at',
        'remember_token',
        'password',
    ];

    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            static::logModelEvent($model, 'created');
        });

        static::updated(function (Model $model) {
            static::logModelEvent($model, 'updated');
        });

        static::deleted(function (Model $model) {
            static::logModelEvent($model, 'deleted');
        });
    }

    protected static function logModelEvent(Model $model, string $event): void
    {
        $properties = [];

        if ($event === 'created') {
            $properties['attributes'] = static::filterAttributes($model->getAttributes());
        } elseif ($event === 'updated') {
            $dirty = $model->getDirty();
            $original = $model->getOriginal();

            $filteredDirty = static::filterAttributes($dirty);
            $filteredOld = [];

            foreach (array_keys($filteredDirty) as $key) {
                $filteredOld[$key] = $original[$key] ?? null;
            }

            $properties['old'] = $filteredOld;
            $properties['attributes'] = $filteredDirty;

            if (empty($filteredDirty)) {
                return;
            }
        } elseif ($event === 'deleted') {
            $properties['old'] = static::filterAttributes($model->getAttributes());
        }

        $causer = auth()->user();
        $request = request();

        ActivityLog::create([
            'log_name'     => static::getLogName(),
            'description'  => static::getLogDescription($model, $event),
            'subject_type' => get_class($model),
            'subject_id'   => $model->getKey(),
            'event'        => $event,
            'causer_type'  => $causer ? get_class($causer) : null,
            'causer_id'    => $causer?->getKey(),
            'properties'   => $properties,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);
    }

    protected static function filterAttributes(array $attributes): array
    {
        $ignoreList = static::$logAttributesToIgnore;

        if (property_exists(static::class, 'logAttributesIgnore')) {
            $ignoreList = array_merge($ignoreList, static::$logAttributesIgnore);
        }

        return array_filter($attributes, function ($key) use ($ignoreList) {
            return !in_array($key, $ignoreList);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected static function getLogName(): string
    {
        if (property_exists(static::class, 'logName')) {
            return static::$logName;
        }

        return 'default';
    }

    protected static function getLogDescription(Model $model, string $event): string
    {
        $modelName = class_basename($model);
        $id = $model->getKey();

        return match ($event) {
            'created' => "{$modelName} #{$id} was created",
            'updated' => "{$modelName} #{$id} was updated",
            'deleted' => "{$modelName} #{$id} was deleted",
            default   => "{$modelName} #{$id} - {$event}",
        };
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
