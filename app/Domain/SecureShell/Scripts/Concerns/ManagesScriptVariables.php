<?php

declare(strict_types=1);

namespace Domain\SecureShell\Scripts\Concerns;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait ManagesScriptVariables
{
    private function makeScriptVariables(array $attributes = []): array
    {
        $token   = $this->server->token;
        $network = $this->server->network;

        $result = array_merge((array) $token->config, $attributes, [
            // All
            'username'         => $this->user(),
            'user_password'    => $this->server->user_password,
            'sudo_password'    => $this->server->sudo_password,
            'privateKey'       => $token->getPrivateKey(),
            'publicKey'        => $token->keypair['publicKey'] ?? '',
            'authorizedKeys'   => $this->server->getAuthorizedKeys(),
            'token'            => $this->user(),
            'name'             => $token->name,
            'network'          => $network->name,
            'epoch'            => $network->epoch(),
            'ipAddress'        => $this->server->ip_address,
            'addressPrefix'    => $network->base58Prefix(),
            'server'           => $this->server,
            'explorerPath'     => '$HOME/core-explorer',
            // These are temporary URLs available during installation. They are no longer accessible after 30 minutes.
            'coreVersion'           => URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(30), ['server' => $this->server->id]),
            'deploymentStatus'      => URL::temporarySignedRoute('server.deployment.status', now()->addMinutes(30), ['server' => $this->server->id]),
            'storeConfigUrl'        => URL::temporarySignedRoute('server.deployment.config.store', now()->addMinutes(30), ['network' => $network->id]),
            'getConfigUrl'          => URL::temporarySignedRoute('server.deployment.config.show', now()->addMinutes(30), ['network' => $network->id]),
            'installationScriptUrl' => URL::temporarySignedRoute('server.deployment.installation-script.show', now()->addMinutes(30), ['network' => $network->id]),
        ]);

        if (array_key_exists('databaseName', $result)) {
            $result['databaseName'] = Str::snake(strtolower($result['databaseName']));
        }

        return $result;
    }
}
