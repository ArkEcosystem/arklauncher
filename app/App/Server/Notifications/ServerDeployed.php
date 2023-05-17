<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class ServerDeployed extends Notification
{
    use Queueable;

    public Token $token;

    public function __construct(public Server $server)
    {
        $this->token = $this->server->token;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromServer($this->server, [
                'content' => trans('notifications.subjects.server_deployed', ['server' => $this->server->name]),
            ])
            ->withAction(trans('actions.view'), route('tokens.servers.show', [$this->token, $this->server->network, $this->server]))
            ->success()
            ->getContent();
    }
}
