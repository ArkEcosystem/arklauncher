<?php

declare(strict_types=1);

namespace Support\Services;

final class Json
{
    public static function parseConfig(string $input): array
    {
        return json_decode(str_replace('$HOME', '\\\\$HOME', $input), true, 512, JSON_THROW_ON_ERROR);
    }
}
