<?php

declare(strict_types=1);

use Support\AddressPrefixes;

it('can get the numerical representation of the prefix', function () {
    expect(AddressPrefixes::get('A'))->toBe(23);
});

it('can determine if the prefix is valid', function () {
    expect(AddressPrefixes::valid('invalid'))->toBeFalse();
    expect(AddressPrefixes::valid('A'))->toBeTrue();
});
