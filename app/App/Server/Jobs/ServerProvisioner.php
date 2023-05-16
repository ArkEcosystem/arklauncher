<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use DateTime;
use Domain\Server\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ServerProvisioner implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Server $server)
    {
    }

    public function handle(): ?PendingDispatch
    {
        // Server was successfully provisioned...
        if ($this->server->isProvisioned()) {
            $this->delete();

            return null;
        }

        // Server has failed to provision within the 30 mins...
        if ($this->server->olderThan(minutes: 30)) {
            $this->failed();

            return null;
        }

        // Server is still provisioning...
        if ($this->server->isProvisioning()) {
            $this->release(10);

            return null;
        }

        // Server is ready for provisioning when it has an IP address assigned to it...
        if ($this->server->isReadyForProvisioning()) {
            $this->server->touch('provisioning_job_dispatched_at');

            return ProvisionUser::dispatch($this->server);
        }

        $this->release(10);

        return null;
    }

    public function retryUntil() : DateTime
    {
        return now()->addMinutes(35);
    }

    public function failed() : void
    {
        $this->server->setStatus('failed');
    }
}
