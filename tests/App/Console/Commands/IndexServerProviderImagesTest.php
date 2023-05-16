<?php

declare(strict_types=1);

use App\Console\Commands\IndexServerProviderImages;
use App\Server\Jobs\IndexServerProviderImages as Job;
use Domain\Server\Models\ServerProvider;

it('dispatches jobs to index available plans for a server provider', function () {
    $this->expectsJobs(Job::class);

    ServerProvider::factory()->createForTest();

    $this->artisan(IndexServerProviderImages::class);
});
