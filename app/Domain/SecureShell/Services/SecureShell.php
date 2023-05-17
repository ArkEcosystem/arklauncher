<?php

declare(strict_types=1);

namespace Domain\SecureShell\Services;

use Domain\SecureShell\Facades\ShellProcessRunner;
use Domain\Server\Models\ServerTask;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class SecureShell
{
    /**
     *  We'll run everything as root and use "su" to act as a different user!
     */
    private string $rootUser = 'root';

    public function __construct(private ServerTask $task)
    {
    }

    public function run(): ServerTask
    {
        try {
            $this->ensureWorkingDirectoryExists();

            $this->upload();
        } catch (ProcessTimedOutException) {
            return $this->task->markAsTimedOut();
        }

        $output = $this->runInline(sprintf(
            'bash %s 2>&1 | tee %s',
            $this->scriptFile(),
            $this->outputFile()
        ), $this->task->options['timeout'] ?? 60);

        return $this->updateForResponse($output);
    }

    public function runWithUser(): ServerTask
    {
        try {
            $this->upload();
        } catch (ProcessTimedOutException) {
            return $this->task->markAsTimedOut();
        }

        $output = $this->runInline(sprintf(
            'su - %s -c "bash %s 2>&1" | tee %s',
            $this->task->user,
            $this->scriptFile(),
            $this->outputFile()
        ), $this->task->options['timeout'] ?? 60);

        return $this->updateForResponse($output);
    }

    private function updateForResponse(ShellResponse $response): ServerTask
    {
        $this->task->setStatus($response->timedOut ? 'timeout' : 'finished');

        return tap($this->task)->update([
            'exit_code' => $response->exitCode,
            'output'    => $response->output,
        ]);
    }

    private function ensureWorkingDirectoryExists(): void
    {
        $this->runInline('mkdir -p '.$this->path(), 10);
    }

    private function upload(): void
    {
        $process = Process::fromShellCommandline(SecureShellCommand::forUpload(
            $this->task->ipAddress(),
            $this->task->port(),
            $this->task->ownerKeyPath(),
            $this->rootUser,
            $localScript = $this->writeScript(),
            $this->scriptFile()
        ), base_path())->setTimeout(15);

        $response = ShellProcessRunner::run($process);

        unlink($localScript);
    }

    private function writeScript(): string
    {
        $hash = md5(Str::random(20).$this->task->script);

        if (! is_dir(storage_path('app/scripts'))) {
            mkdir(storage_path('app/scripts'), 0755, true);
        }

        return tap(storage_path('app/scripts').'/'.$hash, fn ($path) => file_put_contents($path, $this->task->script));
    }

    private function runInline(string $script, int $timeout = 60): ShellResponse
    {
        $token = Str::random(20);

        return ShellProcessRunner::run($this->toProcess('\'bash -s \' << \''.$token.'\'
'.$script.'
'.$token, $timeout));
    }

    private function path(): string
    {
        return "/home/{$this->rootUser}/.deployer";
    }

    private function scriptFile(): string
    {
        return $this->getPath().'.sh';
    }

    private function outputFile(): string
    {
        return $this->getPath().'.out';
    }

    private function toProcess(string $script, int $timeout): Process
    {
        return Process::fromShellCommandline(
            SecureShellCommand::forScript(
                $this->task->ipAddress(),
                $this->task->port(),
                $this->task->ownerKeyPath(),
                $this->rootUser,
                $script
            )
        )->setTimeout($timeout);
    }

    private function getPath(): string
    {
        return $this->path().'/'.Str::snake(class_basename($this->task->type));
    }
}
