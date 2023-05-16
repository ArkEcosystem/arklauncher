<?php

declare(strict_types=1);

use Domain\SecureShell\Exceptions\SecureShellKeyAlreadyInUse;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Services\Providers\DigitalOceanExceptionHandler;

it('handles any exceptions', function () {
    $this->expectException(RuntimeException::class);

    $exception = new RuntimeException();

    DigitalOceanExceptionHandler::new($exception)->handle();
});

it('handles an authentication exception', function () {
    $this->expectException(ServerProviderAuthenticationException::class);

    $exception = $this->createRequestException(401, $this->fixture('digitalocean/unauthorized'));

    DigitalOceanExceptionHandler::new($exception)->handle();
});

it('handles an exception for create', function () {
    $this->expectException(ServerLimitExceeded::class);

    $exception = $this->createRequestException(422, $this->fixture('digitalocean/create-exceeded'));

    DigitalOceanExceptionHandler::new($exception)->handle();
});

it('handles an exception for create when secure shell key addition failed', function () {
    $this->expectException(SecureShellKeyAlreadyInUse::class);

    $exception = $this->createRequestException(422, $this->fixture('digitalocean/ssh-key-create-failed'));

    DigitalOceanExceptionHandler::new($exception)->handle();
});

it('handles an exception for server lookup', function () {
    $this->expectException(ServerNotFound::class);

    $exception = $this->createRequestException(422, $this->fixture('digitalocean/server-not-found'));

    DigitalOceanExceptionHandler::new($exception)->handle();
});

it('handles an general server provider exception', function () {
    $this->expectException(ServerProviderError::class);

    $exception = $this->createRequestException(429, $this->fixture('digitalocean/too-many-requests'));

    DigitalOceanExceptionHandler::new($exception)->handle();
});
