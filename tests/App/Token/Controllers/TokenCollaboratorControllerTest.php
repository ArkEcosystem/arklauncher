<?php

declare(strict_types=1);

use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Domain\User\Models\User;

it('redirects user to tokens page if step not available', function () {
    $token = Token::factory()->withOnboardingServerConfiguration()->create();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.collaborators', $token))
            ->assertRedirect(route('tokens.show', $token));
});

it('doesnt redirects user to tokens page if token cannot be edited', function () {
    $token = Token::factory()->withOnboardingServerConfiguration()->create()->fresh();
    $token->networks()->each(fn ($network) => Server::factory()->ownedBy($network)->createForTest());

    // Cannot be edited
    expect($token->fresh()->canBeEdited())->toBe(false);

    $this
            ->actingAs($token->user)
            ->get(route('tokens.collaborators', $token))
            ->assertViewIs('app.tokens.collaborators');
});

it('users may view tokens if step available', function () {
    $token = Token::factory()->withOnboardingSecureShellKey()->create();

    $this
            ->actingAs($token->user)
            ->get(route('tokens.collaborators', $token))
            ->assertViewIs('app.tokens.collaborators');
});

it('user without permissions cannot view tokens', function () {
    $token      = $this->token();
    $randomUser = User::factory()->create();

    // Make the user collaborator so it has the `view` permission
    // but not the `createCollaborator` or `deleteCollaborator` permission
    $token->collaborators()->attach($randomUser, [
        'role'        => 'collaborator',
        'permissions' => [],
    ]);

    $this->actingAs($randomUser)->get(route('tokens.collaborators', $token))->assertForbidden();
});
