<?php

declare(strict_types=1);

use App\Console\Commands\Jobs\MaintainServerProviderKey as Job;
use App\Console\Commands\MaintainServerProviderKeys;
use Domain\Server\Models\ServerProvider;

it('dispatches jobs to check api keys for a server provider', function () {
    $this->expectsJobs(Job::class);

    ServerProvider::factory()->createForTest();

    $this->artisan(MaintainServerProviderKeys::class);
});
