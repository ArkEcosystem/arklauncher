<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use App\Server\Notifications\ServerProviderServerRemovalFailed;
use Domain\Server\Contracts\ServerProviderClient;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Throwable;

final class DestroyServerOnServerProvider implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 5;

    public function __construct(public Server $server, public ServerProviderClient $serverProviderClient, public int $providerServerId)
    {
    }

    public function handle(): void
    {
        $this->serverProviderClient->delete($this->providerServerId);
    }

    public function failed(Throwable $exception) : void
    {
        report($exception);

        $this->notifiables()->each->notify(
            new ServerProviderServerRemovalFailed($this->server->serverProvider)
        );
    }

    /**
     * @return Collection<int, User>
     */
    private function notifiables() : Collection
    {
        return collect([
            $this->server->creator(),
            $this->server->token->user,
            $this->server->serverProvider->user(),
        ])->filter()->unique('id')->values();
    }
}
