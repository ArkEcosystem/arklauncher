<?php

declare(strict_types=1);

namespace Domain\SecureShell\Scripts;

use Domain\SecureShell\Contracts\Script;
use Domain\SecureShell\Scripts\Concerns\LocatesScript;
use Domain\Server\Models\ServerTask;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\View;

final class ManualScript implements Script
{
    use LocatesScript;

    public function __construct(private Token $token, private string $network)
    {
        //
    }

    public function name(): string
    {
        return trans('scripts.names.manual_server');
    }

    public function script(): string
    {
        /** @var Network $network */
        $network = $this->token->network($this->network);

        return View::make(
            $this->getScriptPath($this->token, 'provision-manual'),
            array_merge((array) $this->token->config, [
                'token'         => $this->user(),
                'name'          => $this->token->name,
                'peers'         => $network->getGenesis()->ip_address,
                'network'       => $network->name,
                'epoch'         => $network->epoch(),
                'addressPrefix' => $network->base58Prefix(),
                'explorerPath'  => '\\'.'$HOME/core-explorer',
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
