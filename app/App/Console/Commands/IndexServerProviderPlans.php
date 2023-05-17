<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Server\Jobs\IndexServerProviderPlans as Job;
use Domain\Server\Models\ServerProvider;
use Illuminate\Console\Command;

final class IndexServerProviderPlans extends Command
{
    protected $signature = 'servers:plans';

    protected $description = 'Index the available plans of all server providers.';

    public function handle(): void
    {
        ServerProvider::chunkById(5000, function ($serverProviders): void {
            foreach ($serverProviders as $serverProvider) {
                Job::dispatch($serverProvider);
            }
        });
    }
}
