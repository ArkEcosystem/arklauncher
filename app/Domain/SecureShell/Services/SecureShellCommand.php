<?php

declare(strict_types=1);

namespace Domain\SecureShell\Services;

final class SecureShellCommand
{
    public static function forScript(string $ipAddress, int $port, string $keyPath, string $user, string $script): string
    {
        return sprintf(
            'ssh -tq -o BatchMode=yes -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i %s -p %s %s %s',
            $keyPath,
            $port,
            $user.'@'.$ipAddress,
            $script,
        );
    }

    public static function forUpload(string $ipAddress, int $port, string $keyPath, string $user, string $from, string $to): string
    {
        return sprintf(
            'scp -i %s -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o PasswordAuthentication=no -P %s %s %s:%s',
            $keyPath,
            $port,
            $from,
            $user.'@'.$ipAddress,
            $to
        );
    }
}
