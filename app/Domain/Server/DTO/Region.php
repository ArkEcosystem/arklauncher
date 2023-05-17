<?php

declare(strict_types=1);

namespace Domain\Server\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
final class Region extends DataTransferObject
{
    public string|int $id;

    public string $name;
}
