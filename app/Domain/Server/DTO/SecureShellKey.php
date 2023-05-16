<?php

declare(strict_types=1);

namespace Domain\Server\DTO;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
final class SecureShellKey extends DataTransferObject
{
    public string|int $id;

    public ?string $publicKey;
}
