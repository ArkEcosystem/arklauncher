<?php

declare(strict_types=1);

use App\Server\Notifications\ServerProviderAuthenticationFailed;
use Domain\Server\Models\ServerProvider;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $serverProvider = ServerProvider::factory()->createForTest();

    $serverProvider->token->user->notify(new ServerProviderAuthenticationFailed($serverProvider));

    Notification::assertSentTo($serverProvider->token->user, ServerProviderAuthenticationFailed::class);
});

it('can determine if notification should be sent to a user', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $user  = User::factory()->create();
    $other = User::factory()->create();

    $serverProvider->token->shareWith($user, 'collaborator', []);

    $notification = new ServerProviderAuthenticationFailed($serverProvider);

    expect($notification->shouldSend($user))->toBeTrue();
    expect($notification->shouldSend($other))->toBeFalse();
});

it('builds the notification as an array', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = new ServerProviderAuthenticationFailed($serverProvider);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new ServerProviderAuthenticationFailed($serverProvider))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('warning');
});

it('should contain the right content', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new ServerProviderAuthenticationFailed($serverProvider))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_provider_auth_failed', ['serverProvider' => $serverProvider->name]));
});
