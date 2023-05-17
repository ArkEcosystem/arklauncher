<?php

declare(strict_types=1);

namespace Domain\SecureShell\Contracts;

use Domain\SecureShell\Services\ShellResponse;
use Symfony\Component\Process\Process;

interface ShellProcessRunner
{
    public function run(Process $process): ShellResponse;
}
