<?php

declare(strict_types=1);

use App\Console\Commands\Jobs\SyncServerRemoteAddress;
use Domain\Server\Models\Server;
use Illuminate\Support\Facades\Http;

it('retrieves and stores the ip address for a server', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server'), 200, []),
    ]);

    $server = Server::factory()->createForTest(['ip_address' => null]);

    $this->assertDatabaseMissing('servers', ['ip_address' => '104.131.186.241']);

    (new SyncServerRemoteAddress($server))->handle();

    $this->assertDatabaseHas('servers', ['ip_address' => '104.131.186.241']);
});

it('does not sync if server has an ip address attached', function () {
    Http::fake();

    $server = Server::factory()->create([
        'ip_address' => '127.0.0.1',
    ]);

    (new SyncServerRemoteAddress($server))->handle();

    Http::assertNothingSent();
});

it('can get job backoff', function () {
    expect((new SyncServerRemoteAddress(Server::factory()->create()))->backoff())->toBe([3, 5, 10]);
});
