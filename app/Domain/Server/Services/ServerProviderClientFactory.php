<?php

declare(strict_types=1);

namespace Domain\Server\Services;

use App\Enums\ServerProviderTypeEnum;
use Domain\Server\Contracts\ServerProviderClient;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Services\Providers\AWS;
use Domain\Server\Services\Providers\DigitalOcean;
use Domain\Server\Services\Providers\Hetzner;
use Domain\Server\Services\Providers\Linode;
use Domain\Server\Services\Providers\Vultr;
use InvalidArgumentException;

final class ServerProviderClientFactory
{
    public static function make(ServerProvider $serverProvider): ServerProviderClient
    {
        return match ($serverProvider->type) {
            ServerProviderTypeEnum::DIGITALOCEAN => new DigitalOcean($serverProvider),
            ServerProviderTypeEnum::HETZNER      => new Hetzner($serverProvider),
            ServerProviderTypeEnum::AWS          => new AWS($serverProvider),
            ServerProviderTypeEnum::VULTR        => new Vultr($serverProvider),
            ServerProviderTypeEnum::LINODE       => new Linode($serverProvider),
            default                              => throw new InvalidArgumentException(trans('exceptions.invalid_server_provider_type')),
        };
    }
}
