<?php

declare(strict_types=1);

use App\Server\Jobs\ProvisionServer;
use App\Server\Jobs\ProvisionUser;
use Domain\SecureShell\Contracts\ShellProcessRunner;
use Domain\SecureShell\Services\ShellResponse;
use Domain\Server\Models\Server;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;

it('dispatches jobs to provision the user', function () {
    $this->expectsJobs([ProvisionServer::class]);

    $server = Server::factory()->createForTest();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '', false));
    });

    (new ProvisionUser($server))->handle();
});

it('sets server status to failed on failed job', function () {
    $server = Server::factory()->createForTest();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '', false));
    });

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
    expect(Server::currentStatus('failed')->count())->toBe(0);

    (new ProvisionUser($server))->failed();

    $this->assertDatabaseHas('servers', ['id' => $server->id]);
    expect(Server::currentStatus('failed')->count())->toBe(1);
});

it('dispatches jobs to provision the user and fails', function () {
    $this->doesntExpectJobs([ProvisionServer::class]);

    $server = Server::factory()->createForTest();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(123, '', false));
    });

    Queue::after(fn (JobProcessed $event) => $this->assertTrue($event->job->isReleased()));

    (new ProvisionUser($server))->handle();
});
