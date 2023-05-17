<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\Collaborator\Models\Invitation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InvitationPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        Invitation::factory()->count(10)->create();
    }
}
