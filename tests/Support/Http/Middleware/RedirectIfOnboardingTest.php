<?php

declare(strict_types=1);

use Domain\Token\Models\Token;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Support\Http\Middleware\RedirectIfOnboarding;

beforeEach(function () {
    Route::model('token', Token::class);

    Route::middleware([
        SubstituteBindings::class,
        RedirectIfOnboarding::class,
    ])->get('{token}', fn () => []);
});

it('redirects the user if it is a first timer', function () {
    $token = Token::factory()->withServers(0)->createForTest();

    $this
        ->actingAs($token->user)
        ->get(route('tokens.show', $token))
        ->assertRedirect(route('tokens.welcome', $token));
});

it('fulfill teh request if the user is not authenticated', function () {
    $token = Token::factory([
        'onboarded_at' => now(),
    ])
        ->withNetwork(1)
        ->withServers(1)
        ->createForTest();

    $this
        ->actingAs($token->user)
        ->get(route('tokens.show', $token))
        ->assertRedirect(route('tokens.details', $token));
});
