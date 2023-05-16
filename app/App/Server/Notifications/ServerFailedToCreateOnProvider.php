<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use App\Enums\ServerProviderTypeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class ServerFailedToCreateOnProvider extends Notification
{
    use Queueable;

    public ServerProvider $serverProvider;

    public Token $token;

    public string $serverName;

    public function __construct(ServerProvider $serverProvider, string $serverName)
    {
        $this->serverProvider = $serverProvider;
        $this->token          = $this->serverProvider->token;
        $this->serverName     = $serverName;
    }

    public function via() : array
    {
        return ['database'];
    }

    public function toArray() : array
    {
        return (new NotificationBuilder())
            ->fromServerProvider($this->serverProvider, [
                'content' => trans('notifications.subjects.server_failed_to_create', ['server' => $this->serverName, 'provider' => $this->serverProviderLabel()]),
            ])
            ->danger()
            ->getContent();
    }

    public function shouldSend(User $notifiable) : bool
    {
        return $this->token->hasCollaborator($notifiable);
    }

    private function serverProviderLabel() : string
    {
        return ServerProviderTypeEnum::label($this->serverProvider->type);
    }
}
