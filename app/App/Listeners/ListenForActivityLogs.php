<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\LogsActivity;
use Illuminate\Contracts\Events\Dispatcher;
use Support\Services\ActivityLogger;

final class ListenForActivityLogs
{
    public function handle(LogsActivity $event) : void
    {
        ActivityLogger::log(
            $event->subject(),
            $event->description(),
            $event->causer(),
            $event->payload()
        );
    }

    public function subscribe(Dispatcher $events) : void
    {
        $events->listen(
            LogsActivity::class,
            static::class.'@handle'
        );
    }
}
