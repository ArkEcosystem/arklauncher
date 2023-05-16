<?php

declare(strict_types=1);

use Domain\Token\Enums\TokenStatusEnum;

it('a token status has enums', function () {
    expect(TokenStatusEnum::PENDING)->toBe('pending');
    expect(TokenStatusEnum::FINISHED)->toBe('finished');
});
