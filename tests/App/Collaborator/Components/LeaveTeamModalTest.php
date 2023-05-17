<?php

declare(strict_types=1);

use App\Collaborator\Components\LeaveTeamModal;
use Domain\Collaborator\Models\Collaborator;
use Livewire\Livewire;

it('can ask for confirmation and set the token data', function () {
    $user        = $this->user();
    $token       = $this->token($user);

    Livewire::test(LeaveTeamModal::class)
            ->assertSet('tokenId', null)
            ->call('showModal', $token->id)
            ->assertSet('tokenId', $token->id);
});

it('can cancel the confirmation', function () {
    $user        = $this->user();
    $token       = $this->token($user);

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    Livewire::test(LeaveTeamModal::class)
            ->assertSet('tokenId', null)
            ->call('showModal', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('close')
            ->assertSet('tokenId', null);

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);
});

it('prevents owners from leaving the team', function () {
    $user        = $this->user();
    $token       = $this->token($user);

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    expect($user->onToken($token))->toBeTrue();

    Livewire::actingAs($user)
            ->test(LeaveTeamModal::class)
            ->assertSet('tokenId', null)
            ->call('showModal', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('leave')
            ->assertSet('tokenId', null)
            ->assertRedirect(route('user.teams'));

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    expect($user->onToken($token->fresh()))->toBeTrue();
});

it('can leave the token if user was part of it', function () {
    $user        = $this->user();
    $anotherUser = $this->user();
    $token       = $this->token($user);

    $token->shareWith($anotherUser, 'test', Collaborator::availablePermissions());

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    expect($anotherUser->onToken($token))->toBeTrue();

    Livewire::actingAs($anotherUser)
            ->test(LeaveTeamModal::class)
            ->assertSet('tokenId', null)
            ->call('showModal', $token->id)
            ->assertSet('tokenId', $token->id)
            ->call('leave')
            ->assertSet('tokenId', null)
            ->assertRedirect(route('user.teams'));

    $this->assertDatabaseHas('tokens', ['id' => $token->id]);

    expect($anotherUser->onToken($token->fresh()))->toBeFalse();
});
