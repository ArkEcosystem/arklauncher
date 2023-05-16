<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\ServerProviderDeleted;

use App\SecureShell\Jobs\RemoveSecureShellKeyFromServerProvider;
use Domain\Token\Events\ServerProviderDeleted;

final class TriggerSecureShellKeyRemovalFromServerProvider
{
    /**
     * Execute the job.
     *
     * @param ServerProviderDeleted $event
     *
     * @return void
     */
    public function handle(ServerProviderDeleted $event)
    {
        RemoveSecureShellKeyFromServerProvider::dispatch($event->serverProvider);
    }
}
