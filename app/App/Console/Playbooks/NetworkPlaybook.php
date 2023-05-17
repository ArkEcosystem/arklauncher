<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\Token\Models\Network;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NetworkPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        Network::factory()->count(10)->create();
    }
}
