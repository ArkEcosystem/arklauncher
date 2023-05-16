<?php

declare(strict_types=1);

use App\Enums\ServerProviderTypeEnum;
use App\Server\Components\RenameServer;
use Domain\Server\Models\Server;
use Domain\Server\Support\Rules\ValidDigitalOceanServerName;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = $this->user();

    $this->token = Token::factory()
        ->ownedBy($this->user)
        ->withNetwork(1)
        ->createForTest();
});

it('can open the rename server modal', function () {
    $token  = $this->token;
    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest();
    $user   = $this->user;

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->assertSet('name', $server->name);
});

it('can cancel the rename server modal', function () {
    $token  = $this->token;
    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest();
    $user   = $this->user;

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->assertSet('name', $server->name)
            ->call('cancel')
            ->assertSet('serverId', null)
            ->assertSet('name', null);
});

it('can rename the server if is the owner', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/rename'), 200, []),
    ]);

    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest();

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->set('name', 'new-name')
            ->call('rename')
            ->assertSet('serverId', null)
            ->assertRedirect();

    expect($server->fresh()->name)->toBe('new-name');
});

it('fails to rename the token if is not the owner', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/rename'), 200, []),
    ]);

    $token = $this->token;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest(['name' => 'original name']);

    $anotherUser = $this->user();
    $token->shareWith($anotherUser, 'collaborator');

    $this->actingAs($anotherUser);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->set('name', 'new name')
            ->call('rename')
            ->assertSet('serverId', $server->id);

    expect($server->fresh()->name)->toBe('original name');
});

it('validates the server name is not empty', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/rename'), 200, []),
    ]);

    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest(['name' => 'original name']);

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->set('name', '')
            ->call('rename')
            ->assertHasErrors(['name' => 'required']);

    expect($server->fresh()->name)->toBe('original name');
});

it('validates the server name against the provider rule', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/rename'), 200, []),
    ]);

    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest(['name' => 'original name']);

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->set('name', 'name with spaces')
            ->call('rename')
            ->assertHasErrors(['name' => new ValidDigitalOceanServerName()]);

    expect($server->fresh()->name)->toBe('original name');
});

it('handles unauthorized exceptions', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/unauthorized'), 401, []),
    ]);

    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest(['name' => 'original-name']);

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->set('name', 'some-name')
            ->call('rename')
            ->assertHasErrors('name')
            ->assertSee(trans('notifications.server_provider_authentication_error', [
                'provider' => ServerProviderTypeEnum::label($server->serverProvider->type),
                'name'     => $server->serverProvider->name,
            ]));

    expect($server->fresh()->name)->toBe('original-name');
});

it('handles generic exceptions', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/errored-general'), 500, []),
    ]);

    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest(['name' => 'original-name']);

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->set('name', 'some-name')
            ->call('rename')
            ->assertHasErrors('name')
            ->assertSee(trans('notifications.something_went_wrong'));

    expect($server->fresh()->name)->toBe('original-name');
});

it('handles server not found exceptions', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest(['name' => 'original-name']);

    $this->actingAs($user);

    Livewire::test(RenameServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('renameServer', $server->id)
            ->assertSet('serverId', $server->id)
            ->set('name', 'some-name')
            ->call('rename')
            ->assertHasErrors('name')
            ->assertSee(trans('notifications.server_not_found', ['server' => 'original-name']));

    expect($server->fresh()->name)->toBe('original-name');
});
