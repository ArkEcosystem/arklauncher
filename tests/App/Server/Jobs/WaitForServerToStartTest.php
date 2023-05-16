<?php

declare(strict_types=1);

use App\Server\Jobs\WaitForServerToStart;
use App\Server\Notifications\ServerUnreachable;
use Domain\SecureShell\Contracts\ShellProcessRunner;
use Domain\SecureShell\Services\ShellResponse;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('deletes the job if the server is online', function () {
    $server = Server::factory()->createForTest();

    $server->touch('provisioned_at');
    $server->setStatus('online');

    (new WaitForServerToStart($server))->handle();

    expect($server->fresh()->isOnline())->toBeTrue();
});

it('marks the server as online if server can be reached', function () {
    $server = Server::factory()->createForTest();

    $server->touch('provisioned_at');
    $server->setStatus('offline');

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->once()->andReturn(new ShellResponse(0, '', false));
    });

    (new WaitForServerToStart($server))->handle();

    expect($server->fresh()->isOnline())->toBeTrue();
});

it('sends a notification if server is unreachable', function () {
    $server = Server::factory()->createForTest();

    $server->touch('provisioned_at');
    $server->setStatus('offline');

    Notification::fake();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->times(6)->andReturn(new ShellResponse(143, '', true));
    });

    (new WaitForServerToStart($server))->handle();

    Notification::assertSentTo($server->token->user, ServerUnreachable::class);
});

it('notifies all collaborators', function () {
    $server = Server::factory()->createForTest();

    $server->touch('provisioned_at');
    $server->setStatus('offline');

    [$collaborator1, $collaborator2] = User::factory()->times(2)->create();

    $server->token->shareWith($collaborator1, 'collaborator', ['server-provider:create']);
    $server->token->shareWith($collaborator2, 'collaborator', ['server-provider:create']);

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->times(6)->andReturn(new ShellResponse(143, '', true));
    });

    (new WaitForServerToStart($server))->handle();

    Notification::assertSentTo([
        $server->token->user, $collaborator1, $collaborator2,
    ], ServerUnreachable::class);

    Notification::assertTimesSent(3, ServerUnreachable::class);
});
