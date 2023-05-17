<?php

declare(strict_types=1);

use App\Server\Jobs\IndexServerProviderRegions;
use App\Server\Notifications\IndexServerProviderRegionsFailed;
use Domain\Server\Enums\ServerAttributeEnum;
use Domain\Server\Models\ServerProvider;
use Domain\Server\Models\ServerProviderRegion;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

it('indexes available regions for a server provider', function () {
    Http::fake([
        'digitalocean.com/*' => Http::response($this->fixture('digitalocean/regions'), 200, []),
    ]);

    $serverProvider = ServerProvider::factory()->createForTest();

    expect(ServerProviderRegion::count())->toBe(0);

    (new IndexServerProviderRegions($serverProvider))->handle();

    expect(ServerProviderRegion::count())->toBeGreaterThan(0);
});

it('sends a notification to the token owner and the creator of the server provider if it fails to index available images for a server provider', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    $serverProvider->token->shareWith($user, 'collaborator', []);

    expect(ServerProviderRegion::count())->toBe(0);

    (new IndexServerProviderRegions($serverProvider))->failed();

    expect(ServerProviderRegion::count())->toBe(0);

    Notification::assertSentTo([$serverProvider->token->user, $user], IndexServerProviderRegionsFailed::class);
    Notification::assertTimesSent(2, IndexServerProviderRegionsFailed::class);
});

it('prevents duplicate notifications', function () {
    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $serverProvider->token->user->id);

    expect(ServerProviderRegion::count())->toBe(0);

    (new IndexServerProviderRegions($serverProvider))->failed();

    expect(ServerProviderRegion::count())->toBe(0);

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderRegionsFailed::class);
    Notification::assertTimesSent(1, IndexServerProviderRegionsFailed::class);
});

it('does not notify users that are not members of the team', function () {
    $user = User::factory()->create();

    $serverProvider = ServerProvider::factory()->hetzner()->createForTest();
    $serverProvider->setMetaAttribute(ServerAttributeEnum::CREATOR, $user->id);

    expect(ServerProviderRegion::count())->toBe(0);

    (new IndexServerProviderRegions($serverProvider))->failed();

    expect(ServerProviderRegion::count())->toBe(0);

    Notification::assertSentTo($serverProvider->token->user, IndexServerProviderRegionsFailed::class);
    Notification::assertTimesSent(1, IndexServerProviderRegionsFailed::class);
});

it('has the right tags', function () {
    $serverProvider = ServerProvider::factory()->createForTest();

    $job = new IndexServerProviderRegions($serverProvider);

    expect($job->tags())->toBe(['regions', 'serverProvider:'.$serverProvider->id]);
});
