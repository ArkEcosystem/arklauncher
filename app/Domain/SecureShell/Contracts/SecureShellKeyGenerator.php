<?php

declare(strict_types=1);

namespace Domain\SecureShell\Contracts;

use Domain\Token\Models\Token;

interface SecureShellKeyGenerator
{
    public function make(string $password): array;

    public function storeFor(Token $token): string;
}
