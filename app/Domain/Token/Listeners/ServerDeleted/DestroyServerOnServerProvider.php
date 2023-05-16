<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\ServerDeleted;

use App\Server\Jobs\DestroyServerOnServerProvider as DestroyServerOnServerProviderJob;
use Domain\Token\Events\ServerDeleted;

final class DestroyServerOnServerProvider
{
    /**
     * Execute the job.
     *
     * @param ServerDeleted $event
     *
     * @return void
     */
    public function handle(ServerDeleted $event)
    {
        /* @phpstan-ignore-next-line  */
        if (is_null($event->providerServerId)) {
            return;
        }

        DestroyServerOnServerProviderJob::dispatch($event->server, $event->serverProviderClient, $event->providerServerId);
    }
}
