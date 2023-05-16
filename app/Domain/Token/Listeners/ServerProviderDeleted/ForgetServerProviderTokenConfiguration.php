<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\ServerProviderDeleted;

use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderDeleted;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Collection;

final class ForgetServerProviderTokenConfiguration
{
    /**
     * Execute the job.
     *
     * @param ServerProviderDeleted $event
     *
     * @return void
     */
    public function handle(ServerProviderDeleted $event)
    {
        $serverProvider = $event->serverProvider;

        $this->getRelatedTokens($serverProvider)->each->forgetServerConfiguration();
    }

    private function getRelatedTokens(ServerProvider $serverProvider): Collection
    {
        return Token::where('extra_attributes->'.ServerAttributeEnum::SERVER_CONFIG.'->server_provider_id', $serverProvider->id)->get();
    }
}
