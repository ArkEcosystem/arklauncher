<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\User\Models\DatabaseNotification;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DatabaseNotificationPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        DatabaseNotification::factory()->count(5)->create();
    }
}
