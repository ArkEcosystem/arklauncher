<?php

declare(strict_types=1);

use App\Server\Notifications\ServerProviderAuthenticationFailed;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('update the server name withouth errors and add a token suffix', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/rename'), 200, []),
    ]);

    $server = Server::factory()->create();

    $server->update(['name' => 'new name']);

    expect($server->fresh()->name)->toBe('new name');
});

it('doesnt update the server name if api request fails', function () {
    $this->expectException(ServerProviderError::class);

    Http::fake([
        'digitalocean.com/*' => Http::response([], 500, []),
    ]);

    $server = Server::factory()->create([
        'name' => 'original name',
    ]);

    $server->update(['name' => 'new name']);

    expect($server->fresh()->name)->toBe('original name');
});

it('only call the rename client method if the name was changed', function () {
    // 500 response, shouldnt affect since the api is not being called
    Http::fake([
        'digitalocean.com/*' => Http::response([], 500, []),
    ]);

    $server = Server::factory()->create(['name' => 'original name']);

    $server->update(['user_password' => 'something else']);

    expect($server->fresh()->user_password)->toBe('something else');
    expect($server->fresh()->name)->toBe('original name');
});

it('notifies the users if unable to authenticate the user', function () {
    $this->expectException(ServerProviderAuthenticationException::class);

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/unauthorized'), 401, []),
    ]);

    $token   = Token::factory()->create();
    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider = ServerProvider::factory()->digitalocean()->create([
        'token_id' => $token->id,
    ]);

    $user = User::factory()->create();

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $server = Server::factory()->create([
        'network_id'         => $network->id,
        'server_provider_id' => $serverProvider->id,
        'name'               => 'original-name',
    ]);

    Notification::fake();

    $server->update(['name' => 'updated-name']);

    expect($server->fresh()->name)->toBe('original-name');

    Notification::assertSentTo([$token->user, $user->id], ServerProviderAuthenticationFailed::class);
    Notification::assertTimesSent(1, ServerProviderAuthenticationFailed::class);
});

it('prevents duplicate notifications', function () {
    $this->expectException(ServerProviderAuthenticationException::class);

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/unauthorized'), 401, []),
    ]);

    $token   = Token::factory()->create();
    $network = Network::factory()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider = ServerProvider::factory()->digitalocean()->create([
        'token_id' => $token->id,
    ]);

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $token->user->id);

    $server = Server::factory()->create([
        'network_id'         => $network->id,
        'server_provider_id' => $serverProvider->id,
        'name'               => 'original-name',
    ]);

    Notification::fake();

    $server->update(['name' => 'updated-name']);

    expect($server->fresh()->name)->toBe('original-name');

    Notification::assertSentTo($token->user, ServerProviderAuthenticationFailed::class);
    Notification::assertTimesSent(1, ServerProviderAuthenticationFailed::class);
});
