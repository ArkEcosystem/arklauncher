<?php

declare(strict_types=1);

namespace Domain\Server\Models;

use Domain\SecureShell\Facades\SecureShellKey;
use Domain\SecureShell\Services\SecureShell;
use Domain\Server\Enums\ServerTaskStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ModelStatus\HasStatuses;
use Support\Eloquent\Model;

/**
 * @property Server $server
 */
final class ServerTask extends Model
{
    use HasStatuses;

    public const DEFAULT_TIMEOUT = 3600;

    protected $fillable = ['type', 'name', 'user', 'exit_code', 'script', 'output', 'options'];

    protected $casts = [
        'options' => 'array',
        'script'  => 'encrypted',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function isSuccessful(): bool
    {
        return $this->exit_code === 0;
    }

    public function isPending(): bool
    {
        $lastStatus = $this->status();

        return $lastStatus !== null && ServerTaskStatusEnum::isPending($lastStatus->name);
    }

    public function isRunning(): bool
    {
        $lastStatus = $this->status();

        return $lastStatus !== null && ServerTaskStatusEnum::isRunning($lastStatus->name);
    }

    public function hasFailed(): bool
    {
        $lastStatus = $this->status();

        return $lastStatus !== null && ServerTaskStatusEnum::isFailed($lastStatus->name);
    }

    public function markAsRunning(): void
    {
        $this->setStatus(ServerTaskStatusEnum::RUNNING);
    }

    public function markAsTimedOut(string $output = ''): self
    {
        $this->setStatus(ServerTaskStatusEnum::TIMEOUT);

        $this->server->setStatus('failed');

        return tap($this)->update([
            'exit_code' => 1,
            'output'    => $output,
        ]);
    }

    public function markAsFinished(int $exitCode = 0, string $output = ''): self
    {
        $this->setStatus(ServerTaskStatusEnum::FINISHED);

        return tap($this)->update([
            'exit_code' => $exitCode,
            'output'    => $output,
        ]);
    }

    public function markAsFailed(int $exitCode = 0, string $output = ''): self
    {
        $this->setStatus(ServerTaskStatusEnum::FAILED);

        $this->server->setStatus('failed');

        return tap($this)->update([
            'exit_code' => $exitCode,
            'output'    => $output,
        ]);
    }

    public function run(): self
    {
        $this->markAsRunning();

        $shell = new SecureShell($this);

        return $this->user === 'root' ? $shell->run() : $shell->runWithUser();
    }

    public function ipAddress(): string
    {
        return $this->server->ip_address ?? '';
    }

    public function port(): int
    {
        return 22;
    }

    public function ownerKeyPath(): string
    {
        return SecureShellKey::storeFor($this->server->token);
    }
}
