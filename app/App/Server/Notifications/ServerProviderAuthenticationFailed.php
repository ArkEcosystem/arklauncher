<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class ServerProviderAuthenticationFailed extends Notification
{
    use Queueable;

    public Token $token;

    public function __construct(public ServerProvider $serverProvider)
    {
        $this->token = $serverProvider->token;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
        ->fromServerProvider($this->serverProvider, [
            'content'             => trans(
                'notifications.subjects.server_provider_auth_failed',
                ['serverProvider' => $this->serverProvider->name]
            ),
        ])
        ->warning()
        ->getContent();
    }

    public function shouldSend(User $notifiable) : bool
    {
        return $this->token->hasCollaborator($notifiable);
    }
}
