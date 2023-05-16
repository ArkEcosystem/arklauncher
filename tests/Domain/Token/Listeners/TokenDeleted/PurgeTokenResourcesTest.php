<?php

declare(strict_types=1);

use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Listeners\TokenDeleted\PurgeTokenResources;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Event;

it('remove server providers, servers, networks, invitations and statuses when deleted', function () {
    $token = Token::factory()
        ->withServers(1)
        ->withServerProviders(1)
        ->withDefaultNetworks()
        ->withInvitations(1)
        ->withNetwork(1)
        ->withStatus(TokenStatusEnum::PENDING)
        ->createForTest();

    expect($token->serverProviders()->exists())->toBeTrue();
    expect($token->servers()->exists())->toBeTrue();
    expect($token->networks()->exists())->toBeTrue();
    expect($token->invitations()->exists())->toBeTrue();
    expect($token->statuses()->exists())->toBeTrue();

    (new PurgeTokenResources())->handle(new TokenDeleted($token));

    expect($token->serverProviders()->exists())->toBeFalse();
    expect($token->servers()->exists())->toBeFalse();
    expect($token->networks()->exists())->toBeFalse();
    expect($token->invitations()->exists())->toBeFalse();
    expect($token->statuses()->exists())->toBeFalse();
});

it('triggers the network deleted event for every network deleted within this task', function () {
    Event::fake();

    $token = Token::factory()->withNetwork(2)->createForTest();

    expect($token->networks()->count())->toBe(2);

    (new PurgeTokenResources())->handle(new TokenDeleted($token));

    expect($token->networks()->count())->toBe(0);
});

it('triggers the server deleted event for every server deleted within this task', function () {
    Event::fake();

    $token = Token::factory()->withNetwork(1)->withServers(2)->createForTest();

    $servers = $token->servers;

    expect($servers->count())->toBe(2);

    (new PurgeTokenResources())->handle(new TokenDeleted($token));

    Event::assertDispatched(ServerDeleted::class, 2);
});
