<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\ServerUpdating;

use App\Server\Notifications\ServerProviderAuthenticationFailed;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Token\Events\ServerUpdating;
use Illuminate\Support\Collection;
use Throwable;

final class UpdateServerProviderName
{
    /**
     * Execute the job.
     *
     * @param ServerUpdating $event
     *
     * @return void
     */
    public function handle(ServerUpdating $event)
    {
        $server = $event->server;

        if ($server->provider_server_id !== null && $server->isDirty('name')) {
            try {
                $server->serverProvider->client()->rename(
                    $server->provider_server_id,
                    $server->name,
                );
            } catch (Throwable $e) {
                if ($e instanceof ServerProviderAuthenticationException) {
                    $this->notifiables($event)->each->notify(new ServerProviderAuthenticationFailed($server->serverProvider));
                }

                throw $e;
            }
        }
    }

    private function notifiables(ServerUpdating $event) : Collection
    {
        return collect([
            $event->server->serverProvider->user(),
            $event->server->token->user,
        ])->filter()->unique('id')->values();
    }
}
