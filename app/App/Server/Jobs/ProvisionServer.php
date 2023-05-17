<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use App\Server\Notifications\ServerProvisioned;
use Domain\SecureShell\Scripts\ProvisionExplorer;
use Domain\SecureShell\Scripts\ProvisionForger;
use Domain\SecureShell\Scripts\ProvisionGenesis;
use Domain\SecureShell\Scripts\ProvisionRelay;
use Domain\SecureShell\Scripts\ProvisionSeed;
use Domain\Server\Models\Server;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProvisionServer implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    private array $presets = [
        'explorer' => ProvisionExplorer::class,
        'forger'   => ProvisionForger::class,
        'genesis'  => ProvisionGenesis::class,
        'relay'    => ProvisionRelay::class,
        'seed'     => ProvisionSeed::class,
    ];

    public function __construct(public Server $server)
    {
    }

    public function handle(): void
    {
        /** @var ProvisionRelay $script */
        $script = new $this->presets[$this->server->preset]($this->server);

        $task = $this->server->addTask($script)->run();

        if (! $task->isSuccessful()) {
            throw new Exception($task->output);
        }

        RemovePasswordsFromServer::dispatch($this->server);

        $this->server
                ->token
                ->collaborators
                ->each
                ->notify(new ServerProvisioned($this->server));
    }
}
