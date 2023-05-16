<?php

declare(strict_types=1);

use Domain\SecureShell\Contracts\ShellProcessRunner;
use Domain\SecureShell\Services\ShellResponse;
use Domain\Server\Models\Server;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Tests\Domain\SecureShell\Services\Concerns\GetCurrentDirectory;
use Tests\Domain\SecureShell\Services\Concerns\ScriptWithUserSwitch;

beforeEach(fn () => File::deleteDirectory(storage_path('app/scripts')));

it('scripts can be run', function () {
    $task = Server::factory()->createForTest()->addTask(new GetCurrentDirectory());

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '/root', false));
    });

    $task->run();

    expect($task->status)->toBe('finished');
    expect($task->exit_code)->toBe(0);
    expect($task->output)->toBe('/root');
});

it('scripts can be run and timeout', function () {
    $task = Server::factory()->createForTest()->addTask(new GetCurrentDirectory());

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andThrow(new ProcessTimedOutException(Process::fromShellCommandline('cmd'), 1));
    });

    $task->run();

    expect($task->status)->toBe('timeout');
    expect($task->exit_code)->toBe(1);
    expect($task->output)->toBe('');
});

it('scripts can be run as a different user', function () {
    $task = Server::factory()->createForTest()->addTask(new ScriptWithUserSwitch());

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andReturn(new ShellResponse(0, '/root', false));
    });

    $task->run();

    expect($task->status)->toBe('finished');
    expect($task->exit_code)->toBe(0);
    expect($task->output)->toBe('/root');
});

it('scripts can be run as a different user and timeout', function () {
    $task = Server::factory()->createForTest()->addTask(new ScriptWithUserSwitch());

    $this->mock(ShellProcessRunner::class, function ($mock) {
        $mock->shouldReceive('run')->andThrow(new ProcessTimedOutException(Process::fromShellCommandline('cmd'), 1));
    });

    $task->run();

    expect($task->status)->toBe('timeout');
    expect($task->exit_code)->toBe(1);
    expect($task->output)->toBe('');
});
