<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\Coin\Models\Coin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CoinPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        Coin::factory()->create([
            'name'   => 'ARK',
            'symbol' => 'ARK',
        ]);
    }
}
