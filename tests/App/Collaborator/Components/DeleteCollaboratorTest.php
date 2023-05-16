<?php

declare(strict_types=1);

use App\Collaborator\Components\DeleteCollaborator;
use Livewire\Livewire;

it('can ask for confirmation and set the token id', function () {
    $user        = $this->user();
    $token       = $this->token($user);

    Livewire::test(DeleteCollaborator::class, compact('token'))
            ->assertSet('collaboratorId', null)
            ->call('askForConfirmation', $user->id)
            ->assertSet('collaboratorId', $user->id);
});

it('can close the modal', function () {
    $user        = $this->user();
    $token       = $this->token($user);

    Livewire::test(DeleteCollaborator::class, compact('token'))
            ->assertSet('collaboratorId', null)
            ->call('askForConfirmation', $user->id)
            ->assertSet('collaboratorId', $user->id)
            ->call('close')
            ->assertSet('collaboratorId', null);
});

it('can destroy the token if it is the owner', function () {
    $user        = $this->user();
    $token       = $this->token($user);

    $anotherUser = $this->user();
    $token->shareWith($anotherUser, 'collaborator');

    $this->actingAs($user);

    $this->assertDatabaseHas('token_users', [
            'token_id'       => $token->id,
            'user_id'        => $anotherUser->id,
            'role'           => 'collaborator',
        ]);

    Livewire::test(DeleteCollaborator::class, compact('token'))
            ->assertSet('collaboratorId', null)
            ->call('askForConfirmation', $anotherUser->id)
            ->assertSet('collaboratorId', $anotherUser->id)
            ->call('destroy')
            ->assertSet('collaboratorId', null);

    $this->assertDatabaseMissing('token_users', [
            'token_id'               => $token->id,
            'user_id'                => $anotherUser->id,
            'role'                   => 'collaborator',
        ]);
});

it('fails to destroy the token if it is not the owner', function () {
    $user        = $this->user();
    $token       = $this->token($user);

    $anotherUser = $this->user();
    $token->shareWith($anotherUser, 'collaborator', ['collaborator:delete']);
    $this->actingAs($anotherUser);

    $this->assertDatabaseHas('token_users', [
            'token_id'       => $token->id,
            'user_id'        => $anotherUser->id,
            'role'           => 'collaborator',
        ]);

    Livewire::test(DeleteCollaborator::class, compact('token'))
            ->assertSet('collaboratorId', null)
            ->call('askForConfirmation', $anotherUser->id)
            ->assertSet('collaboratorId', $anotherUser->id)
            ->call('destroy')
            ->assertSet('collaboratorId', $anotherUser->id);

    $this->assertDatabaseHas('token_users', [
            'token_id'       => $token->id,
            'user_id'        => $anotherUser->id,
            'role'           => 'collaborator',
        ]);
});
