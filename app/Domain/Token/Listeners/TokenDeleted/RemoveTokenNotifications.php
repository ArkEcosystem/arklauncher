<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\TokenDeleted;

use Domain\Token\Events\TokenDeleted;
use Domain\User\Models\DatabaseNotification;

final class RemoveTokenNotifications
{
    public function handle(TokenDeleted $event): void
    {
        DatabaseNotification::where('relatable_id', $event->token->id)->delete();
    }
}
