<?php

declare(strict_types=1);

use Domain\Coin\Models\Coin;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\TokenCreated;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Event;

it('guests may not view the onboarding page', function () {
    $token = $this->token();

    $this
            ->get(route('tokens.welcome', $token))
            ->assertRedirect('login');
});

it('users may view the onboarding page', function () {
    $token = $this->token();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.welcome', $token))
            ->assertViewIs('app.tokens.onboard');
});

it('users are redirected to onboarding when creating token', function () {
    $token = $this->token();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.create', $token))
            ->assertViewIs('app.tokens.onboard');
});

it('redirects user to tokens details page if token cannot be edited', function () {
    $token = Token::factory()->withServers(0)->withNetwork(1)->createForTest();

    $token->networks()->each(fn ($network) => Server::factory()->ownedBy($network)->createForTest());

    expect($token->fresh()->canBeEdited())->toBeFalse();

    $this
        ->actingAs($token->user)
        ->get(route('tokens.welcome', $token))
        ->assertRedirect(route('tokens.show', $token));
});

it('a user can complete the onboarding if the requirements are fulfilled', function () {
    ServerProvider::flushEventListeners();

    $token = Token::factory()->withOnboardingSecureShellKey()->create();

    $this->assertDatabaseHas('tokens', [
        'id'           => $token->id,
        'onboarded_at' => null,
    ]);

    $this
            ->actingAs($token->user)
            ->get(route('tokens.welcome.complete', $token))
            ->assertRedirect(route('tokens', $token));

    $this->assertNotNull($token->fresh()->onboarded_at);
});

it('a user cannot complete the onboarding if the requirements are not fulfilled', function () {
    ServerProvider::flushEventListeners();

    $token = $this->token();

    $this->assertDatabaseHas('tokens', [
        'id'           => $token->id,
        'onboarded_at' => null,
    ]);

    $this
            ->actingAs($token->user)
            ->get(route('tokens.welcome.complete', $token))
            ->assertForbidden();

    $this->assertDatabaseHas('tokens', [
        'id'           => $token->id,
        'onboarded_at' => null,
    ]);
});

it('triggers the created event when a token is creating in onboarding', function () {
    Event::fake();

    Coin::factory()->create();

    $user = $this->user();

    $this
            ->actingAs($user)
            ->get(route('tokens.create'))
            ->assertViewIs('app.tokens.onboard');

    Event::assertDispatched(fn (TokenCreated $event) => $event->token->user->is($user));
});
