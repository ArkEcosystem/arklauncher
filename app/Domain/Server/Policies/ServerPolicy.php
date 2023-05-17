<?php

declare(strict_types=1);

namespace Domain\Server\Policies;

use Domain\Server\Models\Server;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ServerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Server $server): bool
    {
        return $user->onToken($server->token);
    }

    public function view(User $user, Server $server): bool
    {
        return $user->onToken($server->token);
    }

    public function create(User $user, Token $token): bool
    {
        return $token->allows($user, 'server:create');
    }

    public function update(User $user, Server $server): bool
    {
        return $server->token->allows($user, 'server:update');
    }

    public function delete(User $user, Server $server): bool
    {
        return $server->token->allows($user, 'server:delete');
    }

    public function start(User $user, Server $server): bool
    {
        return $server->token->allows($user, 'server:start');
    }

    public function stop(User $user, Server $server): bool
    {
        return $server->token->allows($user, 'server:stop');
    }

    public function restart(User $user, Server $server): bool
    {
        return $server->token->allows($user, 'server:restart');
    }

    public function rename(User $user, Server $server): bool
    {
        return $server->token->allows($user, 'server:rename');
    }
}
