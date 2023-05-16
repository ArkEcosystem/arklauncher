<?php

declare(strict_types=1);

namespace Support\Services;

final class PasswordGenerator
{
    private static string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-=+?';

    public static function make(int $length): string
    {
        return substr(str_shuffle(static::$alphabet), 0, $length);
    }
}
