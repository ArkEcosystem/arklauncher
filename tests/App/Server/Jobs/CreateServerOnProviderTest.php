<?php

declare(strict_types=1);

use App\Server\Jobs\CreateServerOnProvider;
use App\Server\Jobs\ServerProvisioner;
use App\Server\Notifications\RemoteServerLimitReached;
use App\Server\Notifications\ServerDeployed;
use App\Server\Notifications\ServerFailedToCreateOnProvider;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('creates a server on a provider and dispatches the server provisioner job', function () {
    $this->expectsJobs([
        ServerProvisioner::class,
    ]);

    Http::fake([
        'digitalocean.com/*' => Http::sequence()
            ->push($this->fixture('digitalocean/ssh-keys-get'), 200, [])
            ->push($this->fixture('digitalocean/create'), 200, [])
            ->push($this->fixture('digitalocean/server'), 200, [])
            ->push($this->fixture('digitalocean/images'), 200, []),
    ]);

    $user = User::factory()->create();

    $server = Server::factory()->genesis()->createForTest();
    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    ServerProviderImage::factory()->create([
        'uuid'               => 'ubuntu-22-04-x64',
    ]);

    $this->assertDatabaseMissing('servers', [
        'provider_server_id'        => 3164494,
        'preset'                    => 'genesis',
    ]);

    (new CreateServerOnProvider($server))->handle();

    // Notifies the creator & the owner...
    Notification::assertSentTo([$server->creator(), $server->token->user], ServerDeployed::class);
    Notification::assertTimesSent(2, ServerDeployed::class);

    $this->assertDatabaseHas('servers', [
        'provider_server_id'        => 3164494,
        'preset'                    => 'genesis',
    ]);
});

it('does not notify twice the same user when creation was successful', function () {
    $this->expectsJobs([
        ServerProvisioner::class,
    ]);

    Http::fake([
        'digitalocean.com/*' => Http::sequence()
            ->push($this->fixture('digitalocean/ssh-keys-get'), 200, [])
            ->push($this->fixture('digitalocean/create'), 200, [])
            ->push($this->fixture('digitalocean/server'), 200, [])
            ->push($this->fixture('digitalocean/images'), 200, []),
    ]);

    $user = User::factory()->create();

    $server = Server::factory()->genesis()->createForTest();

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $server->token->update([
        'user_id' => $user->id,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => 'ubuntu-22-04-x64',
    ]);

    $this->assertDatabaseMissing('servers', [
        'provider_server_id'        => 3164494,
        'preset'                    => 'genesis',
    ]);

    (new CreateServerOnProvider($server))->handle();

    expect($server->token->user->is($server->creator()))->toBeTrue();

    // Notifies the creator & the owner...
    Notification::assertSentTo($server->creator(), ServerDeployed::class);
    Notification::assertTimesSent(1, ServerDeployed::class);

    $this->assertDatabaseHas('servers', [
        'provider_server_id'        => 3164494,
        'preset'                    => 'genesis',
    ]);
});

it('should delete the server and notify users if failed to create server on a provider', function () {
    $token = Token::factory()->withDefaultNetworks()->createForTest();

    $providerUser   = User::factory()->create();
    $serverProvider = ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $providerUser->id);

    $server = Server::factory()->create([
        'network_id'         => $token->networks()->first()->id,
        'server_provider_id' => $serverProvider->id,
        'provider_server_id' => null,
    ]);

    $user = User::factory()->create();

    $token->shareWith($user, 'collaborator', []);
    $token->shareWith($providerUser, 'collaborator', []);

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    Notification::fake();

    (new CreateServerOnProvider($server))->failed(new Exception('Something went wrong.'));

    expect($server->fresh())->toBeNull();

    // Notifies the creator, the owner and the user that linked the provider...
    Notification::assertSentTo([$server->creator(), $serverProvider->token->user, $server->serverProvider->user()], ServerFailedToCreateOnProvider::class);
    Notification::assertTimesSent(3, ServerFailedToCreateOnProvider::class);
});

it('does not notify users that are not members of a team when failing to create a server on the provider', function () {
    $token = Token::factory()->withDefaultNetworks()->createForTest();

    $providerUser   = User::factory()->create();
    $serverProvider = ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $providerUser->id);

    $server = Server::factory()->create([
        'network_id'         => $token->networks()->first()->id,
        'server_provider_id' => $serverProvider->id,
        'provider_server_id' => null,
    ]);

    $token->shareWith($providerUser, 'collaborator', []);

    $user = User::factory()->create();

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    Notification::fake();

    (new CreateServerOnProvider($server))->failed(new Exception('Something went wrong.'));

    expect($server->fresh())->toBeNull();

    // Notifies the owner and the user that linked the provider...
    Notification::assertSentTo([$server->token->user, $server->serverProvider->user()], ServerFailedToCreateOnProvider::class);
    Notification::assertTimesSent(2, ServerFailedToCreateOnProvider::class);
});

it('should delete the server and notify the user if user has exceeded the server limit amount on a provider', function () {
    $token = Token::factory()->withDefaultNetworks()->createForTest();

    $providerUser   = User::factory()->create();
    $serverProvider = ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $providerUser->id);

    $server = Server::factory()->create([
        'network_id'         => $token->networks()->first()->id,
        'server_provider_id' => $serverProvider->id,
        'provider_server_id' => null,
    ]);

    $user = User::factory()->create();

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $token->shareWith($providerUser, 'collaborator', []);
    $token->shareWith($user, 'collaborator', []);

    Notification::fake();

    (new CreateServerOnProvider($server))->failed(new ServerLimitExceeded('Something went wrong.'));

    expect($server->fresh())->toBeNull();

    // Notifies the creator, the owner and the user that linked the provider...
    Notification::assertSentTo([$server->creator(), $server->token->user, $server->serverProvider->user()], RemoteServerLimitReached::class);
    Notification::assertTimesSent(3, RemoteServerLimitReached::class);
});

it('does not notify users that are not collaborators when exceeded server limit', function () {
    $token = Token::factory()->withDefaultNetworks()->createForTest();

    $providerUser   = User::factory()->create();
    $serverProvider = ServerProvider::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $providerUser->id);

    $server = Server::factory()->create([
        'network_id'         => $token->networks()->first()->id,
        'server_provider_id' => $serverProvider->id,
        'provider_server_id' => null,
    ]);

    $serverProvider->token->shareWith($providerUser, 'collaborator', []);

    $user = User::factory()->create();

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    Notification::fake();

    (new CreateServerOnProvider($server))->failed(new ServerLimitExceeded('Something went wrong.'));

    expect($server->fresh())->toBeNull();

    // Notifies the owner and the user that linked the provider...
    Notification::assertSentTo([$serverProvider->token->user, $serverProvider->user()], RemoteServerLimitReached::class);
    Notification::assertTimesSent(2, RemoteServerLimitReached::class);
});

it('should mark the server as failed if the server was actually created on a provider', function () {
    $server = Server::factory()->genesis()->createForTest([
        'provider_server_id' => '123',
    ]);

    $this->assertDatabaseMissing('statuses', [
        'name' => 'failed',
    ]);

    (new CreateServerOnProvider($server))->failed(new Exception('Something went wrong.'));

    $this->assertDatabaseHas('statuses', [
        'name' => 'failed',
    ]);

    expect($server->fresh())->not->toBeNull();
});
