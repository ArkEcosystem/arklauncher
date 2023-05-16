<?php

declare(strict_types=1);

use App\Collaborator\Components\CollaboratorPermissionsModal;
use Domain\Collaborator\Models\Collaborator;
use Livewire\Livewire;

it('can ask for confirmation and set the permissions data', function () {
    Livewire::test(CollaboratorPermissionsModal::class)
            ->assertSet('permissions', [])
            ->call('setPermissions', Collaborator::availablePermissions())
            ->assertSet('permissions', Collaborator::availablePermissions());
});

it('can cancel the confirmation', function () {
    Livewire::test(CollaboratorPermissionsModal::class)
            ->assertSet('permissions', [])
            ->call('setPermissions', Collaborator::availablePermissions())
            ->assertSet('permissions', Collaborator::availablePermissions())
            ->call('closeModal')
            ->assertSet('modalShown', false);
});
