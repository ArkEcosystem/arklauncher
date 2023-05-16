<?php

declare(strict_types=1);

use Domain\SecureShell\Facades\SecureShellKey;
use Illuminate\Support\Facades\File;

it('creates a command for a script', function () {
    $keys = SecureShellKey::make('password');

    expect($keys['publicKey'])->toBeString();
    expect($keys['privateKey'])->toBeString();
});

it('creates a command for an upload', function () {
    File::deleteDirectory(storage_path('app/keys'));

    $token = $this->token();

    expect(SecureShellKey::storeFor($token))->toBeString();
});
