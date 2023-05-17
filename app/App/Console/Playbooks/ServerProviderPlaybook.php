<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use Domain\Server\Models\ServerProvider;
use Domain\Token\Events\ServerProviderCreated;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ServerProviderPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        ServerProvider::factory()->count(10)->create()->each(function ($serverProvider): void {
            ServerProviderCreated::dispatch($serverProvider);
        });
    }
}
