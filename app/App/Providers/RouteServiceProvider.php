<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Token\Models\Token;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

final class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/app/tokens';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        // Because we register a Token model in a custom namespace, we'll register a Route binding to resolve the model when `{token}` is passed as a parameter...
        // However, Laravel Fortify specifies `{token}` attribute for password reset routes, which is a direct collision with our implementation,
        // so we to dynamically bind the value.
        Route::bind('token', function ($value) {
            // Password Reset Token is usually 64 chars...
            if (strlen($value) >= 64) {
                return $value;
            }

            return (new Token())->resolveRouteBinding($value);
        });

        $this->routes(function (): void {
            $this->mapApiRoutes();

            $this->mapWebRoutes();
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    private function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    private function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60);
        });
    }
}
