<?php

declare(strict_types=1);

use Domain\SecureShell\Services\SecureShellCommand;

it('creates a command for a script', function () {
    expect(SecureShellCommand::forScript('127.0.0.1', 22, 'key', 'user', 'script'))
        ->toBe('ssh -tq -o BatchMode=yes -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i key -p 22 user@127.0.0.1 script');
});

it('creates a command for an upload', function () {
    expect(SecureShellCommand::forUpload('127.0.0.1', 22, 'key', 'user', 'from', 'to'))
        ->toBe('scp -i key -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o PasswordAuthentication=no -P 22 from user@127.0.0.1:to');
});
