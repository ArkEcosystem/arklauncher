<?php

declare(strict_types=1);

use App\Server\Notifications\ServerDeployed;
use Domain\Server\Models\Server;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $server = Server::factory()->createForTest();

    $server->token->user->notify(new ServerDeployed($server));

    Notification::assertSentTo($server->token->user, ServerDeployed::class);
});

it('builds the notification as an array', function () {
    $server = Server::factory()->createForTest();

    $notification = new ServerDeployed($server);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerDeployed($server))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('success');
});

it('should contain the right content', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerDeployed($server))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_deployed', ['server' => $server->name]));
});

it('should contain an action', function () {
    $server = Server::factory()->createForTest();

    $notification = (new ServerDeployed($server))->toArray();

    expect($notification)->toHaveKey('action');
    expect($notification['action'])->toHaveKey('title');
    expect($notification['action'])->toHaveKey('url');

    expect($notification['action']['title'])->toBe(trans('actions.view'));
    expect($notification['action']['url'])->toBe(route('tokens.servers.show', [$server->token, $server->network, $server]));
});
