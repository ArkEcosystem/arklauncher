<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Server\Jobs\IndexServerProviderRegions as Job;
use Domain\Server\Models\ServerProvider;
use Illuminate\Console\Command;

final class IndexServerProviderRegions extends Command
{
    protected $signature = 'servers:regions';

    protected $description = 'Index the available regions of all server providers.';

    public function handle(): void
    {
        ServerProvider::chunkById(5000, function ($serverProviders): void {
            foreach ($serverProviders as $serverProvider) {
                Job::dispatch($serverProvider);
            }
        });
    }
}
