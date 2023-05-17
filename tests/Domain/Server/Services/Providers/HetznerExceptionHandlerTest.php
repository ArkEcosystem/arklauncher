<?php

declare(strict_types=1);

use Domain\Server\Exceptions\ServerCoreLimitExceeded;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Exceptions\ServerProviderSecureShellKeyUniqueness;
use Domain\Server\Services\Providers\HetznerExceptionHandler;

it('handles any exception', function () {
    $this->expectException(RuntimeException::class);

    $exception = new RuntimeException();

    HetznerExceptionHandler::new($exception)->handle();
});

it('handles an exception for create', function () {
    $this->expectException(ServerLimitExceeded::class);

    $exception = $this->createRequestException(422, $this->fixture('hetzner/create-exceeded'));

    HetznerExceptionHandler::new($exception)->handle();
});

it('handles an exception for core limit exceeded', function () {
    $this->expectException(ServerCoreLimitExceeded::class);

    $exception = $this->createRequestException(403, $this->fixture('hetzner/core-limit-exceeded'));

    HetznerExceptionHandler::new($exception)->handle();
});

it('handles an exception for server lookup', function () {
    $this->expectException(ServerNotFound::class);

    $exception = $this->createRequestException(422, $this->fixture('hetzner/server-not-found'));

    HetznerExceptionHandler::new($exception)->handle();
});

it('handles an exception for failure to authenticate', function () {
    $this->expectException(ServerProviderAuthenticationException::class);

    $exception = $this->createRequestException(401, $this->fixture('hetzner/unable-to-authenticate'));

    HetznerExceptionHandler::new($exception)->handle();
});

it('handles an exception for ssh key uniqueness issues', function () {
    $this->expectException(ServerProviderSecureShellKeyUniqueness::class);

    $exception = $this->createRequestException(409, $this->fixture('hetzner/ssh-keys-uniqueness-error'));

    HetznerExceptionHandler::new($exception)->handle();
});

it('handles an general server provider exception', function () {
    $this->expectException(ServerProviderError::class);

    $exception = $this->createRequestException(400, $this->fixture('hetzner/method-not-allowed'));

    HetznerExceptionHandler::new($exception)->handle();
});

it('handles an exception based on only responses', function () {
    $this->expectException(ServerProviderError::class);

    $response = [
            'action' => [
                'status' => 'error',
            ],
        ];

    HetznerExceptionHandler::newWithResponse($response)->handle();
});

it('handles a non exception based on only responses', function () {
    $response = [
            'action' => [
                'status' => 'completed',
            ],
        ];

    expect(HetznerExceptionHandler::newWithResponse($response)->handle())->toBeNull();
});
