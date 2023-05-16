<?php

declare(strict_types=1);

use App\Server\Notifications\RemoteServerLimitReached;
use Domain\Server\DTO\ImageCollection;
use Domain\Server\DTO\PlanCollection;
use Domain\Server\DTO\RegionCollection;
use Domain\Server\DTO\SecureShellKey;
use Domain\Server\DTO\Server;
use Domain\Server\Exceptions\ServerLimitExceeded;
use Domain\Server\Exceptions\ServerNotFound;
use Domain\Server\Exceptions\ServerProviderError;
use Domain\Server\Models\Server as ServerModel;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Server\Services\Providers\DigitalOcean;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $region = ServerProviderRegion::factory()->create([
        'uuid' => 'nyc3',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'uuid' => 's-1vcpu-1gb',
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid' => $this->source->client()->getImageId(),
    ]);

    $this->server = ServerModel::factory()->digitalocean()->createForTest([
        'server_provider_id'        => $this->source->id,
        'server_provider_plan_id'   => $plan->id,
        'server_provider_region_id' => $region->id,
        'server_provider_image_id'  => $image->id,
    ]);
});

it('validates the credentials', function () {
    Http::fake([
            'digitalocean.com/*' => Http::sequence()
                ->push($this->fixture('digitalocean/account'), 200, [])
                ->pushStatus(403),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($source->client()->valid())->toBeTrue();
    expect($source->client()->valid())->toBeFalse();
});

it('can create a new server', function () {
    Http::fake([
            'digitalocean.com/*' => Http::sequence()
                ->push($this->fixture('digitalocean/ssh-keys-get'), 200, [])
                ->push($this->fixture('digitalocean/create'), 200, [])
                ->push($this->fixture('digitalocean/server'), 200, []),
        ]);

    $actual = $this->source->client()->create($this->server);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe(3164494);
    expect($actual->plan)->toBe('s-1vcpu-1gb');
    expect($actual->memory)->toBe(1024);
    expect($actual->cores)->toBe(1);
    expect($actual->disk)->toBe(25);
    expect($actual->region)->toBe('nyc3');
    expect($actual->status)->toBe('active');
    expect($actual->remoteAddress)->toBe('104.131.186.241');
    expect($actual->image)->toBe($this->source->client()->getImageId());
});

it('fails to create a new server if the droplet limit is exceeded', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/create-exceeded'), 422, []),
        ]);

    $this->expectException(ServerLimitExceeded::class);

    $this->source->client()->create($this->server);

    Notification::assertSentTo($this->source->token->user, RemoteServerLimitReached::class);
});

it('throws if the exception is not about the droplet limit', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/create'), 404, []),
        ]);

    $this->expectException(ServerProviderError::class);

    $this->source->client()->create($this->server);
});

it('can request the given server by id', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->server(3164494);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe(3164494);
    expect($actual->plan)->toBe('s-1vcpu-1gb');
    expect($actual->memory)->toBe(1024);
    expect($actual->cores)->toBe(1);
    expect($actual->disk)->toBe(25);
    expect($actual->region)->toBe('nyc3');
    expect($actual->status)->toBe('active');
    expect($actual->remoteAddress)->toBe('104.131.186.241');
    expect($actual->image)->toBe($source->client()->getImageId());
});

it('will throw a server error if server does not exist', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
        ]);

    $this->expectException(ServerNotFound::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();
    $source->client()->server(1234);
});

it('will throw a generic error if server check fails', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();
    $source->client()->server(1234);
});

it('can delete the given server by id', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response('', 204, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($source->client()->delete(3164494))->toBeTrue();
});

it('cant delete the given server by id if action errors', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/errored-general'), 500, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->delete(42);
});

it('gracefully handles server not found excepton', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
    ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->delete(42);

    Http::assertSent(fn ($request) => $request->method() === 'DELETE' && $request->url() === 'https://api.digitalocean.com/v2/droplets/42');
});

it('cant delete the given server by id if it does not exist', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->delete(3164494);
});

it('can rename the server droplet', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/rename'), 200, []),
    ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($source->client()->rename(3164494, 'new name'))->toBeTrue();
});

it('can start the given server by id', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/start'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($source->client()->start(3164494))->toBeTrue();
});

it('cant start the given server by id if it action errored', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
        ]);

    $this->expectException(ServerNotFound::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->start(3164494);
});

it('cant start the given server by id if it does not exist', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->start(3164494);
});

it('can stop the given server by id', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/stop'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($source->client()->stop(3164494))->toBeTrue();
});

it('cant stop the given server by id if action errors', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
        ]);

    $this->expectException(ServerNotFound::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->stop(3164494);
});

it('cant stop the given server by id if it does not exist', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->stop(3164494);
});

it('can reboot the given server by id', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/reboot'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($source->client()->reboot(3164494))->toBeTrue();
});

it('cant reboot the given server by id if action errors', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/server-not-found'), 404, []),
        ]);

    $this->expectException(ServerNotFound::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->reboot(3164494);
});

it('cant reboot the given server by id if it does not exist', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $source->client()->reboot(3164494);
});

it('can request all the available plans', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/plans'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->plans();

    expect($actual)->toBeInstanceOf(PlanCollection::class);
    expect($actual->items)->toHaveCount(20);

    $region = $actual->items[2];

    expect($region->id)->toBe('s-1vcpu-2gb');
    expect($region->disk)->toBe(50);
    expect($region->memory)->toBe(2048);
    expect($region->cores)->toBe(1);
    expect($region->regions)->toBeArray();
});

it('can request all the available regions', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/regions'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->regions();

    expect($actual)->toBeInstanceOf(RegionCollection::class);
    expect($actual->items)->toHaveCount(7);

    $region = $actual->items[2];

    expect($region->id)->toBe('sfo1');
    expect($region->name)->toBe('San Francisco 1');
});

it('can request all the available images', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/images'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->images();

    expect($actual)->toBeInstanceOf(ImageCollection::class);
    expect($actual->items)->toHaveCount(1);

    $image = $actual->items->first();

    expect($image->id)->toBe($source->client()->getImageId());
    expect($image->name)->toBe('18.04.3 (LTS) x64');
});

it('can create a secure shell key', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/ssh-keys-create'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->createSecureShellKey('name', 'publickey');

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe(512190);
});

it('can find a secure shell key', function () {
    Http::fake([
            'digitalocean.com/*' => Http::response($this->fixture('digitalocean/ssh-keys-get'), 200, []),
        ]);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->findSecureShellKey($source->provider_key_id);

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe(512190);
});

it('can delete a secure shell key', function () {
    Http::fake([
            'digitalocean.com/*' => Http::sequence()
                ->push([], 204, [])
                ->push([], 404, []),
        ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->digitalocean()->ownedBy($this->token())->createForTest();

    expect($source->client()->deleteSecureShellKey($source->provider_key_id))->toBeTrue();

    $source->client()->deleteSecureShellKey($source->provider_key_id);
});

it('returns an instanciable rule for a server name', function () {
    $ruleClass = DigitalOcean::nameValidator();

    $rule = new $ruleClass();

    expect($rule)->toBeInstanceOf(Rule::class);
});
