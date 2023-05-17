<?php

declare(strict_types=1);

use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;

it('guests can not view their server providers', function () {
    $this
            ->get(route('tokens.server-providers', $this->token()))
            ->assertRedirect(route('login'));
});

it('users can view their server providers', function () {
    $token = Token::factory()->withOnboardingConfigurationCompleted()->create();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.server-providers', $token))
            ->assertViewIs('app.tokens.server-providers');
});

it('a user can view their server providers', function () {
    $token = Token::factory()->withOnboardingConfigurationCompleted()->create();

    $provider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.server-providers', $provider->token))
            ->assertSee($provider->name);
});

it('redirects user to tokens page if step not available', function () {
    $token = Token::factory()->create();

    $provider = ServerProvider::factory()->ownedBy($token)->createForTest();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.server-providers', $provider->token))
            ->assertRedirect(route('tokens.show', $token));
});

it('doesnt redirects user to tokens page if step not available but token cannot be edited', function () {
    $token = Token::factory()->withServers(0)->withNetwork(1)->createForTest();

    $token->networks()->each(fn ($network) => Server::factory()->ownedBy($network)->createForTest());

    $provider = ServerProvider::factory()->ownedBy($token)->createForTest();

    expect($token->fresh()->canBeEdited())->toBeFalse();

    $this
        ->actingAs($token->user)
        ->get(route('tokens.server-providers', $provider->token))
        ->assertViewIs('app.tokens.server-providers');
});
