<?php

declare(strict_types=1);

namespace Domain\Server\Contracts;

interface ServerProviderExceptionHandler
{
    public function handle(): mixed;
}
