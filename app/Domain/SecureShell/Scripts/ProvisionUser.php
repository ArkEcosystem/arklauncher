<?php

declare(strict_types=1);

namespace Domain\SecureShell\Scripts;

use Domain\SecureShell\Contracts\Script;
use Domain\SecureShell\Scripts\Concerns\LocatesScript;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerTask;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

final class ProvisionUser implements Script
{
    use LocatesScript;

    public function __construct(private Server $server)
    {
    }

    public function name(): string
    {
        return trans('scripts.names.provision_user', ['server' => $this->server->name]);
    }

    public function script(): string
    {
        $token = $this->server->token;

        return View::make($this->getScriptPath($token, 'provision-user'), [
            'username'       => $token->normalized_token,
            'preset'         => $this->server->preset,
            'user_password'  => $this->server->user_password,
            'sudo_password'  => $this->server->sudo_password,
            'privateKey'     => $token->getPrivateKey(),
            'publicKey'      => $token->keypair['publicKey'] ?? '',
            'authorizedKeys' => $this->server->getAuthorizedKeys(),
            // These are temporary URLs available during installation. They are no longer accessible after 30 minutes.
            'deploymentStatus' => URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(30), ['server' => $this->server->id]),
        ])->render();
    }

    public function user(): string
    {
        return 'root';
    }

    public function timeout(): int
    {
        return ServerTask::DEFAULT_TIMEOUT;
    }
}
