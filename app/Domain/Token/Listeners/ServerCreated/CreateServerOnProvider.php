<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\ServerCreated;

use App\Server\Jobs\CreateServerOnProvider as CreateServerOnProviderJob;
use Domain\Token\Events\ServerCreated;

final class CreateServerOnProvider
{
    /**
     * Execute the job.
     *
     * @param ServerCreated $event
     *
     * @return void
     */
    public function handle(ServerCreated $event)
    {
        $server = $event->server;

        CreateServerOnProviderJob::dispatch($server);
    }
}
