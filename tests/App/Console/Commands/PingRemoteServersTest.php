<?php

declare(strict_types=1);

use App\Console\Commands\Jobs\PingServer as Job;
use App\Console\Commands\PingRemoteServers;
use Domain\Server\Models\Server;

it('dispatches jobs to ping remote server', function () {
    $this->expectsJobs(Job::class);

    Server::factory()->createForTest();

    $this->artisan(PingRemoteServers::class);
});
