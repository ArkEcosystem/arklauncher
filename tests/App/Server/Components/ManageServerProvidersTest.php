<?php

declare(strict_types=1);

use App\Enums\ServerProviderTypeEnum;
use App\SecureShell\Jobs\AddSecureShellKeyToServerProvider;
use App\Server\Components\ManageServerProviders;
use App\Server\Jobs\IndexServerProviderImages;
use App\Server\Jobs\IndexServerProviderPlans;
use App\Server\Jobs\IndexServerProviderRegions;
use Carbon\Carbon;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderCreated;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('can select a provider', function () {
    $token = $this->token();

    $this->actingAs($token->user);

    Livewire::actingAs($token->user)
            ->test(ManageServerProviders::class, ['token' => $token])
            ->assertSet('provider', ServerProviderTypeEnum::DIGITALOCEAN)
            ->call('selectProvider', 'foo')
            ->assertSet('provider', 'foo');
});

it('invalid server provider should fail to submit', function () {
    $token = Token::factory()->withServers(0)->createForTest();

    Http::fake([
        'digitalocean.com/*' => Http::response([], 500, []),
    ]);

    Livewire::actingAs($token->user)
            ->test(ManageServerProviders::class, ['token' => $token])
            ->assertSet('provider', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('type', 'foo')
            ->set('name', 'bar')
            ->set('access_token', 'invalid_token')
            ->call('store')
            ->assertSet('isSubmittingFirstProvider', false)
            ->assertSee(trans('tokens.server-providers.added_failed'));
});

it('unauthorized users can not creates server providers', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
            ->test(ManageServerProviders::class, ['token' => $this->token()])
            ->assertSet('provider', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('type', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('name', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('access_token', 'valid_token')
            ->call('store')
            ->assertForbidden();
});

it('authorized users can create a server provider if the credentials are valid', function () {
    $this->expectsJobs([
            AddSecureShellKeyToServerProvider::class,
            IndexServerProviderPlans::class,
            IndexServerProviderRegions::class,
            IndexServerProviderImages::class,
        ]);

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
    ]);

    $token = Token::factory()->withServers(0)->createForTest();

    $count = ServerProvider::all()->count();

    Livewire::actingAs($token->user)
            ->test(ManageServerProviders::class, ['token' => $token])
            ->assertSet('isSubmittingFirstProvider', false)
            ->assertSet('provider', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('type', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('name', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('access_token', 'valid_token')
            ->call('store')
            ->assertSet('isSubmittingFirstProvider', true)
            ->assertSee(trans('tokens.server-providers.added_success_redirect'));

    expect(ServerProvider::all()->count())->toBe($count + 1);
});

it('authorized users can create a server provider and remains on server providers page if the credentials are valid', function () {
    $this->expectsJobs([
            AddSecureShellKeyToServerProvider::class,
            IndexServerProviderPlans::class,
            IndexServerProviderRegions::class,
            IndexServerProviderImages::class,
        ]);

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
    ]);

    $token = Token::factory()
        ->withNetwork(1)
        ->createForTest();

    $token->update(['onboarded_at' => Carbon::now()]);

    $count = ServerProvider::all()->count();

    expect(ServerProvider::all()->count())->toBe($count);

    Livewire::actingAs($token->user)
            ->test(ManageServerProviders::class, ['token' => $token])
            ->assertSet('provider', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('type', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('name', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('access_token', 'valid_token')
            ->call('store')
            ->assertSee(trans('tokens.server-providers.added_success_redirect'));

    expect(ServerProvider::all()->count())->toBe($count + 1);
});

it('should not dispatch the AddSecureShellKeyToServerProvider job if there is an existing provider with the same type', function () {
    $this->doesntExpectJobs(AddSecureShellKeyToServerProvider::class);

    Http::fake([
        'hetzner.cloud/*' => Http::sequence()
            ->push($this->fixture('hetzner/regions'), 200, [])
            ->push($this->fixture('hetzner/ssh-keys-uniqueness-error'), 200, [])
            ->pushStatus(409),
    ]);

    $token = Token::factory()->createForTest();
    $token->update(['onboarded_at' => Carbon::now()]);

    $serverProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();

    $count = ServerProvider::all()->count();

    Livewire::actingAs($token->user)
        ->test(ManageServerProviders::class, ['token' => $token])
        ->assertSet('provider', ServerProviderTypeEnum::DIGITALOCEAN)
        ->call('selectProvider', ServerProviderTypeEnum::HETZNER)
        ->assertSet('provider', ServerProviderTypeEnum::HETZNER)
        ->set('type', ServerProviderTypeEnum::HETZNER)
        ->set('name', ServerProviderTypeEnum::HETZNER)
        ->set('access_token', 'valid_token')
        ->call('store')
        ->assertSee(trans('tokens.server-providers.added_success'));

    $latestServerProvider = ServerProvider::latest()->first();

    expect(ServerProvider::all()->count())->toBe($count + 1);

    $this->assertDatabaseHas('server_providers', [
        'id'              => $latestServerProvider->id,
        'name'            => $latestServerProvider->name,
        'provider_key_id' => $serverProvider->provider_key_id,
    ]);
});

it('triggers the server provider event when is created in the server manager', function () {
    Event::fake();

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/account'), 200, []),
    ]);

    $token = Token::factory()->withServers(0)->createForTest();

    Livewire::actingAs($token->user)
            ->test(ManageServerProviders::class, ['token' => $token])
            ->assertSet('provider', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('type', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('name', ServerProviderTypeEnum::DIGITALOCEAN)
            ->set('access_token', 'valid_token')
            ->call('store')
            ->assertSee(trans('tokens.server-providers.added_success_redirect'));

    Event::assertDispatched(ServerProviderCreated::class);
});
