<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Jobs\MaintainServerProviderKey;
use Domain\Server\Models\ServerProvider;
use Illuminate\Console\Command;

final class MaintainServerProviderKeys extends Command
{
    protected $signature = 'serverproviders:maintain';

    protected $description = 'Checks the API tokens for the server providers and removes any that no longer work.';

    public function handle(): void
    {
        ServerProvider::chunkById(5000, function ($serverProviders): void {
            foreach ($serverProviders as $serverProvider) {
                MaintainServerProviderKey::dispatch($serverProvider);
            }
        });
    }
}
