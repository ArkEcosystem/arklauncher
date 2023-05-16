<?php

declare(strict_types=1);

use App\Server\Notifications\ServerUnreachable;
use Domain\Server\Models\Server;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $server = Server::factory()->createForTest();

    $server->token->user->notify(new ServerUnreachable($server));

    Notification::assertSentTo($server->token->user, ServerUnreachable::class);
});

it('builds the notification as an array', function () {
    $server = Server::factory()->createForTest();

    $notification = new ServerUnreachable($server);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerUnreachable($server))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('warning');
});

it('should contain the right content', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerUnreachable($server))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_unreachable', ['server' => $server->name]));
});
