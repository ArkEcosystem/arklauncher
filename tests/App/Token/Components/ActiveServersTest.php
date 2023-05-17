<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use App\Server\Jobs\WaitForServerToStart;
use App\Token\Components\ActiveServers;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    $this->token = Token::factory()
        ->withStatus(TokenStatusEnum::FINISHED)
        ->withServerProviders(1)
        ->withNetwork(1)
        ->withDefaultNetworks(1)
        ->createForTest();

    $this->user = $this->token->user;

    $key = SecureShellKey::factory()->ownedBy($this->user)->create();

    $this->token->secureShellKeys()->sync($key->id);

    $this->server = Server::factory()->ownedBy($this->token->network(NetworkTypeEnum::MAINNET))->createForTest();
});

it('can select a network', function () {
    $token = $this->token;
    $user  = $this->user;

    $network = $token->network(NetworkTypeEnum::MAINNET);

    Livewire::actingAs($user)
            ->test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertSet('selectedNetwork.id', $network->id)
            ->call('selectNetwork', $network->id)
            ->assertEmitted('setNetwork')
            ->assertSet('selectedNetwork.id', $network->id)
            ->assertSet('network', $network->name);
});

it('can select a network via an event', function () {
    $token = $this->token;
    $user  = $this->user;

    $network = $token->network(NetworkTypeEnum::MAINNET);

    Livewire::actingAs($user)
            ->test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertSet('selectedNetwork.id', $network->id)
            ->emit('selectNetwork', $network->id)
            ->assertEmitted('setNetwork')
            ->assertSet('selectedNetwork.id', $network->id)
            ->assertSet('network', $network->name);
});

it('can select a token', function () {
    $token = $this->token;
    $user  = $this->user;

    $secondToken = Token::factory()
        ->withStatus(TokenStatusEnum::FINISHED)
        ->withServerProviders(1)
        ->withNetwork(1)
        ->withDefaultNetworks(1)
        ->createForTest();

    $network       = $token->network(NetworkTypeEnum::MAINNET);
    $secondNetwork = $secondToken->network(NetworkTypeEnum::MAINNET);

    Livewire::actingAs($user)
            ->test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertSet('selectedNetwork.id', $network->id)
            ->call('setToken', $secondToken->id)
            ->assertSet('selectedToken.id', $secondToken->id)
            ->assertSet('selectedNetwork.id', $secondNetwork->id);
});

it('can select a token via an event', function () {
    $token = $this->token;
    $user  = $this->user;

    $secondToken = Token::factory()
        ->withStatus(TokenStatusEnum::FINISHED)
        ->withServerProviders(1)
        ->withNetwork(1)
        ->withDefaultNetworks(1)
        ->createForTest();

    $network       = $token->network(NetworkTypeEnum::MAINNET);
    $secondNetwork = $secondToken->network(NetworkTypeEnum::MAINNET);

    Livewire::actingAs($user)
            ->test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertSet('selectedNetwork.id', $network->id)
            ->emit('setToken', $secondToken->id)
            ->assertSet('selectedToken.id', $secondToken->id)
            ->assertSet('selectedNetwork.id', $secondNetwork->id);
});

it('cant start a server that does not exist', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertDontSee('alert-error')
            ->call('startServer', $server->id)
            ->assertSee('alert-error');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('displays a descriptive error message', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/too-many-requests'), 429, []),
    ]);

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
        ->assertSet('selectedToken.id', $token->id)
        ->assertDontSee('alert-error')
        ->call('startServer', $server->id)
        ->assertSee('alert-error')
        ->assertSee('API Rate limit exceeded.');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('displays authentication message if unable to authenticate the server provider', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/unauthorized'), 401, []),
    ]);

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
        ->assertSet('selectedToken.id', $token->id)
        ->assertDontSee('alert-error')
        ->call('startServer', $server->id)
        ->assertSee('alert-error')
        ->assertSee(trans('notifications.server_provider_authentication_error', [
            'provider' => 'DigitalOcean',
            'name'     => $server->serverProvider->name,
        ]));

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('cant stop a server that does not exist', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertDontSee('alert-error')
            ->call('stopServer', $server->id)
            ->assertSee('alert-error');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('cant reboot a server that does not exist', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertDontSee('alert-error')
            ->call('rebootServer', $server->id)
            ->assertSee('alert-error');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('can start a server', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/start'), 200, []),
    ]);

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->call('startServer', $server->id)
            ->assertEmitted('toastMessage');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('can stop a server', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/stop'), 200, []),
    ]);

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->call('stopServer', $server->id)
            ->assertEmitted('toastMessage');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('can reboot a server', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/reboot'), 200, []),
    ]);

    Bus::fake();

    $token  = $this->token;
    $user   = $token->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->call('rebootServer', $server->id)
            ->assertEmitted('toastMessage');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Bus::assertDispatched(WaitForServerToStart::class);
});

it('can handle unknown errors', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/reboot'), 422, []),
    ]);

    $token  = $this->token;
    $user   = $this->user;
    $server = $this->server;

    $this->actingAs($user);

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
            ->assertSet('selectedToken.id', $token->id)
            ->assertDontSee('alert-error')
            ->call('rebootServer', $server->id)
            ->assertSee('alert-error');

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
});

it('should change the selected network on network change', function () {
    $token = $this->token;
    $user  = $token->user;

    $networkMainnet = $token->network(NetworkTypeEnum::MAINNET);
    $networkDevnet  = $token->network(NetworkTypeEnum::DEVNET);

    Server::factory()->ownedBy($networkDevnet)->createForTest();

    $this->actingAs($user);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
        ->assertSet('selectedToken.id', $token->id)
        ->assertSet('selectedNetwork.id', $networkMainnet->id)
        ->emit('selectNetwork', $networkDevnet->id)
        ->assertSet('selectedNetwork.id', $networkDevnet->id)
        ->assertSet('network', $networkDevnet->name);
});

it('should not crash when the network specified as query parameter is invalid', function () {
    $user  = $this->user;
    $token = $this->token;

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => 'foo', 'token' => $token->name]));

    expect($response->status())->toBe(200);
});

it('should not crash when the token specified as query parameter is invalid', function () {
    $user = $this->user;

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => NetworkTypeEnum::MAINNET, 'token' => 'foo']));

    expect($response->status())->toBe(200);
});

it('should not crash when the network and token specified as query parameters are invalid', function () {
    $user = $this->user;

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => 'foo', 'token' => 'foo']));

    expect($response->status())->toBe(200);
});

it('should select the first network and first token available if both query parameters are invalid', function () {
    $user  = $this->user;

    $token = $this->token;

    Token::factory()->ownedBy($user)->createForTest();
    Token::factory()->ownedBy($user)->createForTest();

    $tokenNetwork = $token->network(NetworkTypeEnum::MAINNET);

    $this->actingAs($user);

    $response = $this->get(route('tokens', ['network' => 'foo', 'token' => 'foo']));

    expect($response->status())->toBe(200);

    Livewire::test(ActiveServers::class, ['selectedToken' => $token])
        ->assertSet('selectedToken.id', $token->id)
        ->assertSet('selectedNetwork.id', $tokenNetwork->id);
});

it('can sort the network servers by name', function () {
    $token = $this->token;
    $user  = $this->user;

    $network = $token->network(NetworkTypeEnum::DEVNET);

    $x = Server::factory()->ownedBy($network)->createForTest([
        'name'       => 'x name',
        'created_at' => now(),
    ]);
    $z = Server::factory()->ownedBy($network)->createForTest([
        'name'       => 'z name',
        'created_at' => now()->sub(1, 'min'),
    ]);
    $a = Server::factory()->ownedBy($network)->createForTest([
        'name'       => 'a name',
        'created_at' => now()->sub(2, 'min'),
    ]);

    Livewire::actingAs($user)
            ->test(ActiveServers::class, ['selectedToken' => $token])
            ->call('selectNetwork', $network->id)
            ->assertEmitted('setNetwork')
            ->assertSet('selectedNetwork.id', $network->id)
            ->assertSet('network', $network->name)
            ->assertCount('selectedNetworkServers', 3)
            ->assertSet('selectedNetworkServers.0.id', $x->id)
            ->assertSet('selectedNetworkServers.1.id', $z->id)
            ->assertSet('selectedNetworkServers.2.id', $a->id)
            // Sorted by name asc
            ->call('sortBy', 'name')
            ->assertSet('selectedNetworkServers.0.id', $a->id)
            ->assertSet('selectedNetworkServers.1.id', $x->id)
            ->assertSet('selectedNetworkServers.2.id', $z->id)
            // Second time: Sorted by name desc
            ->call('sortBy', 'name')
            ->assertSet('selectedNetworkServers.0.id', $z->id)
            ->assertSet('selectedNetworkServers.1.id', $x->id)
            ->assertSet('selectedNetworkServers.2.id', $a->id)
            // Third time: Sorted by default
            ->call('sortBy', 'name')
            ->assertSet('selectedNetworkServers.0.id', $x->id)
            ->assertSet('selectedNetworkServers.1.id', $z->id)
            ->assertSet('selectedNetworkServers.2.id', $a->id);
});

it('can sort the network servers by size', function () {
    $token = $this->token;
    $user  = $this->user;

    $network = $token->network(NetworkTypeEnum::DEVNET);

    $d500 = Server::factory()->ownedBy($network)->createForTest([
        'server_provider_plan_id'   => ServerProviderPlan::factory()->create(['disk' => 500]),
        'created_at'                => now(),
    ]);
    $d1000 = Server::factory()->ownedBy($network)->createForTest([
        'server_provider_plan_id'   => ServerProviderPlan::factory()->create(['disk' => 1000]),
        'created_at'                => now()->sub(1, 'min'),
    ]);
    $d600 = Server::factory()->ownedBy($network)->createForTest([
        'server_provider_plan_id'   => ServerProviderPlan::factory()->create(['disk' => 600]),
        'created_at'                => now()->sub(2, 'min'),
    ]);

    Livewire::actingAs($user)
            ->test(ActiveServers::class, ['selectedToken' => $token])
            ->call('selectNetwork', $network->id)
            ->assertEmitted('setNetwork')
            ->assertSet('selectedNetwork.id', $network->id)
            ->assertSet('network', $network->name)
            ->assertCount('selectedNetworkServers', 3)
            ->assertSet('selectedNetworkServers.0.id', $d500->id)
            ->assertSet('selectedNetworkServers.1.id', $d1000->id)
            ->assertSet('selectedNetworkServers.2.id', $d600->id)
            // Sorted by plan.disk asc
            ->call('sortBy', 'plan.disk')
            ->assertSet('selectedNetworkServers.0.id', $d500->id)
            ->assertSet('selectedNetworkServers.1.id', $d600->id)
            ->assertSet('selectedNetworkServers.2.id', $d1000->id)
            // Second time: Sorted by plan.disk dec
            ->call('sortBy', 'plan.disk')
            ->assertSet('selectedNetworkServers.0.id', $d1000->id)
            ->assertSet('selectedNetworkServers.1.id', $d600->id)
            ->assertSet('selectedNetworkServers.2.id', $d500->id)
            // Third time: Sorted by default
            ->call('sortBy', 'plan.disk')
            ->assertSet('selectedNetworkServers.0.id', $d500->id)
            ->assertSet('selectedNetworkServers.1.id', $d1000->id)
            ->assertSet('selectedNetworkServers.2.id', $d600->id);
});

it('should filter servers by type/preset', function () {
    $token = $this->token;
    $user  = $this->user;

    $network = $token->network(NetworkTypeEnum::DEVNET);

    Server::factory()->ownedBy($network)->createForTest(['preset' => 'forger']);
    Server::factory()->ownedBy($network)->createForTest(['preset' => 'forger']);
    Server::factory()->ownedBy($network)->createForTest(['preset' => 'relay']);
    Server::factory()->ownedBy($network)->createForTest(['preset' => 'explorer']);
    Server::factory()->ownedBy($network)->createForTest(['preset' => 'genesis']);
    Server::factory()->ownedBy($network)->createForTest(['preset' => 'genesis']);
    Server::factory()->ownedBy($network)->createForTest(['preset' => 'genesis']);

    Livewire::actingAs($user)
        ->test(ActiveServers::class, ['selectedToken' => $token])
        ->call('selectNetwork', $network->id)
        ->assertSet('serverType', 'all')
        ->assertCount('selectedNetworkServers', 7)
        ->assertCount('filteredNetworkServers', 7)
        ->set('serverType', PresetTypeEnum::GENESIS)
        ->assertCount('filteredNetworkServers', 3)
        ->set('serverType', PresetTypeEnum::EXPLORER)
        ->assertCount('filteredNetworkServers', 1)
        ->set('serverType', PresetTypeEnum::FORGER)
        ->assertCount('filteredNetworkServers', 2)
        ->set('serverType', PresetTypeEnum::RELAY)
        ->assertCount('filteredNetworkServers', 1);
});

it('should reset type filter when changing network', function () {
    $token = $this->token;
    $user  = $this->user;

    $network = $token->network(NetworkTypeEnum::DEVNET);

    Livewire::actingAs($user)
        ->test(ActiveServers::class, ['selectedToken' => $token])
        ->set('serverType', PresetTypeEnum::GENESIS)
        ->assertSet('serverType', PresetTypeEnum::GENESIS)
        ->assertCount('selectedNetworkServers', 2)
        ->assertCount('filteredNetworkServers', 0)
        ->call('selectNetwork', $network->id)
        ->assertSet('serverType', 'all');
});
