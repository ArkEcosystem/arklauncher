<?php

declare(strict_types=1);

use Support\Services\PasswordGenerator;

it('can generate a password', function () {
    expect(PasswordGenerator::make(32))->toBestring();
});
