<?php

declare(strict_types=1);

use App\Token\Notifications\TokenDeleted as TokenDeletedNotification;
use Domain\Token\Events\TokenDeleted;
use Domain\Token\Listeners\TokenDeleted\NotifyCollaborators;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('notifies the collaborators if token was soft deleted', function () {
    $token = $this->token();

    $token->shareWith(
        $collaborator = User::factory()->create(),
        'collaborator',
        ['server-provider:create'],
    );

    $token->delete();

    (new NotifyCollaborators())->handle(new TokenDeleted($token));

    Notification::assertTimesSent(2, TokenDeletedNotification::class);

    Notification::assertSentTo([
        $token->user, $collaborator,
    ], TokenDeletedNotification::class);
});

it('does not notify collaborators if token was force deleted', function () {
    $token = $this->token();

    $token->forceDelete();

    (new NotifyCollaborators())->handle(new TokenDeleted($token));

    Notification::assertNothingSent();
});
