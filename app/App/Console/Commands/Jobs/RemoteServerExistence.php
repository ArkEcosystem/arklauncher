<?php

declare(strict_types=1);

namespace App\Console\Commands\Jobs;

use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class RemoteServerExistence implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Server $server)
    {
    }

    public function handle(): void
    {
        try {
            $this->server->serverProvider->client()->server($this->server->provider_server_id);
        } catch (ServerNotFound) {
            $this->server->delete();
        } catch (Throwable) {
            // ignore this?
        }
    }
}
