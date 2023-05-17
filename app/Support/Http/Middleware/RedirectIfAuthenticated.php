<?php

declare(strict_types=1);

namespace Support\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Request     $request
     * @param Closure     $next
     * @param string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null): mixed
    {
        if (Auth::guard($guard)->check()) {
            return redirect(route('tokens'));
        }

        return $next($request);
    }
}
