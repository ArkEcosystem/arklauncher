<?php

declare(strict_types=1);

use Domain\Server\Models\ServerProvider;
use Domain\Server\Services\Providers\AWS;
use Domain\Server\Services\Providers\DigitalOcean;
use Domain\Server\Services\Providers\Hetzner;
use Domain\Server\Services\Providers\Linode;
use Domain\Server\Services\Providers\Vultr;
use Domain\Server\Services\ServerProviderClientFactory;

it('can create a digitalocean client', function () {
    $source = ServerProvider::factory()->digitalocean()->createForTest();

    expect(ServerProviderClientFactory::make($source))->toBeInstanceOf(DigitalOcean::class);
});

it('can create a hetzner client', function () {
    $source = ServerProvider::factory()->hetzner()->createForTest();

    expect(ServerProviderClientFactory::make($source))->toBeInstanceOf(Hetzner::class);
});

it('can create a aws client', function () {
    $source = ServerProvider::factory()->aws()->createForTest();

    expect(ServerProviderClientFactory::make($source))->toBeInstanceOf(AWS::class);
});

it('can create a vultr client', function () {
    $source = ServerProvider::factory()->vultr()->createForTest();

    expect(ServerProviderClientFactory::make($source))->toBeInstanceOf(Vultr::class);
});

it('can create a linode client', function () {
    $source = ServerProvider::factory()->linode()->createForTest();

    expect(ServerProviderClientFactory::make($source))->toBeInstanceOf(Linode::class);
});

it('cant create an invalid client', function () {
    $source       = ServerProvider::factory()->createForTest();
    $source->type = 'invalid';

    $this->expectException(InvalidArgumentException::class);

    ServerProviderClientFactory::make($source);
});
