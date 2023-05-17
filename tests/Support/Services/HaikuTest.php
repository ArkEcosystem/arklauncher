<?php

declare(strict_types=1);

use Support\Services\Haiku;

it('can generate a name', function () {
    expect(Haiku::name())->toBestring();
});

it('can generate a name with a token', function () {
    expect(Haiku::withToken())->toBestring();
});
