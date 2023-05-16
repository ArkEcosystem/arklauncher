<?php

declare(strict_types=1);

use Domain\Token\Models\Token;

beforeEach(function () {
    $this->token = Token::factory()
        ->withNetwork(1)
        ->withDefaultNetworks(1)
        ->createForTest();
});

it('a guest cannot view the details page of a token', function () {
    $token = $this->token;

    $this
        ->get(route('tokens.details', $token))
        ->assertRedirect('login');
});

it('a user can view the details page of their own token', function () {
    $token = $this->token;

    $this
        ->actingAs($token->user)
        ->get(route('tokens.details', $token))
        ->assertSuccessful()
        ->assertViewIs('app.tokens.details');
});

it('a user cannot view the details page of another token', function () {
    $token = $this->token;

    $this
        ->actingAs($this->user())
        ->get(route('tokens.details', $token))
        ->assertForbidden();
});
