<?php

declare(strict_types=1);

use App\SecureShell\Jobs\RemoveSecureShellKeyFromServerProvider;
use App\Server\Components\DeleteServerProvider;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Events\ServerProviderDeleted;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('can ask for confirmation and set the server provider id', function () {
    $provider = ServerProvider::factory()->createForTest();

    ServerProvider::factory()->ownedBy($provider->token)->createForTest();

    Livewire::actingAs($this->token()->user)
            ->test(DeleteServerProvider::class, ['token' => $this->token()])
            ->assertSet('serverProviderId', null)
            ->call('askForConfirmation', $provider->id)
            ->assertSet('serverProviderId', $provider->id);
});

it('can toggle delete on provider', function () {
    Livewire::actingAs($this->token()->user)
            ->test(DeleteServerProvider::class, ['token' => $this->token()])
            ->assertSet('deleteOnProvider', false)
            ->call('toggleDeleteOnProvider')
            ->assertSet('deleteOnProvider', true)
            ->call('toggleDeleteOnProvider')
            ->assertSet('deleteOnProvider', false);
});

it('can cancel the confirmation', function () {
    $provider = ServerProvider::factory()->createForTest();

    ServerProvider::factory()->ownedBy($provider->token)->createForTest();

    $this->assertDatabaseHas('server_providers', ['id' => $provider->id]);

    Livewire::actingAs($this->token()->user)
            ->test(DeleteServerProvider::class, ['token' => $this->token()])
            ->assertSet('serverProviderId', null)
            ->call('askForConfirmation', $provider->id)
            ->assertSet('serverProviderId', $provider->id)
            ->call('cancel')
            ->assertSet('serverProviderId', null);

    $this->assertDatabaseHas('server_providers', ['id' => $provider->id]);
});

it('unauthorized users can not delete server providers', function () {
    $provider = ServerProvider::factory()->createForTest();

    ServerProvider::factory()->ownedBy($provider->token)->createForTest();

    $this->assertDatabaseHas('server_providers', ['id' => $provider->id]);

    Livewire::actingAs($this->token()->user)
            ->test(DeleteServerProvider::class, ['token' => $this->token()])
            ->assertSet('serverProviderId', null)
            ->call('askForConfirmation', $provider->id)
            ->assertSet('serverProviderId', $provider->id)
            ->call('destroy')
            ->assertSet('serverProviderId', $provider->id);

    $this->assertDatabaseHas('server_providers', ['id' => $provider->id]);
});

it('authorized users can delete server providers and its servers', function () {
    $this->expectsJobs(
        RemoveSecureShellKeyFromServerProvider::class
    );

    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
        ]);

    $server   = Server::factory()->createForTest();
    $provider = $server->serverProvider;
    $token    = $provider->token;

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::actingAs($token->user)
            ->test(DeleteServerProvider::class, ['token' => $token])
            ->assertSet('serverProviderId', null)
            ->set('deleteOnProvider', true)
            ->call('askForConfirmation', $provider->id)
            ->assertSet('serverProviderId', $provider->id)
            ->call('destroy')
            ->assertSet('serverProviderId', null)
            ->assertEmitted('toastMessage')
            ->assertSet('modalShown', false)
            ->assertStatus(200);

    $this->assertDatabaseMissing('servers', ['id' => $server->id]);
});

it('dispatch the server deleted event when the server is deleted', function () {
    Event::fake();

    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
        ]);

    $server   = Server::factory()->createForTest();
    $provider = $server->serverProvider;
    $token    = $provider->token;

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::actingAs($token->user)
            ->test(DeleteServerProvider::class, ['token' => $token])
            ->assertSet('serverProviderId', null)
            ->set('deleteOnProvider', true)
            ->call('askForConfirmation', $provider->id)
            ->assertSet('serverProviderId', $provider->id)
            ->call('destroy')
            ->assertSet('serverProviderId', null)
            ->assertStatus(200);

    Event::assertDispatched(fn (ServerDeleted $event) => $event->server->is($server));
});

it('dispatch the server provider deleted event when the server provider is deleted', function () {
    Event::fake();

    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
        ]);

    $server         = Server::factory()->createForTest();
    $serverProvider = $server->serverProvider;
    $token          = $serverProvider->token;

    $this->assertDatabaseHas('servers', ['id' => $server->id]);

    Livewire::actingAs($token->user)
            ->test(DeleteServerProvider::class, ['token' => $token])
            ->assertSet('serverProviderId', null)
            ->set('deleteOnProvider', true)
            ->call('askForConfirmation', $serverProvider->id)
            ->assertSet('serverProviderId', $serverProvider->id)
            ->call('destroy')
            ->assertSet('serverProviderId', null)
            ->assertStatus(200);

    Event::assertDispatched(fn (ServerProviderDeleted $event) => $event->serverProvider->is($serverProvider));
});

it('authorized users can delete server providers', function () {
    $this->expectsJobs(
        RemoveSecureShellKeyFromServerProvider::class
    );

    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
        ]);

    $token = Token::factory()->withServerProviders(1)->createForTest();

    $provider = $token->serverProviders()->first();

    ServerProvider::factory()->ownedBy($token)->createForTest();

    Livewire::actingAs($token->user)
            ->test(DeleteServerProvider::class, ['token' => $token])
            ->assertSet('serverProviderId', null)
            ->call('askForConfirmation', $provider->id)
            ->assertSet('serverProviderId', $provider->id)
            ->call('destroy')
            ->assertSet('serverProviderId', null)
            ->assertStatus(200);

    $token = Token::findOrFail($token->id);

    expect($token->serverProviders->count())->toBe(1);
});

it('deleting server marks step incomplete if none', function () {
    $this->expectsJobs(
        RemoveSecureShellKeyFromServerProvider::class
    );

    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
        ]);

    $token = Token::factory()->withServerProviders(1)->createForTest();

    $provider = $token->serverProviders()->first();

    Livewire::actingAs($token->user)
            ->test(DeleteServerProvider::class, ['token' => $token])
            ->assertSet('serverProviderId', null)
            ->call('askForConfirmation', $provider->id)
            ->assertSet('serverProviderId', $provider->id)
            ->call('destroy')
            ->assertSet('serverProviderId', null)
            ->assertStatus(200);

    $token = Token::findOrFail($token->id);

    expect($token->serverProviders->count())->toBe(0);
    expect($token->onboarding()->completed('server_providers'))->toBeFalse();
});
