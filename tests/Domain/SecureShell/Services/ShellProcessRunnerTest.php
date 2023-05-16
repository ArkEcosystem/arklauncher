<?php

declare(strict_types=1);

use Domain\SecureShell\Services\ShellProcessRunner;
use Symfony\Component\Process\Process;

it('process runner runs process', function () {
    $process = Process::fromShellCommandline('sleep 0.1')->setTimeout(0.2);

    $response = (new ShellProcessRunner())->run($process);

    expect($response->exitCode)->toBe(0);
    expect($response->output)->toBe('');
    expect($response->timedOut)->toBeFalse();
});

it('process runner handles timeouts', function () {
    $process = Process::fromShellCommandline('sleep 0.1')->setTimeout(0.1);

    $response = (new ShellProcessRunner())->run($process);

    expect($response->exitCode)->toBe(0);
    expect($response->output)->toBe('');
    expect($response->timedOut)->toBeTrue();
});
