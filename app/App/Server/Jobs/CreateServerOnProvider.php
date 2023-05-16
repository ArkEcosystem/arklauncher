<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use App\Server\Notifications\RemoteServerLimitReached;
use App\Server\Notifications\ServerDeployed;
use App\Server\Notifications\ServerFailedToCreateOnProvider;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Throwable;

final class CreateServerOnProvider implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Server $server)
    {
    }

    public function handle() : void
    {
        $remoteServer = $this->server->serverProvider->client()->create($this->server);

        $this->server->update([
            'provider_server_id' => $remoteServer->id,
            'ip_address'         => $remoteServer->remoteAddress,
        ]);

        ServerProvisioner::dispatch($this->server);

        $this->notifiables(
            notifyProviderOwner: false
        )->each->notify(new ServerDeployed($this->server));
    }

    public function failed(Throwable $exception) : void
    {
        report($exception);

        // Server was actually created on the provider...
        if ($this->server->provider_server_id !== null) {
            $this->server->setStatus('failed');

            return;
        }

        $this->notifiables(notifyProviderOwner: true)->each->notify(
            $exception instanceof ServerLimitExceeded
                    ? new RemoteServerLimitReached($this->server->serverProvider)
                    : new ServerFailedToCreateOnProvider($this->server->serverProvider, $this->server->name)
        );

        $this->server->delete();
    }

    /**
     * @return Collection<int, User>
     */
    private function notifiables(bool $notifyProviderOwner) : Collection
    {
        return collect([
            $this->server->creator(),
            $this->server->token->user,
            $notifyProviderOwner ? $this->server->serverProvider->user() : null,
        ])->filter()->unique('id')->values();
    }
}
