<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Drop onto an Eloquent model to automatically record create/update/delete
 * activity. Customize the human label via $activityLabel.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            $model->recordActivity('created', "Created {$model->activityName()}");
        });

        static::updated(function (Model $model) {
            $model->recordActivity('updated', "Updated {$model->activityName()}", [
                'changes' => $model->getChanges(),
            ]);
        });

        static::deleted(function (Model $model) {
            $model->recordActivity('deleted', "Deleted {$model->activityName()}");
        });
    }

    protected function recordActivity(string $action, string $description, array $properties = []): void
    {
        app(ActivityLogger::class)->log($action, $description, $this, $properties);
    }

    public function activityName(): string
    {
        $label = property_exists($this, 'activityLabel') ? $this->activityLabel : class_basename($this);

        return strtolower($label) . ' #' . $this->getKey();
    }
}
