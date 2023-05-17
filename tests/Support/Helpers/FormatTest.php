<?php

declare(strict_types=1);

use Support\Helpers\Format;

it('should return a snake case string if string is not in the plural exceptions', function () {
    $initial = 'FooBar';
    expect(Format::stepTitle($initial))->toBe('foo_bar');
});

it('should return a snake case pluralized string if string is in the plural exceptions', function () {
    $initial = 'ServerProvider';
    expect(Format::stepTitle($initial))->toBe('server_providers');
});

it('can format a crypto amount', function () {
    expect(Format::readableCrypto(12500000000000000))->toBe('125,000,000');
    expect(Format::readableCrypto(12500000012345678))->toBe('125,000,000');
});

it('can format a crypto amount with given decimals', function () {
    expect(Format::readableCrypto(12500000012345678, 3))->toBe('125,000,000.123');
    expect(Format::readableCrypto(12500000012395678, 3))->toBe('125,000,000.124');
    expect(Format::readableCrypto(12500000000000000, 3))->toBe('125,000,000.000');
});

it('can format a string with token', function () {
    $string          = 'testasdf';
    $stringWithToken = Format::withToken($string);

    expect($stringWithToken)->toMatch('/(\w+)-(\w+)/');
});
