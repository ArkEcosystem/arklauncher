<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Jobs\SyncServerRemoteAddress;
use Domain\Server\Models\Server;
use Illuminate\Console\Command;

final class SyncServerRemoteAddresses extends Command
{
    protected $signature = 'servers:ips';

    protected $description = 'Sync the remote address of each server that is missing it.';

    public function handle(): void
    {
        Server::whereNull('ip_address')
            ->whereNotNull('provider_server_id')
            ->chunkById(5000, function ($servers): void {
                foreach ($servers as $server) {
                    SyncServerRemoteAddress::dispatch($server);
                }
            });
    }
}
