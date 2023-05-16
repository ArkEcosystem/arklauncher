<?php

declare(strict_types=1);

namespace Domain\SecureShell\Services;

final class ShellResponse
{
    public function __construct(public ?int $exitCode, public string $output, public bool $timedOut = false)
    {
    }
}
