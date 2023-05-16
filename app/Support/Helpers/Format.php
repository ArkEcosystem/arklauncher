<?php

declare(strict_types=1);

namespace Support\Helpers;

use Illuminate\Support\Str;

final class Format
{
    public static function stepTitle(string $title): string
    {
        $toPluralize    = ['server_provider', 'collaborator', 'server'];
        $formattedTitle = Str::snake($title);

        if (in_array($formattedTitle, $toPluralize, true)) {
            $formattedTitle = Str::plural($formattedTitle);
        }

        return $formattedTitle;
    }

    public static function readableCrypto(int $value, int $decimals = 0): string
    {
        return number_format($value / 1e8, $decimals);
    }

    public static function withToken(string $value): string
    {
        return $value.'-'.strtolower(Str::random(8));
    }
}
