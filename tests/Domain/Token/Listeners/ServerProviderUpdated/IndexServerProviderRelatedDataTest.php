<?php

declare(strict_types=1);

use App\Server\Jobs\IndexServerProviderImages;
use App\Server\Jobs\IndexServerProviderPlans;
use App\Server\Jobs\IndexServerProviderRegions;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderUpdated;
use Domain\Token\Listeners\ServerProviderUpdated\IndexServerProviderRelatedData;

it('indexes plans and regions and images when server provider credentials are updated', function () {
    $this->expectsJobs([
        IndexServerProviderPlans::class,
        IndexServerProviderRegions::class,
        IndexServerProviderImages::class,
    ]);

    $serverProvider = ServerProvider::factory()->createForTest();

    (new IndexServerProviderRelatedData())->handle(new ServerProviderUpdated($serverProvider));
});

it('should not fire any jobs on server provider updated if there is no provider key id set', function () {
    $this->doesntExpectJobs([
        IndexServerProviderPlans::class,
        IndexServerProviderRegions::class,
        IndexServerProviderImages::class,
    ]);

    $serverProvider = ServerProvider::factory()->createForTest();

    $serverProvider->provider_key_id = null;

    (new IndexServerProviderRelatedData())->handle(new ServerProviderUpdated($serverProvider));
});
