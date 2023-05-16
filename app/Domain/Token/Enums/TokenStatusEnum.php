<?php

declare(strict_types=1);

namespace Domain\Token\Enums;

final class TokenStatusEnum
{
    public const PENDING = 'pending';

    public const FINISHED = 'finished';

    public static function isPending(string $value):bool
    {
        return $value === static::PENDING;
    }
}
