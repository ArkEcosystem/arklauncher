<?php

declare(strict_types=1);

use App\Collaborator\Mail\InviteExistingUser;
use Domain\Collaborator\Models\Invitation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the invited user', function () {
    $user       = $this->user();
    $invitation = Invitation::factory()->create();

    $user->notify(new InviteExistingUser($invitation));

    Notification::assertSentTo($user, InviteExistingUser::class);
});

it('builds the notification as an array', function () {
    $invitation = Invitation::factory()->create();

    $notification = new InviteExistingUser($invitation);

    expect($notification->toArray())->toBeArray();
});

it('builds the notification as mail', function () {
    $invitation = Invitation::factory()->create();

    $mail = new InviteExistingUser($invitation);

    expect($mail->toMail($invitation))->toBeInstanceOf(MailMessage::class);
});

it('builds the mail with the correct subject', function () {
    $invitation = Invitation::factory()->create();

    $mail = new InviteExistingUser($invitation);

    expect($mail->toMail($invitation)->subject)->toBe(trans('mails.subjects.new_invitation'));
});

it('should contain the type of the notification', function () {
    $invitation = Invitation::factory()->create();

    $notification = (new InviteExistingUser($invitation))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('success');
});

it('should contain the right content', function () {
    $invitation = Invitation::factory()->create();

    $notification = (new InviteExistingUser($invitation))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.new_invitation'));
});

it('should contain an action', function () {
    $invitation = Invitation::factory()->create();

    $notification = (new InviteExistingUser($invitation))->toArray();

    expect($notification)->toHaveKey('action');
    expect($notification['action'])->toHaveKey('title');
    expect($notification['action'])->toHaveKey('url');

    expect($notification['action']['title'])->toBe(trans('actions.view'));
    expect($notification['action']['url'])->toBe(route('user.teams'));
});
