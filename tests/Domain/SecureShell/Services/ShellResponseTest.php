<?php

declare(strict_types=1);

use Domain\SecureShell\Services\ShellResponse;

it('creates a response', function () {
    $response = new ShellResponse(123, 'output', true);

    expect($response->exitCode)->toBe(123);
    expect($response->output)->toBe('output');
    expect($response->timedOut)->toBeTrue();
});
