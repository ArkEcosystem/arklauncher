<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\ServerProviderUpdated;

use App\Server\Jobs\IndexServerProviderImages;
use App\Server\Jobs\IndexServerProviderPlans;
use App\Server\Jobs\IndexServerProviderRegions;
use Domain\Token\Events\ServerProviderUpdated;

final class IndexServerProviderRelatedData
{
    /**
     * Execute the job.
     *
     * @param ServerProviderUpdated $event
     *
     * @return void
     */
    public function handle(ServerProviderUpdated $event)
    {
        $serverProvider = $event->serverProvider;

        if (! is_null($serverProvider->provider_key_id)) {
            IndexServerProviderPlans::dispatch($serverProvider);

            IndexServerProviderRegions::dispatch($serverProvider);

            IndexServerProviderImages::dispatch($serverProvider);
        }
    }
}
