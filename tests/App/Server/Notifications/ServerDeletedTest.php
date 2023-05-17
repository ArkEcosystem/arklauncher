<?php

declare(strict_types=1);

use App\Server\Notifications\ServerDeleted;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $server = Server::factory()->createForTest();

    $server->token->user->notify(new ServerDeleted($server));

    Notification::assertSentTo($server->token->user, ServerDeleted::class);
});

it('can determine if notification should be sent to a user', function () {
    $server = Server::factory()->createForTest();

    $user  = User::factory()->create();
    $other = User::factory()->create();

    $server->token->shareWith($user, 'collaborator', []);

    $notification = new ServerDeleted($server);

    expect($notification->shouldSend($user))->toBeTrue();
    expect($notification->shouldSend($other))->toBeFalse();
});

it('builds the notification as an array', function () {
    $server = Server::factory()->createForTest();

    $notification = new ServerDeleted($server);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerDeleted($server))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('success');
});

it('should contain the right content', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerDeleted($server))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_deleted', ['server' => $server->name]));
});
