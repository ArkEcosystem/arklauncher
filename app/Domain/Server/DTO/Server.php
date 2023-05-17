<?php

declare(strict_types=1);

namespace Domain\Server\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
final class Server extends DataTransferObject
{
    public string|int $id;

    public string|int $name;

    public string|int $plan;

    public int $memory;

    public int $cores;

    public int $disk;

    public string $region;

    public string $status;

    public ?string $remoteAddress;

    public string $image;
}
