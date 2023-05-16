<?php

declare(strict_types=1);

namespace Support\Components\Concerns;

use Domain\Token\Models\Token;

trait InteractsWithToken
{
    /** @var Token */
    public $token;

    public function mount(Token $token): void
    {
        $this->token = $token;
    }
}
