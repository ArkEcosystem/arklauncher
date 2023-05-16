<?php

declare(strict_types=1);

namespace App\SecureShell\Jobs;

use App\Server\Notifications\ServerProviderAuthenticationFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyRemovalFailed;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Models\ServerProvider;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Throwable;

final class RemoveSecureShellKeyFromServerProvider implements ShouldQueue
{
    use SerializesModels;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 1;

    protected ServerProvider $serverProvider;

    public function __construct(ServerProvider $serverProvider)
    {
        $this->serverProvider = ServerProvider::withTrashed()->findOrFail($serverProvider->id);
    }

    public function handle(): void
    {
        /** @var string $providerKeyId */
        $providerKeyId = $this->serverProvider->provider_key_id;

        $this->serverProvider->client()->deleteSecureShellKey(
            $providerKeyId
        );

        $this->serverProvider->forceDelete();
    }

    public function failed(Throwable $exception): void
    {
        $this->serverProvider->forceDelete();

        report($exception);

        $this->notifiables()->each->notify(
            $exception instanceof ServerProviderAuthenticationException
                    ? new ServerProviderAuthenticationFailed($this->serverProvider)
                    : new ServerProviderSecureShellKeyRemovalFailed($this->serverProvider)
        );
    }

    /**
     * @return Collection<int, User>
     */
    private function notifiables() : Collection
    {
        return collect([
            $this->serverProvider->user(),
            $this->serverProvider->token->user,
        ])->filter()->unique('id')->values();
    }
}
