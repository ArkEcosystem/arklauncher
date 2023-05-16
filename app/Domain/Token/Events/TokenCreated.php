<?php

declare(strict_types=1);

namespace Domain\Token\Events;

use App\Contracts\LogsActivity;
use App\Enums\ActivityDescriptionEnum;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final class TokenCreated implements LogsActivity
{
    use Dispatchable;

    public function __construct(
        public Token $token
    ) {
    }

    public function subject() : Model
    {
        return $this->token;
    }

    public function description() : string
    {
        return ActivityDescriptionEnum::CREATED;
    }

    public function causer() : ?Model
    {
        return $this->token;
    }

    public function payload() : array
    {
        return [];
    }
}
