<?php

declare(strict_types=1);

namespace Support\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;

    /**
     * Authorize a list of actions for the current user.
     *
     * @param array       $abilities
     * @param mixed|array $arguments
     *
     * @throws AuthorizationException
     *
     * @return Response
     */
    final protected function authorizeAny(array $abilities, $arguments = [])
    {
        if (app(Gate::class)->any($abilities, $arguments)) {
            return Response::allow();
        }

        throw new AuthorizationException();
    }
}
