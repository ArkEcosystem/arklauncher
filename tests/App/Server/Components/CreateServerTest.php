<?php

declare(strict_types=1);

use App\Server\Components\CreateServer;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Support\Services\Haiku;

it('can store a new server on digitalocean', function () {
    $token = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [$region->uuid],
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => 'ubuntu-18-04-x64',
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->assertDatabaseMissing('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_image_id' => $image->id]);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id)
        ->call('store');

    $this->assertDatabaseHas('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseHas('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseHas('servers', ['server_provider_image_id' => $image->id]);
});

it('can not store a server without correct permissions', function () {
    $token = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [$region->uuid],
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => 'ubuntu-18-04-x64',
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $this->assertDatabaseMissing('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_image_id' => $image->id]);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id)
        ->call('store')
        ->assertForbidden();

    $this->assertDatabaseMissing('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_image_id' => $image->id]);
});

it('can store a new server on linode', function () {
    $token = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->linode()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [$region->uuid],
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => 'linode/ubuntu18.04',
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->assertDatabaseMissing('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_image_id' => $image->id]);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id)
        ->call('store');

    $this->assertDatabaseHas('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseHas('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseHas('servers', ['server_provider_image_id' => $image->id]);
});

it('can store a new server on hetzner', function () {
    $token = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [$region->uuid],
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => 'ubuntu-18.04',
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->assertDatabaseMissing('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_image_id' => $image->id]);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id)
        ->call('store');

    $this->assertDatabaseHas('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseHas('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseHas('servers', ['server_provider_image_id' => $image->id]);
});

it('can store a new server on vultr', function () {
    $token = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->vultr()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [$region->uuid],
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => 270,
        'name'               => 'Ubuntu 18.04 x64',
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->assertDatabaseMissing('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseMissing('servers', ['server_provider_image_id' => $image->id]);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id)
        ->call('store');

    $this->assertDatabaseHas('servers', ['server_provider_region_id' => $region->id]);
    $this->assertDatabaseHas('servers', ['server_provider_plan_id' => $plan->id]);
    $this->assertDatabaseHas('servers', ['server_provider_image_id' => $image->id]);
});

it('can get plans by string uuid', function () {
    $token          = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();
    $serverProvider = $token->serverProviders()->first();

    $regionString = ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderRegion::factory()->create([
        'uuid'               => 1,
    ]);

    $planString = ServerProviderPlan::factory()->create([
        'regions'            => ['string'],
        'memory'             => 4096,
    ]);

    $planInteger = ServerProviderPlan::factory()->create([
        'regions'            => [1],
        'memory'             => 4096,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($planString);
    $serverProvider->regions()->syncWithoutDetaching($regionString);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $regionString->id)
        ->assertSet('region', $regionString->id)
        ->set('plan', $planString->id)
        ->assertSee("{$planString->formatted_memory} RAM [{$planString->uuid}]")
        ->assertDontSee("{$planInteger->formatted_memory} RAM [{$planInteger->uuid}]");
});

it('can get plans by int uuid', function () {
    $token          = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();
    $serverProvider = $token->serverProviders()->first();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    $regionInteger = ServerProviderRegion::factory()->create([
        'uuid'               => 1,
    ]);

    $planString = ServerProviderPlan::factory()->create([
        'regions'            => ['string'],
        'memory'             => 4096,
    ]);

    $planInteger = ServerProviderPlan::factory()->create([
        'regions'            => [1],
        'memory'             => 4096,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($planInteger);
    $serverProvider->regions()->syncWithoutDetaching($regionInteger);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $regionInteger->id)
        ->assertSet('region', $regionInteger->id)
        ->assertSee("{$planInteger->formatted_memory} RAM [{$planInteger->uuid}]")
        ->assertDontSee("{$planString->formatted_memory} RAM [{$planString->uuid}]");
});

it('can get plans for providers without regions', function () {
    $token          = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();
    $serverProvider = $token->serverProviders()->first();

    $region = ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->assertSee("{$plan->formatted_memory} RAM [{$plan->uuid}]");
});

it('can cancel server creation', function () {
    $token          = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();
    $serverProvider = $token->serverProviders()->first();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->call('cancel')
        ->assertRedirect(route('tokens'));
});

it('can select a preset', function () {
    $token          = Token::factory()->withNetwork(1)->withServers(0)->createForTest();
    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverName = Haiku::name();

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->assertSet('preset', 'genesis')
        ->call('selectPreset', 'forger')
        ->assertSet('preset', 'forger');
});

it('cant select a plan without having selected a region first', function () {
    $token          = Token::factory()->withNetwork(1)->withServers(0)->createForTest();
    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->assertSet('preset', 'genesis')
        ->call('selectPreset', 'forger')
        ->assertSet('preset', 'forger')
        ->assertSee(trans('actions.select_region'))
        ->assertSee(trans('actions.select_plan'))
        ->assertDontSee($plan->uuid)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->assertSee($plan->uuid)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id);
});

it('should reset the plan and region selection on provider change', function () {
    $token                = Token::factory()->withNetwork(1)->withServers(0)->createForTest();
    $serverProvider       = ServerProvider::factory()->ownedBy($token)->createForTest();
    $secondServerProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id)
        ->emit('setProvider', $secondServerProvider->id)
        ->assertSet('selectedProvider.id', $secondServerProvider->id)
        ->assertSet('plan', null)
        ->assertSet('region', null);
});

it('should be able to pick a server provider from the list of providers if there is more than one provider for the same type', function () {
    $token          = Token::factory()->withNetwork(1)->withServers(0)->createForTest();

    $serverProvider       = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    $secondServerProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverName = Haiku::name();

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->assertSee(trans('forms.create_server.select_server_provider'))
        ->set('selectedProviderSelectedKey', $secondServerProvider->id)
        ->assertSet('selectedProvider.id', $secondServerProvider->id);
});

it('should return a collection of unique providers', function () {
    $token          = Token::factory()->withNetwork(1)->withServers(0)->createForTest();

    $serverProvider       = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    ServerProvider::factory()->digitalocean()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $this->actingAs($token->user);

    $realComponent = new CreateServer('1');
    $realComponent->mount($token->networks()->first());

    $realComponent->selectProvider($serverProvider->id);

    expect($realComponent->getUniqueProviders())->toBeInstanceOf(Collection::class);
    expect($realComponent->getUniqueProviders()->count())->toBe(2);
    expect($realComponent->hasMultipleKeysOnProvider)->toBeTrue();
});

it('should return a collection of providers matching the given type', function () {
    $token          = Token::factory()->withNetwork(1)->withServers(0)->createForTest();

    $serverProvider       = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    ServerProvider::factory()->digitalocean()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $this->actingAs($token->user);

    $realComponent = new CreateServer('1');
    $realComponent->mount($token->networks()->first());

    $realComponent->selectProvider($serverProvider->id);

    expect($realComponent->providerEntries)->toBeInstanceOf(Collection::class);
    expect($realComponent->providerEntries->count())->toBe(2);
});

it('should return true if the user can select a region or a plan', function () {
    $token = Token::factory()->withNetwork(1)->withServers(0)->createForTest();

    $serverProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $this->actingAs($token->user);

    $realComponent = new CreateServer('1');
    $realComponent->mount($token->networks()->first());

    expect($realComponent->canSelect())->toBeFalse();

    $realComponent->selectProvider($serverProvider->id);
    $realComponent->selectedProviderSelectedKey = $serverProvider->id;

    expect($realComponent->canSelect())->toBeTrue();
});

it('should return a Collection for plans and regions', function () {
    $token = Token::factory()->withNetwork(1)->withServers(0)->createForTest();

    $serverProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $this->actingAs($token->user);

    $realComponent = new CreateServer('1');
    $realComponent->mount($token->networks()->first());

    expect($realComponent->plans)->toBeInstanceOf(Collection::class);
    expect($realComponent->regions)->toBeInstanceOf(Collection::class);
});

it('should set the selectedProviderSelectedKey property to null when selecting a new provider', function () {
    $token          = Token::factory()->withNetwork(1)->withServers(0)->createForTest();

    $serverProvider       = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    $secondServerProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    $thirdServerProvider  = ServerProvider::factory()->digitalocean()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverName = Haiku::name();

    $this->actingAs($token->user);

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->assertSee(trans('forms.create_server.select_server_provider'))
        ->set('selectedProviderSelectedKey', $secondServerProvider->id)
        ->assertSet('selectedProvider.id', $secondServerProvider->id)
        ->emit('setProvider', $thirdServerProvider->id)
        ->assertSet('selectedProviderSelectedKey', null);
});

it('should return an array of presets', function () {
    $token = Token::factory()->withNetwork(1)->withServers(0)->createForTest();

    $serverProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $this->actingAs($token->user);

    $realComponent = new CreateServer('1');
    $realComponent->mount($token->networks()->first());

    expect($realComponent->presets)->toBeArray();
    expect($realComponent->presets)->toHaveCount(4);
});

it('should trigger the server created event when the server is created', function () {
    Event::fake();

    $token = Token::factory()->withNetwork(1)->withServerProviders(3)->withDefaultNetworks()->createForTest();

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [$region->uuid],
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => 'ubuntu-18-04-x64',
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $serverName = Haiku::name();

    Livewire::test(CreateServer::class, ['network' => $token->networks()->first()])
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id)
        ->call('store');

    Event::assertDispatched(ServerCreated::class);
});
