<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Server\Models\Server;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Support\Http\Controllers\Controller;

final class TokenServerController extends Controller
{
    public function index(Token $token): View
    {
        $this->authorize('view', $token);

        return view('app.tokens.servers.index', [
            'token' => $token,
        ]);
    }

    public function create(Token $token, Network $network): View
    {
        $this->authorize('create', [Server::class, $token]);

        return view('app.tokens.servers.create', [
            'token'         => $token,
            'network'       => $network,
        ]);
    }

    public function show(Token $token, Network $network, int $serverId): View|RedirectResponse
    {
        // This method does not use model binding to bind Server model because in the Blade template we render a Livewire component...
        // However, since we want to display Deployment Failed modal even if the Server model is deleted, we can't use model binding
        // as that will (for some weird reason) automatically throw 404 modal when Livewire component is hydrated...

        $server = Server::findOrFail($serverId);

        $this->authorize('view', $server);

        return view('app.tokens.servers.show', [
            'token'   => $token,
            'network' => $network,
            'server'  => $server,
        ]);
    }
}
