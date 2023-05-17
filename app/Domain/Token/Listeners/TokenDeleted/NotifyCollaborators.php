<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\TokenDeleted;

use App\Token\Notifications\TokenDeleted as TokenDeletedNotification;
use Domain\Token\Events\TokenDeleted;

final class NotifyCollaborators
{
    public function handle(TokenDeleted $event) : void
    {
        $event->token->refresh();

        if ($event->token->exists) {
            $event->token
                    ->collaborators
                    ->each
                    ->notify(new TokenDeletedNotification($event->token));
        }
    }
}
