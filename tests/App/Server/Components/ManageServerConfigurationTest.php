<?php

declare(strict_types=1);

use App\Server\Components\ManageServerConfiguration;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Models\Token;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Support\Services\Haiku;

it('can store data', function () {
    $token          = Token::factory()->withServers(0)->createForTest();
    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $regionString = ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
        'name'               => 'test-region',
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

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
        'name'                      => 'test-token',
        'server_provider_region_id' => $regionString->id,
        'server_provider_plan_id'   => $planString->id,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($planString);
    $serverProvider->regions()->syncWithoutDetaching($regionString);
    $serverProvider->images()->syncWithoutDetaching($image);

    $this->actingAs($token->user);

    $serverName = Haiku::name();

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->call('selectProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('region', $regionString->id)
        ->assertSet('region', $regionString->id)
        ->set('plan', $planString->id)
        ->assertSet('plan', $planString->id)
        ->call('store')
        ->assertRedirect(route('tokens.show', compact('token')));

    $token = Token::findOrFail($token->id);
});

it('can store data if there is already a fully indexed serverProvider on the token', function () {
    $token          = Token::factory()->withServers(0)->withServerProviders(1)->createForTest();

    $serverProvider = $token->serverProviders->first();

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

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->set('serverName', 'test-server')
        ->call('selectProvider', $serverProvider->id)
        ->set('region', $region->id)
        ->set('plan', $plan->id)
        ->call('store')
        ->assertRedirect(route('tokens.show', compact('token')));

    $token = Token::findOrFail($token->id);
});

it('should load meta config if needed', function () {
    $token          = Token::factory()->withServers(0)->createForTest();
    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $regionString = ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
        'name'               => 'test-region',
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

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
        'server_provider_id'        => $serverProvider->id,
        'name'                      => 'test-token',
        'server_provider_region_id' => $regionString->id,
        'server_provider_plan_id'   => $planString->id,
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($planString);
    $serverProvider->regions()->syncWithoutDetaching($regionString);
    $serverProvider->images()->syncWithoutDetaching($image);

    $this->actingAs($token->user);

    $token->refresh(); // Ensure relationship counts are updated.

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->assertSet('serverName', 'test-token')
        ->assertSee($regionString->name)
        ->assertSee("{$planString->ram} RAM [{$planString->uuid}]");
});

it('can get plans by string uuid', function () {
    $token          = Token::factory()->withServers(0)->createForTest();
    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

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

    $this->actingAs($token->user);

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->call('selectProvider', $serverProvider->id)
        ->set('region', $regionString->id)
        ->assertSet('region', $regionString->id)
        ->assertSee("{$planString->ram} RAM [{$planString->uuid}]")
        ->assertDontSee("{$planInteger->ram} RAM [{$planInteger->uuid}]");
});

it('can get plans by int uuid', function () {
    $token          = Token::factory()->withServers(0)->createForTest();
    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

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

    $this->actingAs($token->user);

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->call('selectProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('region', $regionInteger->id)
        ->assertSet('region', $regionInteger->id)
        ->assertSee("{$planInteger->ram} RAM [{$planInteger->uuid}]")
        ->assertDontSee("{$planString->disk} RAM [{$planString->uuid}]");
});

it('can get plans for providers without regions', function () {
    $token          = Token::factory()->withServers(0)->createForTest();
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

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->call('selectProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->assertSee("{$plan->ram} RAM [{$plan->uuid}]");
});

it('can cancel server creation', function () {
    $token          = Token::factory()->withServers(0)->createForTest();
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

    $this->actingAs($token->user);

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->call('cancel')
        ->assertRedirect(route('tokens.show', compact('token')));
});

it('step should be disabled if no server providers', function () {
    $token = Token::factory()->withServers(0)->createForTest();
    $this->actingAs($token->user);

    expect($token->hasServerProviders())->toBefalse();

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->assertDontSee('Plans')
        ->assertDontSee('Regions');
});

it('should return true if the user can select a region or a plan', function () {
    $token = Token::factory()->withServers(0)->createForTest();

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

    $realComponent = new ManageServerConfiguration('1');
    $realComponent->mount($token);

    expect($realComponent->canSelect())->toBeFalse();

    $realComponent->selectProvider($serverProvider->id);
    $realComponent->selectedProviderSelectedKey = $serverProvider->id;

    expect($realComponent->canSelect())->toBeTrue();
});

it('should return a Collection for plans and regions', function () {
    $token = Token::factory()->withServers(0)->createForTest();

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

    $realComponent = new ManageServerConfiguration('1');
    $realComponent->mount($token);

    expect($realComponent->plans)->toBeInstanceOf(Collection::class);
    expect($realComponent->regions)->toBeInstanceOf(Collection::class);
});

it('should set the selectedProviderSelectedKey property to null when selecting a new provider', function () {
    $token          = Token::factory()->withServers(0)->createForTest();

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

    Livewire::test(ManageServerConfiguration::class, compact('token'))
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

it('should be able to submit if all the conditions are met', function () {
    $token = Token::factory()->withServers(0)->createForTest();

    $serverProvider = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
        'uuid'               => 'string',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'regions'            => [],
        'memory'             => 4096,
    ]);

    ServerProviderImage::factory()->create([
        'uuid'               => $serverProvider->client()->getImageId(),
    ]);

    $this->actingAs($token->user);

    $realComponent = new ManageServerConfiguration('1');
    $realComponent->mount($token);

    expect($realComponent->canSubmit())->toBeFalse();

    $realComponent->serverName =  Haiku::name();
    $realComponent->region     = $region->id;
    $realComponent->plan       = $plan->id;

    expect($realComponent->canSubmit())->toBeTrue();
});

it('should return a collection of providers matching the given type', function () {
    $token = Token::factory()->withServers(0)->createForTest();

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

    $realComponent = new ManageServerConfiguration('1');
    $realComponent->mount($token);

    $realComponent->selectProvider($serverProvider->id);

    expect($realComponent->providerEntries)->toBeInstanceOf(Collection::class);
    expect($realComponent->providerEntries->count())->toBe(2);
});

it('should return a collection of unique providers', function () {
    $token = Token::factory()->withServers(0)->createForTest();

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

    $realComponent = new ManageServerConfiguration('1');
    $realComponent->mount($token);

    $realComponent->selectProvider($serverProvider->id);

    expect($realComponent->getUniqueProviders())->toBeInstanceOf(Collection::class);
    expect($realComponent->getUniqueProviders()->count())->toBe(2);
    expect($realComponent->hasMultipleKeysOnProvider)->toBeTrue();
});

it('should be able to pick a server provider from the list of providers if there is more than one provider for the same type', function () {
    $token          = Token::factory()->withServers(0)->createForTest();

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

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->emit('setProvider', $serverProvider->id)
        ->assertSet('selectedProvider.id', $serverProvider->id)
        ->set('serverName', $serverName)
        ->assertSet('serverName', $serverName)
        ->assertSee(trans('forms.create_server.select_server_provider'))
        ->set('selectedProviderSelectedKey', $secondServerProvider->id)
        ->assertSet('selectedProvider.id', $secondServerProvider->id);
});

it('should reset the plan and region selection on provider change', function () {
    $token          = Token::factory()->withServers(0)->createForTest();

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

    Livewire::test(ManageServerConfiguration::class, compact('token'))
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

it('cant select a plan without having selected a region first', function () {
    $token          = Token::factory()->withServers(0)->createForTest();
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

    Livewire::test(ManageServerConfiguration::class, compact('token'))
        ->assertSee(trans('actions.select_region'))
        ->assertSee(trans('actions.select_plan'))
        ->assertDontSee($plan->uuid)
        ->set('region', $region->id)
        ->assertSet('region', $region->id)
        ->assertSee($plan->uuid)
        ->set('plan', $plan->id)
        ->assertSet('plan', $plan->id);
});
