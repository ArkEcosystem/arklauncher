<?php

declare(strict_types=1);

namespace Domain\SecureShell\Facades;

use Domain\SecureShell\Contracts\SecureShellKeyGenerator;
use Illuminate\Support\Facades\Facade;

final class SecureShellKey extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SecureShellKeyGenerator::class;
    }
}
