<?php

declare(strict_types=1);

namespace Domain\Server\Services\Providers;

use Domain\SecureShell\Exceptions\SecureShellKeyAlreadyInUse;
use Domain\Server\Contracts\ServerProviderExceptionHandler;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderError;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class DigitalOceanExceptionHandler implements ServerProviderExceptionHandler
{
    public function __construct(private ?Exception $exception)
    {
    }

    public static function new(Exception $exception): self
    {
        return new static($exception);
    }

    public function handle(): mixed
    {
        if (! is_null($this->exception)) {
            $this->handleAuthenticationErrors();
            $this->handleCreate();
            $this->handleServerLookup();
            $this->handleExceptionWithMessage();
        }

        /* @phpstan-ignore-next-line  */
        throw $this->exception;
    }

    private function handleAuthenticationErrors(): void
    {
        if ($this->exception instanceof RequestException && $this->exception->response->status() === 401) {
            throw new ServerProviderAuthenticationException();
        }
    }

    private function handleCreate(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'message');

            if (Str::contains($error, 'exceed your droplet limit')) {
                throw new ServerLimitExceeded();
            }

            if (Str::contains($error, 'SSH Key is already in use')) {
                throw new SecureShellKeyAlreadyInUse();
            }
        }
    }

    private function handleServerLookup(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'message');

            if (Str::contains($error, 'resource you were accessing could not be found')) {
                throw new ServerNotFound();
            }
        }
    }

    private function handleExceptionWithMessage():void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'message');

            if ($error !== null) {
                throw new ServerProviderError($error);
            }

            throw new ServerProviderError();
        }
    }
}
