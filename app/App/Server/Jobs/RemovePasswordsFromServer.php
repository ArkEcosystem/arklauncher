<?php

declare(strict_types=1);

namespace App\Server\Jobs;

use Domain\Server\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RemovePasswordsFromServer implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Server $server)
    {
    }

    public function handle(): void
    {
        $this->server->update([
            'user_password'       => null,
            'sudo_password'       => null,
            'delegate_passphrase' => null,
            'delegate_password'   => null,
            'provisioned_at'      => now(),
        ]);
    }
}
