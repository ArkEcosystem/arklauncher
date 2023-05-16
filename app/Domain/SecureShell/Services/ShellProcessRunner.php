<?php

declare(strict_types=1);

namespace Domain\SecureShell\Services;

use Domain\SecureShell\Contracts\ShellProcessRunner as Contract;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ShellProcessRunner implements Contract
{
    public function run(Process $process): ShellResponse
    {
        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            $timedOut = true;
        }

        return new ShellResponse($process->getExitCode(), $process->getOutput(), $timedOut ?? false);
    }
}
