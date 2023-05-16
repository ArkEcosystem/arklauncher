<?php

declare(strict_types=1);

use App\SecureShell\Jobs\AddSecureShellKeyToServerProvider;
use App\Server\Notifications\ServerProviderAuthenticationFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyAdditionFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyLimitReached;
use App\Server\Notifications\ServerProviderSecureShellKeyUniqueness;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Exceptions\ServerProviderSecureShellKeyLimitReached as ExceptionsServerProviderSecureShellKeyLimitReached;
use Domain\Server\Exceptions\ServerProviderSecureShellKeyUniqueness as UniquenessException;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Enums\TokenOnboardingStatusEnum;
use Domain\Token\Events\ServerProviderUpdated;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('adds a secure shell key to the server provider', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/ssh-keys-create'), 200, []),
    ]);

    $serverProvider = ServerProvider::factory()->createForTest(['provider_key_id' => null]);

    $this->assertDatabaseMissing('server_providers', ['provider_key_id' => 512190]);

    (new AddSecureShellKeyToServerProvider($serverProvider, $serverProvider->token->user))->handle();

    $this->assertDatabaseHas('server_providers', ['provider_key_id' => 512190]);
});

it('sends a notification if the user reached the limit of secure shell keys when trying to create a new one', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/ssh-keys-create'), 403, []),
    ]);

    $serverProvider = ServerProvider::factory()->hetzner()->create();
    $user           = User::factory()->create();
    $creator        = User::factory()->create();

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $creator->id);

    $serverProvider->token->shareWith($user, 'collaborator', []);
    $serverProvider->token->shareWith($creator, 'collaborator', []);

    (new AddSecureShellKeyToServerProvider($serverProvider, $user))->failed(new ExceptionsServerProviderSecureShellKeyLimitReached());

    Notification::assertSentTo([$serverProvider->token->user, $serverProvider->user(), $user], ServerProviderSecureShellKeyLimitReached::class);

    expect($serverProvider->token->onboarding()->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeFalse();
});

it('sends a notification if unable to authenticate the server provider', function () {
    $serverProvider = ServerProvider::factory()->digitalocean()->create();
    $user           = User::factory()->create();

    (new AddSecureShellKeyToServerProvider($serverProvider, $user))->failed(new ServerProviderAuthenticationException());

    Notification::assertSentTo($serverProvider->token->user, ServerProviderAuthenticationFailed::class);
});

it('sends a generic notification if failed', function () {
    $serverProvider = ServerProvider::factory()->digitalocean()->create();
    $user           = User::factory()->create();

    (new AddSecureShellKeyToServerProvider($serverProvider, $user))->failed(new RuntimeException());

    Notification::assertSentTo($serverProvider->token->user, ServerProviderSecureShellKeyAdditionFailed::class);
});

it('sends a notification if a key with the same fingerprint already exists on the provider', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response($this->fixture('hetzner/ssh-keys-uniqueness-error'), 409, []),
    ]);

    $serverProvider = ServerProvider::factory()->hetzner()->create();
    $user           = User::factory()->create();

    (new AddSecureShellKeyToServerProvider($serverProvider, $user))->failed(new UniquenessException());

    Notification::assertSentTo($serverProvider->token->user, ServerProviderSecureShellKeyUniqueness::class);

    expect($serverProvider->token->onboarding()->completed(TokenOnboardingStatusEnum::SERVER_PROVIDERS))->toBeFalse();
});

it('dispatchs a server provider updated event when the provider key is changed', function () {
    Event::fake();

    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/ssh-keys-create'), 200, []),
    ]);

    $serverProvider = ServerProvider::factory()->createForTest(['provider_key_id' => null]);

    (new AddSecureShellKeyToServerProvider($serverProvider, $serverProvider->token->user))->handle();

    Event::assertDispatched(fn (ServerProviderUpdated $event) => $event->serverProvider->is($serverProvider));
});
