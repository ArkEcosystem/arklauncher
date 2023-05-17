<?php

declare(strict_types=1);

use App\Token\Notifications\TokenDeleted;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\Notification;

it('sends the notification to the user', function () {
    Notification::fake();

    $token = Token::factory()->createForTest();

    $token->user->notify(new TokenDeleted($token));

    Notification::assertSentTo($token->user, TokenDeleted::class);
});

it('builds the notification as an array', function () {
    $token = Token::factory()->createForTest();

    $notification = new TokenDeleted($token);

    expect($notification->toArray())->toBeArray();
});

it('should contain the type of the notification', function () {
    $token = Token::factory()->createForTest();

    $notification = (new TokenDeleted($token))->toArray();

    expect($notification)->toHaveKey('type');
    expect($notification['type'])->toBeString();
    expect($notification['type'])->toBe('success');
});

it('should contain the right content', function () {
    $token = Token::factory()->createForTest();

    $notification = (new TokenDeleted($token))->toArray();

    expect($notification)->toHaveKey('content');
    expect($notification['content'])->toBe(trans('notifications.subjects.token_deleted', ['token' => $token->name]));
});
