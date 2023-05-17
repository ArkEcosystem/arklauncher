<?php

declare(strict_types=1);

use App\Server\Notifications\ServerProviderSecureShellKeyUniqueness;
use Domain\Server\Models\ServerProvider;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $serverProvider = ServerProvider::factory()->createForTest();

    $serverProvider->token->user->notify(new ServerProviderSecureShellKeyUniqueness($serverProvider));

    Notification::assertSentTo($serverProvider->token->user, ServerProviderSecureShellKeyUniqueness::class);
});

it('can determine if notification should be sent to a user', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $user  = User::factory()->create();
    $other = User::factory()->create();

    $serverProvider->token->shareWith($user, 'collaborator', []);

    $notification = new ServerProviderSecureShellKeyUniqueness($serverProvider);

    expect($notification->shouldSend($user))->toBeTrue();
    expect($notification->shouldSend($other))->toBeFalse();
});

it('builds the notification as an array', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = new ServerProviderSecureShellKeyUniqueness($serverProvider);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new ServerProviderSecureShellKeyUniqueness($serverProvider))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('danger');
});

it('should contain the right content', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new ServerProviderSecureShellKeyUniqueness($serverProvider))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_provider_ssh_key_uniqueness_error', ['serverProvider' => $serverProvider->name]));
});
