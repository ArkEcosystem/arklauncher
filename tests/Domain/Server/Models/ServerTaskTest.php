<?php

declare(strict_types=1);

use Domain\SecureShell\Contracts\ShellProcessRunner;
use Domain\SecureShell\Services\ShellResponse;
use Domain\Server\Models\ServerTask;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('a task belongs to a server provider', function () {
    expect(ServerTask::factory()->create()->server())->toBeInstanceOf(BelongsTo::class);
});

it('can determines if the task is successful', function () {
    $task = ServerTask::factory()->create(['exit_code' => 0]);

    expect($task->isSuccessful())->toBeTrue();

    $task = ServerTask::factory()->create(['exit_code' => 1]);

    expect($task->isSuccessful())->toBeFalse();
});

it('can determines if the task is pending', function () {
    $task = ServerTask::factory()->create();

    expect($task->isPending())->toBeFalse();

    $task->setStatus('pending');

    expect($task->isPending())->toBeTrue();
});

it('can determines if the task is running', function () {
    $task = ServerTask::factory()->create();

    expect($task->isRunning())->toBeFalse();

    $task->setStatus('running');

    expect($task->isRunning())->toBeTrue();
});

it('can determines if the task has failed', function () {
    $task = ServerTask::factory()->create();

    expect($task->hasFailed())->toBeFalse();

    $task->setStatus('failed');

    expect($task->hasFailed())->toBeTrue();
});

it('marks the task as running', function () {
    $task = ServerTask::factory()->create();

    expect($task->isRunning())->toBeFalse();

    $task->markAsRunning();

    expect($task->isRunning())->toBeTrue();
});

it('marks the task as timed out', function () {
    $task = ServerTask::factory()->create();

    expect($task->status())->toBeNull();
    expect($task->exit_code)->toBe(0);

    $task->markAsTimedOut('output');

    expect($task->status()->name)->toBe('timeout');
    expect($task->exit_code)->toBe(1);
    expect($task->output)->toBe('output');
});

it('marks the task as finished', function () {
    $task = ServerTask::factory()->create();

    expect($task->status())->toBeNull();
    expect($task->exit_code, 0);

    $task->markAsFinished(123, 'output');

    expect($task->status()->name)->toBe('finished');
    expect($task->exit_code)->toBe(123);
    expect($task->output)->toBe('output');
});

it('marks the task as failed', function () {
    $task = ServerTask::factory()->create();

    expect($task->server->status())->toBeNull();
    expect($task->status())->toBeNull();
    expect($task->exit_code)->toBe(0);

    $task->markAsFailed(123, 'output');

    expect($task->server->status()->name)->toBe('failed');
    expect($task->status()->name)->toBe('failed');
    expect($task->exit_code)->toBe(123);
    expect($task->output)->toBe('output');
});

it('runs the task', function () {
    $task = ServerTask::factory()->create(['exit_code' => 143]);

    expect($task->isRunning())->toBeFalse();

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '', false));
    });

    expect($task->isSuccessful())->toBeFalse();

    $task->run();

    expect($task->isSuccessful())->toBeTrue();
});

it('returns the ip address', function () {
    expect(ServerTask::factory()->create()->ipAddress())->toBestring();
});

it('returns the ssh port', function () {
    expect(ServerTask::factory()->create()->port())->toBeNumeric();
});

it('returns the path to the ssh key', function () {
    expect(ServerTask::factory()->create()->ownerKeyPath())->toBestring();
});

it('encrypts the script attribute', function () {
    $task = ServerTask::factory()->create();

    $task->update(['script' => 'some long script']);

    $this->assertDatabaseMissing('server_tasks', ['script' => 'some long script']);

    expect($task->script)->toBe('some long script');
});
