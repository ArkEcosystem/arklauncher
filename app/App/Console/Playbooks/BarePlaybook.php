<?php

declare(strict_types=1);

namespace App\Console\Playbooks;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class BarePlaybook extends Playbook
{
    public function before(): array
    {
        return [
            UserPlaybook::once(),
            CoinPlaybook::once(),
        ];
    }

    public function run(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<info>[Playbook] Bare - success</info>');
    }
}
