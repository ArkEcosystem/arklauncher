<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\ServerDeleted;

use App\Server\Notifications\ServerDeleted as ServerDeletedNotification;
use Domain\Server\Models\Server;
use Domain\Token\Events\ServerDeleted;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

final class NotifyUsersOfServerDeletion
{
    public function handle(ServerDeleted $event) : void
    {
        $this->notifiables($event->server)
                ->each
                ->notify(new ServerDeletedNotification($event->server));
    }

    /**
     * @param Server $server
     * @return Collection<int, User>
     */
    private function notifiables(Server $server) : Collection
    {
        /** @var Token $token */
        $token = $server->token()->withTrashed()->first();

        return $server->isProvisioned() ? $token->collaborators : collect([
            $token->user, $server->creator(),
        ])->filter()->unique('id')->values();
    }
}
