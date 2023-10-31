<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use App\Token\Components\DeployBlockchain;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\ServerCreated;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

beforeEach(function () {
    $this->token = Token::factory()
        ->withNetwork(1)
        ->createForTest();
});

it('can ask for confirmation and set the token id', function () {
    $token = $this->token;

    Livewire::test(DeployBlockchain::class, compact('token'))
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('modalShown', true)
            ->assertSet('tokenId', $token->id);
});

it('deploy method should call the right actions', function () {
    $token = $this->token;
    $token->setStatus(TokenStatusEnum::FINISHED);

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
        ]);

    $plan = ServerProviderPlan::factory()->create([
            'regions'            => [$region->uuid],
        ]);

    $image = ServerProviderImage::factory()->create([
            'uuid'               => 'ubuntu-22-04-x64',
        ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
            'server_provider_id'        => $serverProvider->id,
            'name'                      => 'test-token',
            'server_provider_region_id' => $region->id,
            'server_provider_plan_id'   => $plan->id,
        ]);

    $network = $token->networks()->where('name', NetworkTypeEnum::MAINNET)->firstOrFail();

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::test(DeployBlockchain::class, ['token' => $token])
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', NetworkTypeEnum::MAINNET)
            ->assertSet('selectedOption', NetworkTypeEnum::MAINNET)
            ->call('deploy')
            ->assertRedirect(route('tokens.servers.show', [
                $token,
                $network,
                $network->servers()->orderByDesc('id')->first(),
            ]));
});

it('can cancel the confirmation', function () {
    $token = $this->token;

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::test(DeployBlockchain::class, compact('token'))
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('cancel')
            ->assertSet('tokenId', null);

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);
});

it('can select an option', function () {
    $token = $this->token;
    $token->setStatus(TokenStatusEnum::FINISHED);

    Livewire::test(DeployBlockchain::class, compact('token'))
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', NetworkTypeEnum::DEVNET)
            ->assertSet('selectedOption', NetworkTypeEnum::DEVNET);
});

it('cant unselect an option', function () {
    $token = $this->token;
    $token->setStatus(TokenStatusEnum::FINISHED);

    Livewire::test(DeployBlockchain::class, compact('token'))
            ->assertSet('tokenId', null)
            ->call('askForConfirmation', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('selectOption', NetworkTypeEnum::MAINNET)
            ->assertSet('selectedOption', NetworkTypeEnum::MAINNET)
            ->call('selectOption', NetworkTypeEnum::MAINNET)
            ->assertSet('selectedOption', NetworkTypeEnum::MAINNET);
});

it('should trigger the server created event when created on deploy', function () {
    Event::fake();
    $token = $this->token;
    $token->setStatus(TokenStatusEnum::FINISHED);

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create([
        ]);

    $plan = ServerProviderPlan::factory()->create([
            'regions'            => [$region->uuid],
        ]);

    $image = ServerProviderImage::factory()->create([
            'uuid'               => 'ubuntu-22-04-x64',
        ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
        'server_provider_id'        => $serverProvider->id,
        'name'                      => 'test-token',
        'server_provider_region_id' => $region->id,
        'server_provider_plan_id'   => $plan->id,
    ]);

    Livewire::test(DeployBlockchain::class, ['token' => $token])
            ->call('askForConfirmation', $token->id)
            ->call('selectOption', 'mainnet')
            ->call('deploy');

    Event::assertDispatched(ServerCreated::class);
});

it('appends unique token for the server', function () {
    Event::fake();
    $token = $this->token;
    $token->setStatus(TokenStatusEnum::FINISHED);

    $this->actingAs($token->user);

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $region = ServerProviderRegion::factory()->create();

    $plan = ServerProviderPlan::factory()->create([
        'regions' => [$region->uuid],
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid' => 'ubuntu-22-04-x64',
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
        'server_provider_id'        => $serverProvider->id,
        'name'                      => 'test-token',
        'server_provider_region_id' => $region->id,
        'server_provider_plan_id'   => $plan->id,
    ]);

    Livewire::test(DeployBlockchain::class, ['token' => $token])
            ->call('askForConfirmation', $token->id)
            ->call('selectOption', 'mainnet')
            ->call('deploy');

    $server = Server::latest('id')->first();
    expect($server->preset)->toBe('genesis');
    expect($server->name)->toMatch('/^test-token-[a-zA-Z0-9]{8}$/');
});
