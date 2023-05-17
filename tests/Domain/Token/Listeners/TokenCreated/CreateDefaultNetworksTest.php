<?php

declare(strict_types=1);

use Domain\Token\Events\NetworkCreated;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Listeners\TokenCreated\CreateDefaultNetworks;
use Illuminate\Support\Facades\Event;

it('creates the mainet, devnet and testnet networks', function () {
    $token = $this->token();

    expect($token->networks()->exists())->toBeFalse();

    (new CreateDefaultNetworks())->handle(new TokenCreated($token));

    $expectedNetworks = collect(['mainnet', 'devnet', 'testnet']);
    $expectedNetworks->each(fn ($networkName) => expect($token->networks()->whereName($networkName)->count() === 1)->toBeTrue());
});

it('triggers the network created event when a network is created in this task', function () {
    Event::fake();

    $token = $this->token();

    expect($token->networks()->exists())->toBeFalse();

    (new CreateDefaultNetworks())->handle(new TokenCreated($token));

    Event::assertDispatched(NetworkCreated::class, 3);
});
