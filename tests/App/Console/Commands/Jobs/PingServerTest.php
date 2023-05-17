<?php

declare(strict_types=1);

use App\Console\Commands\Jobs\PingServer;
use App\Server\Notifications\ServerUnreachable;
use Domain\SecureShell\Contracts\ShellProcessRunner;
use Domain\SecureShell\Services\ShellResponse;
use Domain\Server\Models\Server;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Notification;

it('checks if localhost can be reached', function () {
    $server = Server::factory()->createForTest(['ip_address' => '127.0.0.1']);

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->once()->andReturn(new ShellResponse(0, '', false));
    });

    (new PingServer($server))->handle();

    Notification::assertNothingSent();
});

it('checks if reserved ip address fails to be reached', function () {
    $server = Server::factory()->createForTest(['ip_address' => '192.0.2.0']);

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->times(6)->andReturn(new ShellResponse(143, '', true));
    });

    (new PingServer($server))->handle();

    Notification::assertSentTo($server->token->user, ServerUnreachable::class);
});

it('notifies all collaborators', function () {
    $server = Server::factory()->createForTest(['ip_address' => '192.0.2.0']);

    [$collaborator1, $collaborator2] = User::factory()->times(2)->create();

    $server->token->shareWith($collaborator1, 'collaborator', ['server-provider:create']);
    $server->token->shareWith($collaborator2, 'collaborator', ['server-provider:create']);

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->times(6)->andReturn(new ShellResponse(143, '', true));
    });

    (new PingServer($server))->handle();

    Notification::assertSentTo([
        $server->token->user, $collaborator1, $collaborator2,
    ], ServerUnreachable::class);

    Notification::assertTimesSent(3, ServerUnreachable::class);
});

it('checks if incorrect ip address fails', function () {
    $server = Server::factory()->createForTest(['ip_address' => '255.255.255.255']);

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->times(6)->andReturn(new ShellResponse(68, '', false));
    });

    (new PingServer($server))->handle();

    Notification::assertSentTo($server->token->user, ServerUnreachable::class);
});

it('checks if it can exit before reaching max tries', function () {
    $server = Server::factory()->createForTest(['ip_address' => '255.255.255.255']);

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->times(3)->andReturn(new ShellResponse(68, '', false));
        $mock->shouldReceive('run')->once()->andReturn(new ShellResponse(0, '', false));
    });

    (new PingServer($server))->handle();

    Notification::assertSentTo($server->token->user, ServerUnreachable::class);
});
