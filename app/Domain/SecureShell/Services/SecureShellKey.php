<?php

declare(strict_types=1);

namespace Domain\SecureShell\Services;

use Domain\SecureShell\Contracts\SecureShellKeyGenerator;
use Domain\Token\Models\Token;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

final class SecureShellKey implements SecureShellKeyGenerator
{
    public function make(string $password): array
    {
        // config('deployer.openssh.name')
        // config('deployer.openssh.length')

        $name = Str::random(20);

        Process::fromShellCommandline(
            "ssh-keygen -C \"robot@ark.io\" -f {$name} -t rsa -b 4096 -N ".escapeshellarg($password),
            storage_path('app')
        )->run();

        [$publicKey, $privateKey] = [
            file_get_contents(storage_path('app/'.$name.'.pub')),
            file_get_contents(storage_path('app/'.$name)),
        ];

        File::delete(storage_path('app/'.$name.'.pub'));
        File::delete(storage_path('app/'.$name));

        return [
            'publicKey'  => $publicKey,
            'privateKey' => $privateKey,
        ];
    }

    public function storeFor(Token $token): string
    {
        return tap(storage_path('app/keys/'.$token->id), function ($path) use ($token): void {
            $this->ensureKeyDirectoryExists();

            $this->ensureFileExists($path, $token->getPrivateKey(), 0600);
        });
    }

    private function ensureKeyDirectoryExists(): void
    {
        if (! is_dir(storage_path('app/keys'))) {
            mkdir(storage_path('app/keys'), 0755, true);
        }
    }

    private function ensureFileExists(string $path, string $contents, int $chmod): void
    {
        file_put_contents($path, $contents);

        chmod($path, $chmod);
    }
}
