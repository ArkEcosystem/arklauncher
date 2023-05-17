<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Jobs\RemoteServerExistence;
use Domain\Server\Models\Server;
use Illuminate\Console\Command;

final class CheckRemoteServerExistence extends Command
{
    protected $signature = 'servers:existence';

    protected $description = 'Check if servers still exist on the server provider and remove any deleted ones.';

    public function handle(): void
    {
        Server::chunkById(5000, function ($servers): void {
            foreach ($servers as $server) {
                RemoteServerExistence::dispatch($server);
            }
        });
    }
}
