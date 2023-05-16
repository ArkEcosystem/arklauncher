<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Domain\Token\Models\Token;
use Domain\User\Models\User;

trait CreatesModels
{
    protected function user(?array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function token(?User $user = null, ?string $status = null): Token
    {
        $token = $user
            ? Token::factory()->ownedBy($user)->createForTest()
            : Token::factory()->createForTest();

        if ($status) {
            $token->setStatus($status);
        }

        return $token;
    }
}
