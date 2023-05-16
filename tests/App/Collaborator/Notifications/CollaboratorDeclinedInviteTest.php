<?php

declare(strict_types=1);

use App\Collaborator\Notifications\CollaboratorDeclinedInvite;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $token = $this->token();

    $token->user->notify(new CollaboratorDeclinedInvite($token, $this->user()));

    Notification::assertSentTo($token->user, CollaboratorDeclinedInvite::class);
});

it('builds the notification as mail', function () {
    $token = $this->token();

    $notification = new CollaboratorDeclinedInvite($token, $this->user());

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $token = $this->token();

    $user = User::factory()->create();

    $notification = (new CollaboratorDeclinedInvite($token, $user))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('warning');
});

it('should contain the right content', function () {
    $token = $this->token();

    $user = User::factory()->create();

    $notification = (new CollaboratorDeclinedInvite($token, $user))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.collaborator_refused_invitation', ['collaborator' => $user->name]));
});

it('should contain an action', function () {
    $token = $this->token();

    $user = User::factory()->create();

    $notification = (new CollaboratorDeclinedInvite($token, $user))->toArray();

    expect($notification)->toHaveKey('action');
    expect($notification['action'])->toHaveKey('title');
    expect($notification['action'])->toHaveKey('url');

    expect($notification['action']['title'])->toBe(trans('actions.view'));
    expect($notification['action']['url'])->toBe(route('user.teams'));
});
