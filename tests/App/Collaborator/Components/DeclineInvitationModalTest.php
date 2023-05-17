<?php

declare(strict_types=1);

use App\Collaborator\Components\DeclineInvitationModal;
use App\Collaborator\Notifications\CollaboratorDeclinedInvite;
use Domain\Collaborator\Models\Invitation;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('can ask for confirmation and set the invitation data', function () {
    $invitation = Invitation::factory()->create([
        'user_id'    => null,
        'created_at' => now()->subWeek(2),
    ]);

    Livewire::test(DeclineInvitationModal::class)
            ->assertSet('invitationId', null)
            ->call('showModal', $invitation->id)
            ->assertSet('invitationId', $invitation->id);
});

it('can cancel the confirmation', function () {
    $invitation = Invitation::factory()->create([
        'user_id'    => null,
        'created_at' => now()->subWeek(2),
    ]);

    $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);

    Livewire::test(DeclineInvitationModal::class)
            ->assertSet('invitationId', null)
            ->call('showModal', $invitation->id)
            ->assertSet('invitationId', $invitation->id)
            ->call('close')
            ->assertSet('invitationId', null);

    $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
});

it('can decline an invitation as a new user', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);
    $invitation->token->shareWith($this->user(), 'owner');

    Livewire::actingAs($user = $this->user(['email' => $invitation->email]))
        ->test(DeclineInvitationModal::class)
        ->assertSet('invitationId', null)
        ->call('showModal', $invitation->id)
        ->assertSet('invitationId', $invitation->id)
        ->call('decline')
        ->assertRedirect(route('user.teams'));

    $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);

    expect($user->onToken($invitation->token))->toBeFalse();

    Notification::assertSentTo($invitation->token->user, CollaboratorDeclinedInvite::class);
});

it('redirects to the previous page if the invitation doesnt exist anymore when declining', function () {
    $invitation = Invitation::factory()->create(['user_id' => null]);
    $invitation->token->shareWith($this->user(), 'owner');

    $invitation->delete();

    Livewire::actingAs($user = $this->user(['email' => $invitation->email]))
        ->test(DeclineInvitationModal::class)
        ->assertSet('invitationId', null)
        ->call('showModal', $invitation->id)
        ->assertSet('invitationId', $invitation->id)
        ->assertSet('invitation', null)
        ->call('decline')
        ->assertRedirect(route('user.teams'));

    $this->assertSame(__('invitations.messages.invitation_removed'), flash()->message);

    Notification::assertNothingSent();
});

it('redirects to the previous page if the invitation is expired when declining', function () {
    $invitation = Invitation::factory()->create([
        'user_id'    => null,
        'created_at' => now()->subWeek(2),
    ]);

    Livewire::actingAs($user = $this->user(['email' => $invitation->email]))
        ->test(DeclineInvitationModal::class)
        ->assertSet('invitationId', null)
        ->call('showModal', $invitation->id)
        ->assertSet('invitationId', $invitation->id)
        ->call('decline')
        ->assertRedirect(route('user.teams'));

    $this->assertSame(__('invitations.messages.invitation_expired'), flash()->message);

    Notification::assertNothingSent();
});
