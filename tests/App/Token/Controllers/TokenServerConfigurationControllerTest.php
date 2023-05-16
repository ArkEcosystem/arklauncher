<?php

declare(strict_types=1);

use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Models\Token;

it('can view the form to set genesis server configuration', function () {
    $token = Token::factory()->withOnboardingServerProvider()->create();

    $serverProvider = ServerProvider::factory()->ownedBy($token)->createForTest();

    ServerProviderRegion::factory()->create([
            'uuid'               => 'string',
        ]);

    ServerProviderPlan::factory()->create([
            'uuid'               => 'ccx21',
        ]);

    ServerProviderImage::factory()->create([
            'uuid'               => $serverProvider->first()->client()->getImageId(),
        ]);

    $this
            ->actingAs($token->user)
            ->get(route('tokens.server-configuration', [$token]))
            ->assertViewIs('app.tokens.server-configuration');
});

it('redirects user to tokens page if step not available', function () {
    $token = Token::factory()->withOnboardingConfigurationCompleted()->create();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.server-configuration', [$token]))
            ->assertRedirect(route('tokens.show', $token));
});

it('redirects user to server page if not editable', function () {
    $token = Token::factory()->withServers(0)->withNetwork(1)->createForTest();

    $token->networks()->each(fn ($network) => Server::factory()->ownedBy($network)->createForTest());

    expect($token->fresh()->canBeEdited())->toBeFalse();

    $this
        ->actingAs($token->user)
        ->get(route('tokens.server-configuration', [$token]))
        ->assertRedirect(route('tokens.servers.index', $token));
});
