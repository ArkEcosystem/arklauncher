<?php

declare(strict_types=1);

use App\SecureShell\Jobs\RemoveSecureShellKeyFromServerProvider;
use App\Server\Notifications\ServerProviderAuthenticationFailed;
use App\Server\Notifications\ServerProviderSecureShellKeyRemovalFailed;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Exceptions\ServerProviderAuthenticationException;
use Domain\Server\Models\ServerProvider;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('removes a secure shell key from digitalocean', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response([], 204, []),
    ]);

    $serverProvider = ServerProvider::factory()->digitalocean()->createForTest();

    (new RemoveSecureShellKeyFromServerProvider($serverProvider))->handle();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.digitalocean.com/v2/account/keys/512190');
});

it('deletes the server provider in failed method', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response(fn () => throw $this->createRequestException(401, '')),
    ]);

    $serverProvider = ServerProvider::factory()->digitalocean()->createForTest();

    $job = new RemoveSecureShellKeyFromServerProvider($serverProvider);

    $job->failed($this->createRequestException(401, ''));

    expect($serverProvider->fresh())->toBeNull();
});

it('removes a secure shell key from linode', function () {
    Http::fake([
        'linode.com/*' => Http::response([], 200, []),
    ]);

    $serverProvider = ServerProvider::factory()->linode()->createForTest();

    (new RemoveSecureShellKeyFromServerProvider($serverProvider))->handle();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.linode.com/v4/profile/sshkeys/1234');
});

it('removes a secure shell key from vultr', function () {
    Http::fake([
        'vultr.com/*' => Http::response([], 200, []),
    ]);

    $serverProvider = ServerProvider::factory()->vultr()->createForTest();

    (new RemoveSecureShellKeyFromServerProvider($serverProvider))->handle();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.vultr.com/v1/sshkey/destroy');
});

it('removes a secure shell key from hetzner', function () {
    Http::fake([
        'hetzner.cloud/*' => Http::response([], 200, []),
    ]);

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();

    (new RemoveSecureShellKeyFromServerProvider($serverProvider))->handle();

    Http::assertSent(fn ($request) => $request->url() === 'https://api.hetzner.cloud/v1/ssh_keys/2323');
});

it('sends a notification if it fails to remove a secure shell key', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->digitalocean()->create();

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $serverProvider->token->shareWith($user, 'collaborator', []);

    (new RemoveSecureShellKeyFromServerProvider($serverProvider))->failed(new RuntimeException('Something went wrong.'));

    Notification::assertSentTo([$serverProvider->token->user, $user], ServerProviderSecureShellKeyRemovalFailed::class);
});

it('sends a notification if it cannot authenticate the server provider', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->digitalocean()->create();

    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $serverProvider->token->shareWith($user, 'collaborator', []);

    (new RemoveSecureShellKeyFromServerProvider($serverProvider))->failed(new ServerProviderAuthenticationException());

    Notification::assertSentTo([$serverProvider->token->user, $user], ServerProviderAuthenticationFailed::class);
});
