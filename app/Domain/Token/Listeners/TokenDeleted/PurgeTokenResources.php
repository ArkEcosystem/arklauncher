<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\TokenDeleted;

use Domain\Token\Events\TokenDeleted;

final class PurgeTokenResources
{
    /**
     * @param TokenDeleted $event
     * @return void
     */
    public function handle(TokenDeleted $event) : void
    {
        $event->token->purge($event->shouldDeleteServers);
    }
}
