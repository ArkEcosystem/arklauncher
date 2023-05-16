<?php

declare(strict_types=1);

namespace Domain\Server\Enums;

final class PresetTypeEnum
{
    public const GENESIS = 'genesis';

    public const SEED = 'seed';

    public const RELAY = 'relay';

    public const FORGER = 'forger';

    public const EXPLORER = 'explorer';

    public static function isGenesis(string $value):bool
    {
        return $value === static::GENESIS;
    }

    public static function isSeed(string $value):bool
    {
        return $value === static::SEED;
    }

    public static function isRelay(string $value):bool
    {
        return $value === static::RELAY;
    }

    public static function isForger(string $value):bool
    {
        return $value === static::FORGER;
    }

    public static function isExplorer(string $value):bool
    {
        return $value === static::EXPLORER;
    }
}
