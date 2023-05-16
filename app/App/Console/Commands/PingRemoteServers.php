<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Jobs\PingServer;
use Domain\Server\Models\Server;
use Illuminate\Console\Command;

final class PingRemoteServers extends Command
{
    protected $signature = 'servers:ping';

    protected $description = 'Ping all servers to ensure they are accessible from the public.';

    public function handle(): void
    {
        Server::chunkById(5000, function ($servers): void {
            foreach ($servers as $server) {
                PingServer::dispatch($server);
            }
        });
    }
}
