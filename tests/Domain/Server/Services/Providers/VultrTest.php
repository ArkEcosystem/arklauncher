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
use Domain\Server\Models\Server as ServerModel;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Server\Services\Providers\Vultr;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(closure: function () {
    $this->source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $region = ServerProviderRegion::factory()->create([
        'uuid'               => '1',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'uuid'               => '202',
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $this->source->client()->getImageId(),
        'name'               => 'Ubuntu 22.04 x64',
    ]);

    $this->server = ServerModel::factory()->vultr()->createForTest([
        'server_provider_id'        => $this->source->id,
        'server_provider_plan_id'   => $plan->id,
        'server_provider_region_id' => $region->id,
        'server_provider_image_id'  => $image->id,
    ]);
});

it('validates the credentials', function () {
    Http::fake([
        'vultr.com/*' => Http::sequence()
            ->push($this->fixture('vultr/account'), 200, [])
            ->pushStatus(403),
    ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($source->client()->valid())->toBeTrue();
    expect($source->client()->valid())->toBeFalse();
});

it('can create a new server', function () {
    Http::fake([
            'vultr.com/*' => Http::sequence()
                ->push($this->fixture('vultr/ssh-keys-get'), 200, [])
                ->push($this->fixture('vultr/create'), 200, [])
                ->push($this->fixture('vultr/server'), 200, []),
        ]);

    $actual = $this->source->client()->create($this->server);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe('576965');
    expect($actual->plan)->toBe('28');
    expect($actual->memory)->toBe(4096);
    expect($actual->cores)->toBe(2);
    expect($actual->disk)->toBe(60);
    expect($actual->region)->toBe('New Jersey');
    expect($actual->status)->toBe('active');
    expect($actual->remoteAddress)->toBe('123.123.123.123');
    expect($actual->image)->toBe('Ubuntu 22.04 x64');
});

it('fails to create a new server if the droplet limit is exceeded', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/create-exceeded'), 412, []),
        ]);

    $this->expectException(ServerLimitExceeded::class);

    $actual = $this->source->client()->create($this->server);

    Notification::assertSentTo($this->source->token->user, RemoteServerLimitReached::class);
});

it('throws if the exception is not about the dropplet limit', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/create'), 404, []),
        ]);

    $this->expectException(RequestException::class);

    $this->source->client()->create($this->server);
});

it('can request the given server by id', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/server'), 200, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->server(576965);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe('576965');
    expect($actual->plan)->toBe('28');
    expect($actual->memory)->toBe(4096);
    expect($actual->cores)->toBe(2);
    expect($actual->disk)->toBe(60);
    expect($actual->region)->toBe('New Jersey');
    expect($actual->status)->toBe('active');
    expect($actual->remoteAddress)->toBe('123.123.123.123');
    expect($actual->image)->toBe('Ubuntu 22.04 x64');
});

it('will throw a server error if server does not exist', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/server-not-found'), 200, []),
        ]);

    $this->expectException(ServerNotFound::class);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();
    $source->client()->server(1234);
});

it('can delete the given server by id', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($source->client()->delete(576965))->toBeTrue();
});

it('cant delete the given server by id if it does not exist', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $source->client()->delete(576965);
});

it('can start the given server by id', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($source->client()->start(576965))->toBeTrue();
});

it('can rename the server', function () {
    Http::fake([
        'vultr.com/*' => Http::response([], 204, []),
    ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($source->client()->rename(576965, 'new name'))->toBeTrue();
});

it('cant start the given server by id if it does not exist', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $source->client()->start(576965);
});

it('can stop the given server by id', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($source->client()->stop(576965))->toBeTrue();
});

it('cant stop the given server by id if it does not exist', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $source->client()->stop(576965);
});

it('can reboot the given server by id', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($source->client()->reboot(576965))->toBeTrue();
});

it('cant reboot the given server by id if it does not exist', function () {
    Http::fake([
            'vultr.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $source->client()->reboot(576965);
});

it('can request all the available plans', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/plans'), 200, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->plans();

    expect($actual)->toBeInstanceOf(PlanCollection::class);
    expect($actual->items)->toHaveCount(2);

    $plan = $actual->items[1];

    expect($plan->id)->toBe('1');
    expect($plan->disk)->toBe(20);
    expect($plan->memory)->toBe(512);
    expect($plan->cores)->toBe(1);
    expect($plan->regions)->toBeArray();
});

it('can request all the available regions', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/regions'), 200, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->regions();

    expect($actual)->toBeInstanceOf(RegionCollection::class);
    expect($actual->items)->toHaveCount(2);

    $region = $actual->items[1];

    expect($region->id)->toBe('1');
    expect($region->name)->toBe('New Jersey');
});

it('can request all the available images', function () {
    Http::fake([
        'vultr.com/*' => Http::response($this->fixture('vultr/images'), 200, []),
    ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->images();

    expect($actual)->toBeInstanceOf(ImageCollection::class);
    expect($actual->items)->toHaveCount(3);

    $image = $actual->items[270];

    expect($image->id)->toBe($source->client()->getImageId());
    expect($image->name)->toBe('Ubuntu 22.04 x64');
});

it('can create a secure shell key', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/ssh-keys-create'), 200, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->createSecureShellKey('name', 'publickey');

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe('541b4960f23bd');
});

it('can find a secure shell key', function () {
    Http::fake([
            'vultr.com/*' => Http::response($this->fixture('vultr/ssh-keys-get'), 200, []),
        ]);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->findSecureShellKey($source->provider_key_id);

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe('541b4960f23bd');
});

it('can delete a secure shell key', function () {
    Http::fake([
            'vultr.com/*' => Http::sequence()
                ->push([], 204, [])
                ->push([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->vultr()->ownedBy($this->token())->createForTest();

    expect($source->client()->deleteSecureShellKey($source->provider_key_id))->toBeTrue();

    $source->client()->deleteSecureShellKey($source->provider_key_id);
});

it('returns an instantiable rule for a server name', function () {
    $ruleClass = Vultr::nameValidator();

    $rule = new $ruleClass();

    expect($rule)->toBeInstanceOf(Rule::class);
});
