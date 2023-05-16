<?php

declare(strict_types=1);

namespace Domain\SecureShell\Facades;

use Illuminate\Support\Facades\Facade;

final class ShellProcessRunner extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Domain\SecureShell\Contracts\ShellProcessRunner::class;
    }
}
