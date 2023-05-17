<?php

declare(strict_types=1);

use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Services\Providers\VultrExceptionHandler;

it('handles any exception', function () {
    $this->expectException(RuntimeException::class);

    $exception = new RuntimeException();

    VultrExceptionHandler::new($exception)->handle();
});

it('handles an exception for create', function () {
    $this->expectException(ServerLimitExceeded::class);

    $exception = $this->createRequestException(412, $this->fixture('vultr/create-exceeded'));

    VultrExceptionHandler::new($exception)->handle();
});
