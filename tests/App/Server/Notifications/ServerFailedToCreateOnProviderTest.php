<?php

declare(strict_types=1);

use App\Server\Notifications\ServerFailedToCreateOnProvider;
use Domain\Server\Models\ServerProvider;
use Domain\User\Models\User;

it('notifies via database', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = new ServerFailedToCreateOnProvider($serverProvider, 'dummy-server');

    expect($notification->via())->toBe(['database']);
});

it('can determine if notification should be sent to a user', function () {
    $serverProvider = ServerProvider::factory()->createForTest();
    $user           = User::factory()->create();
    $other          = User::factory()->create();

    $serverProvider->token->shareWith($user, 'collaborator', []);

    $notification = new ServerFailedToCreateOnProvider($serverProvider, 'dummy-server');

    expect($notification->shouldSend($user))->toBeTrue();
    expect($notification->shouldSend($other))->toBeFalse();
});

it('builds the notification as an array', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = new ServerFailedToCreateOnProvider($serverProvider, 'dummy-server');

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new ServerFailedToCreateOnProvider($serverProvider, 'dummy-server'))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('danger');
});

it('should contain the right content', function () {
    $serverProvider = ServerProvider::factory()->digitalocean()->createForTest();

    $notification = (new ServerFailedToCreateOnProvider($serverProvider, 'dummy-server'))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_failed_to_create', [
        'server'   => 'dummy-server',
        'provider' => 'DigitalOcean',
    ]));
});
