<?php

declare(strict_types=1);

namespace App\Enums;

final class ServerProviderTypeEnum
{
    public const AWS = 'aws';

    public const AZURE = 'azure';

    public const DIGITALOCEAN = 'digitalocean';

    public const HETZNER = 'hetzner';

    public const LINODE = 'linode';

    public const VULTR = 'vultr';

    public static function isAws(string $value):bool
    {
        return $value === static::AWS;
    }

    public static function label(string $value) : string
    {
        return match ($value) {
            static::AWS          => 'Amazon Web Services',
            static::AZURE        => 'Azure',
            static::DIGITALOCEAN => 'DigitalOcean',
            static::HETZNER      => 'Hetzner',
            static::LINODE       => 'Linode',
            static::VULTR        => 'Vultr',
            default              => 'Other',
        };
    }
}
