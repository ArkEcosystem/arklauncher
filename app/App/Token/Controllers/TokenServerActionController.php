<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use ARKEcosystem\Foundation\UserInterface\Support\Enums\FlashType;
use Domain\Server\Models\Server;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Http\RedirectResponse;
use Support\Http\Controllers\Controller;
use Throwable;

final class TokenServerActionController extends Controller
{
    public function start(Token $token, Network $network, int $serverId): RedirectResponse
    {
        $server = Server::findOrFail($serverId);
        $this->authorize('update', $server);

        try {
            $server->serverProvider->client()->start($server->provider_server_id);

            alert('tokens.servers.actions.started_success', FlashType::SUCCESS, ['server' => $server->name]);
        } catch (Throwable) {
            alert('tokens.servers.actions.started_failed', FlashType::ERROR, ['server' => $server->name]);
        }

        return back();
    }

    public function stop(Token $token, Network $network, int $serverId): RedirectResponse
    {
        $server = Server::findOrFail($serverId);
        $this->authorize('update', $server);

        try {
            $server->serverProvider->client()->stop($server->provider_server_id);

            alert('tokens.servers.actions.stopped_success', FlashType::SUCCESS, ['server' => $server->name]);
        } catch (Throwable) {
            alert('tokens.servers.actions.stopped_failed', FlashType::ERROR, ['server' => $server->name]);
        }

        return back();
    }

    public function reboot(Token $token, Network $network, int $serverId): RedirectResponse
    {
        $server = Server::findOrFail($serverId);
        $this->authorize('update', $server);

        try {
            $server->serverProvider->client()->reboot($server->provider_server_id);

            alert('tokens.servers.actions.rebooted_success', FlashType::SUCCESS, ['server' => $server->name]);
        } catch (Throwable) {
            alert('tokens.servers.actions.rebooted_failed', FlashType::ERROR, ['server' => $server->name]);
        }

        return back();
    }
}
