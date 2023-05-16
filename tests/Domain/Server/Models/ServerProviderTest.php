<?php

declare(strict_types=1);

use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Server\Services\Providers\AWS;
use Domain\Server\Services\Providers\DigitalOcean;
use Domain\Server\Services\Providers\Hetzner;
use Domain\Server\Services\Providers\Linode;
use Domain\Server\Services\Providers\Vultr;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('can get the user that linked the server provider', function () {
    $user = User::factory()->create();

    $provider = ServerProvider::factory()->create();
    $provider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    expect($provider->user()->is($user))->toBeTrue();
});

it('a server provider belongs to a token', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect($serverProvider->token())->toBeInstanceOf(BelongsTo::class);
});

it('a server provider has an access token', function () {
    $serverProvider = ServerProvider::factory()->createForTest([
        'extra_attributes' => ['accessToken' => 'access_token'],
    ]);

    expect($serverProvider->getMetaAttribute(ServerAttributeEnum::ACCESS_TOKEN))->toBe('access_token');
});

it('a server provider has servers', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect($serverProvider->servers())->toBeInstanceOf(HasMany::class);
});

it('a server provider has plans', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect($serverProvider->plans())->toBeInstanceOf(BelongsToMany::class);
});

it('a server provider has regions', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect($serverProvider->regions())->toBeInstanceOf(BelongsToMany::class);
});

it('a server provider has images', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    expect($serverProvider->images())->toBeInstanceOf(BelongsToMany::class);
});

it('a server provider with images plans and regions should be considered as all indexed', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $plan = ServerProviderPlan::factory()->create([
        'uuid' => 'ccx21',
    ]);

    $region = ServerProviderRegion::factory()->create([
        'uuid' => 'fsn1',
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid' => $serverProvider->client()->getImageId(),
    ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    expect($serverProvider->allIndexed())->toBeTrue();
});

it('can create a client instance', function () {
    $serverProvider = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($serverProvider->client())->toBeInstanceOf(DigitalOcean::class);

    $serverProvider = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($serverProvider->client())->toBeInstanceOf(Hetzner::class);

    $serverProvider = ServerProvider::factory()->aws()->ownedBy($this->token())->createForTest();

    expect($serverProvider->client())->toBeInstanceOf(AWS::class);

    $serverProvider = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($serverProvider->client())->toBeInstanceOf(Vultr::class);

    $serverProvider = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($serverProvider->client())->toBeInstanceOf(Linode::class);
});
