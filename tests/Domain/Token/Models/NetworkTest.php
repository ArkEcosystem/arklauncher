<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use Domain\Server\Models\Server;
use Domain\Token\Events\NetworkCreated;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->token = Token::factory()->withNetwork(1)->createForTest();
});

it('belongs to a token', function () {
    $token = $this->token;

    $network = $token->networks->first();

    expect($network->token())->toBeInstanceOf(BelongsTo::class);
});

it('has servers', function () {
    $token = $this->token;

    $network = $token->networks->first();

    expect($network->servers())->toBeInstanceOf(HasMany::class);
});

it('has to be a mainnet', function () {
    $token = $this->token;

    $network = $token->networks->first();

    expect($network->name(NetworkTypeEnum::MAINNET))->toBeInstanceOf(Network::class);
});

it('can determine if a network has a genesis node', function () {
    $network = Network::factory()->createForTest();

    expect($network->hasGenesis())->toBeFalse();

    Server::factory()->ownedBy($network)->createForTest();

    expect($network->fresh()->hasGenesis())->toBeFalse();

    Server::factory()->ownedBy($network)->createForTest(['preset' => 'genesis']);

    expect($network->fresh()->hasGenesis())->toBeTrue();
});

it('can determine if a network has a provisioned genesis node', function () {
    $network = Network::factory()->createForTest();

    expect($network->hasProvisionedGenesis())->toBeFalse();

    $server = Server::factory()->ownedBy($network)->createForTest(['preset' => 'genesis']);

    expect($network->fresh()->hasProvisionedGenesis())->toBeFalse();

    $server->touch('provisioned_at');
    $server->setStatus('online');

    expect($network->fresh()->hasProvisionedGenesis())->toBeTrue();
});

it('can retrieve the genesis node', function () {
    $network = Network::factory()->createForTest();

    expect($network->hasGenesis())->toBeFalse();

    Server::factory()->ownedBy($network)->createForTest();

    expect($network->fresh()->hasGenesis())->toBeFalse();

    $genesisServer = Server::factory()->ownedBy($network)->createForTest(['preset' => 'genesis']);

    expect($network->fresh()->hasGenesis())->toBeTrue();

    expect($genesisServer->id)->toBe($network->fresh()->getGenesis()->id);
});

it('can get the path to show', function () {
    expect(Network::factory()->createForTest()->pathShow())->toBeString();
});

it('should return the correct base58 prefix', function () {
    $token   = Token::factory()->create();
    $mainnet = Network::factory()->ownedBy($token)->create(['name' => NetworkTypeEnum::MAINNET]);
    $devnet  = Network::factory()->ownedBy($token)->create(['name' => NetworkTypeEnum::DEVNET]);
    $testnet = Network::factory()->ownedBy($token)->create(['name' => NetworkTypeEnum::TESTNET]);

    // Check to see if the default values in the factory didn't change
    expect($token->config['mainnetPrefix'])->toBe('M');
    expect($token->config['devnetPrefix'])->toBe('D');
    expect($token->config['testnetPrefix'])->toBe('T');

    expect($mainnet->base58Prefix())->toBe(50);
    expect($devnet->base58Prefix())->toBe(30);
    expect($testnet->base58Prefix())->toBe(65);
});

it('dispatches an event when created', function () {
    Event::fake([NetworkCreated::class]);

    $network = Network::factory()->create();

    Event::assertDispatched(NetworkCreated::class, fn ($event) => $event->network->is($network));
});
