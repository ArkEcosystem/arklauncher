<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Server\Notifications\ServerFailedDeployment;
use Domain\Server\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class PurgeServersWithFailedDeployments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servers:purge-failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all servers with failed deployments.';

    public function handle(): void
    {
        Server::each(function ($server): void {
            if (Str::startsWith($server->status, 'failed')) {
                $server->token->user->notify(new ServerFailedDeployment($server));

                $creator = $server->creator();

                if ($creator && $creator->isNot($server->token->user)) {
                    $creator->notify(new ServerFailedDeployment($server));
                }

                $server->delete();
            }
        });
    }
}
