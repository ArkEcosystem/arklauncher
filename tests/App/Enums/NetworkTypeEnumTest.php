<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;

it('has an enum for the network types', function () {
    expect(NetworkTypeEnum::MAINNET)->toBe('mainnet');
    expect(NetworkTypeEnum::DEVNET)->toBe('devnet');
    expect(NetworkTypeEnum::TESTNET)->toBe('testnet');
});

it('can get an alias for the network type', function () {
    expect(NetworkTypeEnum::alias(NetworkTypeEnum::MAINNET))->toBe('production');
    expect(NetworkTypeEnum::alias(NetworkTypeEnum::DEVNET))->toBe('development');
    expect(NetworkTypeEnum::alias(NetworkTypeEnum::TESTNET))->toBe('development');
});
