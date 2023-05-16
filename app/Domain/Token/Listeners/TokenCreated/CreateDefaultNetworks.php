<?php

declare(strict_types=1);

namespace Domain\Token\Listeners\TokenCreated;

use Domain\Token\Events\TokenCreated;

final class CreateDefaultNetworks
{
    public const DEFAULT_NETWORKS = ['mainnet', 'devnet', 'testnet'];

    public function handle(TokenCreated $event) : void
    {
        $event->token->networks()->createMany($this->networks());
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function networks() : array
    {
        return collect(static::DEFAULT_NETWORKS)->map(fn (string $network) => [
            'name' => $network,
        ])->toArray();
    }
}
