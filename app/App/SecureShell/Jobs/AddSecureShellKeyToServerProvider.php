<?php

declare(strict_types=1);

namespace App\SecureShell\Jobs;

use App\Server\Notifications\ServerProviderAuthenticationFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyAdditionFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyLimitReached as ServerProviderSecureShellKeyLimitReachedNotification;
use App\Server\Notifications\ServerProviderSecureShellKeyUniqueness as ServerProviderSecureShellKeyUniquenessNotification;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderSecureShellKeyLimitReached;
use Domain\Server\Exceptions\ServerProviderSecureShellKeyUniqueness;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderUpdated;
use Domain\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

final class AddSecureShellKeyToServerProvider implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected ServerProvider $serverProvider,
        protected User $initiator
    ) {
    }

    public function handle(): void
    {
        /** @var array $keypair */
        $keypair = $this->serverProvider->token->keypair;

        $key = $this->serverProvider->client()->createSecureShellKey(
            $this->serverProvider->token->slug.'-'.strtolower(Str::random(8)),
            $keypair['publicKey']
        );

        $this->serverProvider->update(['provider_key_id' => $key->id]);

        ServerProviderUpdated::dispatch($this->serverProvider);
    }

    public function failed(Throwable $exception) : void
    {
        report($exception);

        if ($exception instanceof ServerProviderAuthenticationException) {
            $this->notifiables()->each->notify(new ServerProviderAuthenticationFailed($this->serverProvider));
        } elseif ($exception instanceof ServerProviderSecureShellKeyUniqueness) {
            $this->notifiables()->each->notify(new ServerProviderSecureShellKeyUniquenessNotification($this->serverProvider));
        } elseif ($exception instanceof ServerProviderSecureShellKeyLimitReached) {
            $this->notifiables()->each->notify(new ServerProviderSecureShellKeyLimitReachedNotification($this->serverProvider));
        } else {
            $this->notifiables()->each->notify(new ServerProviderSecureShellKeyAdditionFailed($this->serverProvider));
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function notifiables() : Collection
    {
        return collect([
            $this->initiator,
            $this->serverProvider->user(),
            $this->serverProvider->token->user,
        ])->filter()->unique('id')->values();
    }
}
