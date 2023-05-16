<?php

declare(strict_types=1);

use App\Server\Jobs\ProvisionServer;
use App\Server\Jobs\RemovePasswordsFromServer;
use App\Server\Notifications\ServerProvisioned;
use Domain\SecureShell\Contracts\ShellProcessRunner;
use Domain\SecureShell\Services\ShellResponse;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('dispatches jobs to provision the server', function () {
    $server = Server::factory()->createForTest();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '', false));
    });

    (new ProvisionServer($server))->handle();

    $this->addToAssertionCount(1);
});

it('dispatch a job to remove passwords on server provisioned', function () {
    $this->expectsJobs(RemovePasswordsFromServer::class);

    $server = Server::factory()->createForTest();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '', false));
    });

    $server->setStatus('provisioned');

    (new ProvisionServer($server))->handle();
});

it('sends a notification when server is provisioned', function () {
    $server = Server::factory()->createForTest();

    [$collaborator1, $collaborator2] = User::factory()->times(2)->create();

    $server->token->shareWith($collaborator1, 'collaborator', []);
    $server->token->shareWith($collaborator2, 'collaborator', []);

    Notification::fake();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '', false));
    });

    $server->setStatus('provisioned');

    (new ProvisionServer($server))->handle();

    Notification::assertSentTo([$server->token->user, $collaborator1, $collaborator2], ServerProvisioned::class, fn ($notification) => $notification->server->is($server));
    Notification::assertTimesSent(3, ServerProvisioned::class);
});

it('dispatches jobs to provision the server and fails', function () {
    $this->expectException(Exception::class);

    $server = Server::factory()->createForTest();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(123, '', false));
    });

    (new ProvisionServer($server))->handle();
});
