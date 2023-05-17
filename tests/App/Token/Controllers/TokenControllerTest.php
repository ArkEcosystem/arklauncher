<?php

declare(strict_types=1);

use Domain\Coin\Models\Coin;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;

beforeEach(function () {
    $this->token = Token::factory()
        ->withStatus(TokenStatusEnum::FINISHED)
        ->createForTest();

    $this->tokenWithData = Token::factory([
        'onboarded_at' => now(),
    ])
        ->withNetwork(1)
        ->withServers(1)
        ->createForTest();
});

it('guests may not view tokens', function () {
    $this->get('/app/tokens')->assertRedirect('login');
});

it('users may view tokens', function () {
    $this
        ->actingAs($this->user())
        ->get('/app/tokens')
        ->assertViewIs('app.tokens.index');
});

it('a user can view the onboard checklist', function () {
    Coin::factory()->create();

    $this
            ->actingAs($this->user())
            ->get('/app/tokens/new')
            ->assertViewIs('app.tokens.onboard');
});

it('a user can view the manage form to edit tokens', function () {
    $token = $this->token;
    $token->setStatus(TokenStatusEnum::FINISHED);

    $token->update(['onboarded_at' => now()]);

    $this
            ->actingAs($token->user)
            ->get(route('tokens.edit', $token))
            ->assertViewIs('app.tokens.edit');
});

it('a user can view the manage form to edit new tokens', function () {
    $token = $this->token;

    $token->update(['onboarded_at' => now()]);

    $this
            ->actingAs($token->user)
            ->get(route('tokens.edit', $token))
            ->assertViewIs('app.tokens.edit');
});

it('a users cant view the manage form to edit if it has data', function () {
    $token = $this->tokenWithData;

    $this
            ->actingAs($token->user)
            ->get(route('tokens.edit', $token))
            ->assertRedirect(route('tokens.show', [$token]));
});

it('guests may not view a single token', function () {
    $token = $this->token;

    $this->get('/app/tokens/'.$token->id)->assertRedirect('login');
});

it('a user can view a token', function () {
    $token = $this->tokenWithData;

    $this
            ->actingAs($token->user)
            ->get(route('tokens.show', $token))
            ->assertRedirect(route('tokens.details', [$token]));
});

it('should obfuscate the token ID', function () {
    $token = $this->tokenWithData;

    expect($token->getRouteKey())->not()->toBe($token->id);
    expect($token->getRouteKey())->toBe(app('fakeid')->encode($token->id));

    $this
        ->actingAs($token->user)
        ->get(route('tokens.show', $token))
        ->assertRedirect(route('tokens.details', [$token]));
});
