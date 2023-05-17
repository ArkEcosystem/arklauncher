<?php

declare(strict_types=1);

use App\Collaborator\Notifications\CollaboratorAcceptedInvite;
use Domain\Collaborator\Models\Invitation;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('can accept an invitation as an existing user', function () {
    $invitation = Invitation::factory()->create();
    $invitation->token->shareWith($this->user(), 'owner');

    [$collaborator1, $collaborator2] = User::factory()->times(2)->create();

    $invitation->token->shareWith($collaborator1, 'collaborator', ['server-provider:create']);
    $invitation->token->shareWith($collaborator2, 'collaborator', ['server-provider:create']);

    $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);

    $this
        ->actingAs($invitation->user)
        ->get(route('invitations.accept', $invitation))
        ->assertRedirect(route('user.teams'));

    $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);

    expect($invitation->user->onToken($invitation->token))->toBeTrue();

    Notification::assertSentTo([$invitation->token->user, $collaborator1, $collaborator2], CollaboratorAcceptedInvite::class);
});

it('can accept an invitation as a new user', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);
    $invitation->token->shareWith($this->user(), 'owner');

    $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);

    $this
        ->actingAs($user = $this->user(['email' => $invitation->email]))
        ->get(route('invitations.accept', $invitation))
        ->assertRedirect(route('user.teams'));

    $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);

    expect($user->onToken($invitation->token))->toBeTrue();

    Notification::assertSentTo($invitation->token->user, CollaboratorAcceptedInvite::class);
});

it('redirects to the previous page if the invitation doesnt exist anymore', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);
    $invitation->token->shareWith($this->user(), 'owner');

    $invitation->delete();

    $this
        ->actingAs($user = $this->user(['email' => $invitation->email]))
        ->from(route('user.teams'))
        ->get(route('invitations.accept', $invitation))
        ->assertRedirect(route('user.teams'));

    $this->assertSame(__('invitations.messages.invitation_removed'), flash()->message);

    Notification::assertNothingSent();
});

it('redirects to the previous page if the invitation is expired', function () {
    $invitation = Invitation::factory()->create(['user_id' => null, 'created_at' => now()->subWeek(2)]);

    $this
        ->actingAs($user = $this->user(['email' => $invitation->email]))
        ->from(route('user.teams'))
        ->get(route('invitations.accept', $invitation))
        ->assertRedirect(route('user.teams'));

    $this->assertSame(__('invitations.messages.invitation_expired'), flash()->message);

    Notification::assertNothingSent();
});
