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
use Domain\Server\Services\Providers\Hetzner;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(closure: function () {
    $this->source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $region = ServerProviderRegion::factory()->create([
        'uuid'               => 'fsn1',
    ]);

    $plan = ServerProviderPlan::factory()->create([
        'uuid'               => 'ccx21',
    ]);

    $image = ServerProviderImage::factory()->create([
        'uuid'               => $this->source->client()->getImageId(),
    ]);

    $this->server = ServerModel::factory()->hetzner()->createForTest([
        'server_provider_id'        => $this->source->id,
        'server_provider_plan_id'   => $plan->id,
        'server_provider_region_id' => $region->id,
        'server_provider_image_id'  => $image->id,
    ]);
});

it('validates the credentials', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::sequence()
            ->push($this->fixture('hetzner/regions'), 200, [])
            ->pushStatus(403),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($source->client()->valid())->toBeTrue();
    expect($source->client()->valid())->toBeFalse();
});

it('can create a new server', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::sequence()
            ->push($this->fixture('hetzner/ssh-keys-get'), 200, [])
            ->push($this->fixture('hetzner/create'), 200, [])
            ->push($this->fixture('hetzner/server'), 200, []),
    ]);

    $actual = $this->source->client()->create($this->server);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe(42);
    expect($actual->plan)->toBe(12);
    expect($actual->memory)->toBe(16384);
    expect($actual->cores)->toBe(4);
    expect($actual->disk)->toBe(160);
    expect($actual->region)->toBe('fsn1-dc14');
    expect($actual->status)->toBe('running');
    expect($actual->remoteAddress)->toBe('1.2.3.4');
    expect($actual->image)->toBe($this->source->client()->getImageId());
});

it('fails to create a new server if the servers limit is exceeded', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/create-exceeded'), 422, []),
    ]);

    $this->expectException(ServerLimitExceeded::class);

    $this->source->client()->create($this->server);

    Notification::assertSentTo($this->source->token->user, RemoteServerLimitReached::class);
});

it('throws if the exception is not about the server limit', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/create'), 404, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $this->source->client()->create($this->server);
});

it('can request the given server by id', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/server'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->server(42);

    expect($actual)->toBeInstanceOf(Server::class);
    expect($actual->id)->toBe(42);
    expect($actual->plan)->toBe(12);
    expect($actual->memory)->toBe(16384);
    expect($actual->cores)->toBe(4);
    expect($actual->disk)->toBe(160);
    expect($actual->region)->toBe('fsn1-dc14');
    expect($actual->status)->toBe('running');
    expect($actual->remoteAddress)->toBe('1.2.3.4');
    expect($actual->image)->toBe($source->client()->getImageId());
});

it('will throw a server error if server does not exist', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/server-not-found'), 404, []),
    ]);

    $this->expectException(ServerNotFound::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();
    $source->client()->server(1234);
});

it('will throw a generic error if server check fails', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response([], 404, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();
    $source->client()->server(1234);
});

it('can delete the given server by id', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/delete'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($source->client()->delete(42))->toBeTrue();
});

it('cant delete the given server by id if action errors', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/errored-general'), 200, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->delete(42);
});

it('gracefully handles server not found exception', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/server-not-found'), 404, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->delete(42);

    Http::assertSent(fn ($request) => $request->method() === 'DELETE' && $request->url() === 'https://api.hetzner.cloud/v1/servers/42');
});

it('can start the given server by id', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/start'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($source->client()->start(42))->toBeTrue();
});

it('can rename the server', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/rename'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($source->client()->rename(42, 'new name'))->toBeTrue();
});

it('cant start the given server by id if it action errored', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/errored-general'), 200, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->start(42);
});

it('cant start the given server by id if it does not exist', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response([], 404, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->start(42);
});

it('can stop the given server by id', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/stop'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($source->client()->stop(42))->toBeTrue();
});

it('cant stop the given server by id if action errors', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/errored-general'), 200, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->stop(42);
});

it('cant stop the given server by id if it does not exist', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response([], 404, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->stop(42);
});

it('can reboot the given server by id', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/reboot'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($source->client()->reboot(42))->toBeTrue();
});

it('cant reboot the given server by id if action errors', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/errored-general'), 200, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->reboot(42);
});

it('cant reboot the given server by id if it does not exist', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response([], 404, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $source->client()->reboot(42);
});

it('can request all the available plans', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/plans'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->plans();

    expect($actual)->toBeInstanceOf(PlanCollection::class);
    expect($actual->items)->toHaveCount(1);

    $region = $actual->items->first();

    expect($region->id)->toBe('cx11');
    expect($region->disk)->toBe(25);
    expect($region->memory)->toBe(1024);
    expect($region->cores)->toBe(1);
    expect($region->regions)->toBeArray();
});

it('can request all the available regions', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/regions'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->regions();

    expect($actual)->toBeInstanceOf(RegionCollection::class);
    expect($actual->items)->toHaveCount(1);

    $region = $actual->items->first();

    expect($region->id)->toBe('fsn1');
    expect($region->name)->toBe('Falkenstein DC Park 1');
});

it('can request all the available images', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/images'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->images();

    expect($actual)->toBeInstanceOf(ImageCollection::class);
    expect($actual->items)->toHaveCount(1);

    $image = $actual->items->first();

    expect($image->id)->toBe($source->client()->getImageId());
    expect($image->name)->toBe('Ubuntu 22.04 Standard 64 bit');
});

it('should ignore images without a name', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/images-without-name'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->images();

    expect($actual)->toBeInstanceOf(ImageCollection::class);
    expect($actual->items)->toHaveCount(1);

    $image = $actual->items->first();

    expect($image->id)->toBe($source->client()->getImageId());
    expect($image->name)->toBe('Ubuntu 22.04 Standard 64 bit');
});

it('can create a secure shell key', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/ssh-keys-create'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->createSecureShellKey('name', 'publickey');

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe(2323);
});

it('can find a secure shell key', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/ssh-keys-get'), 200, []),
    ]);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    $actual = $source->client()->findSecureShellKey($source->provider_key_id);

    expect($actual)->toBeInstanceOf(SecureShellKey::class);
    expect($actual->id)->toBe(2323);
});

it('can delete a secure shell key', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::sequence()
            ->push([], 204, [])
            ->push([], 404, []),
    ]);

    $this->expectException(ServerProviderError::class);

    $source = ServerProvider::factory()->hetzner()->ownedBy($this->token())->createForTest();

    expect($source->client()->deleteSecureShellKey($source->provider_key_id))->toBeTrue();

    $source->client()->deleteSecureShellKey($source->provider_key_id);
});

it('returns an instanciable rule for a server name', function () {
    $ruleClass = Hetzner::nameValidator();

    $rule = new $ruleClass();

    expect($rule)->toBeInstanceOf(Rule::class);
});
