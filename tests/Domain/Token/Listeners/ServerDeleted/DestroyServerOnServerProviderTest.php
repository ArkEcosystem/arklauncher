<?php

declare(strict_types=1);

use App\Server\Jobs\DestroyServerOnServerProvider as DestroyServerOnServerProviderJob;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Illuminate\Support\Facades\Bus;

it('destroy the server on server provider', function () {
    $serverProvider = ServerProvider::factory()->create();

    $server = Server::factory()->create([
        'server_provider_id' => $serverProvider->id,
    ]);

    $server->delete();

    Bus::assertDispatched(DestroyServerOnServerProviderJob::class);
});

it('doesnt try to destroy the server on server provider if doesnt has a provider_server_id', function () {
    $server = Server::factory()->create([
        'provider_server_id' => null,
    ]);

    $server->delete();

    Bus::assertNotDispatched(DestroyServerOnServerProviderJob::class);
});
