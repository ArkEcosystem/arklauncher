<?php

declare(strict_types=1);

use App\Console\Commands\Jobs\RemoteServerExistence;
use App\Server\Notifications\ServerDeleted as ServerDeletedNotification;
use Domain\Server\Models\Server;
use Domain\Token\Events\ServerDeleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('checks if remote server still exists on digitalocean', function () {
    Event::fake();

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $server = Server::factory()->digitalocean()->createForTest();

    (new RemoteServerExistence($server))->handle();

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($server));
});

it('should not notifiy if server provider has other errors', function () {
    Event::fake();

    Http::fakeSequence()->push([], 500, []);

    $server = Server::factory()->digitalocean()->createForTest();

    (new RemoteServerExistence($server))->handle();

    Event::assertNotDispatched(ServerDeleted::class);
});

it('checks if remote server still exists on vultr', function () {
    Event::fake();

    Http::fake([
        'vultr.com/*' => Http::response($this->fixture('vultr/server-not-found'), 200, []),
    ]);

    $server = Server::factory()->vultr()->createForTest();

    (new RemoteServerExistence($server))->handle();

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($server));
});

it('checks if remote server still exists on linode', function () {
    Event::fake();

    Http::fake([
        'linode.com/*' => Http::response($this->fixture('linode/server-not-found'), 404, []),
    ]);

    $server = Server::factory()->linode()->createForTest();

    (new RemoteServerExistence($server))->handle();

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($server));
});

it('checks if remote server still exists on hetzner', function () {
    Event::fake();

    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/server-not-found'), 404, []),
    ]);

    $server = Server::factory()->hetzner()->createForTest();

    (new RemoteServerExistence($server))->handle();

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($server));
});

it('the notification related with the server deleted event is sent to the server user', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $server = Server::factory()->digitalocean()->createForTest();

    (new RemoteServerExistence($server))->handle();

    Notification::assertSentTo(
        $server->token->user,
        function (ServerDeletedNotification $notification) use ($server) {
            return $notification->server->is($server);
        }
    );
});
