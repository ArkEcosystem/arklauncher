<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\User\Models\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UserPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        User::factory()->create(['email' => 'hello@ark.io']);
    }
}
