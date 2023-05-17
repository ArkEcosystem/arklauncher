<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use Domain\Server\Models\ServerProvider;
use Illuminate\Foundation\Events\Dispatchable;

final class ServerProviderUpdated
{
    use Dispatchable;

    public function __construct(public ServerProvider $serverProvider)
    {
    }
}
