<?php

declare(strict_types=1);

use App\Console\Commands\IndexServerProviderPlans;
use App\Server\Jobs\IndexServerProviderPlans as Job;
use Domain\Server\Models\ServerProvider;

it('dispatches jobs to index available plans for a server provider', function () {
    $this->expectsJobs(Job::class);

    ServerProvider::factory()->createForTest();

    $this->artisan(IndexServerProviderPlans::class);
});
