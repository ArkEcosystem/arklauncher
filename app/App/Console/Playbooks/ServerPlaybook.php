<?php

declare(strict_types=1);

namespace  App\Console\Playbooks;

use App\Listeners\ListenForActivityLogs;
use Domain\Server\Models\Server;
use Domain\Token\Events\ServerCreated;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ServerPlaybook extends Playbook
{
    public function run(InputInterface $input, OutputInterface $output): void
    {
        Server::factory()->count(10)->create()->each(function ($server): void {
            $this->fakeServerCreatedEvent($server);
        });
    }

    /**
     * We should usually call a `ServerCreated::dispatch($server);` event, so
     * the following listeners are called. However, since we are using a fake
     * server, the `CreateServerOnProvider` listener had problems connecting to
     * the server provider for provisioning so I am manually changing the server
     * status and running the listeners.
     *
     * @param \Domain\Server\Models\Server $server
     *
     * @return void
     */
    private function fakeServerCreatedEvent($server): void
    {
        $event = new ServerCreated($server);

        (new ListenForActivityLogs())->handle($event);

        // Instead of (new CreateServerOnProvider())->handle($event);
        $server->update([
            'provider_server_id'             => rand(5, 15),
            'ip_address'                     => '127.0.0.1',
            'provisioning_job_dispatched_at' => now(),
        ]);
        $server->setStatus('provisioning');
        $server->markAsOnline();
    }
}
