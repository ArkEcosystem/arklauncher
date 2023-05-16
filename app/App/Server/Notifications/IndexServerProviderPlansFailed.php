<?php

declare(strict_types=1);

namespace App\Server\Notifications;

use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Support\Builders\NotificationBuilder;

final class IndexServerProviderPlansFailed extends Notification
{
    use Queueable;

    public Token $token;

    public function __construct(public ServerProvider $serverProvider)
    {
        $this->token = $this->serverProvider->token;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return (new NotificationBuilder())
            ->fromServerProvider($this->serverProvider, [
                'content' => trans('notifications.subjects.server_provider_plan_index_failed', ['serverProvider' => $this->serverProvider->name]),
            ])
            ->danger()
            ->getContent();
    }
}
