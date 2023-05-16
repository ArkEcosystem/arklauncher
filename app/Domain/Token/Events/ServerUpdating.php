<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use Domain\Server\Models\Server;
use Illuminate\Foundation\Events\Dispatchable;

final class ServerUpdating
{
    use Dispatchable;

    public function __construct(public Server $server)
    {
    }
}
