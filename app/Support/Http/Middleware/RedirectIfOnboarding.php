<?php

declare(strict_types=1);

namespace Support\Http\Middleware;

use Closure;
use Domain\Token\Models\Token;
use Illuminate\Http\Request;

final class RedirectIfOnboarding
{
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var Token $token */
        $token = $request->route('token');

        if ($token->canBeEdited()) {
            return redirect(route('tokens.welcome', $token));
        }

        return $next($request);
    }
}
