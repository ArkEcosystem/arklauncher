<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use App\Contracts\LogsActivity;
use App\Enums\ActivityDescriptionEnum;
use Domain\Server\Models\ServerProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final class ServerProviderCreated implements LogsActivity
{
    use Dispatchable;

    public function __construct(
        public ServerProvider $provider
    ) {
    }

    public function subject() : Model
    {
        return $this->provider;
    }

    public function description() : string
    {
        return ActivityDescriptionEnum::CREATED;
    }

    public function causer() : ?Model
    {
        return $this->provider->token;
    }

    public function payload() : array
    {
        return [
            'type' => $this->provider->type,
        ];
    }
}
