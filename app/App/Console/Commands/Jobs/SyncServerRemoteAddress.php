<?php

declare(strict_types=1);

namespace App\Console\Commands\Jobs;

use Domain\Server\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SyncServerRemoteAddress implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public Server $server)
    {
    }

    public function handle(): void
    {
        if ($this->server->ip_address === null) {
            $response = $this->server->serverProvider->client()->server($this->server->provider_server_id);

            $this->server->update([
                'ip_address' => $response->remoteAddress,
            ]);
        }
    }

    public function backoff() : array
    {
        return [3, 5, 10];
    }
}
