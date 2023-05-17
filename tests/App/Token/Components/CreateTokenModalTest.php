<?php

declare(strict_types=1);

use App\Token\Components\CreateTokenModal;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\TokenDeleted;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

it('redirects to onboarding if no pending token', function () {
    $token = $this->token();
    $token->setStatus(TokenStatusEnum::FINISHED);
    $user  = $token->user;

    Livewire::actingAs($user)
        ->test(CreateTokenModal::class)
        ->assertSet('token', null)
        ->call('handle')
        ->assertSet('token', null)
        ->assertRedirect(route('tokens.create'));
});

it('shows modal if pending token for user', function () {
    $token = $this->token();
    $token->setStatus(TokenStatusEnum::PENDING);
    $user = $token->user;

    Livewire::actingAs($user)
        ->test(CreateTokenModal::class)
        ->assertSet('token', null)
        ->call('handle')
        ->assertNotSet('token', null);
});

it('can close the modal', function () {
    $token = $this->token();
    $token->setStatus(TokenStatusEnum::PENDING);
    $user = $token->user;

    Livewire::actingAs($user)
        ->test(CreateTokenModal::class)
        ->assertSet('token', null)
        ->call('handle')
        ->assertNotSet('token', null)
        ->call('cancel')
        ->assertSet('token', null);
});

it('can continue with existing token', function () {
    $token = $this->token();
    $token->setStatus(TokenStatusEnum::PENDING);
    $user = $token->user;

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::actingAs($user)
        ->test(CreateTokenModal::class)
        ->assertSet('token', null)
        ->call('handle')
        ->assertNotSet('token', null)
        ->call('continue')
        ->assertRedirect(route('tokens.create'));

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);
});

it('can delete existing token and redirect', function () {
    $token = $this->token();
    $token->setStatus(TokenStatusEnum::PENDING);
    $user = $token->user;

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::actingAs($user)
        ->test(CreateTokenModal::class)
        ->assertSet('token', null)
        ->call('handle')
        ->assertNotSet('token', null)
        ->call('deletePendingToken')
        ->assertRedirect(route('tokens.create'));

    $this->assertDatabaseMissing('tokens', ['id' => $token->id]);
});

it('triggers the token deleted event when deleting the pending token', function () {
    Event::fake();

    $token = $this->token();
    $token->setStatus(TokenStatusEnum::PENDING);
    $user = $token->user;

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::actingAs($user)
        ->test(CreateTokenModal::class)
        ->assertSet('token', null)
        ->call('handle')
        ->assertNotSet('token', null)
        ->call('deletePendingToken')
        ->assertRedirect(route('tokens.create'));

    Event::assertDispatched(fn (TokenDeleted $event) => $event->token->is($token));
});
