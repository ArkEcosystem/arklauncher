<?php

declare(strict_types=1);

use Carbon\Carbon;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\SecureShell\Scripts\ProvisionUser;
use Domain\Server\Enums\PresetTypeEnum;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerTask;
use Domain\Token\Events\ServerDeleted;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Event;
use Znck\Eloquent\Relations\BelongsToThrough;

it('has a creator', function () {
    $user = User::factory()->create();

    $server = Server::factory()->create();

    $server->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    expect($server->creator()->is($user))->toBeTrue();
});

it('a server belongs to a token', function () {
    $server = Server::factory()->createForTest();

    expect($server->token())->toBeInstanceOf(BelongsToThrough::class);
});

it('a server belongs to a network', function () {
    $server = Server::factory()->createForTest();

    expect($server->network())->toBeInstanceOf(BelongsTo::class);
});

it('a server belongs to a server provider', function () {
    $server = Server::factory()->createForTest();

    expect($server->serverProvider())->toBeInstanceOf(BelongsTo::class);
});

it('a server has a server provider plan', function () {
    $server = Server::factory()->createForTest();

    expect($server->plan())->toBeInstanceOf(HasOne::class);
});

it('a server has a server provider region', function () {
    $server = Server::factory()->createForTest();

    expect($server->region())->toBeInstanceOf(HasOne::class);
});

it('a server has a server provider image', function () {
    $server = Server::factory()->createForTest();

    expect($server->image())->toBeInstanceOf(HasOne::class);
});

it('a server has many tasks', function () {
    $server = Server::factory()->createForTest();

    expect($server->tasks())->toBeInstanceOf(HasMany::class);
});

it('can determine if the server is a genesis', function () {
    $server = new Server();

    expect($server->isGenesis())->toBeFalse();

    $server->preset = PresetTypeEnum::GENESIS;

    expect($server->isGenesis())->toBeTrue();
});

it('can determine if the server is a seed', function () {
    $server = new Server();

    expect($server->isSeed())->toBeFalse();

    $server->preset = PresetTypeEnum::SEED;

    expect($server->isSeed())->toBeTrue();
});

it('can determine if the server is a relay', function () {
    $server = new Server();

    expect($server->isRelay())->toBeFalse();

    $server->preset = PresetTypeEnum::RELAY;

    expect($server->isRelay())->toBeTrue();
});

it('can determine if the server is a forger', function () {
    $server = new Server();

    expect($server->isForger())->toBeFalse();

    $server->preset = PresetTypeEnum::FORGER;

    expect($server->isForger())->toBeTrue();
});

it('can determine if the server is an explorer', function () {
    $server = new Server();

    expect($server->isExplorer())->toBeFalse();

    $server->preset = PresetTypeEnum::EXPLORER;

    expect($server->isExplorer())->toBeTrue();
});

it('can determine if a token has the authorized keys', function () {
    $server = Server::factory()->createForTest();

    expect($server->hasAuthorizedKeys())->toBeFalse();

    $key = SecureShellKey::factory()->ownedBy($server->token->user)->create();

    expect($server->fresh()->hasAuthorizedKeys())->toBeFalse();

    $server->token->secureShellKeys()->sync([$key->id]);

    expect($server->fresh()->hasAuthorizedKeys())->toBeTrue();
});

it('can get the authorized keys based on currently enabled keys', function () {
    // Users
    $user1   = $this->user();
    $user2   = $this->user();

    // Servers
    $server = Server::factory()->explorer()->createForTest();

    // Only the owner currently holds an SSH key
    expect($server->getAuthorizedKeys())->toHaveCount(1);

    // Create various keys
    $key1     = SecureShellKey::factory()->ownedBy($user1)->create();
    $key2     = SecureShellKey::factory()->ownedBy($user2)->create();

    // Now the owner and collaborator hold an SSH key
    $server->token->secureShellKeys()->sync([$key1->id]);
    expect($server->fresh()->getAuthorizedKeys())->toHaveCount(2);

    $server->token->secureShellKeys()->sync([$key1->id, $key2->id]);
    expect($server->fresh()->getAuthorizedKeys())->toHaveCount(3);

    $server->token->secureShellKeys()->sync([$key1->id]);
    expect($server->fresh()->getAuthorizedKeys())->toHaveCount(2);
});

it('can add a task', function () {
    $server = Server::factory()->createForTest();

    $task = $server->addTask(new ProvisionUser($server));

    expect($task)->toBeInstanceOf(ServerTask::class);
});

it('returns an existing task if it already exists for the given type', function () {
    $server = Server::factory()->createForTest();

    $task = $server->addTask(new ProvisionUser($server));

    expect($server->addTask(new ProvisionUser($server))->id)->toBe($task->id);
});

it('can determine if a server is ready for provisioning', function () {
    $server = Server::factory()->createForTest(['ip_address' => null]);

    expect($server->isReadyForProvisioning())->toBeFalse();

    $server->update(['ip_address' => '127.0.0.1']);

    expect($server->isReadyForProvisioning())->toBeTrue();

    $server->update(['ip_address' => null]);
    $server->setStatus('provisioning');

    expect($server->isReadyForProvisioning())->toBeFalse();
});

it('can determine if a server is provisioned', function () {
    $server = Server::factory()->createForTest();

    expect($server->isProvisioned())->toBeFalse();

    $server->touch('provisioned_at');
    $server->setStatus('online');

    expect($server->isProvisioned())->toBeTrue();
});

it('can determine if a server is failed', function () {
    $server = Server::factory()->createForTest();

    expect($server->isProvisioned())->toBeFalse();

    $server->setStatus('failed');

    expect($server->isFailed())->toBeTrue();
});

it('can determine if a server is provisioning', function () {
    $server = Server::factory()->createForTest();

    $server->setStatus('updating_system');

    expect($server->isProvisioning())->toBeTrue();

    $server->setStatus('online');

    expect($server->isProvisioning())->toBeFalse();
});

it('can determine if a server is older than the given value', function () {
    expect(Server::factory()->createForTest()->olderThan(10))->toBeFalse();
    expect(Server::factory()->createForTest(['created_at' => Carbon::now()->subMinutes(10)])->olderThan(5))->toBeTrue();
});

it('can get the path to show', function () {
    expect(Server::factory()->createForTest()->pathShow())->toBeString();
});

it('can get the path to start', function () {
    expect(Server::factory()->createForTest()->pathStart())->toBeString();
});

it('can get the path to stop', function () {
    expect(Server::factory()->createForTest()->pathStop())->toBeString();
});

it('can get the path to reboot', function () {
    expect(Server::factory()->createForTest()->pathReboot())->toBeString();
});

it('generates user and sudo password on creation', function () {
    $server = Server::factory()->create([
        'user_password' => null,
        'sudo_password' => null,
    ])->fresh();

    expect($server->user_password)->not->toBeNull();
    expect($server->sudo_password)->not->toBeNull();
});

it('encrypt and decrypt private attributes', function () {
    $privateAttributes = [
        'sudo_password'       => 'something',
        'user_password'       => 's0m21thins!',
        'delegate_passphrase' => 'L0123456789P@assphare',
        'delegate_password'   => '1',
    ];

    $server = Server::factory()->createForTest();

    $server->update($privateAttributes);

    $this->assertDatabaseMissing('servers', $privateAttributes);

    foreach ($privateAttributes as $attribute => $value) {
        expect($server->{$attribute})->toBe($value);
    }
});

it('deletes related tasks when server is deleted', function () {
    $server = Server::factory()->create();

    ServerTask::factory()->create([
        'server_id' => $server->id,
    ]);

    expect($server->tasks)->toHaveCount(1);

    $server->delete();

    expect(ServerTask::count())->toBe(0);
});

it('dispatches a deleted event when deleted', function () {
    Event::fake([ServerDeleted::class]);

    $server = Server::factory()->create();

    $server->delete();

    Event::assertDispatched(ServerDeleted::class, fn ($event) => $event->server->is($server));
});

it('can determine server\'s online/offline status', function () {
    $server = Server::factory()->create();

    $server->markAsOffline();

    expect($server->fresh()->isOnline())->toBeFalse();
    expect($server->fresh()->isOffline())->toBeTrue();

    $server->markAsOnline();

    expect($server->fresh()->isOnline())->toBeTrue();
    expect($server->fresh()->isOffline())->toBeFalse();
});
