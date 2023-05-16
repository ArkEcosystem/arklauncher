<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use Domain\Server\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class ServerUnreachable extends Notification
{
    use Queueable;

    public function __construct(public Server $server)
    {
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromServer($this->server, [
                'content' => trans(
                    'notifications.subjects.server_unreachable',
                    ['server' => $this->server->name]
                ),
            ])
            ->warning()
            ->getContent();
    }
}
