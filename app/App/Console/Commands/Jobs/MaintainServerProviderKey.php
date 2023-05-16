<?php

declare(strict_types=1);

namespace App\Console\Commands\Jobs;

use App\Server\Notifications\ServerProviderAuthenticationFailed;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderDeleted;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class MaintainServerProviderKey implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ServerProvider $serverProvider)
    {
    }

    public function handle(): void
    {
        if (! $this->serverProvider->client()->valid()) {
            $this->notifiables($this->serverProvider)->each->notify(new ServerProviderAuthenticationFailed($this->serverProvider));

            $this->serverProvider->delete();

            ServerProviderDeleted::dispatch($this->serverProvider);
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function notifiables(ServerProvider $provider) : Collection
    {
        return collect([
            $provider->user(),
            $provider->token->user,
        ])->filter()->unique('id')->values();
    }
}
