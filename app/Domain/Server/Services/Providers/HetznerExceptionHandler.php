<?php

declare(strict_types=1);

namespace Domain\Server\Services\Providers;

use Domain\Server\Contracts\ServerProviderExceptionHandler;
use Domain\Server\Exceptions\ServerCoreLimitExceeded;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Exceptions\ServerProviderSecureShellKeyLimitReached;
use Domain\Server\Exceptions\ServerProviderSecureShellKeyUniqueness;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class HetznerExceptionHandler implements ServerProviderExceptionHandler
{
    public function __construct(private ?Exception $exception, private ?array $response = [])
    {
    }

    public static function new(Exception $exception, ?array $response = []): self
    {
        return new static($exception, $response);
    }

    public static function newWithResponse(array $response): self
    {
        return new static(null, $response);
    }

    public function handle(): mixed
    {
        if ($this->response !== null && count($this->response) > 0) {
            $this->handleErrorStatus();
        }

        if (! is_null($this->exception)) {
            $this->handleCreate();
            $this->handleCoreLimit();
            $this->handleServerLookup();
            $this->handleAuthentication();
            $this->handleSecureShellKeyConflict();
            $this->handleSecureShellKeyLimitReached();
            $this->handleExceptionWithMessage();

            /* @phpstan-ignore-next-line  */
            throw $this->exception;
        }

        return null;
    }

    private function handleErrorStatus(): void
    {
        if ($this->response !== null && Arr::get($this->response, 'action.status') === 'error') {
            throw new ServerProviderError();
        }
    }

    private function handleCreate(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'error.message');

            if (Str::contains($error, 'server limit exceeded')) {
                throw new ServerLimitExceeded();
            }
        }
    }

    private function handleAuthentication(): void
    {
        if ($this->exception instanceof RequestException && $this->exception->response->status() === 401) {
            throw new ServerProviderAuthenticationException();
        }
    }

    private function handleCoreLimit(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'error.message');

            if (Str::contains($error, 'core limit exceeded')) {
                throw new ServerCoreLimitExceeded();
            }
        }
    }

    private function handleServerLookup(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'error.code');

            if (Str::contains($error, 'not_found')) {
                throw new ServerNotFound();
            }
        }
    }

    private function handleSecureShellKeyConflict(): void
    {
        if ($this->exception instanceof RequestException) {
            if ($this->exception->response->status() === 409) {
                throw new ServerProviderSecureShellKeyUniqueness();
            }
        }
    }

    private function handleSecureShellKeyLimitReached(): void
    {
        if ($this->exception instanceof RequestException) {
            if ($this->exception->response->status() === 403) {
                throw new ServerProviderSecureShellKeyLimitReached();
            }
        }
    }

    private function handleExceptionWithMessage():void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'error.message');

            if ($error !== null) {
                throw new ServerProviderError($error);
            }

            throw new ServerProviderError();
        }
    }
}
