<?php

declare(strict_types=1);

use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->token = Token::factory()
        ->withNetwork(1)
        ->createForTest();
});

it('a user can start their server', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/start'), 200, []),
        ]);

    $token = $this->token;

    $this
            ->actingAs($token->user)
            ->post($token->servers()->first()->pathStart())
            ->assertRedirect();
});

it('starting server can fail', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/start'), 500, []),
    ]);

    $token = $this->token;

    $this
            ->actingAs($token->user)
            ->post($token->servers()->first()->pathStart())
            ->assertRedirect();
});

it('an user can stop their server', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/stop'), 200, []),
        ]);

    $token = $this->token;

    $this
            ->actingAs($token->user)
            ->delete($token->servers()->first()->pathStop())
            ->assertRedirect();
});

it('stopping a server can fail', function () {
    $token = $this->token;

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/stop'), 500, []),
    ]);

    $this
            ->actingAs($token->user)
            ->delete($token->servers()->first()->pathStop())
            ->assertRedirect();
});

it('an user can reboot their server', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/reboot'), 200, []),
        ]);

    $token = $this->token;

    $this
            ->actingAs($token->user)
            ->put($token->servers()->first()->pathReboot())
            ->assertRedirect();
});

it('rebooting a server can fail', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/reboot'), 500, []),
    ]);

    $token = $this->token;

    $this
            ->actingAs($token->user)
            ->put($token->servers()->first()->pathReboot())
            ->assertRedirect();
});
