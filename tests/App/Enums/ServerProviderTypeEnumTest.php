<?php

declare(strict_types=1);

use App\Enums\ServerProviderTypeEnum;

it('has an enum for the server provider types', function () {
    expect(ServerProviderTypeEnum::AWS)->toBe('aws');
    expect(ServerProviderTypeEnum::AZURE)->toBe('azure');
    expect(ServerProviderTypeEnum::DIGITALOCEAN)->toBe('digitalocean');
    expect(ServerProviderTypeEnum::HETZNER)->toBe('hetzner');
    expect(ServerProviderTypeEnum::LINODE)->toBe('linode');
    expect(ServerProviderTypeEnum::VULTR)->toBe('vultr');
});

it('has labels for server providers types', function () {
    expect(ServerProviderTypeEnum::label(ServerProviderTypeEnum::AWS))->toBe('Amazon Web Services');
    expect(ServerProviderTypeEnum::label(ServerProviderTypeEnum::AZURE))->toBe('Azure');
    expect(ServerProviderTypeEnum::label(ServerProviderTypeEnum::DIGITALOCEAN))->toBe('DigitalOcean');
    expect(ServerProviderTypeEnum::label(ServerProviderTypeEnum::HETZNER))->toBe('Hetzner');
    expect(ServerProviderTypeEnum::label(ServerProviderTypeEnum::LINODE))->toBe('Linode');
    expect(ServerProviderTypeEnum::label(ServerProviderTypeEnum::VULTR))->toBe('Vultr');
    expect(ServerProviderTypeEnum::label('Other'))->toBe('Other');
});

it('it checks if server provider is AWS', function () {
    expect(ServerProviderTypeEnum::isAws(ServerProviderTypeEnum::AWS))->toBeTrue();
    expect(ServerProviderTypeEnum::isAws('foo'))->toBeFalse();
});
