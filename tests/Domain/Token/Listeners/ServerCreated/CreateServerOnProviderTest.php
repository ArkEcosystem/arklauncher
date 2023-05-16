<?php

declare(strict_types=1);

use App\Server\Jobs\CreateServerOnProvider;
use Domain\Server\Models\Server;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Listeners\ServerCreated\CreateServerOnProvider as CreateServerOnProviderListener;

it('create server on provider job is dispatched', function () {
    $this->expectsJobs([CreateServerOnProvider::class]);

    $server = Server::factory()->create();

    (new CreateServerOnProviderListener())->handle(new ServerCreated($server));
});
