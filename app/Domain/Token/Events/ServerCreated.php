<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use App\Contracts\LogsActivity;
use App\Enums\ActivityDescriptionEnum;
use Domain\Server\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final class ServerCreated implements LogsActivity
{
    use Dispatchable;

    public function __construct(
        public Server $server
    ) {
    }

    public function subject() : Model
    {
        return $this->server;
    }

    public function description() : string
    {
        return ActivityDescriptionEnum::CREATED;
    }

    public function causer() : ?Model
    {
        return $this->server->token;
    }

    public function payload() : array
    {
        return [
            'preset' => $this->server->preset,
            'path'   => $this->server->pathShow(),
        ];
    }
}
