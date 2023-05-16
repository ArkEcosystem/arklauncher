<?php

declare(strict_types=1);

use App\Enums\NetworkTypeEnum;
use Domain\SecureShell\Models\SecureShellKey;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderImage;
use Domain\Server\Models\ServerProviderPlan;
use Domain\Server\Models\ServerProviderRegion;
use Domain\Token\Enums\TokenAttributeEnum;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Network;
use Domain\Token\Models\Token;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('a token belongs to an user', function () {
    expect($this->token()->user())->toBeInstanceOf(BelongsTo::class);
});

it('a token belongs to a coin', function () {
    expect($this->token()->coin())->toBeInstanceOf(BelongsTo::class);
});

it('a token has many networks', function () {
    expect($this->token()->networks())->toBeInstanceOf(HasMany::class);
});

it('a token has many servers', function () {
    expect($this->token()->servers())->toBeInstanceOf(HasManyThrough::class);
});

it('a token has secure shell keys', function () {
    expect($this->token()->secureShellKeys())->toBeInstanceOf(BelongsToMany::class);
});

it('a token has a mainnet', function () {
    expect(Token::factory()->withNetwork(1)->withDefaultNetworks()->createForTest()->network(NetworkTypeEnum::MAINNET))->toBeInstanceOf(Network::class);
});

it('a token has a devnet', function () {
    expect(Token::factory()->withNetwork(1)->withDefaultNetworks()->createForTest()->network(NetworkTypeEnum::DEVNET))->toBeInstanceOf(Network::class);
});

it('a token has a testnet', function () {
    expect(Token::factory()->withNetwork(1)->withDefaultNetworks()->createForTest()->network(NetworkTypeEnum::TESTNET))->toBeInstanceOf(Network::class);
});

it('a token has many server providers', function () {
    $user = $this->token();

    ServerProvider::factory()->ownedBy($user)->create();

    expect($user->serverProviders())->toBeInstanceOf(HasMany::class);
});

it('a token has many invitations', function () {
    expect($this->token()->invitations())->toBeInstanceOf(HasMany::class);
});

it('a token has a slug', function () {
    $token = $this->token();

    expect($token->slug)->not()->toBeNull();
});

it('a token has slug options', function () {
    $token       = $this->token();
    $slugOptions = $token->getSlugOptions();

    expect($slugOptions->slugField)->toBe('slug');
    expect($slugOptions->maximumLength)->toBe(250);
});

it('a token can set and get a private key', function () {
    $token = $this->token();

    $token->setKeyPair([
            'publicKey'  => 'public',
            'privateKey' => 'secret',
        ]);

    expect($token->getPrivateKey())->toBe('secret');
    expect($token->keypair['privateKey'])->not()->toBe('secret');
});

it('a token need to complete the onboarding page', function () {
    $token = $this->token();

    expect($token->onboarding()->isFinished())->toBeFalse();

    $token->update(['onboarded_at' => now()]);

    expect($token->onboarding()->isFinished())->toBeTrue();
});

it('can determine if a token can be edited', function () {
    $token = Token::factory()->withServers(0)->withNetwork(1)->createForTest();

    expect($token->canBeEdited())->toBeTrue();

    $token->networks()->each(fn ($network) => Server::factory()->ownedBy($network)->createForTest());

    expect($token->fresh()->canBeEdited())->toBeFalse();
});

it('can determine if user has access to a token', function () {
    $token       = $this->token();
    $user        = $this->user();
    $anotherUser = $this->user();

    $token->shareWith($anotherUser);

    expect($user->onToken($token))->toBeFalse();
    expect($anotherUser->onToken($token))->toBeTrue();

    $token->shareWith($user);

    expect($user->fresh()->onToken($token))->toBeTrue();
    expect($anotherUser->fresh()->onToken($token))->toBeTrue();

    $token->stopSharingWith($user);

    expect($user->fresh()->onToken($token))->toBeFalse();
    expect($anotherUser->fresh()->onToken($token))->toBeTrue();
});

it('can determine if token has servers', function () {
    $token = Token::factory()->withServers(0)->withNetwork(1)->createForTest();

    expect($token->hasServers())->toBeFalse();

    $server = Server::factory()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    expect($token->fresh()->hasServers())->toBeTrue();

    $server->delete();

    expect($token->fresh()->hasServers())->toBeFalse();
});

it('can determine if token has secure shell keys', function () {
    $token = Token::factory()->createForTest();

    expect($token->hasSecureShellKeys())->toBeFalse();

    $key = SecureShellKey::factory()->createForTest();

    $token->secureShellKeys()->sync($key->id);

    expect($token->fresh()->hasSecureShellKeys())->toBeTrue();

    $key->delete();

    expect($token->fresh()->hasSecureShellKeys())->toBeFalse();
});

it('can determine if token has server providers', function () {
    $token = Token::factory()->createForTest();

    expect($token->hasServerProviders())->toBeFalse();

    $serverProvider = ServerProvider::factory()->ownedBy($token)->create();

    expect($token->fresh()->hasServerProviders())->toBeTrue();

    $serverProvider->delete();

    expect($token->fresh()->hasServerProviders())->toBeFalse();
});

it('can determine if token needs server configuration', function () {
    $token = Token::factory()->withServers(1)->withNetwork(1)->createForTest();

    expect($token->needsServerConfiguration())->toBeFalse();

    $token = Token::factory()->withServers(0)->withNetwork(1)->createForTest();

    expect($token->needsServerConfiguration())->toBeTrue();

    $token->setMetaAttribute(TokenAttributeEnum::SERVER_CONFIG, [
        'server_provider_id' => 1,
    ]);

    expect($token->needsServerConfiguration())->toBeFalse();
});

it('can determine if token has available keys', function () {
    $token = $this->token();

    expect($token->availableKeys()->count())->toBe(0);

    SecureShellKey::factory()->ownedBy($token->user)->create();

    expect($token->fresh()->availableKeys()->count())->toBe(1);
});

it('can determine if token has authorized keys', function () {
    $token = $this->token();

    expect($token->hasAuthorizedKeys())->toBeFalse();

    SecureShellKey::factory()->ownedBy($token->user)->create();

    expect($token->fresh()->hasAuthorizedKeys())->toBeTrue();
});

it('can get logo attribute', function () {
    Storage::fake();

    $token = $this->token();

    $this->actingAs($token->user);

    expect($token->logo)->toBeEmpty();

    $token->addMedia(UploadedFile::fake()->image('logo.jpeg'))->toMediaCollection('logo');

    $token->flushCache();

    expect($token->fresh()->getLogoAttribute())->toContain('/storage');
    expect($token->fresh()->logo()['file_name'])->toBe('logo.jpeg');
});

it('can determine if token allows an action', function () {
    $token = $this->token();

    // Owner
    expect($token->allows($token->user, 'server:create'))->toBeTrue();

    // Collaborator with permission
    $token->shareWith($userWithPermission = $this->user(), 'collaborator', ['server:create']);

    expect($token->allows($userWithPermission, 'server:create'))->toBeTrue();

    // Collaborator with all permissions
    $token->shareWith($userWithAllPermissions = $this->user(), 'collaborator', ['*']);

    expect($token->allows($userWithAllPermissions, 'server:create'))->toBeTrue();

    // Collaborator without permission
    $token->shareWith($userWithoutPermission = $this->user(), 'collaborator');

    expect($token->allows($userWithoutPermission, 'server:create'))->toBeFalse();

    // Non Collaborator
    expect($token->allows($this->user(), 'server:create'))->toBeFalse();
});

it('can determine if images and plans and regions are properly indexed', function () {
    $token = Token::factory()->withServerProviders(1)->createForTest();

    $serverProvider = $token->serverProviders()->first();

    expect($serverProvider->plans()->count())->toBe(0);
    expect($serverProvider->regions()->count())->toBe(0);
    expect($serverProvider->images()->count())->toBe(0);

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

    $token->refresh();
    $serverProvider->refresh();

    expect($serverProvider->plans()->count())->toBeGreaterThan(0);
    expect($serverProvider->regions()->count())->toBeGreaterThan(0);
    expect($serverProvider->images()->count())->toBeGreaterThan(0);

    expect($token->hasAnyIndexedServerProvider())->toBeTrue();

    expect($token->serverProviders()->first()->allIndexed())->toBeTrue();
});

it('should returns false if no server provider is indexed', function () {
    $token = Token::factory()->withServerProviders(1)->createForTest();

    $serverProvider = $token->serverProviders()->first();

    expect($serverProvider->plans()->count())->toBe(0);
    expect($serverProvider->regions()->count())->toBe(0);
    expect($serverProvider->images()->count())->toBe(0);

    expect($token->hasAnyIndexedServerProvider())->toBeFalse();
});

it('should returns the first indexed server', function () {
    $token = Token::factory()->withServerProviders(1)->createForTest();

    $serverProvider = $token->serverProviders()->first();

    $plan = ServerProviderPlan::factory()->create([
            'uuid'               => 'ccx21',
        ]);

    $region = ServerProviderRegion::factory()->create([
            'uuid'               => 'fsn1',
        ]);

    $image = ServerProviderImage::factory()->create([
            'uuid'               => $serverProvider->client()->getImageId(),
        ]);

    $serverProvider->plans()->syncWithoutDetaching($plan);
    $serverProvider->regions()->syncWithoutDetaching($region);
    $serverProvider->images()->syncWithoutDetaching($image);

    ServerProvider::factory()->ownedBy($token)->create();

    expect($token->getFirstIndexedServerProvider())->toBeInstanceOf(ServerProvider::class);
});

it('should returns null if no indexed server provider', function () {
    $token = $this->token();

    ServerProvider::factory()->ownedBy($token)->create();

    expect($token->getFirstIndexedServerProvider())->toBeNull();
});

it('should not have a provisioned genesis server if no servers', function () {
    $token = $this->token();

    $token->servers()->delete();

    expect($token->hasProvisionedGenesisServer())->toBeFalse();
});

it('can determine if token has a provisioned genesis server', function () {
    $token = Token::factory()->withNetwork(1)->withServers(1)->createForTest();

    expect($token->hasProvisionedGenesisServer())->toBeFalse();

    $server = Server::factory()->genesis()->ownedBy($token->network(NetworkTypeEnum::MAINNET))->createForTest();

    expect($server->isProvisioned())->toBeFalse();
    expect($token->fresh()->hasProvisionedGenesisServer())->toBeFalse();

    $server->touch('provisioned_at');
    $server->setStatus('online');
    $token->flushCache();

    expect($server->isProvisioned())->toBeTrue();
    expect($token->fresh()->hasProvisionedGenesisServer())->toBeTrue();
});

it('can get the token lowercased', function () {
    $token = $this->token();

    expect(Str::lower($token->config['token']))->toBe($token->normalized_token);
});

it('has a fallbackIdentifier', function () {
    $token = $this->token();

    expect($token->fallbackIdentifier())->toBe($token->name);
});

it('makes user that created the token an owner when token is created', function () {
    $token = Token::factory()->create();

    expect($token->collaborators()->count())->toBe(1);

    expect($token->collaborators()->whereRole('owner')->first()->is($token->user))->toBeTrue();
});

it('marks the token status as pending when token is created', function () {
    $token = $this->token();

    expect($token->status)->toBe(TokenStatusEnum::PENDING);
});
