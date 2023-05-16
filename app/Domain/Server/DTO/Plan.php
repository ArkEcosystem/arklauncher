<?php

declare(strict_types=1);

namespace Domain\Server\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * DigitalOcean : Regions is an array of slugs (strings)
 * Hetzner      : Regions is an array of slugs (strings)
 * Linode       : Regions is not provided
 * Vultr        : Regions is an array of location IDs (integers).
 */
#[Strict]
final class Plan extends DataTransferObject
{
    public string|int $id;

    public int $disk;

    public int $memory;

    public int $cores;

    public array $regions;
}
