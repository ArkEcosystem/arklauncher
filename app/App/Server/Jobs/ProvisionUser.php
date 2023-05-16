<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use Domain\SecureShell\Scripts\ProvisionUser as Script;
use Domain\Server\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProvisionUser implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 30;

    public function __construct(public Server $server)
    {
    }

    public function handle(): void
    {
        $task = $this->server->addTask(new Script($this->server))->run();

        if ($task->isSuccessful()) {
            ProvisionServer::dispatch($this->server)->onQueue('long-running-queue');
        } else {
            $this->release(10);
        }
    }

    public function failed(): void
    {
        $this->server->setStatus('failed');
    }
}
