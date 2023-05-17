<?php

declare(strict_types=1);

use App\Collaborator\Components\InviteCollaborator;
use App\Collaborator\Mail\InviteExistingUser;
use App\Collaborator\Mail\InviteNewUser;
use Domain\Collaborator\Models\Collaborator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('can invite an existing user', function () {
    $user  = $this->user();
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->set('email', $user->email)
            ->set('permissions', Collaborator::availablePermissions())
            ->call('invite')
            ->assertEmitted('refreshCollaborators')
            ->assertEmitted('toastMessage');

    Notification::assertSentTo($user, InviteExistingUser::class);
});

it('can invite an existing user with capitalized email characters', function () {
    $user  = $this->user();
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->set('email', strtoupper($user->email))
            ->set('permissions', Collaborator::availablePermissions())
            ->call('invite')
            ->assertEmitted('refreshCollaborators')
            ->assertEmitted('toastMessage');

    Notification::assertSentTo($user, InviteExistingUser::class);
});

it('can invite a new user', function () {
    Mail::fake();

    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->set('email', 'hello@world.com')
            ->set('permissions', Collaborator::availablePermissions())
            ->call('invite')
            ->assertEmitted('refreshCollaborators')
            ->assertEmitted('toastMessage');

    Mail::assertQueued(InviteNewUser::class);
});

it('cant invite a new user if the email is empty', function () {
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->call('invite')
            ->assertHasErrors(['email' => 'required'])
            ->assertSee('The email field is required.')
            ->assertDontSee(trans('tokens.invitations.mailed_invitation_sent'));
});

it('cant invite a new user if the email is invalid', function () {
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->set('email', 'invalid')
            ->call('invite')
            ->assertHasErrors(['email' => 'email'])
            ->assertSee('The email must be a valid email address.')
            ->assertDontSee(trans('tokens.invitations.mailed_invitation_sent'));
});

it('cant invite a new user if the email is longer than 255 characters', function () {
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->set('email', str_repeat('x', 255).'@example.com')
            ->call('invite')
            ->assertHasErrors(['email' => 'max'])
            ->assertSee('The email may not be greater than 255 characters.')
            ->assertDontSee(trans('tokens.invitations.mailed_invitation_sent'));
});

it('cant invite an user if it is already present on the token', function () {
    $user        = $this->user();
    $token       = $this->token($user);
    $token->shareWith($user);

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->set('email', $user->email)
            ->set('permissions', Collaborator::availablePermissions())
            ->call('invite')
            ->assertSee(trans('tokens.user_already_on_token', ['email' => $user->email]))
            ->assertDontSee(trans('tokens.invitations.mailed_invitation_sent'));
});

it('cant invite an user if it is already invited to the token', function () {
    $user        = $this->user();
    $token       = $this->token();

    $this->createInvitation($token, $user);

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->set('email', $user->email)
            ->set('permissions', Collaborator::availablePermissions())
            ->call('invite')
            ->assertSee(trans('tokens.user_already_invited_to_token', ['email' => $user->email]))
            ->assertDontSee(trans('tokens.invitations.mailed_invitation_sent'));
});

it('can select all permissions', function () {
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->call('selectAll')
            ->assertSet('permissions', Collaborator::availablePermissions());
});

it('can deselect all permissions', function () {
    $token = $this->token();

    Livewire::actingAs($token->user)
            ->test(InviteCollaborator::class, ['token' => $token])
            ->call('selectAll')
            ->call('deselectAll')
            ->assertSet('permissions', []);
});
