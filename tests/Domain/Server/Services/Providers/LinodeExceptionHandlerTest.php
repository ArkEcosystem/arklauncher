<?php

declare(strict_types=1);

use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Services\Providers\LinodeExceptionHandler;

it('handles any exception', function () {
    $this->expectException(RuntimeException::class);

    $exception = new RuntimeException();

    LinodeExceptionHandler::new($exception)->handle();
});

it('handles an exception for create', function () {
    $this->expectException(ServerLimitExceeded::class);

    $exception = $this->createRequestException(422, $this->fixture('linode/create-exceeded'));

    LinodeExceptionHandler::new($exception)->handle();
});

it('handles an exception for server lookup', function () {
    $this->expectException(ServerNotFound::class);

    $exception = $this->createRequestException(422, $this->fixture('linode/server-not-found'));

    LinodeExceptionHandler::new($exception)->handle();
});
