<?php

declare(strict_types=1);

namespace Support\Components\Concerns;

use Domain\User\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * @property User $user
 */
trait InteractsWithUser
{
    public function getUserProperty(): ?User
    {
        /** @var User */
        return Auth::user();
    }
}
