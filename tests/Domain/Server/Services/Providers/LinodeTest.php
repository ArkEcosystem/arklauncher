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
use Domain\Server\Services\Providers\Linode;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $region = ServerProviderRegion::factory()->create([
        'uuid'               => 'us-southeast',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'uuid'               => 'g6-standard-1',
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $this->source->client()->getImageId(),
    ]);

    $this->server = ServerModel::factory()->linode()->createForTest([
        'server_provider_id'        => $this->source->id,
        'server_provider_plan_id'   => $plan->id,
        'server_provider_region_id' => $region->id,
        'server_provider_image_id'  => $image->id,
    ]);
});

it('validates the credentials', function () {
    Http::fake([
            'linode.com/*' => Http::sequence()
                ->push($this->fixture('linode/account'), 200, [])
                ->pushStatus(403),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($source->client()->valid())->toBeTrue();
    expect($source->client()->valid())->toBeFalse();
});

it('can create a new server', function () {
    Http::fake([
            'linode.com/*' => Http::sequence()
                ->push($this->fixture('linode/ssh-keys-get'), 200, [])
                ->push($this->fixture('linode/create'), 200, [])
                ->push($this->fixture('linode/server'), 200, []),
        ]);

    $actual = $this->source->client()->create($this->server);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe(1234);
    expect($actual->plan)->toBe('g6-standard-1');
    expect($actual->memory)->toBe(4096);
    expect($actual->cores)->toBe(2);
    expect($actual->disk)->toBe(81920);
    expect($actual->region)->toBe('us-southeast');
    expect($actual->status)->toBe('running');
    expect($actual->remoteAddress)->toBe('104.131.186.241');
    expect($actual->image)->toBe('linode/ubuntu22.04');
});

it('fails to create a new server if the servers limit is exceeded', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/create-exceeded'), 422, []),
        ]);

    $this->expectException(ServerLimitExceeded::class);

    $this->source->client()->create($this->server);

    Notification::assertSentTo($this->source->token->user, RemoteServerLimitReached::class);
});

it('throws if the exception is not about the server limit', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/create'), 404, []),
        ]);

    $this->expectException(RequestException::class);

    $this->source->client()->create($this->server);
});

it('can request the given server by id', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/server'), 200, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->server(1234);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe(1234);
    expect($actual->plan)->toBe('g6-standard-1');
    expect($actual->memory)->toBe(4096);
    expect($actual->cores)->toBe(2);
    expect($actual->disk)->toBe(81920);
    expect($actual->region)->toBe('us-southeast');
    expect($actual->status)->toBe('running');
    expect($actual->remoteAddress)->toBe('104.131.186.241');
    expect($actual->image)->toBe('linode/ubuntu22.04');
});

it('will throw a server error if server does not exist', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/server-not-found'), 404, []),
        ]);

    $this->expectException(ServerNotFound::class);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();
    $source->client()->server(1234);
});

it('will throw a generic error if server check fails', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();
    $source->client()->server(1234);
});

it('can delete the given server by id', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($source->client()->delete(1234))->toBetrue();
});

it('cant delete the given server by id if it does not exist', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $source->client()->delete(1234);
});

it('can rename the server', function () {
    Http::fake([
        'linode.com/*' => Http::response($this->fixture('linode/update'), 200, []),
    ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($source->client()->rename(1234, 'new name'))->toBeTrue();
});

it('can start the given server by id', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($source->client()->start(1234))->toBeTrue();
});

it('cant start the given server by id if it does not exist', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $source->client()->start(1234);
});

it('can stop the given server by id', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($source->client()->stop(1234))->toBeTrue();
});

it('cant stop the given server by id if it does not exist', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $source->client()->stop(1234);
});

it('can reboot the given server by id', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 204, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($source->client()->reboot(1234))->toBeTrue();
});

it('cant reboot the given server by id if it does not exist', function () {
    Http::fake([
            'linode.com/*' => Http::response([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $source->client()->reboot(1234);
});

it('can request all the available plans', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/plans'), 200, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->plans();

    expect($actual)->toBeInstanceOf(PlanCollection::class);
    expect($actual->items)->toHaveCount(25);

    $plan = $actual->items[0];

    expect($plan->id)->toBe('g6-nanode-1');
    expect($plan->disk)->toBe(25600);
    expect($plan->memory)->toBe(1024);
    expect($plan->cores)->toBe(1);
    expect($plan->regions)->toBeArray();
});

it('can request all the available regions', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/regions'), 200, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->regions();

    expect($actual)->toBeInstanceOf(RegionCollection::class);
    expect($actual->items)->toHaveCount(11);

    $region = $actual->items[0];

    expect($region->id)->toBe('ap-west');
    expect($region->name)->toBe('in');
});

it('can request all the available images', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/images'), 200, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->images();

    expect($actual)->toBeInstanceOf(ImageCollection::class);
    expect($actual->items)->toHaveCount(1);

    $image = $actual->items->first();

    expect($image->id)->toBe($source->client()->getImageId());
    expect($image->name)->toBe('Ubuntu 22.04 LTS');
});

it('can create a secure shell key', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/ssh-keys-create'), 200, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->createSecureShellKey('name', 'publickey');

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe(1234);
});

it('can find a secure shell key', function () {
    Http::fake([
            'linode.com/*' => Http::response($this->fixture('linode/ssh-keys-get'), 200, []),
        ]);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->findSecureShellKey($source->provider_key_id);

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe(1234);
});

it('can delete a secure shell key', function () {
    Http::fake([
            'linode.com/*' => Http::sequence()
                ->push([], 204, [])
                ->push([], 404, []),
        ]);

    $this->expectException(RequestException::class);

    $source = ServerProvider::factory()->linode()->ownedBy($this->token())->createForTest();

    expect($source->client()->deleteSecureShellKey($source->provider_key_id))->toBetrue();

    $source->client()->deleteSecureShellKey($source->provider_key_id);
});

it('returns an instanciable rule for a server name', function () {
    $ruleClass = Linode::nameValidator();

    $rule = new $ruleClass();

    expect($rule)->toBeInstanceOf(Rule::class);
});
