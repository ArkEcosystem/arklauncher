<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use Domain\Server\Models\ServerProvider;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class ServerProviderServerRemovalFailed extends Notification
{
    use Queueable;

    public function __construct(public ServerProvider $serverProvider)
    {
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromServerProvider($this->serverProvider, [
                'content' => trans(
                    'notifications.subjects.server_provider_server_removal_failed',
                    ['serverProvider' => $this->serverProvider->name]
                ),
            ])
            ->danger()
            ->getContent();
    }

    public function shouldSend(User $notifiable) : bool
    {
        return $this->serverProvider->token->hasCollaborator($notifiable);
    }
}
