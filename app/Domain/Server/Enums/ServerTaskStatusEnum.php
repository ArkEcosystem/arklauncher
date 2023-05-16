<?php

declare(strict_types=1);

namespace Domain\Server\Enums;

final class ServerTaskStatusEnum
{
    public const PENDING = 'pending';

    public const RUNNING = 'running';

    public const FAILED = 'failed';

    public const FINISHED = 'finished';

    public const TIMEOUT = 'timeout';

    public static function isPending(string $value):bool
    {
        return $value === static::PENDING;
    }

    public static function isRunning(string $value):bool
    {
        return $value === static::RUNNING;
    }

    public static function isFailed(string $value):bool
    {
        return $value === static::FAILED;
    }
}
