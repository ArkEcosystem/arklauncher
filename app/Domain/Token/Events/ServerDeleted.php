<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use App\Contracts\LogsActivity;
use App\Enums\ActivityDescriptionEnum;
use Domain\Server\Contracts\ServerProviderClient;
use Domain\Server\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final class ServerDeleted implements LogsActivity
{
    use Dispatchable;

    public Server $server;

    public ServerProviderClient $serverProviderClient;

    public ?int $providerServerId;

    public function __construct(Server $server)
    {
        $this->server               = $server;
        $this->serverProviderClient = $this->server->serverProvider->client();
        $this->providerServerId     = $this->server->provider_server_id;
    }

    public function subject() : Model
    {
        return $this->server;
    }

    public function description() : string
    {
        return ActivityDescriptionEnum::DELETED;
    }

    public function causer() : ?Model
    {
        return $this->server->token;
    }

    public function payload() : array
    {
        return [
            'preset' => $this->server->preset,
        ];
    }
}
