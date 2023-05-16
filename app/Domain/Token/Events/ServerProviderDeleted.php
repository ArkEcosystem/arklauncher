<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use App\Contracts\LogsActivity;
use App\Enums\ActivityDescriptionEnum;
use Domain\Server\Models\ServerProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final class ServerProviderDeleted implements LogsActivity
{
    use Dispatchable;

    public function __construct(
        public ServerProvider $serverProvider
    ) {
    }

    public function subject() : Model
    {
        return $this->serverProvider;
    }

    public function description() : string
    {
        return ActivityDescriptionEnum::DELETED;
    }

    public function causer() : ?Model
    {
        return $this->serverProvider->token;
    }

    public function payload() : array
    {
        return [
            'type' => $this->serverProvider->type,
        ];
    }
}
