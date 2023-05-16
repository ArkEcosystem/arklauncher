<?php

declare(strict_types=1);

use App\Collaborator\Components\UpdateCollaborator;
use Domain\Collaborator\Models\Collaborator;
use Livewire\Livewire;

it('can update the permissions', function () {
    [$user, $token] = $this->createModels();

    Livewire::actingAs($token->user)
        ->test(UpdateCollaborator::class, ['token' => $token])
        ->call('edit', $user->id)
        ->set('permissions', Collaborator::availablePermissions())
        ->call('update');

    expect(Collaborator::availablePermissions())
        ->toBe($token->collaborators()->where('user_id', $user->id)->first()->pivot->permissions);
});

it('can close the modal', function () {
    [$user, $token] = $this->createModels();

    Livewire::actingAs($token->user)
        ->test(UpdateCollaborator::class, ['token' => $token])
        ->assertSet('collaboratorId', null)
        ->call('edit', $user->id)
        ->assertSet('collaboratorId', $user->id)
        ->call('close')
        ->assertSet('collaboratorId', null);
});

it('cant update the permissions if it is empty', function () {
    [$user, $token] = $this->createModels();

    Livewire::actingAs($token->user)
        ->test(UpdateCollaborator::class, ['token' => $token])
        ->call('edit', $user->id)
        ->set('permissions', [])
        ->call('update')
        ->assertHasErrors(['permissions' => 'required']);
});

it('cant update the permissions if it is invalid', function () {
    [$user, $token] = $this->createModels();

    Livewire::actingAs($token->user)
        ->test(UpdateCollaborator::class, ['token' => $token])
        ->call('edit', $user->id)
        ->set('permissions', ['invalid'])
        ->call('update')
        ->assertHasErrors(['permissions.0']);
});
