<?php

declare(strict_types=1);

namespace App\Listeners;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

final class FlushMediaCache
{
    public function handle(MediaHasBeenAdded $event): void
    {
        $model = $event->media->model;

        if (method_exists($model, 'flushCache')) {
            /*
             * PHPStan give error: Cannot call method flushCache() on class-string|object.
             * But flushCache() is never called if it wouldn't exist, so PHPStan is wrong in this case.
             * @phpstan-ignore-next-line
             */
            $model->flushCache();
        }
    }
}
