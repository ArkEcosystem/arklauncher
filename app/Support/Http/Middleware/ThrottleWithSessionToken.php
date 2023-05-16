<?php

declare(strict_types=1);

namespace Support\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

final class ThrottleWithSessionToken extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param Request    $request
     * @param Closure    $next
     * @param int|string $maxAttempts
     * @param float|int  $decayMinutes
     * @param string     $prefix
     *
     * @throws ThrottleRequestsException
     *
     * @return Response
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = ''): mixed
    {
        $key = $prefix.$this->resolveRequestSignature($request);

        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $request->session()->forget('app:auth:id');

            throw $this->buildException($request, $key, $maxAttempts);
        }

        $this->limiter->hit($key, (int) $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }
}
