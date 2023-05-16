<?php

declare(strict_types=1);

namespace Domain\Token\Policies;

use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class TokenPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Token $token): bool
    {
        return $user->onToken($token);
    }

    public function view(User $user, Token $token): bool
    {
        return $user->onToken($token);
    }

    public function update(User $user, Token $token): bool
    {
        return $token->allows($user, 'token:update');
    }

    public function delete(User $user, Token $token): bool
    {
        return $token->allows($user, 'token:delete');
    }

    public function createCollaborator(User $user, Token $token): bool
    {
        return $token->allows($user, 'collaborator:create');
    }

    public function deleteCollaborator(User $user, Token $token): bool
    {
        return $token->allows($user, 'collaborator:delete');
    }

    public function manageKeys(User $user, Token $token): bool
    {
        return $token->allows($user, 'ssh-key:manage');
    }
}
