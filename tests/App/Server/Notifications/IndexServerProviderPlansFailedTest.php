<?php

declare(strict_types=1);

use App\Server\Notifications\IndexServerProviderPlansFailed;
use Domain\Server\Models\ServerProvider;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $serverProvider = ServerProvider::factory()->createForTest();

    $serverProvider->token->user->notify(new IndexServerProviderPlansFailed($serverProvider));

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderPlansFailed::class);
});

it('builds the notification as an array', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = new IndexServerProviderPlansFailed($serverProvider);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new IndexServerProviderPlansFailed($serverProvider))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBestring();
    expect($notification['type'])->toBe('danger');
});

it('should contain the right content', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $notification = (new IndexServerProviderPlansFailed($serverProvider))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.server_provider_plan_index_failed', ['serverProvider' => $serverProvider->name]));
});
