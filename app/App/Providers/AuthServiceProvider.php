<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\SecureShell\Models\SecureShellKey;
use Domain\SecureShell\Policies\SecureShellKeyPolicy;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Policies\ServerPolicy;
use Domain\Server\Policies\ServerProviderPolicy;
use Domain\Token\Models\Token;
use Domain\Token\Policies\TokenPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SecureShellKey::class => SecureShellKeyPolicy::class,
        Server::class         => ServerPolicy::class,
        ServerProvider::class => ServerProviderPolicy::class,
        Token::class          => TokenPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
