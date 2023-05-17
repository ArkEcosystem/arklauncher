<?php

declare(strict_types=1);

namespace Domain\SecureShell\Scripts;

use Domain\SecureShell\Contracts\Script;
use Domain\SecureShell\Scripts\Concerns\LocatesScript;
use Domain\SecureShell\Scripts\Concerns\ManagesScriptVariables;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerTask;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\View;

final class ProvisionForger implements Script
{
    use LocatesScript;
    use ManagesScriptVariables;

    private Token $token;

    public function __construct(private Server $server)
    {
        $this->token = $server->token;
    }

    public function name(): string
    {
        return trans('scripts.names.provision_forger', ['server' => $this->server->name]);
    }

    public function script(): string
    {
        return View::make(
            $this->getScriptPath($this->token, 'provision-forger'),
            $this->makeScriptVariables([
                'passphrase'         => $this->server->delegate_passphrase,
                'passphrasePassword' => $this->server->delegate_password, // only relevant for bip38
                'passphraseMethod'   => is_null($this->server->delegate_password) ? 'bip39' : 'bip38',
            ])
        )->render();
    }

    public function user(): string
    {
        return $this->token->normalized_token;
    }

    public function timeout(): int
    {
        return ServerTask::DEFAULT_TIMEOUT;
    }
}
