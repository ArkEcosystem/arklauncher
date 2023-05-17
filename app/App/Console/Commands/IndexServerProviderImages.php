<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Server\Jobs\IndexServerProviderImages as Job;
use Domain\Server\Models\ServerProvider;
use Illuminate\Console\Command;

final class IndexServerProviderImages extends Command
{
    protected $signature = 'servers:images';

    protected $description = 'Index the available images of all server providers.';

    public function handle(): void
    {
        ServerProvider::chunkById(5000, function ($serverProviders): void {
            foreach ($serverProviders as $serverProvider) {
                Job::dispatch($serverProvider);
            }
        });
    }
}
