<?php

declare(strict_types=1);

namespace Domain\Server\Services\Providers;

use Domain\Server\Contracts\ServerProviderExceptionHandler;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class VultrExceptionHandler implements ServerProviderExceptionHandler
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

        throw $this->exception;
    }

    private function handleCreate(): void
    {
        if ($this->exception instanceof RequestException) {
            $error = Arr::get(json_decode($this->exception->response->body(), true), 'reasonPhrase');

            if (Str::is($error, 'You have reached the maximum monthly fee limit for this account.')) {
                throw new ServerLimitExceeded();
            }
        }
    }
}
