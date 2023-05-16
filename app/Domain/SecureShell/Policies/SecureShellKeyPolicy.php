<?php

declare(strict_types=1);

namespace Domain\SecureShell\Policies;

use Domain\SecureShell\Models\SecureShellKey;
use Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class SecureShellKeyPolicy
{
    use HandlesAuthorization;

    public function view(User $user, SecureShellKey $key): bool
    {
        return $key->user->id === $user->id;
    }
}
