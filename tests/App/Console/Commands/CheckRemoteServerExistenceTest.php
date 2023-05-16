<?php

declare(strict_types=1);

use App\Console\Commands\CheckRemoteServerExistence;
use App\Console\Commands\Jobs\RemoteServerExistence as Job;
use Domain\Server\Models\Server;

it('dispatches job to check for remote server existence', function () {
    $this->expectsJobs(Job::class);

    Server::factory()->createForTest();

    $this->artisan(CheckRemoteServerExistence::class);
});
