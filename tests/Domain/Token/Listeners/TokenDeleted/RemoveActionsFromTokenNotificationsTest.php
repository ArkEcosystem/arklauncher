<?php

declare(strict_types=1);

use App\Token\Notifications\TokenDeleted as NotificationsTokenDeleted;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Models\Token;
use Domain\User\Models\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

it('should remove all notifications after a token is deleted, except the delete notification itself', function () {
    $token = Token::factory()
        ->withServers(1)
        ->withServerProviders(1)
        ->withDefaultNetworks()
        ->withInvitations(1)
        ->withNetwork(1)
        ->withStatus(TokenStatusEnum::FINISHED)
        ->createForTest();

    $notification = DatabaseNotification::factory()->ownedBy($token)->create();

    $this->assertDatabaseHas('notifications', [
        'relatable_id' => $token->id,
    ]);

    $token->delete();
    TokenDeleted::dispatch($token);

    $this->assertDatabaseMissing('notifications', [
        'relatable_id' => $token->id,
    ]);

    expect($notification->fresh())->toBeNull();

    Notification::assertSentTo(
        [$token->user],
        NotificationsTokenDeleted::class,
    );
});
