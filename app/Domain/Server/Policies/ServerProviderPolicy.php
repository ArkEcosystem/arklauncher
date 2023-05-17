<?php

declare(strict_types=1);

namespace Domain\Server\Policies;

use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ServerProviderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, ServerProvider $serverProvider): bool
    {
        return $user->onToken($serverProvider->token);
    }

    public function view(User $user, ServerProvider $serverProvider): bool
    {
        return $user->onToken($serverProvider->token);
    }

    public function create(User $user, Token $token): bool
    {
        return $token->allows($user, 'server-provider:create');
    }

    public function update(User $user, ServerProvider $serverProvider): bool
    {
        return $serverProvider->token->allows($user, 'server-provider:update');
    }

    public function delete(User $user, ServerProvider $serverProvider): bool
    {
        return $serverProvider->token->allows($user, 'server-provider:delete');
    }
}
