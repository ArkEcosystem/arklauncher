<?php

declare(strict_types=1);

use App\Server\Notifications\IndexServerProviderImagesFailed;
use Domain\Server\Models\ServerProvider;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $serverProvider = ServerProvider::factory()->createForTest();

    $serverProvider->token->user->notify(new IndexServerProviderImagesFailed($serverProvider));

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderImagesFailed::class);
});

it('builds the notification as an array', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = new IndexServerProviderImagesFailed($serverProvider);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new IndexServerProviderImagesFailed($serverProvider))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('danger');
});

it('should contain the right content', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new IndexServerProviderImagesFailed($serverProvider))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_provider_image_index_failed', ['serverProvider' => $serverProvider->name]));
});
