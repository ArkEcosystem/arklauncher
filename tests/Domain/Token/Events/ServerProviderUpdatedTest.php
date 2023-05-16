<?php

declare(strict_types=1);

use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderUpdated;
use Domain\Token\Listeners\ServerProviderUpdated\IndexServerProviderRelatedData;

it('runs all the intended listeners when a server provider updated is triggered', function () {
    $serverProvider = ServerProvider::factory()->create();

    expectListenersToBeCalled([
        IndexServerProviderRelatedData::class,
    ], fn ($event) => $event->serverProvider->is($serverProvider));

    ServerProviderUpdated::dispatch($serverProvider);
});
