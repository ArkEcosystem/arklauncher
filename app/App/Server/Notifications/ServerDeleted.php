<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class ServerDeleted extends Notification
{
    use Queueable;

    public ?Token $token;

    public function __construct(public Server $server)
    {
        /** @var Token|null $token */
        $token = $server->token()->withTrashed()->first();

        $this->token = $token;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromServer($this->server, [
                'content' => trans('notifications.subjects.server_deleted', ['server' => $this->server->name]),
            ], $this->token)
            ->success()
            ->getContent();
    }

    public function shouldSend(User $notifiable) : bool
    {
        return $this->token?->hasCollaborator($notifiable) ?? false;
    }
}
