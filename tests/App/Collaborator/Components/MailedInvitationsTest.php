<?php

declare(strict_types=1);

use App\Collaborator\Components\MailedInvitations;
use Domain\User\Models\User;
use Livewire\Livewire;

it('can cancel an invitation', function () {
    $user  = $this->user();
    $token = $this->token();

    $invitationId = $this->createInvitation($token, $user);

    $this->assertDatabaseHas('invitations', [
        'id'       => $invitationId,
        'token_id' => $token->id,
        'user_id'  => $user->id,
    ]);

    $this->actingAs(User::find($token->user_id));

    Livewire::test(MailedInvitations::class, ['token' => $token])
        ->call('cancel', $invitationId);

    $this->assertDatabaseMissing('invitations', [
        'id'       => $invitationId,
        'token_id' => $token->id,
        'user_id'  => $user->id,
    ]);
});

it("won't let a user cancel an invitation when the user does not own the token", function () {
    $user  = $this->user();
    $token = $this->token();

    $invitationId = $this->createInvitation($token, $user);

    $this->assertDatabaseHas('invitations', [
        'id'       => $invitationId,
        'token_id' => $token->id,
        'user_id'  => $user->id,
    ]);

    $differentUser = User::factory()->create();
    $this->actingAs($differentUser);

    Livewire::test(MailedInvitations::class, ['token' => $token])
        ->call('cancel', $invitationId)
        ->assertForbidden();

    $this->assertDatabaseHas('invitations', [
        'id'       => $invitationId,
        'token_id' => $token->id,
        'user_id'  => $user->id,
    ]);
});
