<?php

declare(strict_types=1);

use App\Console\Commands\IndexServerProviderRegions;
use App\Server\Jobs\IndexServerProviderRegions as Job;
use Domain\Server\Models\ServerProvider;

it('dispatches jobs to index available regions for a server provider', function () {
    $this->expectsJobs(Job::class);

    ServerProvider::factory()->createForTest();

    $this->artisan(IndexServerProviderRegions::class);
});
