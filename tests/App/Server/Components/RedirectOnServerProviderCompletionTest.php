<?php

declare(strict_types=1);

use App\Server\Components\RedirectOnServerProviderCompletion;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Livewire\Livewire;

it('should redirect if server providers step is ready', function () {
    $token = Token::factory()->withOnboardingServerProvider()->create();

    ServerProvider::factory()->hetzner()->ownedBy($token)->create();

    $this->actingAs($token->user);

    Livewire::actingAs($token->user)
        ->test(RedirectOnServerProviderCompletion::class, ['token' => $token])
        ->assertSet('token.id', $token->id)
        ->assertRedirect(route('tokens.show', $token));
});

it('should not redirect if server providers step is not ready', function () {
    $token = Token::factory()->withOnboardingConfigurationCompleted()->create();

    ServerProvider::factory()->hetzner()->ownedBy($token)->create();

    $this->actingAs($token->user);

    Livewire::actingAs($token->user)
        ->test(RedirectOnServerProviderCompletion::class, ['token' => $token])
        ->assertSet('token.id', $token->id)
        ->assertDontSee(trans('tokens.onboarding.page_header'));
});

it('should not redirect if the latest server provider created doesnt have a provider key id set', function () {
    $token = $this->token();

    $serverProvider                  = ServerProvider::factory()->hetzner()->ownedBy($token)->createForTest();
    $serverProvider->provider_key_id = null;

    $this->actingAs($token->user);

    Livewire::actingAs($token->user)
        ->test(RedirectOnServerProviderCompletion::class, ['token' => $token])
        ->assertSet('token.id', $token->id)
        ->assertDontSee(trans('tokens.onboarding.page_header'));
});
