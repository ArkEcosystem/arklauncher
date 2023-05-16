<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use App\Server\Components\DeleteServer;
use Domain\Server\Models\Server;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = $this->user();

    $this->token = Token::factory()
        ->ownedBy($this->user)
        ->withNetwork(1)
        ->createForTest();
});

it('can ask for confirmation and set the server id', function () {
    $token = $this->token;
    $user  = $this->user;

    Livewire::test(DeleteServer::class, ['token' => $token, 'network' => $token->network(NetworkTypeEnum::MAINNET)])
            ->assertSet('serverId', null)
            ->call('askForConfirmation', $user->id)
            ->assertSet('serverId', $user->id);
});

it('can cancel the confirmation', function () {
    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(DeleteServer::class, ['token' => $token, 'network' => $token->network(NetworkTypeEnum::MAINNET)])
            ->assertSet('serverId', null)
            ->call('askForConfirmation', $server->id)
            ->assertSet('serverId', $server->id)
            ->call('cancel')
            ->assertSet('serverId', null);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('can destroy the token if it is the owner', function () {
    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(DeleteServer::class, ['token' => $token, 'network' => $token->network(NetworkTypeEnum::MAINNET)])
            ->assertSet('serverId', null)
            ->call('askForConfirmation', $server->id)
            ->assertSet('serverId', $server->id)
            ->call('destroy')
            ->assertSet('serverId', null)
            ->assertRedirect();

    $this->assertDatabaseMissing('servers', ['id' => $server->id]);
});

it('dispatchs a server deleted event when the server is removed', function () {
    Event::fake();

    $token = $this->token;
    $user  = $this->user;

    $server = Server::factory()->ownedBy($token->network('mainnet'))->createForTest();

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(DeleteServer::class, ['token' => $token, 'network' => $token->network('mainnet')])
            ->assertSet('serverId', null)
            ->call('askForConfirmation', $server->id)
            ->assertSet('serverId', $server->id)
            ->call('destroy')
            ->assertSet('serverId', null)
            ->assertRedirect();

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($server));
});

it('fails to destroy the token if it is not the owner', function () {
    $token = $this->token;

    $server = Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    $anotherUser = $this->user();
    $token->shareWith($anotherUser, 'collaborator');

    $this->actingAs($anotherUser);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(DeleteServer::class, ['token' => $token, 'network' => $token->network(NetworkTypeEnum::MAINNET)])
            ->assertSet('serverId', null)
            ->call('askForConfirmation', $server->id)
            ->assertSet('serverId', $server->id)
            ->call('destroy')
            ->assertSet('serverId', $server->id);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('can set another token to be used', function () {
    $token = $this->token;
    $user  = $this->user;

    $anotherToken = Token::factory()->ownedBy($user)->withNetwork(1)->createForTest();

    $this->actingAs($user);

    Livewire::test(DeleteServer::class, ['token' => $token, 'network' => $token->network(NetworkTypeEnum::MAINNET)])
            ->assertSet('serverId', null)
            ->assertSet('token.id', $token->id)
            ->assertSet('network.id', $token->network(NetworkTypeEnum::MAINNET)->id)
            ->call('setToken', $anotherToken->id)
            ->assertSet('token.id', $anotherToken->id)
            ->assertSet('network.id', $anotherToken->network(NetworkTypeEnum::MAINNET)->id);
});

it('can set another network to be used', function () {
    $token = $this->token;
    $user  = $this->user;

    $this->actingAs($user);

    Livewire::test(DeleteServer::class, ['token' => $token, 'network' => $token->network(NetworkTypeEnum::MAINNET)])
            ->assertSet('serverId', null)
            ->assertSet('token.id', $token->id)
            ->assertSet('network.id', $token->network(NetworkTypeEnum::MAINNET)->id)
            ->call('setNetwork', $token->network(NetworkTypeEnum::MAINNET)->id)
            ->assertSet('token.id', $token->id)
            ->assertSet('network.id', $token->network(NetworkTypeEnum::MAINNET)->id);
});
