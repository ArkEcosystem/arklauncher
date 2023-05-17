<?php

declare(strict_types=1);

namespace Support\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class ActivityLogger
{
    public static function log(Model $model, string $action, Model $causer = null, array $properties = [], ?int $userId = null) : void
    {
        activity(class_basename($model))
            ->performedOn($model)
            ->causedBy($causer)
            ->withProperties(array_merge(['user_id' => $userId ?? Auth::id()], $properties))
            ->log($action);
    }
}
