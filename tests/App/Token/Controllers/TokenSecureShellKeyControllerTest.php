<?php

declare(strict_types=1);

use Domain\Server\Models\Server;
use Domain\Token\Models\Token;

it('a guest cannot view the secure shell keys page of a token', function () {
    $token = $this->token();

    $this
        ->get(route('tokens.ssh-keys', $token))
        ->assertRedirect('login');
});

it('a user cannot view the secure shell keys page of another token', function () {
    $token = $this->token();

    $this
        ->actingAs($this->user())
        ->get(route('tokens.ssh-keys', $token))
        ->assertForbidden();
});

it('redirects user to tokens page if step not available and token can be edited', function () {
    $token = Token::factory()->withOnboardingServerProvider()->create();

    expect($token->fresh()->canBeEdited())->toBeTrue();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.ssh-keys', $token))
            ->assertRedirect(route('tokens.show', $token));
});

it('doesnt redirects user to tokens page if step not available but token cannot be edited', function () {
    $token = Token::factory()->withServers(0)->withNetwork(1)->createForTest();

    $token->networks()->each(fn ($network) => Server::factory()->ownedBy($network)->createForTest());

    expect($token->fresh()->canBeEdited())->toBeFalse();

    $this
        ->actingAs($token->user)
        ->get(route('tokens.ssh-keys', $token))
        ->assertViewIs('app.tokens.secure-shell-keys');
});

it('users may view tokens if step available and own the token', function () {
    $token = Token::factory()->withOnboardingServerConfiguration()->create();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.ssh-keys', $token))
            ->assertViewIs('app.tokens.secure-shell-keys');
});
