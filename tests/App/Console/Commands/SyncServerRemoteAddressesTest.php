<?php

declare(strict_types=1);

use App\Console\Commands\Jobs\SyncServerRemoteAddress;
use App\Console\Commands\SyncServerRemoteAddresses;
use Domain\Server\Models\Server;

it('dispatches jobs to sync the server remote addresses', function () {
    $this->expectsJobs(SyncServerRemoteAddress::class);

    Server::factory()->createForTest(['ip_address' => null]);

    $this->artisan(SyncServerRemoteAddresses::class);
});

it('ignores servers without a provider id', function () {
    $this->doesntExpectJobs(SyncServerRemoteAddress::class);

    Server::factory()->createForTest(['ip_address' => null, 'provider_server_id' => null]);

    $this->artisan(SyncServerRemoteAddresses::class);
});
