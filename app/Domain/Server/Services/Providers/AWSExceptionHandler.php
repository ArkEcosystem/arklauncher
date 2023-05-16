<?php

declare(strict_types=1);

namespace Domain\Server\Services\Providers;

use Domain\Server\Contracts\ServerProviderExceptionHandler;
use Exception;

final class AWSExceptionHandler implements ServerProviderExceptionHandler
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
        throw $this->exception;
    }
}
