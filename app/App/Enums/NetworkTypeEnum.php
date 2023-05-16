<?php

declare(strict_types=1);

namespace App\Enums;

final class NetworkTypeEnum
{
    public const MAINNET = 'mainnet';

    public const DEVNET = 'devnet';

    public const TESTNET = 'testnet';

    // TODO: this will be removed once this is enum...
    public static function all() : array
    {
        return [
            static::MAINNET,
            static::DEVNET,
            static::TESTNET,
        ];
    }

    public static function alias(string $value) : string
    {
        if ($value === 'mainnet') {
            return 'production';
        }

        return 'development';
    }
}
