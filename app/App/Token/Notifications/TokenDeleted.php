<?php

declare(strict_types=1);

namespace App\Token\Notifications;

use Domain\Token\Models\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class TokenDeleted extends Notification
{
    use Queueable;

    public function __construct(public Token $token)
    {
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromToken($this->token, [
                'content' => trans('notifications.subjects.token_deleted', ['token' => $this->token->name]),
            ])
            ->success()
            ->getContent();
    }
}
