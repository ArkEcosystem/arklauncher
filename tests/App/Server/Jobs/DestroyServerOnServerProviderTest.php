<?php

declare(strict_types=1);

use App\Server\Jobs\DestroyServerOnServerProvider;
use App\Server\Notifications\ServerProviderServerRemovalFailed;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('removes the server from the server provider', function () {
    Http::fakeSequence()
        ->push([], 204, []);

    $server               = Server::factory()->createForTest();
    $serverProviderClient = $server->serverProvider->client();
    $providerServerId     = $server->providerServerId;

    (new DestroyServerOnServerProvider($server, $serverProviderClient, (int) $providerServerId))->handle();

    Http::assertSent(fn ($request) => $request->data() === []);
});

it('notifies if it fails to remove a server', function () {
    $token   = Token::factory()->create();
    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $creator       = User::factory()->create();
    $providerOwner = User::factory()->create();

    $token->shareWith($creator, 'collaborator', []);
    $token->shareWith($providerOwner, 'collaborator', []);

    $serverProvider = ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $providerOwner->id);

    $server = Server::factory()->create([
        'server_provider_id' => $serverProvider->id,
        'network_id'         => $network->id,
    ]);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    $serverProviderClient = $serverProvider->client();
    $providerServerId     = $server->providerServerId;

    (new DestroyServerOnServerProvider($server, $serverProviderClient, (int) $providerServerId))->failed(new RuntimeException('Something went wrong.'));

    Notification::assertSentTo([$token->user, $creator, $providerOwner], ServerProviderServerRemovalFailed::class);
    Notification::assertTimesSent(3, ServerProviderServerRemovalFailed::class);
});

it('prevents duplicate notifications', function () {
    $token   = Token::factory()->create();
    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $creator = User::factory()->create();

    $serverProvider = ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    $server = Server::factory()->create([
        'network_id'         => $network->id,
        'server_provider_id' => $serverProvider,
    ]);

    $token->shareWith($creator, 'collaborator', []);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    $serverProviderClient = $server->serverProvider->client();
    $providerServerId     = $server->providerServerId;

    (new DestroyServerOnServerProvider($server, $serverProviderClient, (int) $providerServerId))->failed(new RuntimeException('Something went wrong.'));

    Notification::assertSentTo([$server->token->user, $creator], ServerProviderServerRemovalFailed::class);
    Notification::assertTimesSent(2, ServerProviderServerRemovalFailed::class);
});

it('does not notify users that are not members of a team', function () {
    $token   = Token::factory()->create();
    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $creator       = User::factory()->create();
    $providerOwner = User::factory()->create();

    $serverProvider = ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $providerOwner->id);

    $server = Server::factory()->create([
        'network_id'         => $network->id,
        'server_provider_id' => $serverProvider,
    ]);

    $token->shareWith($creator, 'collaborator', []);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    $serverProviderClient = $server->serverProvider->client();
    $providerServerId     = $server->providerServerId;

    (new DestroyServerOnServerProvider($server, $serverProviderClient, (int) $providerServerId))->failed(new RuntimeException('Something went wrong.'));

    Notification::assertSentTo([$server->token->user, $creator], ServerProviderServerRemovalFailed::class);
    Notification::assertTimesSent(2, ServerProviderServerRemovalFailed::class);
});

it('can handle errors', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/errored-general'), 500, []),
    ]);

    $server               = Server::factory()->createForTest();
    $serverProviderClient = $server->serverProvider->client();
    $providerServerId     = $server->providerServerId;

    $this->expectException(ServerProviderError::class);

    (new DestroyServerOnServerProvider($server, $serverProviderClient, (int) $providerServerId))->handle();
});

it('gracefully handles server not found excepton', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $server               = Server::factory()->createForTest();
    $serverProviderClient = $server->serverProvider->client();
    $providerServerId     = 1;

    (new DestroyServerOnServerProvider($server, $serverProviderClient, (int) $providerServerId))->handle();

    Http::assertSent(fn ($request) => $request->method() === 'DELETE' && $request->url() === 'https://api.digitalocean.com/v2/droplets/1');
});

it('should delete the server even id the serverprovider is deleted right before the job is executed', function () {
    Http::fakeSequence()
        ->push([], 204, []);

    $server               = Server::factory()->createForTest();
    $serverProviderClient = $server->serverProvider->client();
    $providerServerId     = $server->providerServerId;

    $server->serverProvider->delete();

    (new DestroyServerOnServerProvider($server, $serverProviderClient, (int) $providerServerId))->handle();

    Http::assertSent(fn ($request) => $request->data() === []);
});
