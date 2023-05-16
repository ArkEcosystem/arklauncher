<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use App\Contracts\LogsActivity;
use App\Enums\ActivityDescriptionEnum;
use Domain\Token\Models\Network;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final class NetworkCreated implements LogsActivity
{
    use Dispatchable;

    public function __construct(
        public Network $network
    ) {
    }

    public function subject() : Model
    {
        return $this->network;
    }

    public function description() : string
    {
        return ActivityDescriptionEnum::CREATED;
    }

    public function causer() : ?Model
    {
        return $this->network->token;
    }

    public function payload() : array
    {
        return [];
    }
}
