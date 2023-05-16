<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\SecureShell\Models\SecureShellKey;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SecureShellKeyPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        SecureShellKey::factory()->count(10)->create();
    }
}
