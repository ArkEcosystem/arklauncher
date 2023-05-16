<?php

declare(strict_types=1);

namespace App\Http;

use ARKEcosystem\Foundation\UserInterface\Http\Middlewares\VerifyRecaptcha;
use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;
use Support\Http\Middleware\Authenticate;
use Support\Http\Middleware\EncryptCookies;
use Support\Http\Middleware\PreventRequestsDuringMaintenance;
use Support\Http\Middleware\RedirectIfAuthenticated;
use Support\Http\Middleware\RedirectIfOnboarding;
use Support\Http\Middleware\ThrottleWithSessionToken;
use Support\Http\Middleware\TrimStrings;
use Support\Http\Middleware\TrustProxies;
use Support\Http\Middleware\VerifyCsrfToken;

final class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            // \Spatie\ResponseCache\Middlewares\CacheResponse::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'                     => Authenticate::class,
        'auth.basic'               => AuthenticateWithBasicAuth::class,
        'bindings'                 => SubstituteBindings::class,
        'cache.headers'            => SetCacheHeaders::class,
        'can'                      => Authorize::class,
        'guest'                    => RedirectIfAuthenticated::class,
        'signed'                   => ValidateSignature::class,
        'throttle'                 => ThrottleRequests::class,
        'throttleWithSessionToken' => ThrottleWithSessionToken::class,
        'verified'                 => EnsureEmailIsVerified::class,
        'cacheResponse'            => CacheResponse::class,
        'doNotCacheResponse'       => DoNotCacheResponse::class,
        'onboard'                  => RedirectIfOnboarding::class,
        'recaptcha'                => VerifyRecaptcha::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array<string>
     */
    protected $middlewarePriority = [
        StartSession::class,
        ShareErrorsFromSession::class,
        Authenticate::class,
        ThrottleRequests::class,
        AuthenticateSession::class,
        SubstituteBindings::class,
        Authorize::class,
    ];
}
