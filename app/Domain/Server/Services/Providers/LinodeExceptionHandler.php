<?php

declare(strict_types=1);

namespace Domain\Server\Services\Providers;

use Domain\Server\Contracts\ServerProviderExceptionHandler;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Exceptions\ServerNotFound;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class LinodeExceptionHandler implements ServerProviderExceptionHandler
{
    public function __construct(private Exception $exception)
    {
    }

    public static function new(Exception $exception): self
    {
        return new self($exception);
    }

    public function handle(): mixed
    {
        $this->handleCreate();
        $this->handleServerLookup();

        throw $this->exception;
    }

    private function handleCreate(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'errors.0.reason');

            if (Str::contains($error, 'Account Limit reached')) {
                throw new ServerLimitExceeded();
            }
        }
    }

    private function handleServerLookup(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'errors.0.reason');

            if (Str::contains($error, 'Not found')) {
                throw new ServerNotFound();
            }
        }
    }
}
