<?php

declare(strict_types=1);

namespace Tests\Domain\SecureShell\Services\Concerns;

use Domain\SecureShell\Contracts\Script;

class GetCurrentDirectory implements Script
{
    public function name(): string
    {
        return 'Echoing Current Directory';
    }

    public function script(): string
    {
        return 'pwd';
    }

    public function user(): string
    {
        return 'root';
    }

    public function timeout(): int
    {
        return 1;
    }
}
