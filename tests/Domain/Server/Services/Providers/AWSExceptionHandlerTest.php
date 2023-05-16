<?php

declare(strict_types=1);

use Domain\Server\Services\Providers\AWSExceptionHandler;

it('handles any exceptions', function () {
    $this->expectException(RuntimeException::class);

    $exception = new RuntimeException();

    AWSExceptionHandler::new($exception)->handle();
});
