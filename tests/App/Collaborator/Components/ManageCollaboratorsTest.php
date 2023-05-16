<?php

declare(strict_types=1);

use App\Collaborator\Components\ManageCollaborators;
use Domain\Token\Models\Token;
use Livewire\Livewire;

it('can list the token collaborators', function () {
    $this->actingAs($owner = $this->user());

    $token = Token::factory()->createForTest();
    $token->shareWith($owner, 'owner');
    $token->shareWith($collaborator = $this->user(), 'collaborator');

    Livewire::test(ManageCollaborators::class, ['token' => $token])
            ->assertSee('You')
            ->assertSee($collaborator->name);
});

it('can list the token collaborators with only owner', function () {
    $this->actingAs($owner = $this->user());

    $token = Token::factory()->createForTest();
    $token->shareWith($owner, 'owner');

    Livewire::test(ManageCollaborators::class, ['token' => $token])
            ->assertSee('You');
});

it('can list the token collaborators when user is not owner', function () {
    $this->actingAs($collaborator = $this->user());

    $token                   = Token::factory()->createForTest();
    $token->shareWith($owner = $this->user(), 'owner');
    $token->shareWith($collaborator, 'collaborator');

    Livewire::test(ManageCollaborators::class, ['token' => $token])
            ->assertSee('You')
            ->assertSee($owner->name);
});
